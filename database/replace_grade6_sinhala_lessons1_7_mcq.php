<?php
declare(strict_types=1);

ini_set('session.save_path', __DIR__ . '/../includes/runtime');
require_once __DIR__ . '/../includes/db.php';

$dataFiles = [
    'grade6-sinhala-lessons1-7.json',
    'grade6-sinhala-lessons8-10-13-14.json',
    'grade6-sinhala-lessons15-20.json',
];
$sets = [];
foreach ($dataFiles as $dataFile) {
    $path = __DIR__ . '/data/' . $dataFile;
    $sets = array_replace($sets, json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR));
}
$orders = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 13, 14, 15, 16, 17, 18, 19, 20];
if (array_map('intval', array_keys($sets)) !== $orders) {
    throw new RuntimeException('Expected eighteen exact Sinhala lesson sets.');
}

$db = db();
$db->begin_transaction();
try {
    $base = $db->query("SELECT g.id AS grade_id,s.id AS subject_id FROM grades g JOIN subjects s ON s.grade_id=g.id WHERE g.grade_number=6 AND s.name_en='Sinhala' LIMIT 1")->fetch_assoc();
    if (!$base) {
        throw new RuntimeException('Grade 6 Sinhala subject missing.');
    }
    $gradeId = (int) $base['grade_id'];
    $subjectId = (int) $base['subject_id'];

    $findLesson = $db->prepare("SELECT id,unit_id FROM lessons WHERE grade_id=? AND subject_id=? AND medium IN ('Sinhala','All') AND display_order=? AND status='active' ORDER BY (medium='Sinhala') DESC,id LIMIT 1");
    $findUnit = $db->prepare('SELECT id FROM units WHERE grade_id=? AND subject_id=? AND unit_number=? ORDER BY id LIMIT 1');
    $addUnit = $db->prepare("INSERT IGNORE INTO units(grade_id,subject_id,unit_number,display_order,status) VALUES(?,?,?,?,'active')");
    $fallbackUnit = $db->prepare('SELECT id FROM units WHERE grade_id=? AND subject_id=? ORDER BY unit_number DESC,id DESC LIMIT 1');
    $addLesson = $db->prepare("INSERT INTO lessons(grade_id,medium,subject_id,unit_id,display_order,status) VALUES(?,'Sinhala',?,?,?,'active')");
    $findQuiz = $db->prepare("SELECT id FROM quizzes WHERE lesson_id=? AND status='active' ORDER BY id LIMIT 1");
    $addQuiz = $db->prepare("INSERT INTO quizzes(grade_id,subject_id,unit_id,lesson_id,title_si,timer_minutes,pass_mark,status) VALUES(?,?,?,?,?,30,50,'active')");

    foreach ($orders as $order) {
        $findLesson->bind_param('iii', $gradeId, $subjectId, $order);
        $findLesson->execute();
        $lessonRow = $findLesson->get_result()->fetch_assoc();
        if (!$lessonRow) {
            $title = (string) $sets[(string) $order]['title'];
            $findUnit->bind_param('iii', $gradeId, $subjectId, $order);
            $findUnit->execute();
            $unitRow = $findUnit->get_result()->fetch_assoc();
            if (!$unitRow) {
                $addUnit->bind_param('iiii', $gradeId, $subjectId, $order, $order);
                $addUnit->execute();
                $findUnit->execute();
                $unitRow = $findUnit->get_result()->fetch_assoc();
            }
            if (!$unitRow) {
                $fallbackUnit->bind_param('ii', $gradeId, $subjectId);
                $fallbackUnit->execute();
                $unitRow = $fallbackUnit->get_result()->fetch_assoc();
            }
            if (!$unitRow) {
                throw new RuntimeException("No usable unit for lesson $order.");
            }
            $unitId = (int) $unitRow['id'];
            $addLesson->bind_param('iiii', $gradeId, $subjectId, $unitId, $order);
            $addLesson->execute();
            $lessonRow = ['id' => $db->insert_id, 'unit_id' => $unitId];
        }

        $lessonId = (int) $lessonRow['id'];
        $unitId = (int) $lessonRow['unit_id'];
        $findQuiz->bind_param('i', $lessonId);
        $findQuiz->execute();
        if (!$findQuiz->get_result()->fetch_assoc()) {
            $quizTitle = $sets[(string) $order]['title'] . ' – බහුවරණ ප්‍රශ්න 30ක්';
            $addQuiz->bind_param('iiiis', $gradeId, $subjectId, $unitId, $lessonId, $quizTitle);
            $addQuiz->execute();
        }
    }

    $lesson = $db->prepare("SELECT id FROM lessons WHERE grade_id=? AND subject_id=? AND medium IN ('Sinhala','All') AND display_order=? AND status='active' ORDER BY (medium='Sinhala') DESC,id LIMIT 1");
    $quiz = $db->prepare("SELECT id FROM quizzes WHERE lesson_id=? AND status='active' ORDER BY id LIMIT 1");
    $ids = $db->prepare("SELECT id FROM quiz_questions WHERE quiz_id=? AND activity_type='challenge'");
    $deleteAnswers = $db->prepare('DELETE FROM quiz_answers WHERE question_id=?');
    $deleteQuestions = $db->prepare("DELETE FROM quiz_questions WHERE quiz_id=? AND activity_type='challenge'");
    $addQuestion = $db->prepare("INSERT INTO quiz_questions(quiz_id,activity_type,question_si,option_a_si,option_b_si,option_c_si,option_d_si,correct_option,explanation_si,display_order,status) VALUES(?,'challenge',?,?,?,?,?,?,?,?,'active')");
    $updateQuiz = $db->prepare('UPDATE quizzes SET title_si=?,timer_minutes=30,pass_mark=50 WHERE id=?');

    foreach ($orders as $order) {
        $questions = $sets[(string) $order]['questions'] ?? [];
        if (count($questions) !== 30) {
            throw new RuntimeException("Lesson $order does not contain 30 questions.");
        }
        $lesson->bind_param('iii', $gradeId, $subjectId, $order);
        $lesson->execute();
        $lessonRow = $lesson->get_result()->fetch_assoc();
        if (!$lessonRow) {
            throw new RuntimeException("Sinhala lesson $order missing.");
        }
        $lessonId = (int) $lessonRow['id'];
        $quiz->bind_param('i', $lessonId);
        $quiz->execute();
        $quizRow = $quiz->get_result()->fetch_assoc();
        if (!$quizRow) {
            throw new RuntimeException("Quiz for lesson $order missing.");
        }
        $quizId = (int) $quizRow['id'];

        $ids->bind_param('i', $quizId);
        $ids->execute();
        $oldQuestions = $ids->get_result();
        while ($old = $oldQuestions->fetch_assoc()) {
            $questionId = (int) $old['id'];
            $deleteAnswers->bind_param('i', $questionId);
            $deleteAnswers->execute();
        }
        $deleteQuestions->bind_param('i', $quizId);
        $deleteQuestions->execute();

        foreach ($questions as $index => $question) {
            $position = $index + 1;
            $explanation = 'නිවැරදි පිළිතුර: ' . strtoupper($question['correct']);
            $addQuestion->bind_param('isssssssi', $quizId, $question['question'], $question['a'], $question['b'], $question['c'], $question['d'], $question['correct'], $explanation, $position);
            $addQuestion->execute();
        }
        $title = '6 ශ්‍රේණිය සිංහල – ' . $order . ' වන පාඩම – බහුවරණ ප්‍රශ්න 30ක්';
        $updateQuiz->bind_param('si', $title, $quizId);
        $updateQuiz->execute();
        echo "Lesson $order: 30 questions updated.\n";
    }
    $db->commit();
} catch (Throwable $error) {
    $db->rollback();
    fwrite(STDERR, $error->getMessage() . "\n");
    exit(1);
}
