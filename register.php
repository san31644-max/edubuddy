<?php
declare(strict_types=1);
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
require_once __DIR__.'/includes/auth.php';
require_once __DIR__.'/includes/textlk.php';
require_guest();
$errors=[];$notice='';$referralPromoter=null;
$v=['full_name'=>'','school_name'=>'','district'=>'','phone'=>'','referral_code'=>(string)($_GET['ref']??''),'grade_id'=>'','medium'=>'English'];
foreach($v as $k=>$default)$v[$k]=trim((string)($_POST[$k]??$default));
$v['referral_code']=normalize_referral_code($v['referral_code']);
if($_SERVER['REQUEST_METHOD']!=='POST'&&$v['referral_code']!==''){$referralPromoter=active_referral_promoter($v['referral_code']);if(!$referralPromoter)$errors[]='That referral code is not valid or is no longer active.';elseif(!referral_promoter_has_capacity($referralPromoter)){$errors[]='This referral code has reached its registration limit and has expired.';$referralPromoter=null;}}
$action=(string)($_POST['action']??'');
if($_SERVER['REQUEST_METHOD']==='POST'){
    verify_csrf();
    if($v['referral_code']!==''){$referralPromoter=active_referral_promoter($v['referral_code']);if(!$referralPromoter)$errors[]='That referral code is not valid or is no longer active.';elseif(!referral_promoter_has_capacity($referralPromoter)){$errors[]='This referral code has reached its registration limit and has expired.';$referralPromoter=null;}}
    $phone=normalize_sri_lankan_phone($v['phone']);
    if(!$phone)$errors[]='Enter a valid Sri Lankan mobile number, for example 0712345678.';
    if($action==='send_otp'&&!$errors){
        $last=(int)($_SESSION['phone_otp']['sent_at']??0);$hour=(array)($_SESSION['phone_otp_sends']??[]);$hour=array_values(array_filter($hour,fn($time)=>(int)$time>time()-3600));
        if(time()-$last<60)$errors[]='Please wait 60 seconds before requesting another code.';
        elseif(count($hour)>=5)$errors[]='Too many verification messages. Please try again in one hour.';
        elseif(query_one('SELECT id FROM users WHERE phone=? LIMIT 1','s',[$phone]))$errors[]='That phone number is already registered. Please log in.';
        else{$code=(string)random_int(100000,999999);[$sent,$message]=send_textlk_otp($phone,$code);if($sent){$_SESSION['phone_otp']=['phone'=>$phone,'hash'=>password_hash($code,PASSWORD_DEFAULT),'expires'=>time()+300,'sent_at'=>time(),'attempts'=>0,'verified'=>false];$hour[]=time();$_SESSION['phone_otp_sends']=$hour;$notice='Verification code sent. It expires in 5 minutes.';}else$errors[]=$message;}
    }elseif($action==='verify_otp'&&!$errors){
        $otp=$_SESSION['phone_otp']??null;$code=trim((string)($_POST['otp_code']??''));
        if(!$otp||($otp['phone']??'')!==$phone||time()>(int)($otp['expires']??0))$errors[]='The verification code expired. Send a new code.';
        elseif((int)($otp['attempts']??0)>=5)$errors[]='Too many incorrect attempts. Send a new code.';
        elseif(!preg_match('/^\d{6}$/',$code)||!password_verify($code,(string)$otp['hash'])){$_SESSION['phone_otp']['attempts']=(int)($otp['attempts']??0)+1;$errors[]='Incorrect verification code.';}
        else{$_SESSION['phone_otp']['verified']=true;$notice='Phone number verified. Complete your account below.';}
    }elseif($action==='create'&&!$errors){
        $p=(string)($_POST['password']??'');$grade=query_one("SELECT id FROM grades WHERE id=? AND status='active'",'i',[(int)$v['grade_id']]);$otp=$_SESSION['phone_otp']??null;
        if(mb_strlen($v['full_name'])<2)$errors[]='Enter your full name.';
        if(mb_strlen($v['school_name'])<2)$errors[]='Enter your school name.';
        if(mb_strlen($v['district'])<2)$errors[]='Choose your district.';
        if(strlen($p)<8)$errors[]='Password must contain at least 8 characters.';
        if($p!==($_POST['confirm_password']??''))$errors[]='The password confirmation does not match.';
        if(!$grade)$errors[]='Choose a valid grade.';
        if(!in_array($v['medium'],['Sinhala','Tamil','English'],true))$errors[]='Choose a valid medium.';
        if(!$otp||empty($otp['verified'])||($otp['phone']??'')!==$phone)$errors[]='Verify your phone number first.';
        if($phone&&query_one('SELECT id FROM users WHERE phone=? LIMIT 1','s',[$phone]))$errors[]='That phone number is already registered.';
        if(!$errors){$language=medium_language($v['medium']);$username='u'.substr($phone,-9);$suffix=0;while(query_one('SELECT id FROM users WHERE username=?','s',[$username]))$username='u'.substr($phone,-7).(++$suffix);$hash=password_hash($p,PASSWORD_DEFAULT);$gradeId=(int)$v['grade_id'];$referralPromoterId=$referralPromoter?(int)$referralPromoter['id']:null;$referralCode=$referralPromoter?(string)$referralPromoter['referral_code']:null;$s=db()->prepare("INSERT INTO users(full_name,username,email,phone,phone_verified_at,school_name,district,password_hash,grade_id,medium,preferred_language,profile_image,subscription_expires_at,referral_promoter_id,referral_code_used,status,created_at,updated_at) VALUES(?,?,NULL,?,NOW(),?,?,?,?,?,?,NULL,NULL,?,?,'active',NOW(),NOW())");if(!$s){error_log('Registration prepare failed: '.db()->error);$errors[]='Registration is temporarily unavailable.';}else{$s->bind_param('ssssssissis',$v['full_name'],$username,$phone,$v['school_name'],$v['district'],$hash,$gradeId,$v['medium'],$language,$referralPromoterId,$referralCode);if($s->execute()){$newId=(int)$s->insert_id;unset($_SESSION['phone_otp']);$_SESSION['onboarding_user_id']=$newId;flash('success','Account created. Log in with your phone number.');redirect('login.php');}error_log('Registration execute failed '.$s->errno.': '.$s->error);$errors[]='Account could not be created. Please retry.';}}
    }
}
$verified=!empty($_SESSION['phone_otp']['verified'])&&normalize_sri_lankan_phone($v['phone'])===($_SESSION['phone_otp']['phone']??'');$codeSent=!empty($_SESSION['phone_otp'])&&normalize_sri_lankan_phone($v['phone'])===($_SESSION['phone_otp']['phone']??'');
$districts=['Ampara','Anuradhapura','Badulla','Batticaloa','Colombo','Galle','Gampaha','Hambantota','Jaffna','Kalutara','Kandy','Kegalle','Kilinochchi','Kurunegala','Mannar','Matale','Matara','Monaragala','Mullaitivu','Nuwara Eliya','Polonnaruwa','Puttalam','Ratnapura','Trincomalee','Vavuniya'];$grades=[];foreach(db()->query("SELECT id,grade_number FROM grades WHERE status='active' ORDER BY grade_number") as $g)$grades[]=$g;$pageTitle=tr('register');include __DIR__.'/includes/header.php';
?>
<section class="card" style="max-width:680px;margin-inline:auto"><h1>Create account</h1><p class="muted">Register using your mobile number. No email address is required.</p>
<?php foreach($errors as $x):?><div class="alert error"><?=e($x)?></div><?php endforeach;?><?php if($notice):?><div class="alert"><?=e($notice)?></div><?php endif;?>
<form method="post"><?=csrf_field()?>
<label>Full name<input name="full_name" value="<?=e($v['full_name'])?>" maxlength="100" required></label>
<label>School<input name="school_name" value="<?=e($v['school_name'])?>" maxlength="190" required></label>
<label>District<select name="district" required><option value="">Choose district</option><?php foreach($districts as $district):?><option value="<?=e($district)?>"<?=selected($v['district'],$district)?>><?=e($district)?></option><?php endforeach;?></select></label>
<label>Referral code <small class="muted">optional</small><input name="referral_code" value="<?=e($v['referral_code'])?>" minlength="4" maxlength="24" pattern="[A-Za-z0-9-]{4,24}" placeholder="Enter your teacher's code" autocapitalize="characters" spellcheck="false"></label>
<?php if($referralPromoter):$referralBase=premium_base_price_lkr();$referralDiscount=referral_discount_lkr();?><div class="alert">Referral applied: save Rs. <?=e(number_format($referralDiscount,0))?> on Premium. Your price will be Rs. <?=e(number_format($referralBase-$referralDiscount,0))?>.</div><?php endif;?>
<label>Mobile number<input type="tel" name="phone" value="<?=e($v['phone'])?>" placeholder="0712345678" autocomplete="tel" required <?=$verified?'readonly':''?>></label>
<?php if(!$verified):?><div class="row"><?php if($codeSent):?><label style="flex:1">Verification code<input name="otp_code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" placeholder="6-digit code"></label><button name="action" value="verify_otp" type="submit" formnovalidate>Verify code</button><?php endif;?><button class="alt" name="action" value="send_otp" type="submit" formnovalidate><?=$codeSent?'Resend code':'Send verification code'?></button></div><?php else:?><div class="alert">✓ Phone number verified</div><?php endif;?>
<label>Password<input type="password" name="password" minlength="8" autocomplete="new-password" <?=$verified?'required':''?>></label>
<label>Confirm password<input type="password" name="confirm_password" minlength="8" <?=$verified?'required':''?>></label>
<label>Grade<select name="grade_id" required><option value="">Select grade</option><?php foreach($grades as $g):?><option value="<?=(int)$g['id']?>"<?=selected($v['grade_id'],(string)$g['id'])?>>Grade <?=(int)$g['grade_number']?></option><?php endforeach;?></select></label>
<label>Medium<select name="medium" required><option value="English"<?=selected($v['medium'],'English')?>>English Medium</option><option value="Sinhala"<?=selected($v['medium'],'Sinhala')?>>සිංහල මාධ්‍යය</option><option value="Tamil"<?=selected($v['medium'],'Tamil')?>>தமிழ் மூலம்</option></select></label>
<button name="action" value="create" type="submit" <?=$verified?'':'disabled'?>>Create account</button>
</form><p><a href="login.php">Already registered? Log in</a></p></section>
<?php include __DIR__.'/includes/footer.php';?>
