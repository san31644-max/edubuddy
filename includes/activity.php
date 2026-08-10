<?php
declare(strict_types=1);
function record_student_activity(int $userId,string $type,string $detail,?int $subjectId=null,?int $lessonId=null,?int $quizId=null):void{
    $allowed=['search','lesson_opened','lesson_completed','quiz_completed'];if(!in_array($type,$allowed,true))return;
    $detail=mb_substr(trim($detail),0,500);$s=db()->prepare('INSERT INTO student_activity_events(user_id,event_type,subject_id,lesson_id,quiz_id,detail) VALUES(?,?,?,?,?,?)');
    if(!$s){error_log('Activity event prepare failed: '.db()->error);return;}$s->bind_param('isiiis',$userId,$type,$subjectId,$lessonId,$quizId,$detail);if(!$s->execute())error_log('Activity event save failed: '.$s->error);
}
function latest_student_activity_notification(int $userId,string $studentName):?array{
    $event=query_one("SELECT e.id,e.event_type kind,e.detail,e.event_time,s.name_en subject,l.title_en lesson,q.title_en quiz FROM student_activity_events e LEFT JOIN subjects s ON s.id=e.subject_id LEFT JOIN lessons l ON l.id=e.lesson_id LEFT JOIN quizzes q ON q.id=e.quiz_id WHERE e.user_id=? ORDER BY e.id DESC LIMIT 1",'i',[$userId]);
    if(!$event)return null;
    $subject=trim((string)($event['subject']??''));$lesson=trim((string)($event['lesson']??''));$quiz=trim((string)($event['quiz']??''));$detail=trim((string)$event['detail']);
    $context=implode(' · ',array_values(array_filter([$subject,$lesson])));$kind=(string)$event['kind'];
    if($kind==='quiz_completed'){$score=preg_match('/(\d+(?:\.\d+)?)\s*%/',$detail,$m)?$m[1].'%':$detail;$title='Quiz result: '.$score;$body=$studentName.' completed '.($quiz?:'a quiz').($context?' · '.$context:'').'.';$icon='📝';}
    elseif($kind==='lesson_completed'){$title='Lesson completed';$body=$studentName.' completed '.($lesson?:$detail).($subject?' in '.$subject:'').'.';$icon='✅';}
    elseif($kind==='lesson_opened'){$title='Lesson started';$body=$studentName.' opened '.($lesson?:$detail).($subject?' in '.$subject:'').'.';$icon='📖';}
    else{$title='AI Tutor question';$body=$studentName.' asked: “'.mb_strimwidth($detail,0,180,'…').'”'.($context?' · '.$context:'');$icon='💬';}
    return ['id'=>(int)$event['id'],'kind'=>$kind,'detail'=>$detail,'event_time'=>$event['event_time'],'subject'=>$subject,'lesson'=>$lesson,'quiz'=>$quiz,'notification'=>['title'=>$title,'body'=>$body,'icon'=>$icon]];
}
