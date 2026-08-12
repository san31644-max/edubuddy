<?php
declare(strict_types=1);require_once __DIR__.'/../includes/db.php';$db=db();
$sql=[
"CREATE TABLE IF NOT EXISTS parents(id INT AUTO_INCREMENT PRIMARY KEY,full_name VARCHAR(100) NOT NULL,email VARCHAR(190) NOT NULL UNIQUE,password_hash VARCHAR(255) NOT NULL,status ENUM('active','inactive') DEFAULT 'active',created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)",
"CREATE TABLE IF NOT EXISTS student_parent_codes(student_id INT PRIMARY KEY,link_code CHAR(8) NOT NULL UNIQUE,expires_at DATETIME NOT NULL,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,FOREIGN KEY(student_id) REFERENCES users(id) ON DELETE CASCADE)",
"CREATE TABLE IF NOT EXISTS parent_student_links(parent_id INT NOT NULL,student_id INT NOT NULL,linked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,last_seen_at DATETIME NULL,PRIMARY KEY(parent_id,student_id),INDEX(student_id),FOREIGN KEY(parent_id) REFERENCES parents(id) ON DELETE CASCADE,FOREIGN KEY(student_id) REFERENCES users(id) ON DELETE CASCADE)"
,"CREATE TABLE IF NOT EXISTS student_activity_events(id BIGINT AUTO_INCREMENT PRIMARY KEY,user_id INT NOT NULL,event_type ENUM('search','lesson_opened','lesson_completed','quiz_completed') NOT NULL,subject_id INT NULL,lesson_id INT NULL,quiz_id INT NULL,detail VARCHAR(500) NOT NULL,event_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,INDEX(user_id,event_time),FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,FOREIGN KEY(subject_id) REFERENCES subjects(id) ON DELETE SET NULL,FOREIGN KEY(lesson_id) REFERENCES lessons(id) ON DELETE SET NULL,FOREIGN KEY(quiz_id) REFERENCES quizzes(id) ON DELETE SET NULL)"
];
foreach($sql as $q)if(!$db->query($q))throw new RuntimeException($db->error);
foreach($db->query('SHOW COLUMNS FROM parents') as $column)echo 'parents column: '.$column['Field'].' '.$column['Type'].' null='.$column['Null'].' default='.var_export($column['Default'],true).PHP_EOL;
echo "Parent monitoring tables are ready.\n";
