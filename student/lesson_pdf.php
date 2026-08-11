<?php
declare(strict_types=1);
require_once __DIR__.'/../includes/auth.php';require_login();
$lessonId=filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT);$medium=(string)(user()['medium']??'English');
$source=$lessonId?query_one("SELECT p.local_file FROM lesson_source_pdfs p JOIN lessons l ON l.id=p.lesson_id WHERE l.id=? AND l.grade_id=? AND l.status='active' AND l.content_source='textbook' AND (l.medium='All' OR l.medium=?)",'iis',[$lessonId,(int)user()['grade_id'],$medium]):null;
if(!$source){http_response_code(404);exit('No syllabus book is available for this lesson.');}
$root=realpath(dirname(__DIR__));
$relative=str_replace('\\','/',trim((string)$source['local_file']));

// Only files in the public syllabus directory may be served.  Build the path
// ourselves first: realpath() can be unreliable for otherwise valid paths on
// some Windows/Apache combinations (especially paths containing spaces).
$safeRelative=preg_match('#^uploads/syllabus/.+\.pdf$#i',$relative)
    && !preg_match('#(?:^|/)\.\.(?:/|$)#',$relative)
    && !preg_match('#^[a-z]:|^//#i',$relative);
$candidate=$safeRelative&&$root
    ? $root.DIRECTORY_SEPARATOR.str_replace('/',DIRECTORY_SEPARATOR,$relative)
    : '';
$file=$candidate!==''&&is_file($candidate)&&is_readable($candidate) ? $candidate : false;

if(!$file){
    error_log('Textbook preview file rejected. lesson='.(int)$lessonId.' relative='.json_encode($relative).' candidate='.json_encode($candidate));
    http_response_code(404);exit('The syllabus book is unavailable.');
}

$size=filesize($file);
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="textbook-lesson-'.(int)$lessonId.'.pdf"');
if($size!==false)header('Content-Length: '.$size);
header('Accept-Ranges: bytes');
header('Cache-Control: private, max-age=3600');
header('X-Content-Type-Options: nosniff');
if(readfile($file)===false){error_log('Textbook preview read failed. lesson='.(int)$lessonId.' file='.json_encode($file));}
