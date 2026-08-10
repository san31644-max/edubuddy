<?php
declare(strict_types=1);
require_once __DIR__.'/../includes/db.php';

$db=db();
$queries=[
"CREATE TABLE IF NOT EXISTS student_points(
 id BIGINT AUTO_INCREMENT PRIMARY KEY,user_id INT NOT NULL,grade_id INT NOT NULL,
 subject_id INT NULL,lesson_id INT NULL,quiz_id INT NULL,activity_type VARCHAR(40) NOT NULL,
 points INT NOT NULL,award_key VARCHAR(190) NOT NULL,description VARCHAR(255) NOT NULL,
 awarded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,UNIQUE(user_id,award_key),
 INDEX(grade_id,user_id),INDEX(user_id,awarded_at),
 FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
 FOREIGN KEY(grade_id) REFERENCES grades(id),FOREIGN KEY(subject_id) REFERENCES subjects(id),
 FOREIGN KEY(lesson_id) REFERENCES lessons(id) ON DELETE SET NULL,
 FOREIGN KEY(quiz_id) REFERENCES quizzes(id) ON DELETE SET NULL)",
"CREATE TABLE IF NOT EXISTS student_emblems(
 id BIGINT AUTO_INCREMENT PRIMARY KEY,user_id INT NOT NULL,grade_id INT NOT NULL,
 subject_id INT NOT NULL,lesson_id INT NOT NULL,tier ENUM('bronze','silver','gold','master') NOT NULL,
 earned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,UNIQUE(user_id,lesson_id,tier),
 INDEX(grade_id,user_id),FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
 FOREIGN KEY(grade_id) REFERENCES grades(id),FOREIGN KEY(subject_id) REFERENCES subjects(id),
 FOREIGN KEY(lesson_id) REFERENCES lessons(id) ON DELETE CASCADE)"
];
foreach($queries as $sql){if(!$db->query($sql))throw new RuntimeException($db->error);}

// Backfill verified lesson completions without allowing duplicate rewards.
$db->query("INSERT IGNORE INTO student_points(user_id,grade_id,subject_id,lesson_id,activity_type,points,award_key,description,awarded_at)
 SELECT lp.user_id,l.grade_id,l.subject_id,l.id,'lesson_complete',20,CONCAT('lesson:',l.id,':complete'),'Lesson completed',lp.completed_at
 FROM lesson_progress lp JOIN lessons l ON l.id=lp.lesson_id WHERE lp.completed_at IS NOT NULL");
$db->query("INSERT IGNORE INTO student_emblems(user_id,grade_id,subject_id,lesson_id,tier,earned_at)
 SELECT lp.user_id,l.grade_id,l.subject_id,l.id,'bronze',lp.completed_at
 FROM lesson_progress lp JOIN lessons l ON l.id=lp.lesson_id WHERE lp.completed_at IS NOT NULL");

// Backfill first-attempt and score-threshold rewards.
$db->query("INSERT IGNORE INTO student_points(user_id,grade_id,subject_id,lesson_id,quiz_id,activity_type,points,award_key,description,awarded_at)
 SELECT qa.user_id,q.grade_id,q.subject_id,q.lesson_id,q.id,'quiz_attempt',10,CONCAT('quiz:',q.id,':first'),'First quiz attempt',MIN(qa.completed_at)
 FROM quiz_attempts qa JOIN quizzes q ON q.id=qa.quiz_id GROUP BY qa.user_id,q.id");
foreach([50,70,85,95] as $threshold){
 $db->query("INSERT IGNORE INTO student_points(user_id,grade_id,subject_id,lesson_id,quiz_id,activity_type,points,award_key,description,awarded_at)
 SELECT qa.user_id,q.grade_id,q.subject_id,q.lesson_id,q.id,'quiz_score',10,CONCAT('quiz:',q.id,':score:$threshold'),CONCAT('Quiz score reached $threshold%'),MIN(qa.completed_at)
 FROM quiz_attempts qa JOIN quizzes q ON q.id=qa.quiz_id WHERE qa.percentage>=$threshold GROUP BY qa.user_id,q.id");
}
foreach(['silver'=>70,'gold'=>85,'master'=>95] as $tier=>$threshold){
 $db->query("INSERT IGNORE INTO student_emblems(user_id,grade_id,subject_id,lesson_id,tier,earned_at)
 SELECT qa.user_id,q.grade_id,q.subject_id,q.lesson_id,'$tier',MIN(qa.completed_at)
 FROM quiz_attempts qa JOIN quizzes q ON q.id=qa.quiz_id
 JOIN lesson_progress lp ON lp.user_id=qa.user_id AND lp.lesson_id=q.lesson_id AND lp.completed_at IS NOT NULL
 WHERE q.lesson_id IS NOT NULL AND qa.percentage>=$threshold GROUP BY qa.user_id,q.lesson_id");
}
echo "Gamification tables ready and existing achievements backfilled.\n";
