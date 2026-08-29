<?php
declare(strict_types=1);
require_once __DIR__.'/../includes/db.php';require_once __DIR__.'/../includes/helpers.php';require_once __DIR__.'/../includes/ol_schema.php';require_once __DIR__.'/../includes/ol_pdf.php';
ensure_ol_schema();$id=(int)($argv[1]??0);$paper=$id?query_one('SELECT * FROM ol_papers WHERE id=?','i',[$id]):null;if(!$paper)throw new RuntimeException('Paper not found.');
$file=realpath(__DIR__.'/../'.ltrim((string)$paper['paper_file_path'],'/'));if(!$file)throw new RuntimeException('Paper PDF not found.');$items=ol_read_questions($file);if(!$items)throw new RuntimeException('No questions could be extracted.');
$db=db();$db->begin_transaction();try{$delete=$db->prepare('DELETE FROM ol_paper_questions WHERE paper_id=?');$delete->bind_param('i',$id);$delete->execute();$insert=$db->prepare('INSERT INTO ol_paper_questions(paper_id,question_number,question_text,display_order) VALUES(?,?,?,?)');foreach($items as $i=>$item){$order=$i+1;$insert->bind_param('issi',$id,$item['number'],$item['text'],$order);$insert->execute();}$db->commit();echo 'Extracted '.count($items).' questions.'.PHP_EOL;}catch(Throwable $e){$db->rollback();throw $e;}
