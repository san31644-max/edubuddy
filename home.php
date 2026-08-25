<?php
require_once __DIR__.'/includes/auth.php';
if(user())redirect('student/dashboard.php');
if(parent_user())redirect('parent/dashboard.php');
$pageTitle='Welcome';
$pageDescription='Welcome to K Education - your complete learning journey.';
include __DIR__.'/includes/header.php';
?>
<style>
body{background:#030c15}.top{background:rgba(3,12,21,.72);border-color:rgba(78,201,255,.12)}.brand,.status-pill{color:#fff}.brandtext small{color:#a6c6d6}.wrap{width:100%;max-width:none;padding:0 0 50px}.scroll-hero{position:relative;min-height:calc(100vh - 75px);display:grid;place-items:center;overflow:hidden;background:#030c15;isolation:isolate}.scroll-hero:before{content:"";position:absolute;inset:0;background:radial-gradient(circle at 50% 48%,rgba(28,166,213,.18),transparent 36%),linear-gradient(to bottom,rgba(1,8,15,.08),rgba(1,7,13,.72));z-index:-2}.city-glow{position:absolute;inset:0;z-index:-3;background:url("<?=url('assets/images/k-education-scroll-v2.png')?>") center/cover no-repeat;filter:blur(16px) brightness(.4) saturate(1.35);transform:scale(1.08)}
.scroll-stage{position:relative;width:min(94vw,850px);height:min(84vh,940px);min-height:590px;perspective:1800px;filter:drop-shadow(0 35px 35px rgba(0,0,0,.55));transition:transform .4s}.scroll-image{position:absolute;inset:0;display:flex;transform-style:preserve-3d}.scroll-half{position:absolute;top:0;width:50%;height:100%;background-image:url("<?=url('assets/images/k-education-scroll-v2.png')?>");background-size:200% 100%;background-repeat:no-repeat;transition:transform 1.8s cubic-bezier(.16,.8,.18,1),filter 1.5s;will-change:transform;overflow:hidden}.scroll-half.left{left:0;background-position:left center;transform-origin:right;transform:translateX(49%) rotateY(5deg)}.scroll-half.right{right:0;background-position:right center;transform-origin:left;transform:translateX(-49%) rotateY(-5deg)}.scroll-stage.open .scroll-half.left{transform:translateX(0) rotateY(0)}.scroll-stage.open .scroll-half.right{transform:translateX(0) rotateY(0)}.scroll-stage:not(.open) .scroll-half{filter:brightness(.55)}.scroll-seam{position:absolute;z-index:5;left:50%;top:5%;bottom:5%;width:14px;transform:translateX(-50%);border-radius:50%;background:linear-gradient(90deg,#22130b,#8a5833 48%,#1b0e08);box-shadow:0 0 28px #000;transition:opacity .5s 1s}.scroll-stage.open .scroll-seam{opacity:0}
.scroll-content{position:absolute;z-index:8;left:24%;right:24%;top:20%;bottom:18%;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;color:#28170c;opacity:0;transform:scale(.8);filter:blur(8px);transition:opacity .7s 1.25s,transform .9s 1.2s,filter .7s 1.25s;pointer-events:none}.scroll-stage.open .scroll-content{opacity:1;transform:scale(1);filter:none;pointer-events:auto}.scroll-logo{width:clamp(110px,15vw,170px);height:clamp(110px,15vw,170px);object-fit:contain;border-radius:23px;margin-bottom:8px;filter:drop-shadow(0 7px 8px rgba(60,31,10,.3))}.scroll-content .tiny{margin:0;color:#75502d;font-size:.68rem;font-weight:900;letter-spacing:.18em;text-transform:uppercase}.scroll-content h1{margin:5px 0 6px;font:700 clamp(1.55rem,4.2vw,3.15rem)/1 Georgia,serif;color:#28170c;text-shadow:0 1px #eed59e}.scroll-content h1 span{display:block;color:#4331a8}.scroll-content .intro{max-width:430px;margin:2px auto 13px;font:italic clamp(.72rem,1.5vw,.96rem)/1.35 Georgia,serif;color:#65462f}.scroll-links{display:grid;grid-template-columns:1fr 1fr;gap:7px;width:100%;max-width:430px}.scroll-link{display:flex;align-items:center;justify-content:center;gap:6px;min-height:39px;padding:7px 9px;border:1px solid rgba(78,43,20,.22);border-radius:8px;background:rgba(255,244,211,.55);color:#372116;text-decoration:none;font-size:clamp(.67rem,1.35vw,.83rem);font-weight:900;box-shadow:0 3px 8px rgba(61,34,13,.1);transition:.2s}.scroll-link:hover{transform:translateY(-2px);background:#fff4d2}.scroll-link.primary{grid-column:1/-1;background:linear-gradient(135deg,#4a36b2,#1d78c7);color:#fff;border:0}.scroll-tag{margin-top:11px;font:italic .68rem Georgia,serif;color:#775236}
.scroll-prompt{position:absolute;z-index:15;left:50%;bottom:12%;transform:translateX(-50%);display:flex;flex-direction:column;align-items:center;color:#ffe6a2;text-align:center;text-shadow:0 2px 7px #000;transition:opacity .3s}.scroll-stage.open .scroll-prompt{opacity:0;pointer-events:none}.scroll-prompt button{min-height:45px;border:1px solid rgba(255,226,154,.65);background:rgba(15,12,20,.66);box-shadow:0 0 28px rgba(255,189,73,.2);animation:promptPulse 1.8s infinite}.scroll-prompt small{margin-top:7px}.replay-scroll{position:absolute;z-index:20;right:18px;bottom:18px;min-height:42px;padding:8px 14px;background:rgba(4,15,25,.72);border:1px solid rgba(123,211,255,.3);box-shadow:none;opacity:0;pointer-events:none}.scroll-stage.open+.replay-scroll{opacity:1;pointer-events:auto}.dust{position:absolute;z-index:3;width:4px;height:4px;border-radius:50%;background:#ffd27a;box-shadow:0 0 12px #ffd27a;opacity:0}.scroll-stage.open .dust{animation:dustFly 2.1s ease-out var(--d) both}
@keyframes promptPulse{50%{transform:scale(1.06);box-shadow:0 0 0 12px rgba(255,207,110,0)}}@keyframes dustFly{0%{opacity:0;transform:translate(0,20px)}30%{opacity:1}100%{opacity:0;transform:translate(var(--x),var(--y)) scale(.2)}}
@media(max-width:700px){.scroll-stage{width:100vw;height:calc(100vh - 70px);min-height:650px}.scroll-content{left:21%;right:21%;top:21%;bottom:17%}.scroll-links{grid-template-columns:1fr;gap:5px}.scroll-link.primary{grid-column:auto}.scroll-content .intro{margin-bottom:8px}.scroll-tag{display:none}.replay-scroll{right:8px;bottom:8px;font-size:.72rem}}
@media(max-width:390px){.scroll-content{left:20%;right:20%}.scroll-logo{width:100px;height:100px}.scroll-content h1{font-size:1.45rem}.scroll-link{min-height:34px;padding:5px;font-size:.65rem}}
@media(prefers-reduced-motion:reduce){.scroll-half,.scroll-content{transition-duration:.01ms!important}.scroll-prompt button,.dust{animation:none!important}}
</style>
<section class="scroll-hero">
<div class="city-glow"></div>
<div class="scroll-stage" id="scrollStage" aria-label="K Education opening welcome scroll">
 <div class="scroll-image" aria-hidden="true"><div class="scroll-half left"></div><div class="scroll-half right"></div><div class="scroll-seam"></div></div>
 <?php for($i=0;$i<18;$i++):?><i class="dust" style="left:<?=10+($i*5)%80?>%;top:<?=18+($i*11)%62?>%;--x:<?=($i%2?1:-1)*(30+$i*4)?>px;--y:<?=-60-($i%6)*25?>px;--d:<?=($i%5)*.1?>s"></i><?php endfor;?>
 <div class="scroll-content">
  <img class="scroll-logo" src="<?=url('logo/k-transparent.png')?>" alt="K Education">
  <p class="tiny">Your journey begins here</p><h1>Welcome to <span>K Education</span></h1>
  <p class="intro">Open the door to lessons, quizzes, AI guidance and a brighter future.</p>
  <nav class="scroll-links" aria-label="K Education menu">
   <a class="scroll-link primary" href="<?=url('register.php')?>">&#10022; Join K Education</a>
   <a class="scroll-link" href="<?=url('login.php')?>">&#127891; Student Login</a>
   <a class="scroll-link" href="<?=url('parent/login.php')?>">&#128106; Parent Portal</a>
   <a class="scroll-link" href="<?=url('ol-kuppiya/index.php')?>">&#128221; O/L Kuppiya</a>
   <a class="scroll-link" href="<?=url('download.php')?>">&#128241; Android App</a>
  </nav><p class="scroll-tag">Grades 6-11 &bull; Sinhala &bull; Tamil &bull; English</p>
 </div>
 <div class="scroll-prompt"><button id="openScroll" type="button">Unroll your future &nbsp; &#10095;</button><small>Tap to open</small></div>
</div>
<button class="replay-scroll" id="replayScroll" type="button">&#8634; Replay opening</button>
</section>
<script>
(()=>{const stage=document.getElementById('scrollStage'),open=document.getElementById('openScroll'),replay=document.getElementById('replayScroll');const reveal=()=>stage.classList.add('open');open.addEventListener('click',reveal);stage.addEventListener('click',e=>{if(!stage.classList.contains('open'))reveal()});replay.addEventListener('click',()=>{stage.classList.remove('open');setTimeout(reveal,650)});setTimeout(reveal,900)})();
</script>
<?php include __DIR__.'/includes/footer.php'; ?>
