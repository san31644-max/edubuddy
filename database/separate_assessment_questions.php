<?php
declare(strict_types=1);
require_once __DIR__.'/../includes/db.php';
$db=db();
$column=$db->query("SHOW COLUMNS FROM quiz_questions LIKE 'activity_type'");
if(!$column||!$column->num_rows){
    if(!$db->query("ALTER TABLE quiz_questions ADD activity_type ENUM('lesson_quiz','challenge','missing','matching') NOT NULL DEFAULT 'lesson_quiz' AFTER quiz_id, ADD INDEX quiz_activity (quiz_id,activity_type,status)")) throw new RuntimeException($db->error);
    echo "Separated assessment question banks; existing questions assigned to Lesson Quiz.\n";
}else echo "Assessment question banks are already separated.\n";
