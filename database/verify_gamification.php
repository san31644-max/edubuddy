<?php
declare(strict_types=1);
require_once __DIR__.'/../includes/db.php';
require_once __DIR__.'/../includes/helpers.php';
require_once __DIR__.'/../includes/gamification.php';
$db=db();
foreach(['student_points','student_emblems'] as $table){
    if(!$db->query("SELECT 1 FROM `$table` LIMIT 1"))throw new RuntimeException("Missing $table");
}
$user=query_one("SELECT id,grade_id FROM users WHERE status='active' ORDER BY id LIMIT 1");
if($user){
    $db->begin_transaction();
    $key='verification:'.bin2hex(random_bytes(6));
    $first=award_points((int)$user['id'],(int)$user['grade_id'],null,null,null,'verification',1,$key,'Rollback-only verification');
    $duplicate=award_points((int)$user['id'],(int)$user['grade_id'],null,null,null,'verification',1,$key,'Rollback-only verification');
    $db->rollback();
    if(!$first||$duplicate)throw new RuntimeException('Duplicate award protection failed');
}
$totals=$db->query("SELECT (SELECT COUNT(*) FROM student_points) point_rows,(SELECT COUNT(*) FROM student_emblems) emblem_rows")->fetch_assoc();
$leaderboard=$db->prepare("SELECT u.id,COALESCE(SUM(sp.points),0) points,COUNT(DISTINCT CASE WHEN sp.activity_type='lesson_complete' THEN sp.lesson_id END) completed FROM users u LEFT JOIN student_points sp ON sp.user_id=u.id WHERE u.grade_id=? AND u.status='active' GROUP BY u.id ORDER BY points DESC,completed DESC LIMIT 100");
$grade=1;$leaderboard->bind_param('i',$grade);if(!$leaderboard->execute())throw new RuntimeException('Leaderboard query failed');$leaderboard->get_result()->fetch_all(MYSQLI_ASSOC);
echo 'Gamification verification passed. Points rows: '.(int)$totals['point_rows'].', emblem rows: '.(int)$totals['emblem_rows']."\n";
