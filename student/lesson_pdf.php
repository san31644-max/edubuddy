<?php
declare(strict_types=1);
require_once __DIR__.'/../includes/auth.php';require_login();
$lessonId=filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT);$medium=(string)(user()['medium']??'English');
$source=$lessonId?query_one("SELECT p.local_file FROM lesson_source_pdfs p JOIN lessons l ON l.id=p.lesson_id WHERE l.id=? AND l.grade_id=? AND l.status='active' AND l.content_source='textbook' AND (l.medium='All' OR l.medium=?)",'iis',[$lessonId,(int)user()['grade_id'],$medium]):null;
if(!$source){http_response_code(404);exit('No syllabus book is available for this lesson.');}
$root=realpath(__DIR__.'/..');$file=realpath($root.'/'.str_replace(['/', '\\'],DIRECTORY_SEPARATOR,(string)$source['local_file']));$allowed=$root.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'syllabus'.DIRECTORY_SEPARATOR;
if(!$file||!is_file($file)||!str_starts_with(strtolower($file),strtolower($allowed))||strtolower(pathinfo($file,PATHINFO_EXTENSION))!=='pdf'){http_response_code(404);exit('The syllabus book is unavailable.');}
header('Content-Type: application/pdf');header('Content-Disposition: inline; filename="'.str_replace('"','',basename($file)).'"');header('Content-Length: '.filesize($file));header('X-Content-Type-Options: nosniff');readfile($file);
