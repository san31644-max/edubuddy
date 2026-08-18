<?php
declare(strict_types=1);

require_once __DIR__.'/../includes/db.php';

$db = db();
$sql = "UPDATE quiz_questions qq
        JOIN quizzes q ON q.id=qq.quiz_id
        JOIN lessons l ON l.id=q.lesson_id
        JOIN subjects s ON s.id=l.subject_id
        JOIN grades g ON g.id=l.grade_id
        SET qq.status='inactive'
        WHERE g.grade_number=6
          AND s.name_en='Buddhism'
          AND l.medium='Sinhala'
          AND l.display_order=1
          AND qq.activity_type='challenge'
          AND qq.status='active'";

if (!$db->query($sql)) {
    throw new RuntimeException('Could not remove Grade 6 Buddhism Lesson 1 Quick Challenge: '.$db->error);
}

echo 'Disabled '.$db->affected_rows." Grade 6 Buddhism Lesson 1 Quick Challenge question(s).\n";
