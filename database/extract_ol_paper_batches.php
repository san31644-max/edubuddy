<?php
declare(strict_types=1);
require_once __DIR__.'/../includes/db.php';require_once __DIR__.'/../includes/helpers.php';require_once __DIR__.'/../includes/ol_schema.php';require_once __DIR__.'/../includes/ol_pdf.php';
ensure_ol_schema();$id=(int)($argv[1]??0);$folder=(string)($argv[2]??'');$paper=$id?query_one('SELECT id FROM ol_papers WHERE id=?','i',[$id]):null;if(!$paper)throw new RuntimeException('Paper not found.');
$files=glob(rtrim($folder,'/\\').'/*.pdf')?:[];sort($files,SORT_NATURAL);if(!$files)throw new RuntimeException('No PDF batches found.');$items=[];
foreach($files as $file){echo 'Reading '.basename($file).'...'.PHP_EOL;$batch=ol_ai_extract_questions($file);foreach($batch as $item)$items[]=$item;echo count($batch).' questions found.'.PHP_EOL;}
if(!$items)throw new RuntimeException('No questions could be extracted.');$db=db();$db->begin_transaction();
try{$delete=$db->prepare('DELETE FROM ol_paper_questions WHERE paper_id=?');$delete->bind_param('i',$id);$delete->execute();$insert=$db->prepare('INSERT INTO ol_paper_questions(paper_id,question_number,question_text,display_order) VALUES(?,?,?,?)');$seen=[];foreach($items as $i=>$item){$number=trim((string)$item['number']);$key=$number;if(isset($seen[$key]))$number.='.'.(++$seen[$key]);else$seen[$key]=1;$order=$i+1;$text=(string)$item['text'];$insert->bind_param('issi',$id,$number,$text,$order);$insert->execute();}$db->commit();echo 'Extracted '.count($items).' questions total.'.PHP_EOL;}catch(Throwable $e){$db->rollback();throw $e;}
