<?php
require_once __DIR__.'/../includes/auth.php'; require_admin();
$db=db(); $message=''; $error='';
function importer_query_all(string $sql):array{
 $result=db()->query($sql);
 return $result ? ($result->fetch_all(MYSQLI_ASSOC) ?: []) : [];
}
function parse_mcq_block(string $text):array{
 $chunks=preg_split('/(?=^\s*\d+\.\s)/mu',$text,-1,PREG_SPLIT_NO_EMPTY)?:[]; $out=[];
 foreach($chunks as $chunk){
  // Find the answer line first. Some questions have an extra sentence after
  // option D (for example “සැලකේ.”), so it cannot be part of the option regex.
  if(!preg_match('/(?:^|\R)\s*[^:\r\n]{1,60}:\s*([ABCD])\s*(?:\R|$)/iu',$chunk,$answer,PREG_OFFSET_CAPTURE)) continue;
  $body=substr($chunk,0,(int)$answer[0][1]);
  if(!preg_match('/^\s*(\d+)\.\s*(.*?)\R\s*A[.)]\s*(.*?)\R\s*B[.)]\s*(.*?)\R\s*C[.)]\s*(.*?)\R\s*D[.)]\s*(.*)$/isu',$body,$m)) continue;
  $out[]=['number'=>(int)$m[1],'question'=>trim($m[2]),'a'=>trim($m[3]),'b'=>trim($m[4]),'c'=>trim($m[5]),'d'=>trim($m[6]),'correct'=>strtolower($answer[1][0])];
 }
 return $out;
}
if($_SERVER['REQUEST_METHOD']==='POST'){verify_csrf();try{
 $raw=trim((string)($_POST['raw_text']??'')); if(mb_strlen($raw)<100)throw new RuntimeException('Paste the full Grade 11 Buddhism MCQ text first.');
 $lessons=importer_query_all("SELECT l.*,g.grade_number,s.name_en FROM lessons l JOIN grades g ON g.id=l.grade_id JOIN subjects s ON s.id=l.subject_id WHERE g.grade_number=11 AND l.medium='Sinhala' AND s.name_en='Buddhism' AND l.status='active' ORDER BY l.display_order");
 if(!$lessons)throw new RuntimeException('Grade 11 Sinhala Buddhism subject was not found.');
 $titles=[2=>'බුදුගුණ අනන්තය',3=>'බුදුකුරු දම් පුරා – දිවිමග ගනිමු සපුරා',4=>'සමාධිගත සිතක මහිම',5=>'ආදර්ශවත් චරිත',6=>'දිවි මඟට එළිය දෙන දහම් පද',7=>'සීලය හා භෞතික සංවර්ධනය',8=>'දියුණුවේ හා පිරිහීමේ දොරටු',9=>'බුදු දහමින් හෙළිවන සිතීමේ හා විමසීමේ නිදහස',10=>'බෞද්ධ අනන්‍යතාව සුරකිමින් සහජීවනයෙන් ක්‍රියා කරමු',11=>'පුද්ගල විෂමතා සහ කර්මය',12=>'සසර දුකත් දුකින් මිදීමත් උගන්වන බෞද්ධ හේතුඵල ධර්මය',13=>'බුදු දහම පදනම් කරගත් ජීවන දැක්මක්',14=>'පරිසර හිතකාමී වෙමු',15=>'බුදුබණ ඇසුරෙන් නිරෝගී දිවියක්',16=>'නිමල දහම රැකුණු අයුරු',17=>'මහින්දාගමනයෙන් ශ්‍රී ලාංකික ජන ජීවිතය ඔපවත් වූ ආකාරය',18=>'හෙළ බොදු කලාවේ අසිරිය',19=>'බුදු සමයෙන් පෝෂණය වූ සිංහල සාහිත්‍යය',20=>'දැහැමි ධනය ගෙනෙයි සැපය',21=>'දැහැමි ව උපයා දැහැමි ව වැය කරමු',22=>'පාලකයන්ට මඟ කියන බුදු දහම',23=>'ලොව්තුරු සුවේ පදනම සම්මා දිට්ඨියයි'];
 $base=$lessons[0];$unitId=(int)$base['unit_id'];foreach($titles as $n=>$title){$found=null;foreach($lessons as $l)if((int)$l['display_order']===$n)$found=$l;if($found)continue;$q=$db->prepare("INSERT INTO lessons(grade_id,medium,content_source,subject_id,unit_id,title_si,short_description_si,display_order,status) VALUES(?,?,'textbook',?,?,?, ?,?,'active')");$desc='Grade 11 Buddhism lesson '.$n;$q->bind_param('isiissi',$base['grade_id'],$base['medium'],$base['subject_id'],$unitId,$title,$desc,$n);$q->execute();$new=$base;$new['id']=$db->insert_id;$new['display_order']=$n;$new['title_si']=$title;$lessons[]=$new;}
 $byOrder=[];foreach($lessons as $l)$byOrder[(int)$l['display_order']][]=$l;
 $heads=[];preg_match_all('/^\s*11\s*ශ්‍රේණිය\s*බුද්ධ\s*ධර්මය\s*(?:\x{2013}|\x{2014}|-)\s*(\d+)\s*පාඩම[^\r\n]*/imu',$raw,$hm,PREG_OFFSET_CAPTURE);
 foreach($hm[1] as $i=>$hit){$heads[]=['n'=>(int)$hit[0],'pos'=>(int)$hit[1],'start'=>strpos($raw,$hm[0][$i][0])];}
 $sections=[];$firstStart=$heads[0]['start']??strlen($raw);$sections[2]=substr($raw,0,$firstStart);
 for($i=0;$i<count($heads);$i++){$n=$heads[$i]['n'];$start=$heads[$i]['start'];$end=$heads[$i+1]['start']??strlen($raw);$sections[$n]=substr($raw,$start,$end-$start);}
 $db->begin_transaction();$total=0;$done=[];
 foreach($sections as $n=>$section){if(!isset($byOrder[$n]))continue;$items=parse_mcq_block($section);if(!$items)continue;foreach($byOrder[$n] as $lesson){$quiz=query_one('SELECT id FROM quizzes WHERE lesson_id=? ORDER BY id LIMIT 1','i',[$lesson['id']]);if($quiz)$qid=(int)$quiz['id'];else{$q=$db->prepare('INSERT INTO quizzes(grade_id,subject_id,unit_id,lesson_id,timer_minutes,pass_mark,status,title_en,title_si,title_ta) VALUES(?,?,?,?,15,50,"active",?,?,?)');$title='Lesson '.$n.' Quiz';$q->bind_param('iiiisss',$lesson['grade_id'],$lesson['subject_id'],$lesson['unit_id'],$lesson['id'],$title,$title,$title);$q->execute();$qid=(int)$db->insert_id;}
  $del=$db->prepare("DELETE FROM quiz_questions WHERE quiz_id=? AND activity_type='lesson_quiz'");$del->bind_param('i',$qid);$del->execute();$ins=$db->prepare('INSERT INTO quiz_questions(quiz_id,activity_type,question_si,option_a_si,option_b_si,option_c_si,option_d_si,correct_option,explanation_si,display_order,status) VALUES(? ,\'lesson_quiz\',?,?,?,?,?,?,?,? ,\'active\')');$ord=0;foreach($items as $x){$ord++;$ex='නිවැරදි පිළිතුර: '.strtoupper($x['correct']);$ins->bind_param('isssssssi',$qid,$x['question'],$x['a'],$x['b'],$x['c'],$x['d'],$x['correct'],$ex,$ord);$ins->execute();}$total+=count($items);$done[]='Lesson '.$n.' ('.count($items).' questions)';}
 }}$db->commit();$message='Imported: '.implode(', ',$done).'. Total '.$total.' questions.';}
}catch(Throwable $e){if($db instanceof mysqli)@$db->rollback();$error=$e->getMessage();}}
$pageTitle='Grade 11 MCQ text import';include __DIR__.'/_top.php';
?>
<section class="card"><h1>Grade 11 Buddhism · Paste MCQ importer</h1><p class="muted">Paste the full Sinhala MCQ text. Lesson headings split lessons automatically. Lesson 2 may contain 29 questions.</p><?php if($message):?><div class="alert success"><?=e($message)?></div><?php endif;?><?php if($error):?><div class="alert error"><?=e($error)?></div><?php endif;?><form method="post"><?=csrf_field()?><label>MCQ text<textarea name="raw_text" required style="min-height:520px" placeholder="Paste the Grade 11 Buddhism questions here..."></textarea></label><button type="submit">Import / replace Grade 11 quizzes</button></form></section>
<?php include __DIR__.'/../includes/footer.php';
