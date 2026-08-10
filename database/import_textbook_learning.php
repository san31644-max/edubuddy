<?php
declare(strict_types=1);
require_once __DIR__.'/../includes/db.php';
require_once __DIR__.'/../includes/helpers.php';

function sentences(string $text):array{
    $text=preg_replace('/\s+/u',' ',str_replace(['For free distribution','For Free Distribution'],'',$text))??$text;
    $parts=preg_split('/(?<=[.!?])\s+/u',$text)?:[];
    return array_values(array_filter(array_map('trim',$parts),fn($s)=>mb_strlen($s)>=55&&mb_strlen($s)<=300&&!preg_match('/^(Activity|Exercise|Figure|Fig\.)\s*\d*/i',$s)));
}
function spread(array $items,int $count):array{
    if(count($items)<=$count)return $items;$out=[];$last=count($items)-1;
    for($i=0;$i<$count;$i++)$out[]=$items[(int)round($i*$last/max(1,$count-1))];
    return array_values(array_unique($out));
}
function terms(array $sentences,int $count=14):array{
    $stop=array_flip(explode(' ','about after again also among because before being below between both can could during each from further have having into itself more most other over same should some such than that their them then there these they this those through under very what when where which while will with would your grade lesson activity figure distribution free using used called known shown following'));$freq=[];
    foreach($sentences as $s){preg_match_all('/[\p{L}][\p{L}\p{M}\-]{3,}/u',mb_strtolower($s),$m);foreach($m[0] as $w)if(!isset($stop[$w]))$freq[$w]=($freq[$w]??0)+1;}
    arsort($freq);return array_slice(array_keys($freq),0,$count);
}
function notes_and_quiz(array $payload,string $title,string $language='en'):array{
    $all=[];foreach($payload['chunks']??[] as $chunk)$all=array_merge($all,sentences((string)($chunk['text']??'')));
    $points=spread($all,8);$keywords=terms($all,18);
    $summary=implode("\n",array_map(fn($s)=>'- '.$s,array_slice($points,0,6)));
    $content=($language==='si'?"කෙටි සටහන්":($language==='ta'?'சுருக்கக் குறிப்புகள்':'SHORT NOTES'))."\n\n";foreach($points as $i=>$s)$content.=($i+1).'. '.$s."\n\n";
    $examples=implode("\n",array_map(fn($s)=>'- '.$s,array_slice(array_values(array_filter($all,fn($s)=>preg_match('/\b(example|activity|observe|consider|such as|for instance)\b/i',$s))),0,4)));
    if($examples==='')$examples=implode("\n",array_map(fn($s)=>'- '.$s,array_slice($points,-3)));
    $questions=[];
    foreach($all as $sentence){
        if(count($questions)>=5)break;
        $correct=null;foreach($keywords as $term){if(mb_strlen($term)>=5&&preg_match('/(?<![\p{L}\p{M}])'.preg_quote($term,'/').'(?![\p{L}\p{M}])/iu',$sentence)){$correct=$term;break;}}
        if(!$correct||isset($questions[$correct]))continue;
        $distractors=array_values(array_filter($keywords,fn($x)=>$x!==$correct&&mb_strlen($x)>=4));
        if(count($distractors)<3)continue;
        $offset=abs(crc32($title.$correct))%count($distractors);$wrong=[];
        for($i=0;$i<count($distractors)&&count($wrong)<3;$i++)$wrong[]=$distractors[($offset+$i)%count($distractors)];
        $options=array_values(array_unique(array_merge([$correct],$wrong)));if(count($options)<4)continue;
        usort($options,fn($a,$b)=>strcmp(hash('sha256',$title.$correct.$a),hash('sha256',$title.$correct.$b)));
        $correctIndex=array_search($correct,$options,true);$blank=preg_replace('/(?<![\p{L}\p{M}])'.preg_quote($correct,'/').'(?![\p{L}\p{M}])/iu','_____',$sentence,1);
        $prompt=$language==='si'?'පෙළපොතේ ප්‍රකාශය සම්පූර්ණ කරන්න: ':($language==='ta'?'பாடநூல் கூற்றை நிறைவு செய்யுங்கள்: ':'Complete this textbook statement: ');
        $questions[$correct]=['question'=>$prompt.$blank,'options'=>$options,'correct'=>['a','b','c','d'][(int)$correctIndex],'explanation'=>$sentence];
    }
    foreach($points as $i=>$sentence){
        if(count($questions)>=5)break;
        if($language==='si')$questions['fallback'.$i]=['question'=>'"'.$title.'" පාඩමේ ඇතුළත් නිවැරදි ප්‍රකාශය කුමක්ද?','options'=>[$sentence,'මෙම පාඩමේ ඉගෙනුම් ක්‍රියාකාරකම් නොමැත.','මෙම පාඩමේ සියලු ප්‍රකාශ වැරදියි.','මෙම පාඩම විෂයට සම්බන්ධ නැත.'],'correct'=>'a','explanation'=>$sentence];
        elseif($language==='ta')$questions['fallback'.$i]=['question'=>'"'.$title.'" பாடத்தில் இடம்பெறும் சரியான கூற்று எது?','options'=>[$sentence,'இந்தப் பாடத்தில் கற்றல் செயல்பாடுகள் இல்லை.','இந்தப் பாடத்தின் எல்லாக் கூற்றுகளும் தவறானவை.','இந்தக் கூற்று பாடத்துடன் தொடர்பற்றது.'],'correct'=>'a','explanation'=>$sentence];
        else $questions['fallback'.$i]=['question'=>'Which statement is included in the lesson "'.$title.'"?','options'=>[$sentence,'This lesson contains no learning activities.','Every statement in this lesson is false.','The lesson has no connection to the subject.'],'correct'=>'a','explanation'=>$sentence];
    }
    return ['description'=>$points[0]??('Study notes for '.$title.'.'),'content'=>trim($content),'summary'=>$summary,'terms'=>implode(', ',array_slice($keywords,0,12)),'examples'=>$examples,'questions'=>array_values(array_slice($questions,0,5))];
}

if(defined('TEXTBOOK_IMPORT_LIBRARY_ONLY'))return;
$catalogPath=__DIR__.'/../uploads/syllabus/textbook-cache/catalog.json';$catalog=json_decode((string)file_get_contents($catalogPath),true,512,JSON_THROW_ON_ERROR);$db=db();
$column=$db->query("SHOW COLUMNS FROM lessons LIKE 'content_source'");
if(!$column||$column->num_rows===0)$db->query("ALTER TABLE lessons ADD content_source VARCHAR(30) NOT NULL DEFAULT 'manual' AFTER medium");
$db->query("INSERT INTO subjects(grade_id,subject_code,name_en,name_si,name_ta,description_en,description_si,description_ta,icon,status) SELECT 1,'BUD','Buddhism','බුද්ධ ධර්මය','பௌத்தம்','Buddhist learning and values.','බෞද්ධ ඉගෙනුම හා සාරධර්ම.','பௌத்த கற்றலும் விழுமியங்களும்.','☸️','active' WHERE NOT EXISTS(SELECT 1 FROM subjects WHERE grade_id=1 AND name_en='Buddhism')");
$db->query("INSERT INTO subjects(grade_id,subject_code,name_en,name_si,name_ta,description_en,description_si,description_ta,icon,status) SELECT 1,'PTS','Practical and Technical Skills','ප්‍රායෝගික හා තාක්ෂණික කුසලතා','நடைமுறை தொழில்நுட்பத் திறன்கள்','Practical, agricultural and technical skills.','ප්‍රායෝගික, කෘෂිකාර්මික හා තාක්ෂණික කුසලතා.','நடைமுறை, விவசாய மற்றும் தொழில்நுட்பத் திறன்கள்.','🛠️','active' WHERE NOT EXISTS(SELECT 1 FROM subjects WHERE grade_id=1 AND name_en='Practical and Technical Skills')");
$subjects=[];foreach($db->query('SELECT id,name_en FROM subjects WHERE grade_id=1') as $r)$subjects[mb_strtolower($r['name_en'])]=(int)$r['id'];
$lessonCount=$quizCount=$questionCount=0;$db->begin_transaction();
try{
 foreach(['en'=>'English','si'=>'Sinhala','ta'=>'Tamil'] as $language=>$medium){foreach($catalog[$language]??[] as $slug=>$book){
  $subjectId=$subjects[mb_strtolower((string)$book['subject'])]??0;if(!$subjectId)continue;
  foreach($book['lessons'] as $entry){$number=(int)$entry['number'];$title=(string)$entry['title'];$file=__DIR__.'/../uploads/syllabus/textbook-cache/'.$language.'/'.$slug.'/lesson-'.$number.'.json';if(!is_file($file))continue;
   $study=notes_and_quiz(json_decode((string)file_get_contents($file),true,512,JSON_THROW_ON_ERROR),$title,$language);
   $unit=query_one('SELECT id FROM units WHERE subject_id=? AND unit_number=?','ii',[$subjectId,$number]);
   if(!$unit){$s=$db->prepare('INSERT INTO units(grade_id,subject_id,unit_number,name_en,name_si,name_ta,description_en,display_order) VALUES(1,?,?,?,?,?,?,?)');$en=$language==='en'?$title:'Lesson '.$number;$si=$language==='si'?$title:$en;$ta=$language==='ta'?$title:$en;$desc=$study['description'];$s->bind_param('iissssi',$subjectId,$number,$en,$si,$ta,$desc,$number);$s->execute();$unitId=(int)$db->insert_id;}
   else{$unitId=(int)$unit['id'];if($language==='en'){$s=$db->prepare('UPDATE units SET name_en=?,description_en=?,display_order=?,status="active" WHERE id=?');$s->bind_param('ssii',$title,$study['description'],$number,$unitId);$s->execute();}else{$nameField=$language==='ta'?'name_ta':'name_si';$s=$db->prepare("UPDATE units SET $nameField=? WHERE id=?");$s->bind_param('si',$title,$unitId);$s->execute();}}
   $lesson=query_one('SELECT id FROM lessons WHERE unit_id=? AND medium=? ORDER BY id LIMIT 1','is',[$unitId,$medium]);
   if(!$lesson&&$language==='en')$lesson=query_one("SELECT id FROM lessons WHERE unit_id=? AND medium='All' ORDER BY id LIMIT 1",'i',[$unitId]);
   if($lesson){$lessonId=(int)$lesson['id'];}else{$s=$db->prepare('INSERT INTO lessons(grade_id,medium,subject_id,unit_id,display_order,status) VALUES(1,?,?,?,?,"active")');$s->bind_param('siii',$medium,$subjectId,$unitId,$number);$s->execute();$lessonId=(int)$db->insert_id;}
   $field=['si'=>'si','ta'=>'ta'][$language]??'en';$sql="UPDATE lessons SET medium=?,content_source='textbook',title_$field=?,short_description_$field=?,content_$field=?,learning_objectives_$field=?,key_terms_$field=?,examples_$field=?,summary_$field=?,display_order=?,status='active' WHERE id=?";$objectives=$language==='si'?"මෙම පාඩමේ ප්‍රධාන අදහස් තේරුම් ගැනීම.\nප්‍රධාන කරුණු පැහැදිලි කිරීම.\nපුනරීක්ෂණ ක්‍රියාකාරකම්වලදී දැනුම භාවිත කිරීම.":($language==='ta'?"இந்தப் பாடத்தின் முக்கிய கருத்துகளைப் புரிந்துகொள்ளுதல்.\nமுக்கிய விடயங்களை விளக்குதல்.\nமீளாய்வுச் செயல்பாடுகளில் பாட அறிவைப் பயன்படுத்துதல்.":"Understand $title.\nExplain its main ideas.\nUse the lesson knowledge in revision activities.");$s=$db->prepare($sql);$s->bind_param('ssssssssii',$medium,$title,$study['description'],$study['content'],$objectives,$study['terms'],$study['examples'],$study['summary'],$number,$lessonId);$s->execute();$lessonCount++;
   $quiz=query_one('SELECT id FROM quizzes WHERE lesson_id=? ORDER BY id LIMIT 1','i',[$lessonId]);if(!$quiz){$quiz=query_one('SELECT id FROM quizzes WHERE unit_id=? AND lesson_id IS NULL ORDER BY id LIMIT 1','i',[$unitId]);}
   if($quiz)$quizId=(int)$quiz['id'];else{$s=$db->prepare('INSERT INTO quizzes(grade_id,subject_id,unit_id,lesson_id,timer_minutes,pass_mark,status) VALUES(1,?,?,?,?,50,"active")');$timer=10;$s->bind_param('iiii',$subjectId,$unitId,$lessonId,$timer);$s->execute();$quizId=(int)$db->insert_id;}
   $quizTitle=$title.' Revision Quiz';$sql="UPDATE quizzes SET lesson_id=?,unit_id=?,title_$field=?,timer_minutes=10,pass_mark=50,status='active' WHERE id=?";$s=$db->prepare($sql);$s->bind_param('iisi',$lessonId,$unitId,$quizTitle,$quizId);$s->execute();$quizCount++;
   $existing=[];$qs=$db->prepare('SELECT id FROM quiz_questions WHERE quiz_id=? ORDER BY display_order,id');$qs->bind_param('i',$quizId);$qs->execute();foreach($qs->get_result() as $row)$existing[]=(int)$row['id'];
   foreach($study['questions'] as $i=>$question){$opts=$question['options'];$position=$i+1;if(isset($existing[$i])){$questionId=$existing[$i];$sql="UPDATE quiz_questions SET question_$field=?,option_a_$field=?,option_b_$field=?,option_c_$field=?,option_d_$field=?,correct_option=?,explanation_$field=?,display_order=?,status='active' WHERE id=?";$s=$db->prepare($sql);$s->bind_param('sssssssii',$question['question'],$opts[0],$opts[1],$opts[2],$opts[3],$question['correct'],$question['explanation'],$position,$questionId);$s->execute();}else{$sql="INSERT INTO quiz_questions(quiz_id,question_$field,option_a_$field,option_b_$field,option_c_$field,option_d_$field,correct_option,explanation_$field,display_order,status) VALUES(?,?,?,?,?,?,?,?,?,'active')";$s=$db->prepare($sql);$s->bind_param('isssssssi',$quizId,$question['question'],$opts[0],$opts[1],$opts[2],$opts[3],$question['correct'],$question['explanation'],$position);$s->execute();}$questionCount++;}
  }
 }}
 $db->commit();echo "Imported/updated $lessonCount lessons, $quizCount quizzes and $questionCount questions.\n";
}catch(Throwable $e){$db->rollback();throw $e;}
