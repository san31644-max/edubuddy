<?php
declare(strict_types=1);require_once __DIR__.'/../includes/db.php';$db=db();$fail=[];
foreach(['parents','student_parent_codes','parent_student_links','student_activity_events'] as $table){$r=$db->query("SHOW TABLES LIKE '$table'");if(!$r||$r->num_rows!==1)$fail[]="Missing table: $table";}
$queries=[
 "SELECT u.id FROM student_parent_codes c JOIN users u ON u.id=c.student_id WHERE c.link_code=? AND c.expires_at>NOW()",
 "SELECT COUNT(DISTINCT l.id) total,COUNT(DISTINCT CASE WHEN lp.completed_at IS NOT NULL THEN l.id END) completed FROM lessons l LEFT JOIN lesson_progress lp ON lp.lesson_id=l.id AND lp.user_id=? WHERE l.grade_id=(SELECT grade_id FROM users WHERE id=?) AND l.content_source='textbook' AND l.status='active' AND (l.medium='All' OR l.medium=?)",
 "SELECT cm.message,cm.created_at FROM chat_messages cm WHERE cm.user_id=? AND cm.role='user' ORDER BY cm.created_at DESC LIMIT 20"
 ,"SELECT kind,detail,event_time FROM (SELECT 'search' kind,message detail,created_at event_time FROM chat_messages WHERE user_id=? AND role='user' UNION ALL SELECT 'lesson',l.title_en,lp.completed_at FROM lesson_progress lp JOIN lessons l ON l.id=lp.lesson_id WHERE lp.user_id=? AND lp.completed_at IS NOT NULL UNION ALL SELECT 'quiz',CONCAT(q.title_en,' - ',qa.percentage,'%'),qa.completed_at FROM quiz_attempts qa JOIN quizzes q ON q.id=qa.quiz_id WHERE qa.user_id=?) events ORDER BY event_time DESC LIMIT 1"
 ,"SELECT COUNT(*) total,SUM(event_type='search') searches,SUM(event_type IN ('lesson_opened','lesson_completed')) lessons,SUM(event_type='quiz_completed') quizzes FROM student_activity_events WHERE user_id=? AND event_time>?"
];
foreach($queries as $i=>$sql)if(!$db->prepare($sql))$fail[]='Query '.($i+1).': '.$db->error;
if($fail){fwrite(STDERR,"FAILED\n- ".implode("\n- ",$fail)."\n");exit(1);}echo "PASS: parent accounts, consent linking, monitoring and activity queries are ready.\n";
