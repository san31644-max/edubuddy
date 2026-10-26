<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli')exit;
require_once __DIR__.'/../includes/auth.php';
mysqli_report(MYSQLI_REPORT_ERROR|MYSQLI_REPORT_STRICT);
function history_expect(bool $condition,string $message):void{if(!$condition)throw new RuntimeException($message);}
history_expect(ensure_student_account_events_table(),'History table missing');
$db=db();$db->begin_transaction();
try {
    $grade=(int)$db->query('SELECT id FROM grades ORDER BY id LIMIT 1')->fetch_row()[0];
    $name='Temporary history verification';$username='historyverify_'.bin2hex(random_bytes(6));$hash=password_hash(bin2hex(random_bytes(12)),PASSWORD_DEFAULT);
    $insert=$db->prepare("INSERT INTO users(full_name,username,password_hash,grade_id,medium) VALUES(?,?,?,?,'English')");$insert->bind_param('sssi',$name,$username,$hash,$grade);$insert->execute();$id=(int)$db->insert_id;
    history_expect(record_student_account_event($id,'login','Student signed in'),'Login not saved');
    history_expect(!record_student_account_event($id,'invalid','Invalid type'),'Unknown event accepted');
    $_SESSION['user']=['id'=>$id];unset($_SESSION['student_visit']);$_SERVER['REQUEST_METHOD']='GET';$_SERVER['SCRIPT_NAME']='/student/dashboard.php';
    record_student_visit();record_student_visit();
    $count=(int)$db->query('SELECT COUNT(*) FROM student_account_events WHERE user_id='.$id)->fetch_row()[0];history_expect($count===2,'Repeated refresh creates duplicate visit');
    $_SERVER['SCRIPT_NAME']='/student/subjects.php';record_student_visit();
    $_SESSION['student_visit']['at']=time()-1900;record_student_visit();
    $_SERVER['REQUEST_METHOD']='POST';record_student_visit();
    $counts=$db->query('SELECT event_type,COUNT(*) total FROM student_account_events WHERE user_id='.$id.' GROUP BY event_type')->fetch_all(MYSQLI_ASSOC);$counts=array_column($counts,'total','event_type');
    history_expect((int)($counts['site_opened']??0)===2,'Idle return not tracked');
    history_expect((int)($counts['page_opened']??0)===1,'Page navigation not tracked');
    history_expect(record_student_account_event($id,'premium_enabled','Premium enabled for 30 days',1),'Manual premium change not saved');
    history_expect(record_student_account_event($id,'payment_approved','Payment approved',1),'Payment review not saved');
    echo "PASS: login, visit throttling, page navigation, idle return, premium and payment audit events.\n";
}finally{$db->rollback();session_destroy();}
echo "STUDENT_HISTORY_CHECKS_PASSED\n";
