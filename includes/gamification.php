<?php
declare(strict_types=1);

function award_points(int $userId,int $gradeId,?int $subjectId,?int $lessonId,?int $quizId,string $type,int $points,string $key,string $description):bool
{
    $s=db()->prepare('INSERT IGNORE INTO student_points(user_id,grade_id,subject_id,lesson_id,quiz_id,activity_type,points,award_key,description) VALUES(?,?,?,?,?,?,?,?,?)');
    if(!$s)return false;
    $s->bind_param('iiiiisiss',$userId,$gradeId,$subjectId,$lessonId,$quizId,$type,$points,$key,$description);
    $s->execute();$awarded=$s->affected_rows===1;$s->close();return $awarded;
}

function award_emblem(int $userId,int $gradeId,int $subjectId,int $lessonId,string $tier):void
{
    $s=db()->prepare('INSERT IGNORE INTO student_emblems(user_id,grade_id,subject_id,lesson_id,tier) VALUES(?,?,?,?,?)');
    if($s){$s->bind_param('iiiis',$userId,$gradeId,$subjectId,$lessonId,$tier);$s->execute();$s->close();}
}

function reward_lesson_completion(int $userId,array $lesson):int
{
    $earned=award_points($userId,(int)$lesson['grade_id'],(int)$lesson['subject_id'],(int)$lesson['id'],null,'lesson_complete',20,'lesson:'.$lesson['id'].':complete','Lesson completed')?20:0;
    award_emblem($userId,(int)$lesson['grade_id'],(int)$lesson['subject_id'],(int)$lesson['id'],'bronze');
    $best=query_one('SELECT MAX(qa.percentage) percentage FROM quiz_attempts qa JOIN quizzes q ON q.id=qa.quiz_id WHERE qa.user_id=? AND q.lesson_id=?','ii',[$userId,(int)$lesson['id']]);
    $percentage=(int)($best['percentage']??0);
    if($percentage>=70)award_emblem($userId,(int)$lesson['grade_id'],(int)$lesson['subject_id'],(int)$lesson['id'],'silver');
    if($percentage>=85)award_emblem($userId,(int)$lesson['grade_id'],(int)$lesson['subject_id'],(int)$lesson['id'],'gold');
    if($percentage>=95)award_emblem($userId,(int)$lesson['grade_id'],(int)$lesson['subject_id'],(int)$lesson['id'],'master');
    return $earned;
}

function reward_quiz_attempt(int $userId,array $quiz,int $percentage):int
{
    $earned=award_points($userId,(int)$quiz['grade_id'],(int)$quiz['subject_id'],$quiz['lesson_id']?(int)$quiz['lesson_id']:null,(int)$quiz['id'],'quiz_attempt',10,'quiz:'.$quiz['id'].':first','First quiz attempt')?10:0;
    foreach([50,70,85,95] as $threshold){
        if($percentage>=$threshold&&award_points($userId,(int)$quiz['grade_id'],(int)$quiz['subject_id'],$quiz['lesson_id']?(int)$quiz['lesson_id']:null,(int)$quiz['id'],'quiz_score',10,'quiz:'.$quiz['id'].':score:'.$threshold,'Quiz score reached '.$threshold.'%'))$earned+=10;
    }
    if(!empty($quiz['lesson_id'])){
        $done=query_one('SELECT id FROM lesson_progress WHERE user_id=? AND lesson_id=? AND completed_at IS NOT NULL','ii',[$userId,(int)$quiz['lesson_id']]);
        if($done){
            if($percentage>=70)award_emblem($userId,(int)$quiz['grade_id'],(int)$quiz['subject_id'],(int)$quiz['lesson_id'],'silver');
            if($percentage>=85)award_emblem($userId,(int)$quiz['grade_id'],(int)$quiz['subject_id'],(int)$quiz['lesson_id'],'gold');
            if($percentage>=95)award_emblem($userId,(int)$quiz['grade_id'],(int)$quiz['subject_id'],(int)$quiz['lesson_id'],'master');
        }
    }
    return $earned;
}
