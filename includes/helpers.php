<?php
declare(strict_types=1);
require_once __DIR__.'/language.php';
function e(mixed $v):string{return htmlspecialchars((string)$v,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');}
function url(string $p=''):string{return APP_URL.'/'.ltrim($p,'/');}
function redirect(string $p):never{header('Location: '.url($p));exit;}
function csrf_token():string{if(empty($_SESSION['csrf']))$_SESSION['csrf']=bin2hex(random_bytes(32));return $_SESSION['csrf'];}
function csrf_field():string{return '<input type="hidden" name="csrf" value="'.e(csrf_token()).'">';}
function verify_csrf():void{if(!hash_equals($_SESSION['csrf']??'',(string)($_POST['csrf']??''))){http_response_code(419);exit('Your session expired. Please go back and try again.');}}
function flash(string $type,string $msg):void{$_SESSION['flash']=[$type,$msg];}
function take_flash():?array{$f=$_SESSION['flash']??null;unset($_SESSION['flash']);return $f;}
function selected(string $a,string $b):string{return $a===$b?' selected':'';}
function is_premium(?array $u=null):bool
{
    if ($u === null) {
        $u = $_SESSION['user'] ?? null;
        static $refreshed = false;
        if ($u && !$refreshed) {
            $refreshed = true;
            $row = query_one('SELECT subscription_expires_at FROM users WHERE id=?', 'i', [(int)$u['id']]);
            if ($row) {
                $u['subscription_expires_at'] = $row['subscription_expires_at'];
                $_SESSION['user']['subscription_expires_at'] = $row['subscription_expires_at'];
            }
        }
    }
    return $u && !empty($u['subscription_expires_at']) && strtotime((string)$u['subscription_expires_at']) > time();
}
function require_premium():void{if(!is_premium()){flash('warning','This feature needs K Education Premium.');redirect('subscription.php');}}
function query_one(string $sql,string $types='',array $args=[]):?array{$s=db()->prepare($sql);if(!$s)return null;if($types)$s->bind_param($types,...$args);$s->execute();return $s->get_result()->fetch_assoc()?:null;}
function repair_legacy_text(string $value):string{
    // Some early Sinhala/Tamil imports decoded their UTF-8 bytes as DOS CP850,
    // producing visible sequences such as "ÓÀ...". Recover only strings with
    // that unmistakable signature and leave valid multilingual text untouched.
    if($value===''||!str_contains($value,'Ó')||(!str_contains($value,'À')&&!str_contains($value,'Â')))return $value;
    $fixed=@iconv('UTF-8','CP850//IGNORE',$value);
    if($fixed!==false&&mb_check_encoding($fixed,'UTF-8')&&preg_match('/[\x{0D80}-\x{0DFF}\x{0B80}-\x{0BFF}]/u',$fixed))return $fixed;
    return $value;
}
function locale_value(array $row,string $base):string{return repair_legacy_text((string)($row[$base.'_'.($_SESSION['lang']??'en')]??$row[$base.'_en']??''));}
