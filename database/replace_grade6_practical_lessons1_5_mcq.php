<?php
declare(strict_types=1);

ini_set('session.save_path', __DIR__ . '/../includes/runtime');
require_once __DIR__ . '/../includes/db.php';

$sets = json_decode((string) file_get_contents(__DIR__ . '/data/grade6-practical-lessons1-5.json'), true, 512, JSON_THROW_ON_ERROR);
if (array_map('intval', array_keys($sets)) !== range(1, 5)) {
    throw new RuntimeException('Expected five exact Practical and Technical Skills lesson sets.');
}

$db = db();
$db->begin_transaction();
try {
    $lesson = $db->prepare("SELECT l.id FROM lessons l JOIN grades g ON g.id=l.grade_id JOIN subjects s ON s.id=l.subject_id WHERE g.grade_number=6 AND s.name_en='Practical and Technical Skills' AND l.medium IN ('Sinhala','All') AND l.display_order=? AND l.status='active' ORDER BY (l.medium='Sinhala') DESC,l.id LIMIT 1");
    $quiz = $db->prepare("SELECT id FROM quizzes WHERE lesson_id=? AND status='active' ORDER BY id LIMIT 1");
    $oldIds = $db->prepare("SELECT id FROM quiz_questions WHERE quiz_id=? AND activity_type='challenge'");
    $deleteAnswers = $db->prepare('DELETE FROM quiz_answers WHERE question_id=?');
    $deleteQuestions = $db->prepare("DELETE FROM quiz_questions WHERE quiz_id=? AND activity_type='challenge'");
    $add = $db->prepare("INSERT INTO quiz_questions(quiz_id,activity_type,question_si,option_a_si,option_b_si,option_c_si,option_d_si,correct_option,explanation_si,display_order,status) VALUES(?,'challenge',?,?,?,?,?,?,?,?,'active')");
    $update = $db->prepare('UPDATE quizzes SET title_si=?,timer_minutes=30,pass_mark=50 WHERE id=?');

    foreach (range(1, 5) as $order) {
        $questions = $sets[(string) $order]['questions'] ?? [];
        if (count($questions) !== 30) {
            throw new RuntimeException("Lesson $order does not contain 30 questions.");
        }
        $lesson->bind_param('i', $order);
        $lesson->execute();
        $lessonRow = $lesson->get_result()->fetch_assoc();
        if (!$lessonRow) {
            throw new RuntimeException("Grade 6 Practical lesson $order missing.");
        }
        $lessonId = (int) $lessonRow['id'];
        $quiz->bind_param('i', $lessonId);
        $quiz->execute();
        $quizRow = $quiz->get_result()->fetch_assoc();
        if (!$quizRow) {
            throw new RuntimeException("Quiz for Practical lesson $order missing.");
        }
        $quizId = (int) $quizRow['id'];

        $oldIds->bind_param('i', $quizId);
        $oldIds->execute();
        $old = $oldIds->get_result();
        while ($row = $old->fetch_assoc()) {
            $questionId = (int) $row['id'];
            $deleteAnswers->bind_param('i', $questionId);
            $deleteAnswers->execute();
        }
        $deleteQuestions->bind_param('i', $quizId);
        $deleteQuestions->execute();

        foreach ($questions as $index => $question) {
            $position = $index + 1;
            $explanation = 'නිවැරදි පිළිතුර: ' . strtoupper($question['correct']);
            $add->bind_param('isssssssi', $quizId, $question['question'], $question['a'], $question['b'], $question['c'], $question['d'], $question['correct'], $explanation, $position);
            $add->execute();
        }
        $title = '6 ශ්‍රේණිය ප්‍රායෝගික හා තාක්ෂණික කුසලතා – ' . $order . ' වන පාඩම – බහුවරණ ප්‍රශ්න 30ක්';
        $update->bind_param('si', $title, $quizId);
        $update->execute();
        echo "Practical lesson $order: 30 questions updated.\n";
    }
    $db->commit();
} catch (Throwable $error) {
    $db->rollback();
    fwrite(STDERR, $error->getMessage() . "\n");
    exit(1);
}
