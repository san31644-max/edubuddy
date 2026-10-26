<?php
declare(strict_types=1);
require_once __DIR__.'/../includes/auth.php';
require_admin();
$db=db();
// TIMESTAMP columns are displayed in the same time zone as the application.
$db->query("SET time_zone='+05:30'");
function history_rows(string $sql,string $types='',array $args=[]):array {
    $statement=db()->prepare($sql);if(!$statement)throw new RuntimeException('History query failed: '.db()->error);if($types)$statement->bind_param($types,...$args);
    $statement->execute();return $statement->get_result()->fetch_all(MYSQLI_ASSOC);
}
function history_date(?string $value):string{return $value?date('d M Y, g:i A',strtotime($value)):'Not recorded';}
function history_label(string $value):string{return ucwords(str_replace('_',' ',$value));}
function history_pager(int $total,int $page,array $params):void {
    $pages=max(1,(int)ceil($total/50));if($pages<2)return;
    echo '<nav class="row history-pager" aria-label="History pages">';
    if($page>1)echo '<a class="btn alt" href="?'.e(http_build_query(array_replace($params,['page'=>$page-1]))).'">← Previous</a>';
    echo '<span>Page '.$page.' of '.$pages.' · '.number_format($total).' records</span>';
    if($page<$pages)echo '<a class="btn alt" href="?'.e(http_build_query(array_replace($params,['page'=>$page+1]))).'">Next →</a>';
    echo '</nav>';
}
$id=filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT)?:0;
$page=max(1,(int)($_GET['page']??1));$offset=($page-1)*50;
$tabs=['overview'=>'Overview','timeline'=>'All activity','lessons'=>'Lesson history','payments'=>'Payments','quizzes'=>'Quiz history','ai'=>'AI conversations'];
$tab=(string)($_GET['tab']??'overview');if(!isset($tabs[$tab]))$tab='overview';
$student=$id?query_one('SELECT u.*,g.grade_number FROM users u JOIN grades g ON g.id=u.grade_id WHERE u.id=?','i',[$id]):null;
if($id&&!$student){flash('error','Student not found.');redirect('admin/student_history.php');}
$pageTitle=$student?'Student history · '.$student['full_name']:'Student history';
include __DIR__.'/_top.php';
?>
<style>
.history-head{display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap}.history-head h1{margin:8px 0}.history-tabs{display:flex;gap:8px;overflow:auto;margin:18px 0}.history-tabs a{white-space:nowrap;padding:11px 15px;border:1px solid var(--line);border-radius:12px;text-decoration:none;color:var(--ink);background:#fff;font-weight:800}.history-tabs a.active{background:var(--violet);color:#fff}.history-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px}.history-stats .card{margin:0}.history-stats strong{display:block;font-size:1.4rem;color:var(--violet)}.history-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin:18px 0}.history-field{padding:10px 0;border-bottom:1px solid var(--line)}.history-field small,.history-table small{display:block;color:var(--muted)}.history-table{width:100%;min-width:700px}.history-table td{vertical-align:top}.history-message{white-space:pre-wrap;overflow-wrap:anywhere;max-width:780px;padding:12px;background:#f7f8fc;border-radius:12px}.history-search{display:flex;gap:10px;flex-wrap:wrap}.history-search input{flex:1;min-width:200px}.history-pager{justify-content:space-between;padding:15px 0}.history-note{font-size:.85rem}.history-plan{display:inline-block;padding:6px 10px;border-radius:10px;background:#fff1cc;color:#805800}.history-event{padding:15px 0;border-bottom:1px solid var(--line)}.history-event time{display:block;color:var(--muted);font-size:.82rem}.history-event p{margin:6px 0;overflow-wrap:anywhere}@media(max-width:700px){.history-stats{grid-template-columns:1fr 1fr}.history-grid{grid-template-columns:1fr}.history-head .btn{width:100%;text-align:center}}
</style>
<?php if(!$student):
    $search=trim((string)($_GET['q']??''));$where='';$types='';$args=[];
    if($search!==''){$where=' WHERE (u.full_name LIKE ? OR u.username LIKE ? OR u.phone LIKE ? OR u.email LIKE ? OR u.school_name LIKE ?)';$like='%'.$search.'%';$types='sssss';$args=array_fill(0,5,$like);}
    $total=(int)(query_one('SELECT COUNT(*) total FROM users u'.$where,$types,$args)['total']??0);
    $directory=history_rows('SELECT u.id,u.full_name,u.username,u.phone,u.school_name,u.medium,u.created_at,u.subscription_expires_at,g.grade_number FROM users u JOIN grades g ON g.id=u.grade_id'.$where.' ORDER BY u.id DESC LIMIT 50 OFFSET '.$offset,$types,$args);
?>
<section class="card history-head"><div><span class="badge">Student records</span><h1>Student history</h1><p class="muted">Choose a student to see their account, visits, lessons, payments, quizzes and AI conversations.</p></div><strong><?=number_format($total)?> students</strong></section>
<form class="card history-search"><input name="q" value="<?=e($search)?>" placeholder="Search name, username, phone, email or school"><button>Find student</button></form>
<section class="card scroll"><table class="history-table"><tr><th>Student</th><th>School / grade</th><th>Joined</th><th>Plan</th><th></th></tr><?php foreach($directory as $row):?><tr><td><strong><?=e($row['full_name'])?></strong><small>@<?=e($row['username'])?> · <?=e($row['phone']?:'No phone')?></small></td><td><?=e($row['school_name']?:'Not provided')?><small>Grade <?=(int)$row['grade_number']?> · <?=e($row['medium'])?></small></td><td><?=e(history_date($row['created_at']))?></td><td><?=$row['subscription_expires_at']&&strtotime($row['subscription_expires_at'])>time()?'Premium':'Free'?></td><td><a class="btn alt" href="?id=<?=(int)$row['id']?>">Open history →</a></td></tr><?php endforeach;?></table><?php if(!$directory):?><p class="muted">No students match your search.</p><?php endif;?></section>
<?php history_pager($total,$page,['q'=>$search]);include __DIR__.'/../includes/footer.php';exit;endif;
$account=query_one("SELECT MAX(CASE WHEN event_type='login' THEN created_at END) last_login,MAX(CASE WHEN event_type='site_opened' THEN created_at END) last_opened,MAX(created_at) last_seen FROM student_account_events WHERE user_id=?",'i',[$id])??[];
$summary=query_one("SELECT (SELECT COUNT(*) FROM lesson_progress WHERE user_id=?) lessons,(SELECT COUNT(*) FROM lesson_progress WHERE user_id=? AND completed_at IS NOT NULL) completed,(SELECT COUNT(*) FROM quiz_attempts WHERE user_id=?) quizzes,(SELECT COUNT(*) FROM chat_messages WHERE user_id=? AND role='user') questions,(SELECT COUNT(*) FROM subscriptions WHERE user_id=?) payments,(SELECT COALESCE(SUM(amount_lkr),0) FROM subscriptions WHERE user_id=? AND status IN ('active','expired')) paid,(SELECT COALESCE(SUM(points),0) FROM student_points WHERE user_id=?) points",'iiiiiii',array_fill(0,7,$id))??[];
$premium=$student['subscription_expires_at']&&strtotime($student['subscription_expires_at'])>time();
?>
<p><a href="student_history.php">← Student history directory</a> · <a href="student.php?id=<?=$id?>">Learning profile</a></p>
<section class="card history-head"><div><span class="badge">Complete student record</span><?php if(str_starts_with($student['username'],'demo_lk_')):?><span class="badge">Synthetic demo profile</span><?php endif;?><h1><?=e($student['full_name'])?></h1><p class="muted">@<?=e($student['username'])?> · Grade <?=(int)$student['grade_number']?> · <?=e($student['medium'])?> · <?=e($student['school_name']?:'School not provided')?></p></div><span class="history-plan"><?=$premium?'Premium active':'Free account'?></span></section>
<nav class="history-tabs" aria-label="Student history sections"><?php foreach($tabs as $key=>$label):?><a class="<?=$tab===$key?'active':''?>" href="?id=<?=$id?>&amp;tab=<?=$key?>"><?=e($label)?></a><?php endforeach;?></nav>
<?php if($tab==='overview'):?>
<section class="history-stats"><?php foreach(['Lessons opened'=>$summary['lessons'],'Quizzes attempted'=>$summary['quizzes'],'AI questions'=>$summary['questions'],'Payment submissions'=>$summary['payments']] as $label=>$value):?><article class="card"><strong><?=number_format((int)$value)?></strong><small><?=e($label)?></small></article><?php endforeach;?></section>
<section class="history-grid"><article class="card"><h2>Account details</h2><?php foreach(['Joined'=>history_date($student['created_at']),'Phone'=>$student['phone']?:'Not provided','Email'=>$student['email']?:'Not provided','School'=>$student['school_name']?:'Not provided','District'=>$student['district']?:'Not provided','Account status'=>history_label($student['status']),'Premium expiry'=>history_date($student['subscription_expires_at'])] as $label=>$value):?><div class="history-field"><small><?=e($label)?></small><strong><?=e($value)?></strong></div><?php endforeach;?></article><article class="card"><h2>Visits &amp; learning</h2><?php foreach(['Last login'=>history_date($account['last_login']??null),'Last site opened'=>history_date($account['last_opened']??null),'Last recorded account activity'=>history_date($account['last_seen']??null),'Completed lessons'=>(int)$summary['completed'],'Points'=>number_format((int)$summary['points']),'Approved payment total'=>'LKR '.number_format((float)$summary['paid'],2)] as $label=>$value):?><div class="history-field"><small><?=e($label)?></small><strong><?=e($value)?></strong></div><?php endforeach;?><p class="muted history-note">Login and site-visit tracking starts when this feature is enabled. Earlier logins cannot be reconstructed. Existing lesson, quiz, AI and payment records are available in their tabs.</p></article></section>
<?php elseif($tab==='timeline'):
    $union="SELECT 'joined' COLLATE utf8mb4_unicode_ci kind,created_at happened_at,'Student account created' COLLATE utf8mb4_unicode_ci detail,id record_id,'users' source_kind FROM users WHERE id=? UNION ALL SELECT event_type COLLATE utf8mb4_unicode_ci,event_time,detail COLLATE utf8mb4_unicode_ci,id,'activity' FROM student_activity_events WHERE user_id=? UNION ALL SELECT event_type COLLATE utf8mb4_unicode_ci,created_at,CONCAT(detail,IF(actor_admin_id IS NULL,'',CONCAT(' · Administrator #',actor_admin_id))) COLLATE utf8mb4_unicode_ci,id,'account' FROM student_account_events WHERE user_id=? UNION ALL SELECT 'payment_submitted' COLLATE utf8mb4_unicode_ci,created_at,CONCAT('Payment #',id,' · LKR ',amount_lkr,' · ',payment_method,' · Reference: ',payment_reference,' · Status: ',status) COLLATE utf8mb4_unicode_ci,id,'payment' FROM subscriptions WHERE user_id=?";
    $args=array_fill(0,4,$id);$total=(int)(query_one('SELECT COUNT(*) total FROM ('.$union.') history','iiii',$args)['total']??0);
    $rows=history_rows('SELECT * FROM ('.$union.') history ORDER BY happened_at DESC,source_kind,record_id DESC LIMIT 50 OFFSET '.$offset,'iiii',$args);
?>
<section class="card"><h2>All recorded activity</h2><p class="muted">Account creation, site visits, lesson events, AI questions, payment submissions and account changes.</p><?php foreach($rows as $row):?><article class="history-event"><span class="badge"><?=e(history_label($row['kind']))?></span><time><?=e(history_date($row['happened_at']))?></time><p><?=e($row['detail'])?></p></article><?php endforeach;?></section>
<?php history_pager($total,$page,['id'=>$id,'tab'=>$tab]);
elseif($tab==='lessons'):
    $total=(int)(query_one('SELECT COUNT(*) total FROM lesson_progress WHERE user_id=?','i',[$id])['total']??0);
    $rows=history_rows('SELECT lp.*,l.title_en,s.name_en subject FROM lesson_progress lp JOIN lessons l ON l.id=lp.lesson_id JOIN subjects s ON s.id=l.subject_id WHERE lp.user_id=? ORDER BY lp.last_opened_at DESC,lp.id DESC LIMIT 50 OFFSET '.$offset,'i',[$id]);
?>
<section class="card scroll"><h2>Lessons opened &amp; completed</h2><table class="history-table"><tr><th>Subject / lesson</th><th>First opened</th><th>Last opened</th><th>Completed</th></tr><?php foreach($rows as $row):?><tr><td><?=e($row['subject'])?><small><?=e($row['title_en'])?></small></td><td><?=e(history_date($row['opened_at']))?></td><td><?=e(history_date($row['last_opened_at']))?></td><td><?=e($row['completed_at']?history_date($row['completed_at']):'Not completed')?></td></tr><?php endforeach;?></table><?php if(!$rows):?><p class="muted">No lesson visits recorded.</p><?php endif;?></section>
<?php history_pager($total,$page,['id'=>$id,'tab'=>$tab]);
elseif($tab==='payments'):
    $total=(int)$summary['payments'];
    $rows=history_rows('SELECT p.*,a.full_name reviewer FROM subscriptions p LEFT JOIN admins a ON a.id=p.reviewed_by WHERE p.user_id=? ORDER BY p.created_at DESC,p.id DESC LIMIT 50 OFFSET '.$offset,'i',[$id]);
?>
<section class="card scroll"><h2>Payment history</h2><table class="history-table"><tr><th>Submitted / reference</th><th>Amount / method</th><th>Status</th><th>Premium period</th><th>Receipt</th></tr><?php foreach($rows as $row):?><tr><td><?=e(history_date($row['created_at']))?><small><?=e($row['payment_reference'])?></small></td><td>LKR <?=number_format((float)$row['amount_lkr'],2)?><small><?=e(history_label($row['payment_method']))?></small></td><td><?=e(history_label($row['status']))?><small>Last updated <?=e(history_date($row['updated_at']))?></small><?php if($row['reviewer']):?><small>Reviewed by <?=e($row['reviewer'])?></small><?php endif;?></td><td><?=e(history_date($row['starts_at']))?><small>Until <?=e(history_date($row['expires_at']))?></small></td><td><?php if(!empty($row['receipt_path'])):?><a href="receipt.php?id=<?=(int)$row['id']?>" target="_blank" rel="noopener">View receipt</a><?php else:?>Not recorded<?php endif;?></td></tr><?php endforeach;?></table><?php if(!$rows):?><p class="muted">No payment submissions recorded for this student.</p><?php endif;?></section>
<?php history_pager($total,$page,['id'=>$id,'tab'=>$tab]);
elseif($tab==='quizzes'):
    $total=(int)$summary['quizzes'];
    $rows=history_rows('SELECT qa.*,q.title_en,s.name_en subject FROM quiz_attempts qa JOIN quizzes q ON q.id=qa.quiz_id JOIN subjects s ON s.id=q.subject_id WHERE qa.user_id=? ORDER BY qa.completed_at DESC,qa.id DESC LIMIT 50 OFFSET '.$offset,'i',[$id]);
?>
<section class="card scroll"><h2>Every quiz attempt</h2><table class="history-table"><tr><th>Quiz / subject</th><th>Completed</th><th>Score</th><th>Result</th></tr><?php foreach($rows as $row):?><tr><td><?=e($row['title_en'])?><small><?=e($row['subject'])?></small></td><td><?=e(history_date($row['completed_at']))?></td><td><?=(int)$row['score']?> / <?=(int)$row['total_questions']?> · <?=e($row['percentage'])?>%</td><td><?=$row['passed']?'Passed':'Retry needed'?></td></tr><?php endforeach;?></table><?php if(!$rows):?><p class="muted">No quiz attempts recorded.</p><?php endif;?></section>
<?php history_pager($total,$page,['id'=>$id,'tab'=>$tab]);
elseif($tab==='ai'):
    $total=(int)(query_one('SELECT COUNT(*) total FROM chat_messages WHERE user_id=?','i',[$id])['total']??0);
    $rows=history_rows('SELECT m.*,s.name_en subject,l.title_en lesson FROM chat_messages m LEFT JOIN subjects s ON s.id=m.subject_id LEFT JOIN lessons l ON l.id=m.lesson_id WHERE m.user_id=? ORDER BY m.created_at DESC,m.id DESC LIMIT 50 OFFSET '.$offset,'i',[$id]);
?>
<section class="card"><h2>AI conversation history</h2><?php foreach($rows as $row):?><article class="history-event"><span class="badge"><?=$row['role']==='user'?'Student question':'AI reply'?></span><time><?=e(history_date($row['created_at']))?> · Conversation #<?=(int)$row['session_id']?></time><?php if($row['subject']):?><p class="muted"><?=e($row['subject'])?> · <?=e($row['lesson']??'')?></p><?php endif;?><details><summary><?=e(mb_strimwidth($row['message'],0,180,'…'))?></summary><div class="history-message"><?=e($row['message'])?></div></details></article><?php endforeach;if(!$rows):?><p class="muted">No AI conversations recorded.</p><?php endif;?></section>
<?php history_pager($total,$page,['id'=>$id,'tab'=>$tab]);endif;?>
<?php include __DIR__.'/../includes/footer.php';?>
