<?php
declare(strict_types=1);require_once __DIR__.'/../includes/auth.php';header('Content-Type: application/json; charset=utf-8');
if(!parent_user()){http_response_code(401);echo json_encode(['ok'=>false]);exit;}
$parentId=(int)parent_user()['id'];$studentId=filter_input(INPUT_GET,'child',FILTER_VALIDATE_INT);
$link=$studentId?query_one('SELECT psl.last_seen_at,psl.linked_at,u.full_name FROM parent_student_links psl JOIN users u ON u.id=psl.student_id WHERE psl.parent_id=? AND psl.student_id=?','ii',[$parentId,$studentId]):null;
if(!$link){http_response_code(403);echo json_encode(['ok'=>false]);exit;}
$since=$link['last_seen_at']?:$link['linked_at'];
$counts=query_one("SELECT COUNT(*) total,SUM(event_type='search') searches,SUM(event_type IN ('lesson_opened','lesson_completed')) lessons,SUM(event_type='quiz_completed') quizzes FROM student_activity_events WHERE user_id=? AND event_time>?",'is',[$studentId,$since])??[];$searches=(int)($counts['searches']??0);$lessons=(int)($counts['lessons']??0);$quizzes=(int)($counts['quizzes']??0);
$latest=latest_student_activity_notification((int)$studentId,(string)$link['full_name']);
echo json_encode(['ok'=>true,'student'=>$link['full_name'],'total'=>$searches+$lessons+$quizzes,'counts'=>['searches'=>$searches,'lessons'=>$lessons,'quizzes'=>$quizzes],'latest'=>$latest,'notification'=>$latest['notification']??null],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
