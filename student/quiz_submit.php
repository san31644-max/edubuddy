<?php
require_once __DIR__.'/../includes/auth.php';require_login();
if($_SERVER['REQUEST_METHOD']!=='POST')redirect('student/quiz.php');verify_csrf();
$qid=filter_input(INPUT_POST,'quiz_id',FILTER_VALIDATE_INT);
$q=$qid?query_one("SELECT q.* FROM quizzes q JOIN lessons l ON l.id=q.lesson_id WHERE q.id=? AND q.grade_id=? AND q.status='active' AND l.status='active' AND l.content_source='textbook' AND (l.medium='All' OR l.medium=?)",'iis',[$qid,user()['grade_id'],user()['medium']]):null;
if(!$q)redirect('student/quiz.php');
$questionType=user_grade_number()===8?'challenge':'lesson_quiz';
$s=db()->prepare("SELECT id,correct_option FROM quiz_questions WHERE quiz_id=? AND activity_type=? AND status='active' ORDER BY display_order,id LIMIT 30");$s->bind_param('is',$qid,$questionType);$s->execute();$qs=$s->get_result()->fetch_all(MYSQLI_ASSOC);
$answers=$_POST['answers']??[];$score=0;foreach($qs as $x)if(($answers[$x['id']]??'')===$x['correct_option'])$score++;
$total=count($qs);$percent=$total?(int)round($score*100/$total):0;$passed=$percent>=$q['pass_mark']?1:0;$db=db();$db->begin_transaction();
try{
    $s=$db->prepare('INSERT INTO quiz_attempts(user_id,quiz_id,score,total_questions,percentage,passed,completed_at) VALUES(?,?,?,?,?,?,NOW())');$uid=(int)user()['id'];$s->bind_param('iiiiii',$uid,$qid,$score,$total,$percent,$passed);$s->execute();$aid=(int)$db->insert_id;
    $a=$db->prepare('INSERT INTO quiz_answers(attempt_id,question_id,selected_option,is_correct) VALUES(?,?,?,?)');
    foreach($qs as $x){$sel=in_array(($answers[$x['id']]??''),['a','b','c','d'],true)?$answers[$x['id']]:null;$ok=$sel===$x['correct_option']?1:0;$a->bind_param('iisi',$aid,$x['id'],$sel,$ok);$a->execute();}
    $perfect=$total>0&&$score===$total;$earned=reward_quiz_attempt($uid,$q,$percent);record_student_activity($uid,'quiz_completed','Scored '.$percent.'% · '.($perfect?'Perfect score':'Retry needed'),(int)$q['subject_id'],isset($q['lesson_id'])?(int)$q['lesson_id']:null,$qid);
    if($perfect&&!empty($q['lesson_id'])){
        $lessonId=(int)$q['lesson_id'];$wasDone=query_one('SELECT id FROM lesson_progress WHERE user_id=? AND lesson_id=? AND completed_at IS NOT NULL','ii',[$uid,$lessonId]);
        $p=$db->prepare('INSERT INTO lesson_progress(user_id,lesson_id,opened_at,last_opened_at,completed_at) VALUES(?,?,NOW(),NOW(),NOW()) ON DUPLICATE KEY UPDATE last_opened_at=NOW(),completed_at=COALESCE(completed_at,NOW())');$p->bind_param('ii',$uid,$lessonId);$p->execute();
        if(!$wasDone){$lessonData=['id'=>$lessonId,'grade_id'=>(int)$q['grade_id'],'subject_id'=>(int)$q['subject_id']];$earned+=reward_lesson_completion($uid,$lessonData);record_student_activity($uid,'lesson_completed','Completed with a perfect quiz score',(int)$q['subject_id'],$lessonId,$qid);}
    }
    $db->commit();
    if($earned)flash('success','Quiz saved! +'.$earned.' new points earned.');
    redirect('student/results.php?id='.$aid);
}catch(Throwable $e){$db->rollback();error_log('Quiz save error: '.$e->getMessage());flash('error','Quiz result could not be saved.');redirect('student/quiz.php?id='.$qid);}
