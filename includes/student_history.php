<?php
declare(strict_types=1);

function ensure_student_account_events_table(): bool
{
    return (bool)db()->query("CREATE TABLE IF NOT EXISTS student_account_events (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        event_type VARCHAR(40) NOT NULL,
        detail VARCHAR(500) NOT NULL,
        actor_admin_id INT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX student_account_history (user_id,created_at,id),
        FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

function record_student_account_event(int $userId,string $type,string $detail,?int $adminId=null): bool
{
    if($userId<1 || !in_array($type,['login','logout','site_opened','page_opened','payment_approved','payment_rejected','premium_enabled','premium_removed'],true))return false;
    try {
        $statement=db()->prepare('INSERT INTO student_account_events(user_id,event_type,detail,actor_admin_id) VALUES(?,?,?,?)');
        if(!$statement)throw new RuntimeException(db()->error);
        $detail=mb_substr(trim($detail),0,500);
        $statement->bind_param('issi',$userId,$type,$detail,$adminId);
        $saved=$statement->execute();$statement->close();return $saved;
    } catch(Throwable $error) {error_log('Student account history could not be saved: '.$error->getMessage());return false;}
}

function record_student_visit(): void
{
    if(!user() || ($_SERVER['REQUEST_METHOD']??'')!=='GET')return;
    $path=(string)($_SERVER['SCRIPT_NAME']??'');
    if($path==='' || str_contains($path,'/admin/'))return;
    $previous=$_SESSION['student_visit']??[];$now=time();
    if(($previous['path']??'')===$path && $now-(int)($previous['at']??0)<300)return;
    $type=!$previous || $now-(int)($previous['at']??0)>=1800?'site_opened':'page_opened';
    if(record_student_account_event((int)user()['id'],$type,'Opened '.$path))$_SESSION['student_visit']=['path'=>$path,'at'=>$now];
}
