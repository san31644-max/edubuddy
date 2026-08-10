<?php
declare(strict_types=1);require_once __DIR__.'/../includes/auth.php';
if(!parent_user()){http_response_code(401);exit;}
$parentId=(int)parent_user()['id'];$studentId=filter_input(INPUT_GET,'child',FILTER_VALIDATE_INT);
$link=$studentId?query_one('SELECT psl.last_seen_at,psl.linked_at,u.full_name FROM parent_student_links psl JOIN users u ON u.id=psl.student_id WHERE psl.parent_id=? AND psl.student_id=?','ii',[$parentId,$studentId]):null;
if(!$link){http_response_code(403);exit;}
$since=$link['last_seen_at']?:$link['linked_at'];session_write_close();
header('Content-Type: text/event-stream');header('Cache-Control: no-cache, no-store');header('Connection: keep-alive');header('X-Accel-Buffering: no');
echo ": connected\n\n";@ob_flush();@flush();$lastSignature='';$started=time();
while(!connection_aborted()&&time()-$started<25){
 $counts=query_one("SELECT COUNT(*) total,SUM(event_type='search') searches,SUM(event_type IN ('lesson_opened','lesson_completed')) lessons,SUM(event_type='quiz_completed') quizzes FROM student_activity_events WHERE user_id=? AND event_time>?",'is',[$studentId,$since])??[];$searches=(int)($counts['searches']??0);$lessons=(int)($counts['lessons']??0);$quizzes=(int)($counts['quizzes']??0);
 $latest=latest_student_activity_notification((int)$studentId,(string)$link['full_name']);
 $payload=['student'=>$link['full_name'],'total'=>$searches+$lessons+$quizzes,'counts'=>['searches'=>$searches,'lessons'=>$lessons,'quizzes'=>$quizzes],'latest'=>$latest,'notification'=>$latest['notification']??null];$signature=hash('sha256',json_encode($payload));
 if($signature!==$lastSignature){echo "event: activity\ndata: ".json_encode($payload,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)."\n\n";$lastSignature=$signature;}else echo ": keepalive\n\n";
 @ob_flush();@flush();sleep(2);
}
