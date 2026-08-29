CREATE TABLE IF NOT EXISTS ol_papers (
 id INT AUTO_INCREMENT PRIMARY KEY,
 subject_id INT NOT NULL,
 year SMALLINT NOT NULL,
 medium ENUM('si','ta','en') NOT NULL,
 paper_type VARCHAR(50) NOT NULL DEFAULT 'main',
 title VARCHAR(255) NULL,
 paper_file_path VARCHAR(500) NOT NULL,
 answer_sheet_path VARCHAR(500) NULL,
 status TINYINT(1) NOT NULL DEFAULT 1,
 uploaded_by INT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 UNIQUE KEY ol_paper_unique(subject_id,year,medium,paper_type),
 INDEX ol_paper_subject(subject_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS ol_paper_questions (
 id INT AUTO_INCREMENT PRIMARY KEY,
 paper_id INT NOT NULL,
 question_number VARCHAR(40) NOT NULL,
 question_text LONGTEXT NOT NULL,
 answer_text LONGTEXT NULL,
 display_order INT NOT NULL DEFAULT 0,
 UNIQUE KEY ol_question_unique(paper_id,question_number),
 INDEX ol_question_paper(paper_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
