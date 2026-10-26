<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli')exit;
require_once __DIR__.'/../includes/auth.php';
require_once __DIR__.'/../includes/student_dashboard_data.php';
mysqli_report(MYSQLI_REPORT_ERROR|MYSQLI_REPORT_STRICT);
function dashboard_expect(bool $condition,string $message):void{if(!$condition)throw new RuntimeException($message);}
$db=db();$db->begin_transaction();
try {
    $lesson=$db->query("SELECT l.* FROM lessons l WHERE status='active' AND content_source='textbook' AND medium IN ('English','All') ORDER BY grade_id,subject_id,display_order,id LIMIT 1")->fetch_assoc();
    dashboard_expect((bool)$lesson,'A sample lesson is required');
    $grade=(int)$lesson['grade_id'];$name='Temporary dashboard verification';$username='dashcheck_'.bin2hex(random_bytes(5));$hash=password_hash(bin2hex(random_bytes(12)),PASSWORD_DEFAULT);
    $insert=$db->prepare("INSERT INTO users(full_name,username,password_hash,grade_id,medium,status) VALUES(?,?,?,?,'English','active')");$insert->bind_param('sssi',$name,$username,$hash,$grade);$insert->execute();$id=(int)$db->insert_id;
    $_SESSION['user']=['id'=>$id,'grade_id'=>$grade,'medium'=>'English','subscription_expires_at'=>null];
    $empty=student_dashboard_snapshot(user());
    dashboard_expect($empty['level']===1&&$empty['level_progress']===0&&$empty['streak']===0,'New student should have no invented XP or streak');
    dashboard_expect($empty['completed']===0&&$empty['last_quiz']===null,'New student should have no invented learning results');
    dashboard_expect(count(array_filter($empty['trail'],fn($row)=>!$row['locked']))===1,'Free student should have only one unlocked lesson in this trail');
    dashboard_expect($empty['next']&&!$empty['next']['locked'],'Recommended free lesson must be accessible');
    award_points($id,$grade,null,null,null,'verification',249,'dashboard:249','Temporary dashboard verification');
    $almost=student_dashboard_snapshot(user());dashboard_expect($almost['level']===1&&$almost['level_progress']===249,'Level threshold is incorrect');
    award_points($id,$grade,null,null,null,'verification',1,'dashboard:250','Temporary dashboard verification');
    $level=student_dashboard_snapshot(user());dashboard_expect($level['level']===2&&$level['level_progress']===0,'250 points must reach level two');
    $lessonId=(int)$lesson['id'];$subjectId=(int)$lesson['subject_id'];
    $statement=$db->prepare('INSERT INTO lesson_progress(user_id,lesson_id,opened_at,last_opened_at,completed_at) VALUES(?,?,NOW(),NOW(),NOW())');$statement->bind_param('ii',$id,$lessonId);$statement->execute();
    foreach([0,1,3] as $days) {
        $date=date('Y-m-d H:i:s',strtotime('-'.$days.' days'));
        $statement=$db->prepare("INSERT INTO student_activity_events(user_id,event_type,lesson_id,detail,event_time) VALUES(?,'lesson_opened',?,'Temporary dashboard verification',?)");$statement->bind_param('iis',$id,$lessonId,$date);$statement->execute();
    }
    award_emblem($id,$grade,$subjectId,$lessonId,'bronze');
    $active=student_dashboard_snapshot(user());
    dashboard_expect($active['completed']===1,'Completed lesson count is incorrect');
    dashboard_expect($active['streak']===2,'Missing day must break the streak');
    dashboard_expect((int)$active['today']['opened']===1&&(int)$active['today']['completed']===1&&(int)$active['today']['quizzes']===0,'Daily missions must match recorded activity');
    dashboard_expect(($active['emblems']['bronze']??0)===1,'Earned emblems must come from real records');
    $_SESSION['user']['subscription_expires_at']=date('Y-m-d H:i:s',strtotime('+1 day'));
    $paid=student_dashboard_snapshot(user());dashboard_expect(!array_filter($paid['trail'],fn($row)=>$row['locked']),'Premium lesson trail should be unlocked');
    echo "PASS: new-user states, XP level boundaries, real streaks and missions, earned badges, free/premium lesson links.\n";
}finally{$db->rollback();session_destroy();}
echo "STUDENT_DASHBOARD_CHECKS_PASSED\n";
