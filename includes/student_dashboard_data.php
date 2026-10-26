<?php
declare(strict_types=1);

function dashboard_rows(string $sql,string $types,array $args):array
{
    $statement=db()->prepare($sql);
    if(!$statement)throw new RuntimeException('Dashboard data could not be loaded.');
    $statement->bind_param($types,...$args);$statement->execute();
    return $statement->get_result()->fetch_all(MYSQLI_ASSOC);
}

function student_dashboard_snapshot(array $student):array
{
    $uid=(int)$student['id'];$grade=(int)$student['grade_id'];$medium=(string)$student['medium'];
    sync_completion_emblems($uid);
    $stats=query_one("SELECT COUNT(*) opened,COALESCE(SUM(lp.completed_at IS NOT NULL OR EXISTS(SELECT 1 FROM student_points sp WHERE sp.user_id=? AND sp.lesson_id=l.id AND sp.activity_type='lesson_complete')),0) completed FROM lesson_progress lp JOIN lessons l ON l.id=lp.lesson_id WHERE lp.user_id=? AND l.grade_id=? AND l.content_source='textbook' AND l.status='active' AND (l.medium='All' OR l.medium=?)",'iiis',[$uid,$uid,$grade,$medium])??[];
    $total=(int)(query_one("SELECT COUNT(*) total FROM lessons WHERE grade_id=? AND status='active' AND content_source='textbook' AND (medium='All' OR medium=?)",'is',[$grade,$medium])['total']??0);
    $points=(int)(query_one('SELECT COALESCE(SUM(points),0) total FROM student_points WHERE user_id=?','i',[$uid])['total']??0);
    $last=query_one('SELECT percentage FROM quiz_attempts WHERE user_id=? ORDER BY completed_at DESC,id DESC LIMIT 1','i',[$uid]);
    $subjects=dashboard_rows("SELECT s.id,s.icon,s.name_en,s.name_si,s.name_ta,COUNT(l.id) lessons,COALESCE(SUM(EXISTS(SELECT 1 FROM lesson_progress lp WHERE lp.user_id=? AND lp.lesson_id=l.id AND lp.completed_at IS NOT NULL) OR EXISTS(SELECT 1 FROM student_points sp WHERE sp.user_id=? AND sp.lesson_id=l.id AND sp.activity_type='lesson_complete')),0) completed FROM subjects s JOIN lessons l ON l.subject_id=s.id AND l.grade_id=s.grade_id AND l.status='active' AND l.content_source='textbook' AND (l.medium='All' OR l.medium=?) WHERE s.grade_id=? AND s.status='active' GROUP BY s.id ORDER BY s.name_en",'iisi',[$uid,$uid,$medium,$grade]);
    $recent=dashboard_rows("SELECT l.id,l.subject_id,l.title_en,l.title_si,l.title_ta,s.name_en,s.name_si,s.name_ta FROM lesson_progress lp JOIN lessons l ON l.id=lp.lesson_id JOIN subjects s ON s.id=l.subject_id WHERE lp.user_id=? AND l.grade_id=? AND l.status='active' AND l.content_source='textbook' AND (l.medium='All' OR l.medium=?) ORDER BY lp.last_opened_at DESC,lp.id DESC LIMIT 3",'iis',[$uid,$grade,$medium]);
    $subjectId=(int)($recent[0]['subject_id']??$subjects[0]['id']??0);
    $trail=$subjectId?dashboard_rows("SELECT l.id,l.subject_id,l.display_order,l.title_en,l.title_si,l.title_ta,lp.opened_at,(lp.completed_at IS NOT NULL OR EXISTS(SELECT 1 FROM student_points sp WHERE sp.user_id=? AND sp.lesson_id=l.id AND sp.activity_type='lesson_complete')) completed FROM lessons l LEFT JOIN lesson_progress lp ON lp.lesson_id=l.id AND lp.user_id=? WHERE l.subject_id=? AND l.grade_id=? AND l.status='active' AND l.content_source='textbook' AND (l.medium='All' OR l.medium=?) ORDER BY l.display_order,l.id LIMIT 5",'iiiis',[$uid,$uid,$subjectId,$grade,$medium]):[];
    $next=null;
    foreach($trail as &$lesson){$lesson['locked']=!can_access_lesson((int)$lesson['id']);if(!$next&&!$lesson['locked']&&!$lesson['completed'])$next=$lesson;}
    unset($lesson);
    if(!$next)foreach($trail as $lesson)if(!$lesson['locked']){$next=$lesson;break;}
    $start=date('Y-m-d').' 00:00:00';$end=date('Y-m-d',strtotime('+1 day')).' 00:00:00';
    $today=query_one("SELECT (SELECT COUNT(DISTINCT lesson_id) FROM student_activity_events WHERE user_id=? AND event_type='lesson_opened' AND event_time>=? AND event_time<?) opened,(SELECT COUNT(*) FROM lesson_progress WHERE user_id=? AND completed_at>=? AND completed_at<?) completed,(SELECT COUNT(*) FROM quiz_attempts WHERE user_id=? AND completed_at>=? AND completed_at<?) quizzes",'issississ',[$uid,$start,$end,$uid,$start,$end,$uid,$start,$end])??[];
    $activeRows=dashboard_rows("SELECT DISTINCT DATE(event_time) active_date FROM student_activity_events WHERE user_id=? AND event_type IN ('lesson_opened','lesson_completed','quiz_completed') ORDER BY active_date DESC",'i',[$uid]);
    $activeDays=array_fill_keys(array_column($activeRows,'active_date'),true);
    $day=isset($activeDays[date('Y-m-d')])?date('Y-m-d'):date('Y-m-d',strtotime('-1 day'));
    $streak=0;while(isset($activeDays[$day])){$streak++;$day=date('Y-m-d',strtotime($day.' -1 day'));}
    $week=[];$monday=strtotime('monday this week');
    for($i=0;$i<7;$i++){$date=date('Y-m-d',strtotime('+'.$i.' days',$monday));$week[]=['label'=>date('D',strtotime($date)),'date'=>$date,'active'=>isset($activeDays[$date]),'today'=>$date===date('Y-m-d')];}
    $emblems=dashboard_rows('SELECT tier,COUNT(*) total FROM student_emblems WHERE user_id=? GROUP BY tier','i',[$uid]);
    $emblems=array_map('intval',array_column($emblems,'total','tier'));
    $latestEmblem=query_one('SELECT MAX(id) latest_id,COUNT(*) total FROM student_emblems WHERE user_id=?','i',[$uid])??[];
    return ['opened'=>(int)($stats['opened']??0),'completed'=>(int)($stats['completed']??0),'total'=>$total,'points'=>$points,'level'=>1+intdiv(max(0,$points),250),'level_progress'=>max(0,$points)%250,'last_quiz'=>$last['percentage']??null,'subjects'=>$subjects,'recent'=>$recent,'trail'=>$trail,'trail_subject'=>$subjectId,'next'=>$next,'today'=>$today,'streak'=>$streak,'week'=>$week,'emblems'=>$emblems,'latest_emblem_id'=>(int)($latestEmblem['latest_id']??0),'emblem_total'=>(int)($latestEmblem['total']??0)];
}
