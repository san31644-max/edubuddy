<?php
require_once __DIR__.'/includes/auth.php';
require_guest();
$errors=[];
$v=['full_name'=>'','username'=>'','email'=>'','school_name'=>'','grade_id'=>'','medium'=>'English'];
if($_SERVER['REQUEST_METHOD']==='POST'){
    verify_csrf();
    foreach($v as $k=>$x)$v[$k]=trim((string)($_POST[$k]??$x));
    $p=(string)($_POST['password']??'');
    $grade=query_one("SELECT id FROM grades WHERE id=? AND status='active'",'i',[(int)$v['grade_id']]);
    if(mb_strlen($v['full_name'])<2||mb_strlen($v['school_name'])<2||!preg_match('/^[A-Za-z0-9_.]{3,30}$/',$v['username'])||!filter_var($v['email'],FILTER_VALIDATE_EMAIL)||strlen($p)<8||$p!==($_POST['confirm_password']??'')||!$grade||!in_array($v['medium'],['Sinhala','Tamil','English'],true))$errors[]=tr('invalid');
    if(!$errors){
        $language=medium_language($v['medium']);
        $s=db()->prepare('INSERT INTO users(full_name,username,email,school_name,password_hash,grade_id,medium,preferred_language) VALUES(?,?,?,?,?,?,?,?)');
        $hash=password_hash($p,PASSWORD_DEFAULT);
        $gradeId=(int)$v['grade_id'];$s->bind_param('sssssiss',$v['full_name'],$v['username'],$v['email'],$v['school_name'],$hash,$gradeId,$v['medium'],$language);
        if($s->execute()){$_SESSION['onboarding_user_id']=(int)db()->insert_id;flash('success','Account created. Please log in.');redirect('login.php');}
        $errors[]=$s->errno===1062?'That username or email is already registered.':'Account could not be created.';
    }
}
$grades=[];foreach(db()->query("SELECT id,grade_number FROM grades WHERE status='active' ORDER BY grade_number") as $g)$grades[]=$g;$pageTitle=tr('register');include __DIR__.'/includes/header.php';
?>
<section class="card"><h1><?=tr('register')?></h1>
<?php foreach($errors as $x):?><div class="alert error"><?=e($x)?></div><?php endforeach;?>
<form method="post"><?=csrf_field()?>
<label><?=tr('full_name')?><input name="full_name" value="<?=e($v['full_name'])?>" maxlength="100" required></label>
<label><?=tr('username')?><input name="username" value="<?=e($v['username'])?>" maxlength="30" autocomplete="username" required></label>
<label><?=tr('email')?><input type="email" name="email" value="<?=e($v['email'])?>" maxlength="190" required></label>
<label>School name<input name="school_name" value="<?=e($v['school_name'])?>" maxlength="190" required></label>
<label><?=tr('password')?><input type="password" name="password" minlength="8" autocomplete="new-password" required></label>
<label><?=tr('confirm')?><input type="password" name="confirm_password" minlength="8" required></label>
<label><?=tr('grade')?><select name="grade_id" required><option value="">Select grade</option><?php foreach($grades as $g):?><option value="<?=intval($g['id'])?>"<?=selected((string)$v['grade_id'],(string)$g['id'])?>>Grade <?=intval($g['grade_number'])?></option><?php endforeach;?></select></label>
<label><?=tr('medium')?><select name="medium" required>
<option value="English"<?=selected($v['medium'],'English')?>>English Medium</option>
<option value="Sinhala"<?=selected($v['medium'],'Sinhala')?>>සිංහල මාධ්‍ය</option>
<option value="Tamil"<?=selected($v['medium'],'Tamil')?>>தமிழ் மூலம்</option>
</select></label>
<p class="muted">The app language, subjects, lessons, quizzes and AI tutor will follow this medium automatically.</p>
<button type="submit"><?=tr('register')?></button></form><p><a href="login.php"><?=tr('login')?></a></p></section>
<?php include __DIR__.'/includes/footer.php';?>
