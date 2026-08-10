<?php
require_once __DIR__ . '/../includes/db.php';
$sql = [
"CREATE TABLE IF NOT EXISTS curriculum_sources(id INT AUTO_INCREMENT PRIMARY KEY,grade_id INT NOT NULL,subject_name VARCHAR(180) NOT NULL,language ENUM('en','si','ta','mixed') DEFAULT 'mixed',title VARCHAR(255) NOT NULL,source_url TEXT NOT NULL,local_file VARCHAR(255) NOT NULL,file_hash CHAR(64) NOT NULL UNIQUE,status ENUM('active','inactive') DEFAULT 'active',created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,FOREIGN KEY(grade_id) REFERENCES grades(id))",
"CREATE TABLE IF NOT EXISTS curriculum_chunks(id BIGINT AUTO_INCREMENT PRIMARY KEY,source_id INT NOT NULL,page_number INT NULL,chunk_index INT NOT NULL,content MEDIUMTEXT NOT NULL,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,INDEX(source_id,page_number),FULLTEXT(content),FOREIGN KEY(source_id) REFERENCES curriculum_sources(id) ON DELETE CASCADE)"
];
foreach ($sql as $query) if (!db()->query($query)) die(db()->error);
echo 'Curriculum tables ready';
