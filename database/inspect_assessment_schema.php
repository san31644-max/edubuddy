<?php
require_once __DIR__.'/../includes/db.php';
foreach(['quizzes','quiz_questions','practice_papers','paper_questions'] as $table){echo "\n$table\n";$r=db()->query("SHOW COLUMNS FROM $table");while($c=$r->fetch_assoc())echo $c['Field'].' '.$c['Type'].PHP_EOL;}
