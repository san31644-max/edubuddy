<?php
require_once __DIR__.'/../includes/auth.php'; require_admin();
$db=db(); $message=''; $error='';
function parse_mcq_block(string $text):array{
 $chunks=preg_split('/(?=^\s*\d+\.\s)/mu',$text,-1,PREG_SPLIT_NO_EMPTY)?:[]; $out=[];
 foreach($chunks as $chunk){
  if(!preg_match('/^\s*(\d+)\.\s*(.*?)\R\s*A[.)]\s*(.*?)\R\s*B[.)]\s*(.*?)\R\s*C[.)]\s*(.*?)\R\s*D[.)]\s*(.*?)(?:\R\s*පිළිතුර\s*:\s*([ABCD]))\b/isu',$chunk,$m)) continue;
  $out[]=['number'=>(int)$m[1],'question'=>trim($m[2]),'a'=>trim($m[3]),'b'=>trim($m[4]),'c'=>trim($m[5]),'d'=>trim($m[6]),'correct'=>strtolower($m[7])];
 }
 return $out;
}
if($_SERVER['REQUEST_METHOD']==='POST'){verify_csrf();try{
 $raw=trim((string)($_POST['raw_text']??'')); if(mb_strlen($raw)<100)throw new RuntimeException('Paste the full Grade 11 Buddhism MCQ text first.');
 $lessons=query_all("SELECT l.*,g.grade_number,s.name_en FROM lessons l JOIN grades g ON g.id=l.grade_id JOIN subjects s ON s.id=l.subject_id WHERE g.grade_number=11 AND l.medium='Sinhala' AND s.name_en='Buddhism' AND l.status='active' ORDER BY l.display_order");
 if(!$lessons)throw new RuntimeException('Grade 11 Sinhala Buddhism lessons were not found.'); $byOrder=[];foreach($lessons as $l)$byOrder[(int)$l['display_order']]=$l;
 $heads=[];preg_match_all('/^\s*11\s*ශ්‍රේණිය\s*බුද්ධ\s*ධර්මය\s*[–-]\s*(\d+)\s*පාඩම[^\r\n]*/imu',$raw,$hm,PREG_OFFSET_CAPTURE);
 foreach($hm[1] as $i=>$hit){$heads[]=['n'=>(int)$hit[0],'pos'=>(int)$hit[1],'start'=>strpos($raw,$hm[0][$i][0])];}
 $sections=[];$firstStart=$heads[0]['start']??strlen($raw);$sections[2]=substr($raw,0,$firstStart);
 for($i=0;$i<count($heads);$i++){$n=$heads[$i]['n'];$start=$heads[$i]['start'];$end=$heads[$i+1]['start']??strlen($raw);$sections[$n]=substr($raw,$start,$end-$start);}
 $db->begin_transaction();$total=0;$done=[];
 foreach($sections as $n=>$section){if(!isset($byOrder[$n]))continue;$items=parse_mcq_block($section);if(!$items)continue;$lesson=$byOrder[$n];$quiz=query_one('SELECT id FROM quizzes WHERE lesson_id=? ORDER BY id LIMIT 1','i',[$lesson['id']]);if($quiz)$qid=(int)$quiz['id'];else{$q=$db->prepare('INSERT INTO quizzes(grade_id,subject_id,unit_id,lesson_id,timer_minutes,pass_mark,status,title_en,title_si,title_ta) VALUES(?,?,?,?,15,50,"active",?,?,?)');$title='Lesson '.$n.' Quiz';$q->bind_param('iiiisss',$lesson['grade_id'],$lesson['subject_id'],$lesson['unit_id'],$lesson['id'],$title,$title,$title);$q->execute();$qid=(int)$db->insert_id;}
  $del=$db->prepare("DELETE FROM quiz_questions WHERE quiz_id=? AND activity_type='lesson_quiz'");$del->bind_param('i',$qid);$del->execute();$ins=$db->prepare('INSERT INTO quiz_questions(quiz_id,activity_type,question_si,option_a_si,option_b_si,option_c_si,option_d_si,correct_option,explanation_si,display_order,status) VALUES(? ,\'lesson_quiz\',?,?,?,?,?,?,?,? ,\'active\')');$ord=0;foreach($items as $x){$ord++;$ex='නිවැරදි පිළිතුර: '.strtoupper($x['correct']);$ins->bind_param('isssssssi',$qid,$x['question'],$x['a'],$x['b'],$x['c'],$x['d'],$x['correct'],$ex,$ord);$ins->execute();}$total+=count($items);$done[]='Lesson '.$n.' ('.count($items).' questions)';}
 $db->commit();$message='Imported: '.implode(', ',$done).'. Total '.$total.' questions.';
}catch(Throwable $e){if($db instanceof mysqli)@$db->rollback();$error=$e->getMessage();}}
$pageTitle='Grade 11 MCQ text import';include __DIR__.'/_top.php';
?>
<section class="card"><h1>Grade 11 Buddhism · Paste MCQ importer</h1><p class="muted">Paste the full Sinhala MCQ text. Lesson headings split lessons automatically. Lesson 2 may contain 29 questions.</p><?php if($message):?><div class="alert success"><?=e($message)?></div><?php endif;?><?php if($error):?><div class="alert error"><?=e($error)?></div><?php endif;?><form method="post"><?=csrf_field()?><label>MCQ text<textarea name="raw_text" required style="min-height:520px" placeholder="Paste the Grade 11 Buddhism questions here..."></textarea></label><button type="submit">Import / replace Grade 11 quizzes</button></form></section>
<?php include __DIR__.'/../includes/footer.php';
