<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';

$file = __DIR__ . '/data/grade6-history-lessons1-3-5.json';
if (!is_file($file)) {
    throw new RuntimeException('Grade 6 History MCQ data is missing.');
}

$sets = json_decode((string) file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
$db = db();
$db->begin_transaction();

try {
    $lessonQuery = $db->prepare(
        "SELECT l.id
         FROM lessons l
         JOIN grades g ON g.id = l.grade_id
         JOIN subjects s ON s.id = l.subject_id
         WHERE g.grade_number = 6
           AND s.name_en = 'History'
           AND l.medium = 'Sinhala'
           AND l.display_order = ?
           AND l.status = 'active'
         LIMIT 1"
    );
    $quizQuery = $db->prepare("SELECT id FROM quizzes WHERE lesson_id = ? AND status = 'active' LIMIT 1");
    $questionIds = $db->prepare("SELECT id FROM quiz_questions WHERE quiz_id = ? AND activity_type = 'challenge'");
    $deleteAnswers = $db->prepare('DELETE FROM quiz_answers WHERE question_id = ?');
    $deleteQuestions = $db->prepare("DELETE FROM quiz_questions WHERE quiz_id = ? AND activity_type = 'challenge'");
    $insert = $db->prepare(
        "INSERT INTO quiz_questions
         (quiz_id, activity_type, question_si, option_a_si, option_b_si, option_c_si, option_d_si,
          correct_option, explanation_si, display_order, status)
         VALUES (?, 'challenge', ?, ?, ?, ?, ?, ?, ?, ?, 'active')"
    );
    $updateQuiz = $db->prepare('UPDATE quizzes SET title_si = ?, timer_minutes = 30, pass_mark = 50 WHERE id = ?');

    foreach ([1, 2, 3, 4, 5] as $order) {
        $set = $sets[(string) $order] ?? null;
        if (!$set || count($set['questions'] ?? []) !== 30) {
            throw new RuntimeException("Lesson $order data must contain exactly 30 questions.");
        }

        $lessonQuery->bind_param('i', $order);
        $lessonQuery->execute();
        $lesson = $lessonQuery->get_result()->fetch_assoc();
        if (!$lesson) {
            throw new RuntimeException("Grade 6 History lesson $order is missing.");
        }

        $lessonId = (int) $lesson['id'];
        $quizQuery->bind_param('i', $lessonId);
        $quizQuery->execute();
        $quiz = $quizQuery->get_result()->fetch_assoc();
        if (!$quiz) {
            throw new RuntimeException("Grade 6 History lesson $order quiz is missing.");
        }
        $quizId = (int) $quiz['id'];

        $questionIds->bind_param('i', $quizId);
        $questionIds->execute();
        $ids = $questionIds->get_result();
        while ($row = $ids->fetch_assoc()) {
            $questionId = (int) $row['id'];
            $deleteAnswers->bind_param('i', $questionId);
            $deleteAnswers->execute();
        }

        $deleteQuestions->bind_param('i', $quizId);
        $deleteQuestions->execute();

        foreach ($set['questions'] as $index => $question) {
            $displayOrder = $index + 1;
            $insert->bind_param(
                'isssssssi',
                $quizId,
                $question['question'],
                $question['a'],
                $question['b'],
                $question['c'],
                $question['d'],
                $question['correct'],
                $question['explanation'],
                $displayOrder
            );
            $insert->execute();
        }

        $title = "$order වන පාඩම — MCQ ප්‍රශ්න 30";
        $updateQuiz->bind_param('si', $title, $quizId);
        $updateQuiz->execute();

        $count = query_one(
            "SELECT COUNT(*) AS total FROM quiz_questions WHERE quiz_id = ? AND activity_type = 'challenge' AND status = 'active'",
            'i',
            [$quizId]
        );
        if ((int) ($count['total'] ?? 0) !== 30) {
            throw new RuntimeException("Lesson $order quiz verification failed.");
        }
    }

    $db->commit();
    echo "Grade 6 History lessons 1 to 5 replaced with 30 MCQs each.\n";
} catch (Throwable $error) {
    $db->rollback();
    throw $error;
}
