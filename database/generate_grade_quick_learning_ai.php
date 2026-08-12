<?php
declare(strict_types=1);

ini_set('session.save_path',__DIR__.'/../includes/runtime');
ini_set('memory_limit','512M');
set_time_limit(0);
require_once __DIR__.'/../includes/db.php';
require_once __DIR__.'/../includes/helpers.php';
require_once __DIR__.'/../includes/gemini_transport.php';

if(PHP_SAPI!=='cli'){http_response_code(403);exit("Run from the command line.\n");}
$gradeNumber=(int)($argv[1]??0);$limit=max(0,(int)($argv[2]??0));$force=in_array('--force',$argv,true);
if(!in_array($gradeNumber,[6,7,9,10],true))throw new RuntimeException('Usage: php generate_grade_quick_learning_ai.php 6 [limit] [--force]');
if(!defined('GEMINI_API_KEYS')||!GEMINI_API_KEYS)throw new RuntimeException('Gemini API keys are not configured.');

function quick_language(string $medium,string $subject):array{
 if(str_contains(mb_strtolower($subject),'second language tamil'))return ['ta','Tamil Unicode'];
 if(str_contains(mb_strtolower($subject),'second language sinhala'))return ['si','Sinhala Unicode'];
 return match($medium){'Sinhala'=>['si','Sinhala Unicode'],'Tamil'=>['ta','Tamil Unicode'],default=>['en','English']};
}
function quick_json(string $text):?array{
 $text=trim($text);$text=preg_replace('/^```(?:json)?\s*|\s*```$/iu','',$text)??$text;
 $start=strpos($text,'{');$end=strrpos($text,'}');if($start===false||$end===false||$end<$start)return null;
 $decoded=json_decode(substr($text,$start,$end-$start+1),true);return is_array($decoded)?$decoded:null;
}
function quick_clean(string $text):string{return trim(preg_replace('/^```(?:markdown)?\s*|\s*```$/iu','',$text)??$text);}
function quick_valid(array $data):bool{
 foreach(['description','notes','objectives','terms','examples','summary','questions'] as $key)if(!isset($data[$key]))return false;
 if(mb_strlen(trim((string)$data['notes']))<350||count((array)$data['questions'])!==5)return false;
 foreach($data['questions'] as $q){if(trim((string)($q['question']??''))===''||!in_array(strtolower((string)($q['correct']??'')),['a','b','c','d'],true))return false;foreach(['a','b','c','d'] as $o)if(trim((string)($q[$o]??''))==='')return false;}
 return true;
}

$db=db();$grade=query_one('SELECT id FROM grades WHERE grade_number=?','i',[$gradeNumber]);if(!$grade)throw new RuntimeException("Grade $gradeNumber is missing.");$gradeId=(int)$grade['id'];
$sql="SELECT l.*,s.subject_code,s.name_en subject FROM lessons l JOIN subjects s ON s.id=l.subject_id WHERE l.grade_id=? AND l.status='active' AND l.content_source='textbook' ORDER BY l.medium,s.name_en,l.display_order,l.id";$s=$db->prepare($sql);$s->bind_param('i',$gradeId);$s->execute();$lessons=$s->get_result()->fetch_all(MYSQLI_ASSOC);
$done=$skipped=$failed=0;
foreach($lessons as $lesson){
 if($limit>0&&$done>=$limit)break;[$field,$language]=quick_language((string)$lesson['medium'],(string)$lesson['subject']);$id=(int)$lesson['id'];
 $existing=(int)(query_one("SELECT COUNT(*) total FROM quiz_questions qq JOIN quizzes q ON q.id=qq.quiz_id WHERE q.lesson_id=? AND q.status='active' AND qq.activity_type='challenge' AND qq.status='active'",'i',[$id])['total']??0);
 if(!$force&&$existing===5&&mb_strlen(trim((string)($lesson['short_notes_'.$field]??'')))>=350){$skipped++;continue;}
 $title=trim((string)($lesson['title_'.$field]?:$lesson['title_en']?:('Lesson '.$lesson['display_order'])));$content=trim((string)($lesson['content_'.$field]?:$lesson['short_notes_'.$field]?:$lesson['content_en']?:''));if($content===''){echo "FAILED $id: empty source\n";$failed++;continue;}
 $source=mb_substr($content,0,3500);$prompt="Return compact JSON only in $language for a Grade $gradeNumber Sri Lankan lesson, using only the extract. Keys: description; notes (opening + 8 specific bullets + 3-item checklist); objectives (4 lines); terms (8 comma items); examples (3 lines); summary (5 bullets); questions (exactly 5 objects: question,a,b,c,d,correct,explanation). Make it clear, warm and lesson-specific. Test different key ideas; plausible wrong options. Ignore OCR noise.\nSubject: {$lesson['subject']}\nLesson: $title\nEXTRACT:\n$source";
 $payload=json_encode(['contents'=>[['parts'=>[['text'=>$prompt]]]],'generationConfig'=>['temperature'=>0.15,'maxOutputTokens'=>3000,'responseMimeType'=>'application/json']],JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR);$data=null;$problem='';
 for($attempt=1;$attempt<=3;$attempt++){$response=gemini_http_json(GEMINI_API_BASE.rawurlencode(GEMINI_MODEL).':generateContent?key='.rawurlencode(GEMINI_API_KEY),['Content-Type: application/json'],$payload);if($response['status']===200){$body=json_decode($response['body'],true);$text='';foreach($body['candidates'][0]['content']['parts']??[] as $part)$text.=(string)($part['text']??'');$candidate=quick_json($text);if($candidate&&quick_valid($candidate)){$data=$candidate;break;}$problem='Gemini returned incomplete lesson JSON.';}else $problem=(string)($response['error']?:('HTTP '.$response['status']));if($attempt<3)sleep(4);}
 if(!$data){echo "FAILED {$lesson['medium']} / {$lesson['subject']} / $title: $problem\n";$failed++;continue;}
 $db->begin_transaction();try{
  foreach(['description','notes','objectives','terms','examples','summary'] as $key)$data[$key]=quick_clean((string)$data[$key]);
  $update=$db->prepare("UPDATE lessons SET short_description_$field=?,short_notes_$field=?,learning_objectives_$field=?,key_terms_$field=?,examples_$field=?,summary_$field=? WHERE id=?");$update->bind_param('ssssssi',$data['description'],$data['notes'],$data['objectives'],$data['terms'],$data['examples'],$data['summary'],$id);if(!$update->execute())throw new RuntimeException($update->error);
  $quiz=query_one('SELECT id FROM quizzes WHERE lesson_id=? AND status="active" ORDER BY id LIMIT 1','i',[$id]);if($quiz)$quizId=(int)$quiz['id'];else{$add=$db->prepare('INSERT INTO quizzes(grade_id,subject_id,unit_id,lesson_id,timer_minutes,pass_mark,status) VALUES(?,?,?,?,10,50,"active")');$subjectId=(int)$lesson['subject_id'];$unitId=(int)$lesson['unit_id'];$add->bind_param('iiii',$gradeId,$subjectId,$unitId,$id);if(!$add->execute())throw new RuntimeException($add->error);$quizId=(int)$db->insert_id;}
  $db->query("DELETE qa FROM quiz_answers qa JOIN quiz_questions qq ON qq.id=qa.question_id WHERE qq.quiz_id=$quizId AND qq.activity_type='challenge'");$delete=$db->prepare("DELETE FROM quiz_questions WHERE quiz_id=? AND activity_type='challenge'");$delete->bind_param('i',$quizId);if(!$delete->execute())throw new RuntimeException($delete->error);
  $insert=$db->prepare("INSERT INTO quiz_questions(quiz_id,activity_type,question_$field,option_a_$field,option_b_$field,option_c_$field,option_d_$field,correct_option,explanation_$field,display_order,status) VALUES(?,'challenge',?,?,?,?,?,?,?,?,'active')");
  foreach($data['questions'] as $index=>$q){$question=quick_clean((string)$q['question']);$a=quick_clean((string)$q['a']);$b=quick_clean((string)$q['b']);$c=quick_clean((string)$q['c']);$d=quick_clean((string)$q['d']);$correct=strtolower((string)$q['correct']);$explanation=quick_clean((string)$q['explanation']);$order=$index+1;$insert->bind_param('isssssssi',$quizId,$question,$a,$b,$c,$d,$correct,$explanation,$order);if(!$insert->execute())throw new RuntimeException($insert->error);}
  $db->commit();$done++;echo "UPDATED [$done] {$lesson['medium']} / {$lesson['subject']} / $title\n";
 }catch(Throwable $error){$db->rollback();echo "FAILED save $id: {$error->getMessage()}\n";$failed++;}
}
echo "Grade $gradeNumber quick learning complete: $done updated, $skipped already ready, $failed failed.\n";exit($failed?2:0);
