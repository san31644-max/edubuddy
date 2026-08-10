<?php
require_once __DIR__.'/includes/auth.php';require_guest();$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    verify_csrf();$key=strtolower(trim((string)($_POST['identity']??'')));
    $bucket='login_'.hash('sha256',($_SERVER['REMOTE_ADDR']??'local').$key);$tries=$_SESSION[$bucket]??['n'=>0,'at'=>0];
    if($tries['n']>=5&&time()-$tries['at']<300)$error='Too many attempts. Try again in five minutes.';
    else{
        $u=query_one('SELECT id,full_name,username,email,school_name,password_hash,grade_id,medium,preferred_language,profile_image,subscription_expires_at,status FROM users WHERE (username=? OR email=?) LIMIT 1','ss',[$key,$key]);
        if($u&&$u['status']==='active'&&password_verify((string)($_POST['password']??''),$u['password_hash'])){
            unset($u['password_hash'],$_SESSION[$bucket]);session_regenerate_id(true);$u['preferred_language']=medium_language((string)$u['medium']);$_SESSION['user']=$u;$_SESSION['lang']=$u['preferred_language'];if((int)($_SESSION['onboarding_user_id']??0)===(int)$u['id'])$_SESSION['new_student_onboarding']=true;unset($_SESSION['onboarding_user_id']);redirect('student/dashboard.php');
        }
        $tries=['n'=>$tries['n']+1,'at'=>time()];$_SESSION[$bucket]=$tries;$error='Incorrect username, email or password.';
    }
}
$pageTitle='Student Login';include __DIR__.'/includes/header.php';?>
<style>
.wrap{width:min(1160px,100%);padding-top:18px}.login-shell{position:relative;display:grid;overflow:hidden;min-height:650px;border:1px solid rgba(255,255,255,.9);border-radius:34px;background:#fff;box-shadow:0 30px 90px rgba(48,46,116,.19)}
.login-art{position:relative;isolation:isolate;display:flex;flex-direction:column;justify-content:center;padding:42px 34px;color:#fff;background:linear-gradient(145deg,#5140ce 0%,#7454ec 44%,#158ecc 100%);overflow:hidden}.login-art:before,.login-art:after{content:"";position:absolute;z-index:-1;border-radius:50%;background:rgba(255,255,255,.11)}.login-art:before{width:330px;height:330px;right:-135px;top:-130px}.login-art:after{width:270px;height:270px;left:-120px;bottom:-115px}.art-logo{width:112px;height:112px;border:7px solid rgba(255,255,255,.22);border-radius:29px;object-fit:cover;box-shadow:0 19px 45px rgba(30,25,92,.28);transform:rotate(-3deg)}.login-art h1{max-width:540px;margin:24px 0 8px;font-size:clamp(2.3rem,6vw,4.25rem);color:#fff}.login-art .lead{max-width:510px;margin:0;color:rgba(255,255,255,.85);font-size:1.05rem}.subject-pills{display:flex;flex-wrap:wrap;gap:9px;margin-top:27px}.subject-pills span{padding:8px 12px;border:1px solid rgba(255,255,255,.2);border-radius:99px;background:rgba(255,255,255,.12);font-size:.79rem;font-weight:850;backdrop-filter:blur(8px)}.fun-icon{position:absolute;display:grid;place-items:center;width:55px;height:55px;border-radius:19px;background:#fff;box-shadow:0 15px 30px rgba(29,26,92,.22);font-size:1.65rem;animation:loginFloat 4s ease-in-out infinite}.fun-one{right:9%;top:24%}.fun-two{right:23%;bottom:14%;animation-delay:-1.7s}.fun-three{left:7%;top:10%;animation-delay:-.8s}
.login-panel{display:flex;align-items:center;padding:35px 25px;background:linear-gradient(160deg,#fff,#fbfaff)}.login-box{width:min(100%,430px);margin:auto}.welcome-badge{display:inline-flex;align-items:center;gap:7px;padding:7px 11px;border-radius:99px;background:#efedff;color:var(--violet);font-size:.76rem;font-weight:900}.login-box h2{margin:15px 0 7px;font-size:clamp(1.9rem,5vw,2.55rem)}.login-box>.muted{margin:0 0 24px}.field{position:relative}.field .field-icon{position:absolute;left:15px;top:42px;z-index:2}.field input{padding-left:45px;background:#f8f9ff}.field input:focus{background:#fff}.password-field input{padding-right:52px}.show-password{position:absolute;right:8px;top:34px;width:42px;min-height:42px;padding:0;border-radius:12px;background:transparent;color:#68738c;box-shadow:none;font-size:1.1rem}.show-password:hover{transform:none;background:#efedff;box-shadow:none}.login-options{display:flex;align-items:center;justify-content:space-between;gap:12px;margin:-2px 0 20px}.remember{display:flex;align-items:center;gap:8px;color:var(--muted);font-size:.82rem;font-weight:750}.remember input{width:18px;min-height:18px;margin:0;accent-color:var(--violet)}.login-button{width:100%;min-height:55px;font-size:1.03rem}.login-button span{transition:.2s}.login-button:hover span{transform:translateX(4px)}.join-card{margin-top:20px;padding:17px;border:1px dashed #c9c3fa;border-radius:19px;background:linear-gradient(135deg,#f5f3ff,#eefaff);text-align:center}.join-card p{margin:0 0 10px;color:var(--muted);font-size:.88rem}.join-card .btn{width:100%;background:#fff;color:var(--violet);border:1px solid #ded9ff;box-shadow:0 7px 20px rgba(101,84,232,.1)}.mediums{display:flex;justify-content:center;gap:7px;margin-top:17px;color:#7a849a;font-size:.7rem;font-weight:800}.mediums span{padding:5px 8px;border-radius:99px;background:#f4f5fa}
@keyframes loginFloat{50%{transform:translateY(-12px) rotate(5deg)}}
@media(min-width:820px){.login-shell{grid-template-columns:minmax(0,1.18fr) minmax(390px,.82fr)}.login-art{padding:55px}.login-panel{padding:48px 38px}}
@media(max-width:819px){.login-shell{min-height:0}.login-art{min-height:315px;padding:32px 25px}.art-logo{width:84px;height:84px;border-radius:23px}.login-art h1{margin-top:15px;font-size:2.45rem}.login-art .lead{font-size:.91rem}.subject-pills{margin-top:18px}.fun-icon{width:45px;height:45px;font-size:1.3rem}.fun-one{right:7%;top:17%}.fun-two{display:none}.login-panel{padding:30px 20px 34px}}
@media(max-width:420px){.top{display:none}.wrap{padding:12px 10px 35px}.login-shell{border-radius:27px}.login-art{min-height:288px}.subject-pills span:nth-child(n+4){display:none}.login-options{align-items:flex-start}.mediums{flex-wrap:wrap}}
/* K Education logo palette */
.login-shell{--k-blue:#006dcc;--k-blue-dark:#003f8f;--k-orange:#ff5a16;--k-gold:#ffad22;--k-charcoal:#17212d;border-color:rgba(255,173,34,.38);box-shadow:0 30px 90px rgba(0,63,143,.22)}
.login-art{background:radial-gradient(circle at 78% 22%,rgba(255,173,34,.3),transparent 31%),linear-gradient(145deg,#101923 0%,#003f8f 48%,#007bd6 100%)}
.art-logo{width:210px;height:210px;padding:0;border:3px solid rgba(255,173,34,.72);border-radius:30px;background:#4b4d4e;object-fit:contain;box-shadow:0 20px 55px rgba(0,0,0,.36),0 0 34px rgba(255,90,22,.24);transform:none}
.login-art{animation:loginPaletteCycle 20s steps(1,end) infinite}
.login-art .art-logo{border-color:rgba(255,255,255,.38);background:transparent;filter:drop-shadow(0 16px 18px rgba(0,0,0,.3));box-shadow:none}
@keyframes loginPaletteCycle{
  0%,19.999%{background:radial-gradient(circle at 78% 22%,rgba(255,173,34,.34),transparent 31%),linear-gradient(145deg,#101923 0%,#003f8f 48%,#007bd6 100%)}
  20%,39.999%{background:radial-gradient(circle at 76% 20%,rgba(255,122,24,.3),transparent 32%),linear-gradient(145deg,#052f46 0%,#006e9f 48%,#00a9c7 100%)}
  40%,59.999%{background:radial-gradient(circle at 76% 22%,rgba(255,191,56,.38),transparent 32%),linear-gradient(145deg,#34213f 0%,#8b3150 48%,#e35b28 100%)}
  60%,79.999%{background:radial-gradient(circle at 78% 22%,rgba(255,173,34,.32),transparent 31%),linear-gradient(145deg,#082c32 0%,#087b75 48%,#159e83 100%)}
  80%,100%{background:radial-gradient(circle at 78% 22%,rgba(255,129,24,.36),transparent 31%),linear-gradient(145deg,#1c1838 0%,#413485 48%,#155fa8 100%)}
}
.login-panel{background:linear-gradient(160deg,#fff 0%,#f4f9ff 58%,#fff7ef 100%)}
.welcome-badge{background:#fff0e7;color:#c43d08}
.login-box h2{color:var(--k-charcoal)}
.field input:focus{border-color:var(--k-blue);box-shadow:0 0 0 5px rgba(0,109,204,.12)}
.show-password:hover{background:#e9f4ff;color:var(--k-blue-dark)}
.login-button{background:linear-gradient(135deg,var(--k-orange),#ff7b18 58%,var(--k-gold));box-shadow:0 10px 25px rgba(255,90,22,.3)}
.login-button:hover{box-shadow:0 14px 32px rgba(255,90,22,.38)}
.join-card{border-color:#b9d9f5;background:linear-gradient(135deg,#edf7ff,#fff4e9)}
.join-card .btn{color:#fff;border:0;background:linear-gradient(135deg,var(--k-blue-dark),var(--k-blue));box-shadow:0 8px 22px rgba(0,63,143,.2)}
.mediums span{background:#edf6ff;color:#315b7f}
@media(max-width:819px){.art-logo{width:138px;height:138px;border-radius:23px}}
@media(max-width:420px){.art-logo{width:122px;height:122px}}
.login-art .art-logo{width:165px;height:165px}
.login-art h1{margin-top:17px}
.knowledge-intro{margin:0 0 12px;color:rgba(255,255,255,.82);font-size:.85rem;font-weight:750;letter-spacing:.04em}
.knowledge-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:8px;width:min(100%,560px)}
.knowledge-item{display:flex;align-items:center;gap:8px;min-width:0;padding:8px 10px;border:1px solid rgba(255,255,255,.18);border-radius:14px;background:rgba(255,255,255,.1);box-shadow:0 8px 22px rgba(0,0,0,.1);backdrop-filter:blur(8px);opacity:0;transform:translateY(12px) scale(.96);animation:knowledgeReveal .55s cubic-bezier(.2,.8,.2,1) forwards,knowledgePulse 9s ease-in-out infinite;animation-delay:calc(var(--i)*.11s),calc(1.3s + var(--i)*1s)}
.knowledge-item b{display:grid;flex:0 0 29px;place-items:center;width:29px;height:29px;border-radius:9px;background:linear-gradient(135deg,var(--k-orange),var(--k-gold));color:#17212d;box-shadow:0 5px 14px rgba(255,90,22,.28);font-size:1rem;font-weight:950}
.knowledge-item span{overflow:hidden;color:#fff;font-size:.75rem;font-weight:850;text-overflow:ellipsis;white-space:nowrap}
@keyframes knowledgeReveal{to{opacity:1;transform:none}}
@keyframes knowledgePulse{0%,11%{border-color:rgba(255,173,34,.95);background:rgba(255,90,22,.25);transform:translateY(-3px);box-shadow:0 10px 28px rgba(255,90,22,.22)}18%,100%{border-color:rgba(255,255,255,.18);background:rgba(255,255,255,.1);transform:none;box-shadow:0 8px 22px rgba(0,0,0,.1)}}
@media(max-width:819px){.login-art .art-logo{width:112px;height:112px}.knowledge-grid{gap:6px}.knowledge-item{padding:6px 7px;border-radius:11px}.knowledge-item b{flex-basis:25px;width:25px;height:25px;border-radius:7px;font-size:.86rem}.knowledge-item span{font-size:.65rem}}
@media(max-width:420px){.login-art{min-height:440px}.login-art .art-logo{width:98px;height:98px}.login-art h1{font-size:2rem}.knowledge-item{gap:5px;padding:5px}.knowledge-item span{font-size:.59rem}}
@media(prefers-reduced-motion:reduce){.knowledge-item{opacity:1;transform:none}}
.knowledge-stage{position:relative;width:min(100%,560px);height:92px;margin-top:4px;overflow:hidden;border:1px solid rgba(255,255,255,.2);border-radius:22px;background:rgba(5,24,49,.3);box-shadow:inset 0 1px rgba(255,255,255,.12),0 15px 35px rgba(0,0,0,.16);backdrop-filter:blur(10px)}
.knowledge-slide{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;gap:15px;padding:15px;opacity:0;transform:translateY(24px) scale(.94);filter:blur(7px);transition:opacity .42s ease,transform .55s cubic-bezier(.2,.9,.2,1),filter .42s ease;pointer-events:none}
.knowledge-slide.active{opacity:1;transform:none;filter:none}
.knowledge-slide .value-letter{display:grid;flex:0 0 52px;place-items:center;width:52px;height:52px;border-radius:16px;background:linear-gradient(135deg,var(--k-orange),var(--k-gold));color:#17212d;box-shadow:0 8px 22px rgba(255,90,22,.38);font-size:1.7rem;font-weight:1000}
.knowledge-slide .value-word{color:#fff;font-size:clamp(1.35rem,3.5vw,2rem);font-weight:900;letter-spacing:.01em}
.knowledge-final{gap:4px;flex-wrap:wrap}
.knowledge-final .final-letter{display:inline-block;color:#fff;font-size:clamp(1.55rem,4vw,2.45rem);font-weight:1000;letter-spacing:.08em;text-shadow:0 4px 18px rgba(0,0,0,.25);transform:translateY(12px);opacity:0}
.knowledge-final.active .final-letter{animation:finalLetterIn .42s cubic-bezier(.2,.9,.2,1) forwards;animation-delay:calc(var(--i)*.07s)}
.knowledge-final .final-letter:nth-child(odd){color:var(--k-gold)}
.knowledge-final small{flex-basis:100%;margin-top:2px;color:rgba(255,255,255,.76);font-size:.65rem;font-weight:850;letter-spacing:.2em;text-align:center}
@keyframes finalLetterIn{to{opacity:1;transform:none}}
@media(max-width:819px){.knowledge-stage{height:78px;border-radius:17px}.knowledge-slide{gap:10px;padding:10px}.knowledge-slide .value-letter{flex-basis:43px;width:43px;height:43px;border-radius:13px;font-size:1.35rem}.knowledge-slide .value-word{font-size:1.3rem}}
@media(prefers-reduced-motion:reduce){.knowledge-slide{transition:none}.knowledge-final.active .final-letter{animation:none;opacity:1;transform:none}}
@media(prefers-reduced-motion:reduce){.login-art{animation:none}}
</style>
<section class="login-shell">
<div class="login-art">
    <span class="fun-icon fun-one">🧪</span><span class="fun-icon fun-two">📐</span><span class="fun-icon fun-three">💡</span>
    <img class="art-logo" src="<?=url('logo/k-transparent.png')?>" alt="K Education logo">
    <h1>K Education</h1>
    <p class="knowledge-intro">🇱🇰 Empowering Sri Lanka's next generation through KNOWLEDGE</p>
    <div class="knowledge-stage" id="knowledgeStage" aria-label="KNOWLEDGE values">
        <div class="knowledge-slide active"><b class="value-letter">K</b><span class="value-word">Knowledge</span></div>
        <div class="knowledge-slide"><b class="value-letter">N</b><span class="value-word">Nurture</span></div>
        <div class="knowledge-slide"><b class="value-letter">O</b><span class="value-word">Opportunity</span></div>
        <div class="knowledge-slide"><b class="value-letter">W</b><span class="value-word">Wisdom</span></div>
        <div class="knowledge-slide"><b class="value-letter">L</b><span class="value-word">Learning</span></div>
        <div class="knowledge-slide"><b class="value-letter">E</b><span class="value-word">Excellence</span></div>
        <div class="knowledge-slide"><b class="value-letter">D</b><span class="value-word">Discovery</span></div>
        <div class="knowledge-slide"><b class="value-letter">G</b><span class="value-word">Growth</span></div>
        <div class="knowledge-slide"><b class="value-letter">E</b><span class="value-word">Empowerment</span></div>
        <div class="knowledge-slide knowledge-final" aria-label="KNOWLEDGE">
            <span class="final-letter" style="--i:0">K</span><span class="final-letter" style="--i:1">N</span><span class="final-letter" style="--i:2">O</span><span class="final-letter" style="--i:3">W</span><span class="final-letter" style="--i:4">L</span><span class="final-letter" style="--i:5">E</span><span class="final-letter" style="--i:6">D</span><span class="final-letter" style="--i:7">G</span><span class="final-letter" style="--i:8">E</span>
            <small>EMPOWERING NEW GENERATIONS</small>
        </div>
    </div>
</div>
<div class="login-panel"><div class="login-box">
    <span class="welcome-badge">👋 WELCOME BACK, STUDENT!</span>
    <h2><?=tr('login')?></h2><p class="muted">Continue your learning journey today.</p>
    <?php if($error):?><div class="alert error" role="alert"><?=e($error)?></div><?php endif;?>
    <form method="post"><?=csrf_field()?>
        <label class="field"><?=tr('username')?> / <?=tr('email')?><span class="field-icon">👤</span><input name="identity" autocomplete="username" placeholder="Enter username or email" value="<?=e((string)($_POST['identity']??''))?>" required autofocus></label>
        <label class="field password-field"><?=tr('password')?><span class="field-icon">🔒</span><input id="loginPassword" type="password" name="password" autocomplete="current-password" placeholder="Enter your password" required><button class="show-password" type="button" id="showPassword" aria-label="Show password" title="Show password">👁️</button></label>
        <div class="login-options"><label class="remember"><input type="checkbox" name="remember" value="1"> Keep me signed in</label><span class="badge">🎓 Grades 6–7</span></div>
        <button class="login-button" type="submit">🚀 <?=tr('login')?> <span>→</span></button>
    </form>
    <div class="join-card"><p>New to K Education? Create your student account and select your learning medium.</p><a class="btn" href="register.php">✨ <?=tr('register')?></a></div>
    <div class="mediums"><span>English Medium</span><span>සිංහල මාධ්‍ය</span><span>தமிழ் மூலம்</span></div>
</div></div>
</section>
<script>
(()=>{const stage=document.getElementById('knowledgeStage');if(!stage)return;const slides=[...stage.querySelectorAll('.knowledge-slide')];if(window.matchMedia('(prefers-reduced-motion: reduce)').matches){slides.forEach(x=>x.classList.remove('active'));slides[slides.length-1].classList.add('active');return}let index=1;const advance=()=>{slides.forEach(x=>x.classList.remove('active'));slides[index].classList.add('active');const hold=index===slides.length-1?3200:1600;index=(index+1)%slides.length;window.setTimeout(advance,hold)};window.setTimeout(advance,1600)})();
document.getElementById('showPassword').addEventListener('click',function(){const input=document.getElementById('loginPassword');const show=input.type==='password';input.type=show?'text':'password';this.textContent=show?'🙈':'👁️';this.setAttribute('aria-label',show?'Hide password':'Show password')});
</script>
<?php include __DIR__.'/includes/footer.php';?>
