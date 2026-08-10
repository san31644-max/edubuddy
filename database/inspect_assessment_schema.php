<?php
require_once __DIR__.'/../includes/db.php';
foreach(['quizzes','quiz_questions','practice_papers','paper_questions'] as $table){echo "\n$table\n";$r=db()->query("SHOW COLUMNS FROM $table");while($c=$r->fetch_assoc())echo $c['Field'].' '.$c['Type'].PHP_EOL;}
echo "\nForeign keys referencing quizzes/questions/papers\n";$r=db()->query("SELECT TABLE_NAME,CONSTRAINT_NAME,REFERENCED_TABLE_NAME,DELETE_RULE FROM information_schema.REFERENTIAL_CONSTRAINTS WHERE CONSTRAINT_SCHEMA=DATABASE() AND REFERENCED_TABLE_NAME IN ('quizzes','quiz_questions','practice_papers','paper_questions')");while($x=$r->fetch_assoc())echo implode(' | ',$x).PHP_EOL;
