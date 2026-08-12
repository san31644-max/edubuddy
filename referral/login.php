<?php
declare(strict_types=1);
require_once __DIR__.'/../includes/auth.php';require_referral_promoter_guest();$error='';$identity='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();$identity=trim((string)($_POST['identity']??''));$code=normalize_referral_code($identity);$email=strtolower($identity);$password=(string)($_POST['password']??'');$bucket='referral_login_'.hash('sha256',($_SERVER['REMOTE_ADDR']??'local').$email);$tries=$_SESSION[$bucket]??['n'=>0,'at'=>0];
 if($tries['n']>=5&&time()-(int)$tries['at']<300)$error='Too many attempts. Try again in five minutes.';else{$r=query_one("SELECT * FROM referral_promoters WHERE (referral_code=? OR email=? OR phone=?) AND status='active' LIMIT 1",'sss',[$code,$email,$identity]);if($r&&password_verify($password,$r['password_hash'])){unset($r['password_hash'],$_SESSION[$bucket]);session_regenerate_id(true);$_SESSION['referral_promoter']=$r;unset($_SESSION['user'],$_SESSION['parent'],$_SESSION['admin']);$s=db()->prepare('UPDATE referral_promoters SET last_login_at=NOW() WHERE id=?');$id=(int)$r['id'];$s->bind_param('i',$id);$s->execute();redirect('referral/dashboard.php');}$tries=['n'=>(int)$tries['n']+1,'at'=>time()];$_SESSION[$bucket]=$tries;$error='The login or password is incorrect.';}
}
$pageTitle='Referral portal login';include __DIR__.'/../includes/header.php';
?>
<section class="card" style="max-width:620px;margin:25px auto"><span class="badge">K Education Referral Portal</span><h1>Promoter login</h1><p class="muted">See students who joined with your code and verified Premium purchases.</p><?php if($error):?><div class="alert error"><?=e($error)?></div><?php endif;?><form method="post"><?=csrf_field()?><label>Referral code, email or phone<input name="identity" value="<?=e($identity)?>" autocomplete="username" required autofocus></label><label>Password<input type="password" name="password" autocomplete="current-password" required></label><button>Open referral portal →</button></form><p><a href="<?=url('login.php')?>">Student login</a></p></section>
<?php include __DIR__.'/../includes/footer.php';?>
