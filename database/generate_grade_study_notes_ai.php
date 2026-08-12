<?php
declare(strict_types=1);

ini_set('session.save_path',__DIR__.'/../includes/runtime');
require_once __DIR__.'/../includes/db.php';
require_once __DIR__.'/../includes/config.php';
require_once __DIR__.'/../includes/gemini_transport.php';

if(PHP_SAPI!=='cli'){http_response_code(403);exit("Run from the command line.\n");}
$gradeNumber=(int)($argv[1]??0);
$medium=(string)($argv[2]??'Sinhala');
$subjectFilter=trim((string)($argv[3]??''));
if($gradeNumber<1||!in_array($medium,['Sinhala','Tamil','English'],true))throw new RuntimeException('Usage: php generate_grade_study_notes_ai.php 11 Sinhala');
if(GEMINI_API_KEY==='')throw new RuntimeException('Gemini API key is not configured.');
$field=['Sinhala'=>'si','Tamil'=>'ta','English'=>'en'][$medium];
$language=['Sinhala'=>'Sinhala (Unicode)','Tamil'=>'Tamil (Unicode)','English'=>'English'][$medium];
if(strcasecmp($subjectFilter,'Second Language Tamil')===0)$language='Tamil (Unicode)';
$db=db();
$sql="SELECT l.id,l.display_order,l.title_$field title,l.content_$field content,s.name_en subject FROM lessons l JOIN subjects s ON s.id=l.subject_id JOIN grades g ON g.id=l.grade_id WHERE g.grade_number=? AND l.medium=? AND l.content_source='textbook' AND l.status='active'";
if($subjectFilter!=='')$sql.=' AND s.name_en=?';
$sql.=' ORDER BY s.name_en,l.display_order,l.id';$q=$db->prepare($sql);
if($subjectFilter!=='')$q->bind_param('iss',$gradeNumber,$medium,$subjectFilter);else $q->bind_param('is',$gradeNumber,$medium);
$q->execute();$lessons=$q->get_result()->fetch_all(MYSQLI_ASSOC);
$save=$db->prepare("UPDATE lessons SET short_notes_$field=? WHERE id=?");$done=0;$failed=0;
foreach($lessons as $lesson){
 $content=trim((string)$lesson['content']);if($content===''){echo "Skipped empty lesson {$lesson['id']}\n";continue;}
 $prompt="You are preparing accurate Grade $gradeNumber Sri Lankan school study notes. Write in $language. Use ONLY the supplied textbook extract. Do not mention that you are an AI. Do not copy page headers, exercises, garbled encoding, or unrelated fragments. Produce clear, student-friendly notes with: a short introduction; 6-10 key points; definitions/formulas/dates where relevant; and a 3-item revision checklist. Preserve mathematical symbols in Unicode and do not use LaTeX dollar signs.\n\nSubject: {$lesson['subject']}\nLesson: {$lesson['title']}\n\nTEXTBOOK EXTRACT:\n".mb_substr($content,0,50000);
 $payload=json_encode(['contents'=>[['parts'=>[['text'=>$prompt]]]],'generationConfig'=>['temperature'=>0.15,'maxOutputTokens'=>4096]],JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR);
 $notes='';$problem='';
 for($attempt=1;$attempt<=3;$attempt++){
  $r=gemini_http_json(GEMINI_API_BASE.rawurlencode(GEMINI_MODEL).':generateContent?key='.rawurlencode(GEMINI_API_KEY),['Content-Type: application/json'],$payload);
  if($r['status']===200){$body=json_decode($r['body'],true);foreach($body['candidates'][0]['content']['parts']??[] as $part)$notes.=(string)($part['text']??'');if(mb_strlen(trim($notes))>=150)break;}
  $problem=$r['error']?:('HTTP '.$r['status']);if($attempt<3)sleep(20);
 }
 if(mb_strlen(trim($notes))<150){$failed++;echo "FAILED {$lesson['subject']} / {$lesson['title']}: $problem\n";continue;}
 $notes=trim(preg_replace('/^```(?:markdown)?\s*|\s*```$/iu','',$notes)??$notes);$id=(int)$lesson['id'];$save->bind_param('si',$notes,$id);$save->execute();$done++;
 echo "Updated {$lesson['subject']} / {$lesson['title']}\n";
 sleep(4);
}
echo "Grade $gradeNumber $medium AI notes complete: $done updated, $failed failed.\n";
exit($failed?2:0);
