<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli')exit;
$root=getenv('EDUCHAT_TEST_ROOT')?:dirname(__DIR__);
require_once $root.'/includes/auth.php';
require_once $root.'/includes/ai_quota.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
function expect(bool $condition,string $message):void {if(!$condition)throw new RuntimeException($message);}
expect(ensure_ai_daily_usage_table(),'Quota storage unavailable');
if(($argv[1]??'')==='--claim') {
    $allowed=reserve_free_ai_message((int)$argv[2]);session_destroy();echo $allowed?'1':'0';exit;
}
$db=db();
$quotaId=-random_int(100000,2000000000);
try {
    $workers=[];
    for($i=0;$i<6;$i++) {
        $process=proc_open([PHP_BINARY,__FILE__,'--claim',(string)$quotaId],[1=>['pipe','w'],2=>['pipe','w']],$pipes);
        expect(is_resource($process),'Could not start quota concurrency check');
        $workers[]=[$process,$pipes];
    }
    $claims=0;
    foreach($workers as [$process,$pipes]) {
        $output=stream_get_contents($pipes[1]);$errors=stream_get_contents($pipes[2]);
        fclose($pipes[1]);fclose($pipes[2]);$code=proc_close($process);
        expect($code===0,'Quota worker failed: '.$errors);$claims+=(int)$output;
    }
    expect($claims===1,'Concurrent requests claimed more than one free message');
    expect(free_ai_used_today($quotaId)===1,'Daily usage exceeded one');
    echo "PASS: six concurrent quota claims allow exactly one message.\n";
} finally {$db->query('DELETE FROM ai_daily_usage WHERE user_id='.(int)$quotaId);}
$db->begin_transaction();
try {
    $lessons=$db->query("SELECT l.id,l.subject_id,l.grade_id,l.display_order,l.medium FROM lessons l WHERE l.status='active' AND l.content_source='textbook' AND l.medium IN ('English','All') ORDER BY l.grade_id,l.subject_id,l.display_order,l.id")->fetch_all(MYSQLI_ASSOC);
    $pairs=[];foreach($lessons as $lesson)$pairs[$lesson['grade_id'].':'.$lesson['subject_id']][]=$lesson;
    $pair=null;foreach($pairs as $rows)if(count($rows)>1){$pair=$rows;break;}
    expect($pair!==null,'Two sample lessons are required');
    $gradeId=(int)$pair[0]['grade_id'];
    $name='Premium policy verification';$username='verify_premium_'.bin2hex(random_bytes(5));$hash=password_hash(bin2hex(random_bytes(16)),PASSWORD_DEFAULT);
    $statement=$db->prepare("INSERT INTO users(full_name,username,password_hash,grade_id,medium,status) VALUES(?,?,?,?,'English','active')");
    $statement->bind_param('sssi',$name,$username,$hash,$gradeId);$statement->execute();$userId=(int)$db->insert_id;
    $_SESSION['user']=['id'=>$userId,'grade_id'=>$gradeId,'medium'=>'English','subscription_expires_at'=>null];
    expect(can_access_lesson((int)$pair[0]['id']),'Free preview blocked');
    expect(!can_access_lesson((int)$pair[1]['id']),'Second lesson accessible for free');
    foreach($pairs as $rows)if((int)$rows[0]['grade_id']===$gradeId && $rows[0]['subject_id']!==$pair[0]['subject_id']) {expect(can_access_lesson((int)$rows[0]['id']),'First lesson in another subject blocked');break;}
    $_SESSION['user']['subscription_expires_at']=date('Y-m-d H:i:s',time()+86400);
    expect(can_access_lesson((int)$pair[1]['id']),'Active Premium cannot access second lesson');
    $_SESSION['user']['subscription_expires_at']=date('Y-m-d H:i:s',time()-86400);
    expect(!can_access_lesson((int)$pair[1]['id']),'Expired Premium can access locked lesson');
    expect(!can_access_lesson(-1),'Invalid lesson accessible');
    expect(reserve_free_ai_message($userId),'First daily message blocked');
    expect(!reserve_free_ai_message($userId),'Second daily message allowed');
    $db->query('DELETE FROM ai_daily_usage WHERE user_id='.$userId);
    $day=date('Y-m-d',strtotime('-1 day'));$previous=$db->prepare('INSERT INTO ai_daily_usage(user_id,usage_date,response_count) VALUES(?,?,1)');$previous->bind_param('is',$userId,$day);$previous->execute();
    expect(free_ai_used_today($userId)===0,'Yesterday usage counts toward today');
    expect(reserve_free_ai_message($userId),'Daily quota failed to reset');
    echo "PASS: one preview per subject, active/expired Premium, invalid lessons, daily reset.\n";
} finally {$db->rollback();session_destroy();}
echo "PREMIUM_POLICY_CHECKS_PASSED\n";
