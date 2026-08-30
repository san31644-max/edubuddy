<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require_once __DIR__.'/../includes/db.php';
require_once __DIR__.'/../includes/helpers.php';
require_once __DIR__.'/../includes/ol_schema.php';

ensure_ol_schema();
$db=db();
$dataFile=__DIR__.'/ol_science_2025_si_questions.php';
$paperPath='uploads/ol-papers/35/2025/si/paper_4d17c15dcf3b34b4.pdf';
$answerPath='uploads/ol-papers/35/2025/si/answers_d0f7291add0e7026.pdf';

if(!is_file(__DIR__.'/../'.$paperPath))throw new RuntimeException('Deployed O/L Science question paper is missing.');
if(!is_file(__DIR__.'/../'.$answerPath))throw new RuntimeException('Deployed O/L Science answer paper is missing.');
$questions=require $dataFile;
if(!is_array($questions)||count($questions)!==194)throw new RuntimeException('Expected 194 prepared O/L Science questions.');

$subject=query_one("SELECT id FROM subjects WHERE status='active' AND LOWER(name_en)='science' ORDER BY id LIMIT 1");
if(!$subject)throw new RuntimeException('Active Science subject was not found.');
$subjectId=(int)$subject['id'];
$year=2025;
$medium='si';
$paperType='main';
$title='2025 O/L Science Paper';

$db->begin_transaction();
try{
    $paperStatement=$db->prepare('INSERT INTO ol_papers(subject_id,year,medium,paper_type,title,paper_file_path,answer_sheet_path,status) VALUES(?,?,?,?,?,?,?,1) ON DUPLICATE KEY UPDATE title=VALUES(title),paper_file_path=VALUES(paper_file_path),answer_sheet_path=VALUES(answer_sheet_path),status=1');
    if(!$paperStatement)throw new RuntimeException('Could not prepare O/L paper provisioning.');
    $paperStatement->bind_param('iisssss',$subjectId,$year,$medium,$paperType,$title,$paperPath,$answerPath);
    if(!$paperStatement->execute())throw new RuntimeException('Could not provision O/L Science paper.');
    $paper=query_one('SELECT id FROM ol_papers WHERE subject_id=? AND year=? AND medium=? AND paper_type=?','iiss',[$subjectId,$year,$medium,$paperType]);
    if(!$paper)throw new RuntimeException('Provisioned O/L Science paper could not be found.');
    $paperId=(int)$paper['id'];
    $delete=$db->prepare('DELETE FROM ol_paper_questions WHERE paper_id=?');
    $delete->bind_param('i',$paperId);
    $delete->execute();
    $insert=$db->prepare('INSERT INTO ol_paper_questions(paper_id,question_number,question_text,display_order) VALUES(?,?,?,?)');
    if(!$insert)throw new RuntimeException('Could not prepare O/L question provisioning.');
    foreach($questions as $index=>$question){
        $number=trim((string)($question['number']??''));
        $text=trim((string)($question['text']??''));
        $order=(int)($question['display_order']??($index+1));
        if($number===''||$text==='')throw new RuntimeException('Invalid prepared question at position '.($index+1).'.');
        $insert->bind_param('issi',$paperId,$number,$text,$order);
        if(!$insert->execute())throw new RuntimeException('Could not provision question '.$number.'.');
    }
    $db->commit();
    echo 'O/L Science 2025 ready: '.count($questions).' questions.'.PHP_EOL;
}catch(Throwable $error){
    $db->rollback();
    throw $error;
}