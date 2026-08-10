<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
header('Content-Type: application/json; charset=utf-8');
if (!user()) { http_response_code(401); echo '[]'; exit; }
$uid=(int)user()['id'];
$languageCode=['Sinhala'=>'si','Tamil'=>'ta','English'=>'en'][user()['medium']]??'en';
if($_SERVER['REQUEST_METHOD']==='DELETE'){
    if(!hash_equals($_SESSION['csrf']??'',$_SERVER['HTTP_X_CSRF_TOKEN']??'')){http_response_code(419);exit;}
    $s=db()->prepare('DELETE FROM chat_messages WHERE user_id=? AND language=?');
    if($s){$s->bind_param('is',$uid,$languageCode);$s->execute();}
    echo '{"ok":true}';exit;
}
$s=db()->prepare('SELECT role,message,created_at FROM chat_messages WHERE user_id=? AND language=? ORDER BY id DESC LIMIT 50');
if(!$s){echo '[]';exit;}
$s->bind_param('is',$uid,$languageCode);$s->execute();
echo json_encode(array_reverse($s->get_result()->fetch_all(MYSQLI_ASSOC)),JSON_UNESCAPED_UNICODE);
