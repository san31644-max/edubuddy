<?php
declare(strict_types=1);

require_once __DIR__.'/../includes/db.php';

$db = db();
$subject = $db->query("SELECT s.id FROM subjects s JOIN grades g ON g.id=s.grade_id WHERE g.grade_number=8 AND s.subject_code='ENGLI8' AND s.name_en='English' AND s.status='active' LIMIT 1")->fetch_assoc();
if (!$subject) {
    throw new RuntimeException('Active Grade 8 English subject was not found.');
}

$subjectId = (int)$subject['id'];
$update = $db->prepare("UPDATE lessons SET medium='All' WHERE subject_id=? AND status='active' AND content_source='textbook' AND medium='English'");
$update->bind_param('i', $subjectId);
if (!$update->execute()) {
    throw new RuntimeException($update->error);
}

$audit = $db->query("SELECT COUNT(*) lessons,SUM(COALESCE(challenges,0)=5) quiz_ready FROM (SELECT l.id,COUNT(qq.id) challenges FROM lessons l LEFT JOIN quizzes q ON q.lesson_id=l.id AND q.status='active' LEFT JOIN quiz_questions qq ON qq.quiz_id=q.id AND qq.activity_type='challenge' AND qq.status='active' WHERE l.subject_id={$subjectId} AND l.medium='All' AND l.status='active' AND l.content_source='textbook' GROUP BY l.id) ready")->fetch_assoc();
if ((int)$audit['lessons'] < 1 || (int)$audit['quiz_ready'] !== (int)$audit['lessons']) {
    throw new RuntimeException('Grade 8 English lesson or Quick Challenge verification failed.');
}

echo "Grade 8 English enabled for every learning medium: {$audit['lessons']} lessons, {$audit['quiz_ready']} quizzes ready.\n";
