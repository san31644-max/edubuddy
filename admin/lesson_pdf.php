<?php
declare(strict_types=1);
require_once __DIR__.'/../includes/auth.php';require_admin();
$lessonId=filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT);$source=$lessonId?query_one('SELECT local_file FROM lesson_source_pdfs WHERE lesson_id=?','i',[$lessonId]):null;
if(!$source){http_response_code(404);exit('No syllabus PDF is linked to this lesson.');}
$root=realpath(__DIR__.'/..');$file=realpath($root.'/'.str_replace(['/', '\\'],DIRECTORY_SEPARATOR,(string)$source['local_file']));
if(!$file||!is_file($file)||!str_starts_with(strtolower($file),strtolower($root.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'syllabus'.DIRECTORY_SEPARATOR))||strtolower(pathinfo($file,PATHINFO_EXTENSION))!=='pdf'){http_response_code(404);exit('The syllabus PDF is unavailable.');}
header('Content-Type: application/pdf');header('Content-Disposition: inline; filename="'.str_replace('"','',basename($file)).'"');header('Content-Length: '.filesize($file));header('X-Content-Type-Options: nosniff');readfile($file);
