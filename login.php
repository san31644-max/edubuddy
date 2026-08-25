<?php
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
require_once __DIR__.'/includes/auth.php';require_once __DIR__.'/includes/textlk.php';require_guest();$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    verify_csrf();$key=strtolower(trim((string)($_POST['identity']??'')));
    $bucket='login_'.hash('sha256',($_SERVER['REMOTE_ADDR']??'local').$key);$tries=$_SESSION[$bucket]??['n'=>0,'at'=>0];
    if($tries['n']>=5&&time()-$tries['at']<300)$error='Too many attempts. Try again in five minutes.';
    else{
        $phone=normalize_sri_lankan_phone($key)??$key;
        $u=query_one('SELECT id,full_name,username,email,phone,phone_verified_at,school_name,district,password_hash,grade_id,medium,preferred_language,profile_image,subscription_expires_at,referral_promoter_id,referral_code_used,status FROM users WHERE (username=? OR email=? OR phone=?) LIMIT 1','sss',[$key,$key,$phone]);
        if($u&&$u['status']==='active'&&password_verify((string)($_POST['password']??''),$u['password_hash'])){
            unset($u['password_hash'],$_SESSION[$bucket]);session_regenerate_id(true);$u['preferred_language']=medium_language((string)$u['medium']);$_SESSION['user']=$u;$_SESSION['lang']=$u['preferred_language'];if((int)($_SESSION['onboarding_user_id']??0)===(int)$u['id'])$_SESSION['new_student_onboarding']=true;unset($_SESSION['onboarding_user_id']);redirect('student/dashboard.php');
        }
        $tries=['n'=>$tries['n']+1,'at'=>time()];$_SESSION[$bucket]=$tries;$error='Incorrect phone number or password.';
    }
}
$pageTitle='Student Login';include __DIR__.'/includes/header.php';?>
<style>
@import url('https://fonts.googleapis.com/css2?family=Lobster&family=Playball&display=swap');
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
.login-art .art-logo{width:180px;height:180px}
.login-art h1{margin-top:17px;font-family:"Lobster","Brush Script MT","Segoe Script",cursive;font-size:clamp(3.25rem,7vw,5.6rem);font-weight:400;line-height:.95;letter-spacing:.025em;text-shadow:0 3px 0 rgba(0,0,0,.16),0 8px 24px rgba(0,0,0,.28),0 0 28px rgba(255,173,34,.3);transform:none}
.login-art h1 .k-letter{display:inline-block;font-family:"Playball","Brush Script MT","Segoe Script",cursive;font-size:1.35em;font-weight:400;line-height:.7}
.login-art h1 .education-word{display:inline-block;margin-left:.42em}
.knowledge-intro{margin:0 0 12px;color:rgba(255,255,255,.82);font-size:.85rem;font-weight:750;letter-spacing:.04em}
.knowledge-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:8px;width:min(100%,560px)}
.knowledge-item{display:flex;align-items:center;gap:8px;min-width:0;padding:8px 10px;border:1px solid rgba(255,255,255,.18);border-radius:14px;background:rgba(255,255,255,.1);box-shadow:0 8px 22px rgba(0,0,0,.1);backdrop-filter:blur(8px);opacity:0;transform:translateY(12px) scale(.96);animation:knowledgeReveal .55s cubic-bezier(.2,.8,.2,1) forwards,knowledgePulse 9s ease-in-out infinite;animation-delay:calc(var(--i)*.11s),calc(1.3s + var(--i)*1s)}
.knowledge-item b{display:grid;flex:0 0 29px;place-items:center;width:29px;height:29px;border-radius:9px;background:linear-gradient(135deg,var(--k-orange),var(--k-gold));color:#17212d;box-shadow:0 5px 14px rgba(255,90,22,.28);font-size:1rem;font-weight:950}
.knowledge-item span{overflow:hidden;color:#fff;font-size:.75rem;font-weight:850;text-overflow:ellipsis;white-space:nowrap}
@keyframes knowledgeReveal{to{opacity:1;transform:none}}
@keyframes knowledgePulse{0%,11%{border-color:rgba(255,173,34,.95);background:rgba(255,90,22,.25);transform:translateY(-3px);box-shadow:0 10px 28px rgba(255,90,22,.22)}18%,100%{border-color:rgba(255,255,255,.18);background:rgba(255,255,255,.1);transform:none;box-shadow:0 8px 22px rgba(0,0,0,.1)}}
@media(max-width:819px){.login-art .art-logo{width:120px;height:120px}.knowledge-grid{gap:6px}.knowledge-item{padding:6px 7px;border-radius:11px}.knowledge-item b{flex-basis:25px;width:25px;height:25px;border-radius:7px;font-size:.86rem}.knowledge-item span{font-size:.65rem}}
@media(max-width:420px){.login-art{min-height:440px}.login-art .art-logo{width:105px;height:105px}.login-art h1{font-size:3rem;word-spacing:.16em}.knowledge-item{gap:5px;padding:5px}.knowledge-item span{font-size:.59rem}}
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
/* Premium refinement — preserves the original K Education login concept. */
.login-shell:before{content:"";position:absolute;inset:0;z-index:5;border:1px solid rgba(255,255,255,.72);border-radius:inherit;pointer-events:none}.login-shell:after{content:"";position:absolute;z-index:6;top:0;left:12%;width:32%;height:2px;background:linear-gradient(90deg,transparent,var(--k-gold),transparent);pointer-events:none;animation:loginShimmer 7s ease-in-out infinite}.login-art{min-width:0}.login-art:before{background:radial-gradient(circle,rgba(255,255,255,.2),rgba(255,255,255,0) 68%)}.login-art:after{box-shadow:0 0 0 55px rgba(255,255,255,.025),0 0 0 110px rgba(255,255,255,.018)}
.login-panel{position:relative;isolation:isolate}.login-panel:before{content:"";position:absolute;z-index:-1;top:7%;right:8%;width:170px;height:170px;border-radius:50%;background:radial-gradient(circle,rgba(0,109,204,.09),transparent 68%)}.login-box{padding:8px}.welcome-badge{border:1px solid rgba(255,90,22,.13);box-shadow:0 7px 20px rgba(255,90,22,.08)}.login-box h2{letter-spacing:-.035em}.field{display:block;color:#26384a;font-size:.84rem;font-weight:850}.field input{min-height:54px;border:1px solid #d8e4ef;border-radius:15px;box-shadow:0 4px 13px rgba(20,54,82,.035);transition:border-color .2s,box-shadow .2s,background .2s}.field input::placeholder{color:#98a7b7}.field input:hover{border-color:#b9cfe2}.field .field-icon{top:43px}.show-password{top:35px}.login-button{position:relative;overflow:hidden;border:0;border-radius:15px;background:linear-gradient(115deg,var(--k-blue-dark),var(--k-blue) 58%,#0b96d2);box-shadow:0 14px 28px rgba(0,83,163,.24)}.login-button:before{content:"";position:absolute;inset:-2px auto -2px -35%;width:30%;transform:skewX(-20deg);background:linear-gradient(90deg,transparent,rgba(255,255,255,.32),transparent);transition:left .55s ease}.login-button:hover:before{left:115%}.login-button:disabled{cursor:wait;opacity:.75;transform:none}.join-card{box-shadow:0 12px 28px rgba(20,72,116,.07)}.join-card .btn{border-radius:13px}.mediums span{border:1px solid rgba(0,109,204,.07)}.secure-note{display:flex;align-items:center;justify-content:center;gap:7px;margin:14px 0 0;color:#74869a;font-size:.7rem;font-weight:750}.secure-note b{display:grid;width:20px;height:20px;place-items:center;border-radius:50%;background:#e7f8f2;color:#07865f}.field-help{display:block;margin:6px 2px 0;color:#7c8b9b;font-size:.68rem;font-weight:650}.login-divider{display:flex;align-items:center;gap:10px;margin:18px 0;color:#96a4b2;font-size:.67rem;font-weight:800;text-transform:uppercase;letter-spacing:.08em}.login-divider:before,.login-divider:after{content:"";height:1px;flex:1;background:#e1eaf2}
@keyframes loginShimmer{0%,100%{transform:translateX(-25%);opacity:.35}50%{transform:translateX(145%);opacity:1}}
@media(max-width:819px){.login-box{padding:0}.login-shell:after{display:none}.login-art{min-height:430px}.login-panel{padding-top:35px}}
@media(max-width:420px){.login-art{min-height:440px}.login-panel{padding-inline:18px}.login-box h2{font-size:2rem}.login-options{font-size:.78rem}.secure-note{line-height:1.4;text-align:center}}
@media(prefers-reduced-motion:reduce){.login-shell:after,.login-button:before{animation:none;transition:none}}

/* Premium phone-first login experience. */
@media(max-width:600px){
 html,body{min-height:100%;background:#07182a}
 body:before,body:after{display:none}
 .top{display:none}
 .wrap{width:100%;min-height:100dvh;padding:8px;background:radial-gradient(circle at 15% 0,rgba(0,143,214,.35),transparent 38%),radial-gradient(circle at 90% 100%,rgba(255,106,24,.23),transparent 38%),#07182a}
 .login-shell{display:flex;flex-direction:column;min-height:calc(100dvh - 16px);overflow:hidden;border:1px solid rgba(255,255,255,.25);border-radius:27px;background:#f8fbff;box-shadow:0 24px 65px rgba(0,0,0,.38)}
 .login-shell:before{border-color:rgba(255,255,255,.26);border-radius:27px}
 .login-shell:after{display:block;top:0;left:12%;height:2px;animation:none}
 .login-art{display:grid;flex:0 0 178px;grid-template-columns:70px 1fr;align-items:center;align-content:center;justify-content:initial;gap:15px;min-height:178px!important;padding:11px 20px;background:radial-gradient(circle at 85% 0,rgba(255,173,34,.38),transparent 40%),linear-gradient(135deg,#071b31,#004f91 55%,#008bc4)}
 .login-art:before{width:190px;height:190px;right:-80px;top:-105px}
 .login-art:after{width:160px;height:160px;left:-90px;bottom:-105px}
 .login-art .art-logo{grid-row:1;width:66px;height:66px;padding:3px;border:2px solid rgba(255,196,86,.8);border-radius:24px;background:rgba(255,255,255,.96);filter:drop-shadow(0 10px 14px rgba(0,0,0,.28))}
 .login-art h1{grid-column:2;grid-row:1;margin:0;font-size:2.35rem;line-height:.9;text-align:left;text-shadow:0 3px 15px rgba(0,0,0,.38)}
 .login-art h1 .k-letter{font-size:1.25em}
 .login-art h1 .education-word{display:block;margin:2px 0 0}
 .fun-icon,.subject-pills,.login-art .lead{display:none}

 .knowledge-intro{display:none;grid-column:1/-1;margin:0;color:rgba(255,255,255,.82);font-size:.61rem;line-height:1.2;text-align:center;letter-spacing:.025em}
 .knowledge-stage{display:block;grid-column:1/-1;width:100%;height:52px;margin:2px 0 0;border-radius:15px}
 .knowledge-slide{gap:9px;padding:7px 10px}
 .knowledge-slide .value-letter{flex-basis:38px;width:38px;height:38px;border-radius:11px;font-size:1.12rem}
 .knowledge-slide .value-word{font-size:1.08rem}
 .knowledge-final{gap:2px}
 .knowledge-final .final-letter{font-size:1.18rem}
 .knowledge-final small{font-size:.48rem}
 .login-panel{flex:1;align-items:flex-start;padding:14px 18px 14px;background:linear-gradient(165deg,#fff 0%,#f3f8fd 64%,#fff7ef 100%)}
 .login-panel:before{top:-30px;right:-25px;width:145px;height:145px}
 .login-box{width:100%;margin:0 auto;padding:0}
 .welcome-badge{padding:5px 9px;border-radius:9px;font-size:.65rem;letter-spacing:.025em}
 .login-box h2{margin:8px 0 3px;font-size:1.75rem}
 .login-box>.muted{margin:0 0 9px;font-size:.8rem}
 .alert{padding:9px 11px;margin-bottom:9px;border-radius:11px;font-size:.78rem}
 .field{font-size:.77rem;letter-spacing:.005em}
 .field input{min-height:46px;margin:3px 0 7px;padding-top:10px;padding-bottom:10px;border-radius:13px;background:#fff;box-shadow:0 5px 15px rgba(11,60,98,.055)}
 .field .field-icon{top:37px}
 .field-help{display:none}
 .show-password{top:28px;min-height:40px;height:40px}
 .login-options{margin:0 0 9px}
 .remember{font-size:.73rem}
 .login-options>a{font-size:.75rem;font-weight:800;text-decoration:none}
 .login-button{min-height:49px;border-radius:14px;font-size:.93rem;box-shadow:0 11px 24px rgba(0,83,163,.25)}
 .login-divider{margin:8px 0 6px;font-size:.59rem}
 .join-card{display:flex;align-items:center;gap:9px;margin:0;padding:7px;border:0;border-radius:13px;background:#eaf4fd;box-shadow:none}
 .join-card p{display:none}
 .join-card .btn{flex:1 1 auto;width:100%;min-height:39px;padding:7px 13px;border-radius:10px;font-size:.76rem;white-space:nowrap}
 .mediums{margin-top:10px;gap:4px}
 .mediums span{padding:3px 6px;font-size:.58rem}
 .secure-note{display:none}
}
@media(max-width:370px){
 .login-art{grid-template-columns:74px 1fr;gap:11px;padding-inline:18px}
 .login-art .art-logo{width:72px;height:72px;border-radius:19px}
 .login-art h1{font-size:2rem}
 .login-panel{padding-inline:16px}
 .join-card p{display:none}
 .join-card .btn{width:100%}
}
@media(max-width:600px) and (max-height:720px){
 .wrap{padding:5px}
 .login-shell{min-height:calc(100dvh - 10px);border-radius:22px}
 .login-art{flex-basis:132px;grid-template-columns:50px 1fr;min-height:132px!important;padding:8px 16px}
 .login-art .art-logo{width:52px;height:52px;border-radius:14px}
 .login-art h1{font-size:1.65rem}

 .knowledge-intro{display:none}
 .knowledge-stage{display:block;height:43px;margin-top:2px;border-radius:12px}
 .knowledge-slide{padding:5px 8px}
 .knowledge-slide .value-letter{flex-basis:33px;width:33px;height:33px;font-size:1rem}
 .knowledge-slide .value-word{font-size:.98rem}
 .login-panel{padding-top:12px;padding-bottom:11px}
 .welcome-badge{display:none}
 .login-box h2{margin-top:0}
 .login-box>.muted{margin-bottom:8px}
 .field input{min-height:44px;margin-bottom:7px}
 .field .field-icon{top:34px}
 .show-password{top:25px}
 .login-options{margin-bottom:8px}
 .login-button{min-height:45px}
 .login-divider{margin:7px 0}
 .mediums{display:none}
}

/* Ancient cinematic theme shared with the opening-scroll landing page. */
body{background:#06111b}
.login-shell{border-color:#a8793e;background:#1a1109;box-shadow:0 32px 90px rgba(0,0,0,.42),0 0 0 1px rgba(244,196,110,.12)}
.login-shell:before{border-color:rgba(255,220,151,.38)}
.login-shell:after{background:linear-gradient(90deg,transparent,#ffd47d,transparent)}
.login-art{animation:none;background:linear-gradient(135deg,rgba(3,14,24,.91),rgba(5,57,78,.7) 52%,rgba(76,42,15,.68)),url("<?=url('assets/images/k-education-scroll-v2.png')?>") center/cover no-repeat}
.login-art:before{background:radial-gradient(circle,rgba(255,202,105,.22),transparent 68%)}
.login-art .art-logo{border-color:#d6a44e;background:rgba(255,250,229,.96);box-shadow:0 0 0 5px rgba(73,42,18,.45),0 15px 35px rgba(0,0,0,.38);filter:none}
.login-art h1{font-family:Georgia,"Times New Roman",serif;text-shadow:0 3px 0 #2e190b,0 0 24px rgba(255,196,84,.35)}
.login-art h1 .k-letter{font-family:Georgia,"Times New Roman",serif;color:#ffd27b}
.knowledge-intro{color:#efd7a8}
.knowledge-stage{border-color:rgba(255,206,113,.45);background:linear-gradient(135deg,rgba(21,12,7,.82),rgba(7,47,62,.82));box-shadow:inset 0 0 24px rgba(0,0,0,.38),0 0 22px rgba(255,177,58,.08)}
.knowledge-slide .value-letter{background:linear-gradient(145deg,#8b4d18,#f0a633 58%,#ffd778);color:#291407;box-shadow:0 6px 17px rgba(0,0,0,.35),inset 0 1px rgba(255,255,255,.45)}
.knowledge-slide .value-word,.knowledge-final .final-letter{font-family:Georgia,"Times New Roman",serif;color:#fff1cf}
.knowledge-final .final-letter:nth-child(odd){color:#ffc85f}
.login-panel{background-color:#f1ddb0;background-image:radial-gradient(circle at 12% 18%,rgba(117,73,28,.08),transparent 24%),radial-gradient(circle at 87% 72%,rgba(100,57,20,.08),transparent 27%),repeating-linear-gradient(0deg,rgba(103,68,32,.025) 0 1px,transparent 1px 4px);box-shadow:inset 12px 0 28px rgba(71,39,13,.13)}
.login-panel:after{content:"";position:absolute;inset:12px;z-index:-1;border:1px solid rgba(105,64,25,.2);border-radius:18px;pointer-events:none}
.welcome-badge{border-color:rgba(120,67,22,.25);background:rgba(255,242,205,.72);color:#85400e;box-shadow:0 5px 14px rgba(73,39,11,.09)}
.login-box h2{font-family:Georgia,"Times New Roman",serif;color:#302015}
.login-box>.muted{color:#705d4b}
.field{color:#3e2b1e}
.field input{border-color:#cdb990;background:rgba(255,251,235,.78);color:#302015;box-shadow:inset 0 1px 3px rgba(81,50,20,.07),0 5px 13px rgba(71,44,18,.05)}
.field input:hover{border-color:#ad8551}.field input:focus{border-color:#9c6224;background:#fffdf4;box-shadow:0 0 0 4px rgba(156,98,36,.13)}
.field input::placeholder{color:#9a8975}.field-help,.remember{color:#786550}.show-password{color:#735238}.show-password:hover{background:#ebd9b4;color:#4e2e15}
.login-options>a{color:#7046bd}
.login-button{border:1px solid #d7a34c;background:linear-gradient(135deg,#162f43,#075774 56%,#98702e);box-shadow:0 12px 28px rgba(15,43,58,.3),inset 0 1px rgba(255,231,174,.3)}
.login-divider{color:#88745e}.login-divider:before,.login-divider:after{background:#cdbb94}
.join-card{border-color:#b9925d;background:rgba(255,243,211,.58);box-shadow:0 9px 22px rgba(74,44,18,.08)}
.join-card .btn{background:linear-gradient(135deg,#734213,#b97828);box-shadow:0 7px 18px rgba(92,50,14,.23)}
.mediums span{border-color:rgba(112,71,32,.15);background:rgba(255,244,214,.58);color:#674b32}
.secure-note{color:#77644f}
@media(max-width:600px){
 .wrap{background:radial-gradient(circle at 15% 0,rgba(8,105,137,.3),transparent 38%),radial-gradient(circle at 90% 100%,rgba(162,91,22,.24),transparent 38%),#06111b}
 .login-shell{background:#26160b}
 .login-art{background:linear-gradient(115deg,rgba(3,16,25,.92),rgba(3,75,91,.72),rgba(89,48,15,.76)),url("<?=url('assets/images/k-education-scroll-v2.png')?>") center 28%/cover no-repeat}
 .login-panel{box-shadow:inset 0 10px 25px rgba(75,43,17,.11)}
 .login-panel:after{inset:8px;border-radius:15px}
 .join-card{background:rgba(255,242,207,.7)}
}

/* Cinematic motion layer for the ancient login. */
.login-shell{transform-origin:center;animation:ancientArrival .9s cubic-bezier(.16,.85,.18,1) both}
.login-art{background-size:112% 112%;animation:ancientBackdrop 16s ease-in-out infinite alternate}
.login-art .art-logo{animation:ancientLogoFloat 4.8s ease-in-out infinite}
.login-art h1{animation:titleGlow 4s ease-in-out infinite}
.login-panel{transform-origin:top center;animation:parchmentUnroll 1.05s cubic-bezier(.16,.82,.18,1) .18s both}
.login-panel:before{animation:parchmentGlow 6s ease-in-out infinite}
.knowledge-stage{overflow:hidden;animation:runePulse 4s ease-in-out infinite}
.knowledge-stage:after{content:"";position:absolute;z-index:5;inset:-40% auto -40% -35%;width:22%;transform:skewX(-18deg);background:linear-gradient(90deg,transparent,rgba(255,222,153,.28),transparent);animation:runeSweep 5.5s ease-in-out infinite;pointer-events:none}
.login-box>*{animation:formRuneIn .55s cubic-bezier(.2,.8,.2,1) both}
.login-box>*:nth-child(1){animation-delay:.75s}.login-box>*:nth-child(2){animation-delay:.82s}.login-box>*:nth-child(3){animation-delay:.89s}.login-box>*:nth-child(4){animation-delay:.96s}.login-box>*:nth-child(5){animation-delay:1.03s}.login-box>*:nth-child(6){animation-delay:1.1s}.login-box>*:nth-child(7){animation-delay:1.17s}
.ancient-ember{position:absolute;z-index:4;left:var(--left);bottom:-14px;width:var(--size);height:var(--size);border-radius:50%;background:#ffd36d;box-shadow:0 0 8px #ff9d31,0 0 16px rgba(255,173,51,.5);opacity:0;pointer-events:none;animation:emberRise var(--speed) linear var(--delay) infinite}
@keyframes ancientArrival{from{opacity:0;transform:translateY(28px) scale(.96);filter:blur(8px)}to{opacity:1;transform:none;filter:none}}
@keyframes ancientBackdrop{0%{background-position:47% 45%;background-size:112% 112%}100%{background-position:54% 58%;background-size:120% 120%}}
@keyframes ancientLogoFloat{0%,100%{transform:translateY(0) rotate(-1deg)}50%{transform:translateY(-7px) rotate(1.5deg);filter:drop-shadow(0 13px 17px rgba(255,180,53,.22))}}
@keyframes titleGlow{0%,100%{filter:none}50%{filter:drop-shadow(0 0 9px rgba(255,202,101,.28))}}
@keyframes parchmentUnroll{from{opacity:0;transform:scaleY(.18) translateY(-35px);filter:brightness(.65) blur(5px)}to{opacity:1;transform:none;filter:none}}
@keyframes parchmentGlow{0%,100%{opacity:.55;transform:scale(.9)}50%{opacity:1;transform:scale(1.15)}}
@keyframes runePulse{0%,100%{box-shadow:inset 0 0 24px rgba(0,0,0,.38),0 0 10px rgba(255,177,58,.06)}50%{box-shadow:inset 0 0 28px rgba(0,0,0,.46),0 0 25px rgba(255,177,58,.22)}}
@keyframes runeSweep{0%,58%{left:-35%;opacity:0}68%{opacity:1}88%,100%{left:125%;opacity:0}}
@keyframes formRuneIn{from{opacity:0;transform:translateY(13px)}to{opacity:1;transform:none}}
@keyframes emberRise{0%{opacity:0;transform:translate(0,0) scale(.4)}12%{opacity:.85}70%{opacity:.55}100%{opacity:0;transform:translate(var(--drift),-210px) scale(1.15)}}
@media(max-width:600px){.ancient-ember{animation-duration:calc(var(--speed) * .85)}.login-panel{animation-duration:.82s}.login-art .art-logo{animation-duration:4s}}
@media(prefers-reduced-motion:reduce){.login-shell,.login-art,.login-art .art-logo,.login-art h1,.login-panel,.login-panel:before,.knowledge-stage,.knowledge-stage:after,.login-box>*,.ancient-ember{animation:none!important}}
</style>
<section class="login-shell">
<div class="login-art">
    <span class="fun-icon fun-one">🧪</span><span class="fun-icon fun-two">📐</span><span class="fun-icon fun-three">💡</span>
    <img class="art-logo" src="<?=url('logo/k-transparent.png')?>" alt="K Education logo">
    <h1><span class="k-letter">K</span><span class="education-word">Education</span></h1>
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
    <form method="post" id="studentLoginForm"><?=csrf_field()?>
        <label class="field">Phone number, username or email<span class="field-icon">👤</span><input name="identity" autocomplete="username" placeholder="Enter your login ID" value="<?=e((string)($_POST['identity']??''))?>" required autofocus><small class="field-help">Use the details registered with your student account.</small></label>
        <label class="field password-field"><?=tr('password')?><span class="field-icon">🔒</span><input id="loginPassword" type="password" name="password" autocomplete="current-password" placeholder="Enter your password" required><button class="show-password" type="button" id="showPassword" aria-label="Show password" title="Show password">👁️</button></label>
        <div class="login-options"><label class="remember"><input type="checkbox" name="remember" value="1"> Keep me signed in</label><a href="forgot-password.php">Forgot password?</a></div>
        <button class="login-button" id="studentLoginButton" type="submit">🚀 <?=tr('login')?> <span>→</span></button>
    </form>
    <div class="login-divider">or join K Education</div>
    <div class="join-card"><p>New to K Education? Create your student account and select your learning medium.</p><a class="btn" href="register.php">✨ <?=tr('register')?></a></div>
    <div class="mediums"><span>English Medium</span><span>සිංහල මාධ්‍ය</span><span>தமிழ் மூலம்</span></div>
    <p class="secure-note"><b>✓</b> Secure student access with protected login attempts</p>
</div></div>
</section>
<script>
(()=>{const stage=document.getElementById('knowledgeStage');if(!stage)return;const slides=[...stage.querySelectorAll('.knowledge-slide')];if(window.matchMedia('(prefers-reduced-motion: reduce)').matches){slides.forEach(x=>x.classList.remove('active'));slides[slides.length-1].classList.add('active');return}let index=1;const advance=()=>{slides.forEach(x=>x.classList.remove('active'));slides[index].classList.add('active');const hold=index===slides.length-1?3200:1600;index=(index+1)%slides.length;window.setTimeout(advance,hold)};window.setTimeout(advance,1600)})();
document.getElementById('showPassword').addEventListener('click',function(){const input=document.getElementById('loginPassword');const show=input.type==='password';input.type=show?'text':'password';this.textContent=show?'🙈':'👁️';this.setAttribute('aria-label',show?'Hide password':'Show password')});
document.getElementById('studentLoginForm').addEventListener('submit',function(){const button=document.getElementById('studentLoginButton');button.disabled=true;button.textContent='Signing in…'});
</script>

<script>
(()=>{const art=document.querySelector('.login-art');if(!art||matchMedia('(prefers-reduced-motion: reduce)').matches)return;for(let i=0;i<18;i++){const ember=document.createElement('i');ember.className='ancient-ember';ember.setAttribute('aria-hidden','true');ember.style.setProperty('--left',(4+(i*17)%93)+'%');ember.style.setProperty('--size',(2+(i%4))+'px');ember.style.setProperty('--speed',(4.2+(i%6)*.7)+'s');ember.style.setProperty('--delay',(-i*.53)+'s');ember.style.setProperty('--drift',((i%2?1:-1)*(12+(i%5)*9))+'px');art.appendChild(ember)}})();
</script>
<?php include __DIR__.'/includes/footer.php';?>
