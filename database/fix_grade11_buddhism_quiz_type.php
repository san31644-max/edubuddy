<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli'){http_response_code(404);exit('CLI only');}
require_once __DIR__.'/../includes/db.php';
$db=db();
$s=$db->prepare("UPDATE quiz_questions qq JOIN quizzes q ON q.id=qq.quiz_id JOIN lessons l ON l.id=q.lesson_id JOIN grades g ON g.id=l.grade_id JOIN subjects sub ON sub.id=l.subject_id SET qq.activity_type='lesson_quiz' WHERE g.grade_number=11 AND sub.subject_code='BUDDHISM11' AND l.display_order=1 AND qq.activity_type='challenge'");
if(!$s->execute())throw new RuntimeException($s->error);
echo "Grade 11 Buddhism Lesson 1 quiz type fixed. Updated {$s->affected_rows} questions.\n";
