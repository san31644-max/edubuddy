<?php
declare(strict_types=1);
require_once __DIR__.'/auth.php';
require_login();refresh_user();
require_once __DIR__.'/student_dashboard_data.php';
require_once __DIR__.'/ai_quota.php';
$uid=(int)user()['id'];$medium=(string)user()['medium'];$gradeNumber=user_grade_number();
$game=student_dashboard_snapshot(user());$premium=is_premium();$offer=premium_offer_for_user($uid);
$aiRemaining=$premium?null:max(0,FREE_AI_DAILY_LIMIT-free_ai_used_today($uid));
$firstName=preg_split('/\s+/u',trim((string)user()['full_name']))[0]??'Explorer';
$initial=mb_strtoupper(mb_substr($firstName,0,1));
$nextUrl=$game['next']?url('student/lesson.php?id='.(int)$game['next']['id']):url('student/subjects.php');
$progress=$game['total']?min(100,(int)round(100*$game['completed']/$game['total'])):0;
$levelProgress=(int)round(100*$game['level_progress']/250);
$missionCount=(int)!empty($game['today']['opened'])+(int)!empty($game['today']['completed'])+(int)!empty($game['today']['quizzes']);
$trailSubject=null;foreach($game['subjects'] as $subject)if((int)$subject['id']===$game['trail_subject'])$trailSubject=$subject;
$showOnboarding=!empty($_SESSION['show_dashboard_guide'])||!empty($_SESSION['new_student_onboarding'])||filter_input(INPUT_GET,'tour',FILTER_VALIDATE_INT)===1;unset($_SESSION['show_dashboard_guide'],$_SESSION['new_student_onboarding']);
$showAyubowan=!empty($_SESSION['show_ayubowan']);unset($_SESSION['show_ayubowan']);
function qd_icon(string $name):string {return game_icon($name);}
$pageTitle='Your learning adventure';include __DIR__.'/header.php';
?>
<script>document.body.classList.add('student-game-dashboard');</script>
<link rel="stylesheet" href="<?=url('assets/css/student-dashboard.css')?>?v=<?=filemtime(__DIR__.'/../assets/css/student-dashboard.css')?>">
<link rel="stylesheet" href="<?=url('assets/css/student-dashboard-game.css')?>?v=<?=filemtime(__DIR__.'/../assets/css/student-dashboard-game.css')?>">
<link rel="stylesheet" href="<?=url('assets/css/student-dashboard-motion.css')?>?v=<?=filemtime(__DIR__.'/../assets/css/student-dashboard-motion.css')?>">
<link rel="stylesheet" href="<?=url('assets/css/student-subject-wheel.css')?>?v=<?=filemtime(__DIR__.'/../assets/css/student-subject-wheel.css')?>">
<link rel="stylesheet" href="<?=url('assets/css/student-trail-3d.css')?>?v=<?=filemtime(__DIR__.'/../assets/css/student-trail-3d.css')?>">
<link rel="stylesheet" href="<?=url('assets/css/student-trophy-celebration.css')?>?v=<?=filemtime(__DIR__.'/../assets/css/student-trophy-celebration.css')?>">
<?php if($showAyubowan):?><link rel="stylesheet" href="<?=url('assets/css/student-ayubowan.css')?>?v=<?=filemtime(__DIR__.'/../assets/css/student-ayubowan.css')?>"><?php endif;?>
<?php if($showOnboarding):?><link rel="stylesheet" href="<?=url('assets/css/student-dashboard-tour.css')?>?v=<?=filemtime(__DIR__.'/../assets/css/student-dashboard-tour.css')?>"><?php endif;?>
<div class="qd">
 <aside class="qd-sidebar" aria-label="Learning navigation">
  <div class="qd-side-title"><span class="qd-game-mark"><?=qd_icon('bolt')?></span><div>THE LEARNING CLUB<small>Small steps. Big discoveries.</small></div></div>
  <div class="qd-nav-label">YOUR WORLD</div>
  <nav class="qd-nav"><?php foreach([['home','Base camp','student/dashboard.php'],['book','My subjects','student/subjects.php'],['star','Play & relax','student/play-relax.php'],['target','Practice arena',$premium?'student/quiz.php':'subscription.php'],['trophy','Leaderboard','student/leaderboard.php'],['chart','My progress','student/progress.php'],['users','Parent access','student/parent_access.php']] as $index=>$item):?><a class="<?=$index===0?'active':''?>" href="<?=url($item[2])?>"<?=$index===0?' aria-current="page"':''?>><?=qd_icon($item[0])?><span><?=e($item[1])?></span><?=$index===0?'<i></i>':''?></a><?php endforeach;?></nav>
  <div class="qd-side-note"><span>✦</span><strong>Curiosity is your superpower.</strong><p>One lesson today. A little more confidence tomorrow.</p></div>
  <a class="qd-side-profile" href="<?=url('profile.php')?>"><span class="qd-avatar-mini"><?=e($initial)?></span><span><?=e($firstName)?><small>My profile & settings</small></span><?=qd_icon('arrow')?></a>
 </aside>
 <div class="qd-content">
  <header class="qd-welcome"><div><p class="qd-eyebrow">YOUR LEARNING ADVENTURE</p><h1>Ready to level up, <?=e($firstName)?>?</h1><p>A little curiosity goes a long way. Let’s make today count.</p></div><div class="qd-date"><span><?=date('d')?></span><div><?=date('M Y')?><small><?=date('l')?></small></div></div></header>
  <nav class="qd-app-tabs" aria-label="Dashboard sections"><a class="active" href="#quest-world">My world</a><a href="#daily-missions">Daily missions <span><?=$missionCount?>/3</span></a><a href="#subject-world">Subjects</a><button id="qdSound" type="button" aria-pressed="false" aria-label="Enable optional interface sounds"><?=qd_icon('bolt')?><span>Sound off</span></button></nav><div class="qd-workspace">
   <div class="qd-main">
    <section class="qd-hero" id="quest-world">
     <div class="qd-hero-copy"><span class="qd-world-label"><i></i> GRADE <?=$gradeNumber?> · <?=e($medium)?> MEDIUM</span><h2>Your next quest<br><span>starts here.</span></h2><p>Step into a world of discovery. Master a lesson, collect real rewards, and turn curiosity into your next superpower.</p><a class="qd-button qd-button-lime" href="<?=$nextUrl?>"><?=$game['next']&&!empty($game['next']['opened_at'])?'Continue adventure':'Start your adventure'?> <?=qd_icon('arrow')?></a><div class="qd-hero-note"><?=qd_icon('shield')?> Your textbook. Your pace. Your potential.</div></div>
     <div class="qd-hero-art" aria-hidden="true"><img src="<?=url('assets/images/learning-world-v2.webp')?>" alt="" fetchpriority="high" decoding="async"><span class="qd-art-tag qd-art-tag-one"><?=qd_icon('star')?> THE KNOWLEDGE REALM</span><span class="qd-art-tag qd-art-tag-two"><?=qd_icon('book')?> YOUR ADVENTURE AWAITS</span></div>
    </section>
    <section class="qd-stats" aria-label="Your learning stats">
     <a class="qd-stat" href="<?=url('student/leaderboard.php')?>"><span class="qd-icon qd-icon-purple"><?=qd_icon('bolt')?></span><div><strong><?=number_format($game['points'])?> <small>XP</small></strong><span>Learning points</span></div><span class="qd-stat-arrow">↗</span></a>
     <a class="qd-stat" href="<?=url('student/progress.php')?>"><span class="qd-icon qd-icon-mint"><?=qd_icon('book')?></span><div><strong><?=$game['completed']?> <small>/ <?=$game['total']?></small></strong><span>Lessons completed</span></div><span class="qd-stat-arrow">↗</span></a>
     <a class="qd-stat" href="<?=url('student/progress.php')?>"><span class="qd-icon qd-icon-peach"><?=qd_icon('target')?></span><div><strong><?=$game['last_quiz']!==null?(int)$game['last_quiz'].'%':'—'?></strong><span><?=$game['last_quiz']!==null?'Latest quiz score':'Your first quiz awaits'?></span></div><span class="qd-stat-arrow">↗</span></a>
    </section>
    <section class="qd-panel qd-trail-panel">
     <div class="qd-section-head"><div><span class="qd-eyebrow">ONE STEP AT A TIME</span><h2>Your learning trail <span>✦</span></h2></div><a href="<?=url($game['trail_subject']?'student/units.php?subject_id='.$game['trail_subject']:'student/subjects.php')?>" data-tour="lessons">Full map <?=qd_icon('arrow')?></a></div>
     <?php if($trailSubject):?><div class="qd-trail-subject"><span class="qd-trail-dot"></span><?=e(locale_value($trailSubject,'name'))?><small>Grade <?=$gradeNumber?> chapter trail</small></div><?php endif;?>
     <?php if($game['trail']):?>
     <div class="qd-trail-3d" data-trail-3d>
      <div class="qd-trail-stage" role="listbox" aria-label="Your lesson journey" tabindex="0">
       <div class="qd-trail-road" aria-hidden="true"><i></i><i></i><i></i></div>
       <?php $trailStart=0;foreach($game['trail'] as $index=>$lesson){$isCurrent=$game['next']&&(int)$lesson['id']===(int)$game['next']['id'];if($isCurrent)$trailStart=$index;}foreach($game['trail'] as $index=>$lesson):$current=$game['next']&&(int)$lesson['id']===(int)$game['next']['id'];$state=$lesson['locked']?'locked':($lesson['completed']?'completed':($current?'current':'available'));$lessonUrl=url($lesson['locked']?'subscription.php':'student/lesson.php?id='.(int)$lesson['id']);$lessonTitle=locale_value($lesson,'title');?>
       <button class="qd-trail-orb is-<?=$state?>" id="qd-trail-lesson-<?=(int)$lesson['id']?>" type="button" role="option" aria-selected="<?=$index===$trailStart?'true':'false'?>" tabindex="-1" data-index="<?=$index?>" data-title="<?=e($lessonTitle)?>" data-url="<?=e($lessonUrl)?>" data-order="<?=(int)$lesson['display_order']?>" data-state="<?=$state?>">
        <span class="qd-trail-orb-node"><?=$lesson['locked']?qd_icon('lock'):($lesson['completed']?qd_icon('check'):qd_icon('book'))?></span>
        <span class="qd-trail-orb-label">LESSON <?=(int)$lesson['display_order']?></span><strong><?=e(mb_strimwidth($lessonTitle,0,34,'…'))?></strong>
       </button>
       <?php endforeach;?>
       <button class="qd-trail-control qd-trail-previous" type="button" aria-label="Previous lesson"><?=qd_icon('arrow')?></button>
       <button class="qd-trail-control qd-trail-next" type="button" aria-label="Next lesson"><?=qd_icon('arrow')?></button>
       <div class="qd-trail-swipe" aria-hidden="true">SWIPE THE PATH</div>
      </div>
      <div class="qd-trail-focus" aria-live="polite">
       <div class="qd-trail-focus-top"><span class="qd-trail-status"></span><span class="qd-trail-step"><b>01</b> / <?=str_pad((string)count($game['trail']),2,'0',STR_PAD_LEFT)?></span></div>
       <span class="qd-trail-focus-order"></span><h3></h3><p></p>
       <a class="qd-button qd-trail-open" href="<?=$nextUrl?>"><span>Open lesson</span><?=qd_icon('arrow')?></a>
      </div>
     </div>
     <?php endif;?>
     <?php if(!$game['trail']):?><div class="qd-empty"><strong>Your journey starts with a subject.</strong><p>Explore the available textbooks for your grade and medium.</p><a href="<?=url('student/subjects.php')?>">Discover subjects →</a></div><?php endif;?>
     <div class="qd-course-progress"><div><span>Overall journey</span><strong><?=$progress?>% explored</strong></div><div class="qd-progress-track" role="progressbar" aria-label="Completed lessons" aria-valuenow="<?=$progress?>" aria-valuemin="0" aria-valuemax="100"><span style="width:<?=$progress?>%"></span></div></div>
    </section>
    <section class="qd-subjects" id="subject-world"><div class="qd-section-head"><div><span class="qd-eyebrow">PICK YOUR NEXT DISCOVERY</span><h2>Explore your subjects</h2></div><a href="<?=url('student/subjects.php')?>" data-tour="subjects">View all <?=qd_icon('arrow')?></a></div>
     <?php if($game['subjects']):?>
     <div class="qd-wheel-shell" data-subject-wheel>
      <div class="qd-wheel-stage">
       <div class="qd-wheel-guide" aria-hidden="true"><span></span><b>SELECT</b><span></span></div>
       <button class="qd-wheel-step qd-wheel-up" type="button" aria-label="Previous subject"><?=qd_icon('arrow')?></button>
       <div class="qd-wheel" role="listbox" aria-label="Choose a subject. Swipe or scroll up and down." tabindex="0">
        <div class="qd-wheel-spacer" aria-hidden="true"></div>
        <?php foreach($game['subjects'] as $index=>$subject):$subjectProgress=$subject['lessons']?min(100,(int)round(100*$subject['completed']/$subject['lessons'])):0;$name=strtolower((string)$subject['name_en']);$icon=str_contains($name,'science')?'science':(str_contains($name,'math')?'math':((str_contains($name,'geograph')||str_contains($name,'history'))?'globe':'book'));$subjectLabel=locale_value($subject,'name');$subjectUrl=url('student/units.php?subject_id='.(int)$subject['id']);?>
        <button class="qd-wheel-item" id="qd-wheel-subject-<?=(int)$subject['id']?>" type="button" role="option" aria-selected="<?=$index===0?'true':'false'?>" tabindex="-1" data-index="<?=$index?>" data-name="<?=e($subjectLabel)?>" data-url="<?=e($subjectUrl)?>" data-lessons="<?=(int)$subject['lessons']?>" data-completed="<?=(int)$subject['completed']?>" data-progress="<?=$subjectProgress?>">
         <span class="qd-wheel-icon"><?=qd_icon($icon)?></span><span class="qd-wheel-name"><?=e($subjectLabel)?></span><span class="qd-wheel-count"><?=(int)$subject['lessons']?> lessons</span>
        </button>
        <?php endforeach;?>
        <div class="qd-wheel-spacer" aria-hidden="true"></div>
       </div>
       <button class="qd-wheel-step qd-wheel-down" type="button" aria-label="Next subject"><?=qd_icon('arrow')?></button>
      </div>
      <div class="qd-wheel-detail" aria-live="polite">
       <span class="qd-wheel-kicker">CURRENT SELECTION</span><div class="qd-wheel-position"><b>01</b><span>/ <?=str_pad((string)count($game['subjects']),2,'0',STR_PAD_LEFT)?></span></div>
       <h3></h3><p class="qd-wheel-description"></p>
       <div class="qd-wheel-progress"><div><span>Learning progress</span><strong>0%</strong></div><div class="qd-progress-track"><span></span></div></div>
       <a class="qd-button qd-wheel-open" href="<?=url('student/subjects.php')?>">Open subject <?=qd_icon('arrow')?></a>
       <small>Swipe or use the mouse wheel to explore</small>
      </div>
     </div>
     <?php endif;?>
     <?php if(!$game['subjects']):?><div class="qd-panel qd-empty">Your grade’s subjects will appear here when textbook lessons are available.</div><?php endif;?>
    </section>
    <section class="qd-toolkit"><div class="qd-section-head"><div><span class="qd-eyebrow">A LITTLE HELP ALONG THE WAY</span><h2>Your learning toolkit</h2></div></div><div class="qd-tools">
     <a class="qd-tool qd-tool-ai" href="<?=url('chatbot/chat.php')?>" data-tour="chat"><span class="qd-tool-icon"><?=qd_icon('chat')?></span><div><span class="qd-tool-kicker">YOUR AI STUDY BUDDY</span><h3><?=e(tr('chat'))?></h3><p><?=$premium?'Big questions welcome. Unlimited AI tutoring.':($aiRemaining?'Your free AI message is ready for today.':'Today’s free message is used. Premium unlocks more.')?></p></div><img class="qd-companion" src="<?=url('assets/images/study-companion-v2.webp')?>" width="140" height="160" loading="lazy" alt="Your robot study companion"><span class="qd-tool-arrow"><?=qd_icon('arrow')?></span></a>
     <a class="qd-tool" href="<?=url('student/play-relax.php')?>"><span class="qd-tool-icon qd-icon-purple"><?=qd_icon('star')?></span><div><h3>Play & Relax</h3><p>Animated mind games, Checkers, focus sounds and calm breathing.</p></div><?=qd_icon('arrow')?></a>
     <a class="qd-tool" href="<?=url($premium?'student/quiz.php':'subscription.php')?>" data-tour="quiz"><span class="qd-tool-icon qd-icon-peach"><?=qd_icon('target')?></span><div><h3>Practice arena <?=$premium?'':'<span class="qd-mini-lock">'.qd_icon('lock').'</span>'?></h3><p>Put your knowledge to the test.</p></div><?=qd_icon('arrow')?></a>
     <?php if(in_array($gradeNumber,[10,11],true)):?><a class="qd-tool" href="<?=url($premium?'ol-kuppiya/index.php':'subscription.php')?>" data-tour="ol_kuppiya"><span class="qd-tool-icon qd-icon-purple"><?=qd_icon('shield')?></span><div><h3>O/L Kuppiya</h3><p>Past papers and exam preparation.<?=$premium?'':' Premium.'?></p></div><?=qd_icon('arrow')?></a><?php endif;?>
     <a class="qd-tool" href="<?=url('student/leaderboard.php')?>" data-tour="leaderboard"><span class="qd-tool-icon qd-icon-gold"><?=qd_icon('trophy')?></span><div><h3>Hall of fame</h3><p>Meet your grade’s learning champions.</p></div><?=qd_icon('arrow')?></a>
    </div><div class="qd-utility-links"><a href="<?=url('student/progress.php')?>" data-tour="progress"><?=qd_icon('chart')?> My progress</a><a href="<?=url('student/parent_access.php')?>" data-tour="parent_access"><?=qd_icon('users')?> Parent access</a><a href="<?=url('profile.php')?>" data-tour="profile"><?=qd_icon('user')?> My profile</a></div></section>
   </div>
   <aside class="qd-rail" aria-label="Player profile and daily goals">
    <section class="qd-player"><div class="qd-player-top"><span>EXPLORER PROFILE</span><span class="qd-player-star">✦</span></div><a class="qd-player-avatar" href="<?=url('profile.php')?>" aria-label="Open your profile"><span><?=e($initial)?></span><b><?=$game['level']?></b></a><h2><?=e($firstName)?></h2><p>Grade <?=$gradeNumber?> knowledge explorer</p><span class="qd-level-title">LEVEL <?=str_pad((string)$game['level'],2,'0',STR_PAD_LEFT)?> · <?=e($premium?'PREMIUM EXPLORER':'FREE EXPLORER')?></span><div class="qd-level-progress"><div><span>Next level</span><strong><?=$game['level_progress']?> / 250 XP</strong></div><div class="qd-progress-track" role="progressbar" aria-label="Progress to next level" aria-valuenow="<?=$game['level_progress']?>" aria-valuemin="0" aria-valuemax="250"><span style="width:<?=$levelProgress?>%"></span></div><small><?=250-$game['level_progress']?> more points to level <?=$game['level']+1?></small></div><a class="qd-player-link" href="<?=url('student/leaderboard.php')?>">View my achievements <?=qd_icon('arrow')?></a></section>
    <section class="qd-panel qd-missions" id="daily-missions"><div class="qd-section-head"><h2>Daily missions</h2><span class="qd-mission-count"><?=$missionCount?> / 3</span></div><p class="qd-panel-intro">A few small wins for a great day.</p>
     <?php $missions=[['book','Discover a lesson','Read something new today.','opened',$nextUrl,false],['check','Finish a chapter','Complete a lesson to earn points.','completed',($premium?$nextUrl:url('subscription.php')),!$premium],['target','Challenge your mind','Try a quiz and practise your skills.','quizzes',url($premium?'student/quiz.php':'subscription.php'),!$premium]];foreach($missions as $mission):$done=!empty($game['today'][$mission[3]]);?><a class="qd-mission <?=$done?'is-done':''?>" href="<?=$mission[4]?>"><span class="qd-mission-symbol"><?=qd_icon($done?'check':$mission[0])?></span><span><strong><?=e($mission[1])?></strong><small><?=e($done?'Mission completed!':$mission[2])?></small></span><span class="qd-mission-state"><?=qd_icon($done?'check':($mission[5]?'lock':'arrow'))?></span></a><?php endforeach;?>
     <div class="qd-streak"><div><span class="qd-flame"><?=qd_icon('flame')?></span><strong><?=$game['streak']?> day streak<small>Keep your curiosity going.</small></strong></div><div class="qd-week" aria-label="This week’s learning activity"><?php foreach($game['week'] as $day):?><span class="<?=$day['active']?'is-active ':''?><?=$day['today']?'is-today':''?>" title="<?=e($day['date'].($day['active']?' · Studied':' · No learning recorded'))?>"><small><?=substr($day['label'],0,1)?></small><b><?=$day['active']?'✓':date('j',strtotime($day['date']))?></b></span><?php endforeach;?></div></div>
    </section>
    <section class="qd-panel qd-rewards"><div class="qd-section-head"><h2>Trophy shelf</h2><a href="<?=url('student/leaderboard.php')?>" aria-label="View all achievements"><?=qd_icon('arrow')?></a></div><p class="qd-panel-intro">Every milestone tells your story.</p><div class="qd-badges"><?php foreach(['bronze'=>'First steps','silver'=>'Rising star','gold'=>'High flyer','master'=>'Lesson master'] as $tier=>$label):$earned=(int)($game['emblems'][$tier]??0);?><a class="qd-earned-badge qd-badge-<?=$tier?> <?=$earned?'is-earned':'is-unearned'?>" href="<?=url('student/leaderboard.php')?>" title="<?=e($label.': '.$earned.' earned')?>"><span><?=qd_icon($tier==='master'?'crown':($tier==='bronze'?'shield':'star'))?></span><strong><?=e($label)?></strong><small><?=$earned?$earned.' earned':'Not earned yet'?></small></a><?php endforeach;?></div></section>
    <section class="qd-upgrade"><span class="qd-upgrade-icon"><?=qd_icon('crown')?></span><span class="qd-eyebrow"><?=$premium?'YOUR PREMIUM ADVENTURE':'READY FOR MORE?'?></span><h2><?=$premium?'Your whole world.<br>Unlocked.':'More worlds.<br>More possibilities.'?></h2><p><?=$premium?'Every lesson, every quiz, and unlimited AI help are ready when you are.':'Unlock every lesson, the practice arena, and unlimited AI help.'?></p><a class="qd-button" href="<?=url('subscription.php')?>"><?=$premium?'View my membership':'Explore Premium'?> <?=qd_icon('arrow')?></a><?php if(!$premium):?><small>Rs. <?=number_format($offer['total'],0)?> / 30 days</small><?php endif;?></section>
   </aside>
  </div>
  <form class="qd-search" action="<?=url('student/lessons.php')?>" data-tour="search"><label for="questSearch"><?=qd_icon('search')?><span class="qd-sr-only">Search textbook lessons</span></label><input id="questSearch" name="q" placeholder="Looking for something? Search your textbook lessons…"><button type="submit">Search <?=qd_icon('arrow')?></button></form>
  <footer class="qd-footer"><span>Made for curious minds. Built for brighter futures.</span><a href="<?=url('student/dashboard.php?tour=1')?>">Take a guided tour ↗</a></footer>
 </div>
</div>
<?php if($game['emblem_total']>0&&!$showAyubowan&&!$showOnboarding):?><div class="trophy-unlock" id="trophyUnlock" hidden data-user="<?=$uid?>" data-latest="<?=$game['latest_emblem_id']?>" data-total="<?=$game['emblem_total']?>" role="dialog" aria-modal="true" aria-labelledby="trophyUnlockTitle"><div class="trophy-unlock-glow" aria-hidden="true"></div><div class="trophy-unlock-card"><button class="trophy-unlock-close" type="button" aria-label="Close achievement">×</button><div class="trophy-rays" aria-hidden="true"></div><div class="trophy-cup" aria-hidden="true"><?=qd_icon('trophy')?><i>✦</i></div><span class="trophy-unlock-kicker">ACHIEVEMENT UNLOCKED</span><h2 id="trophyUnlockTitle">You achieved this!</h2><p>Your completed lessons have earned a growing trophy collection.</p><div class="trophy-unlock-count"><strong><?=number_format($game['emblem_total'])?></strong><span>Total trophies</span></div><div class="trophy-unlock-tiers"><?php foreach(['bronze'=>'First Steps','silver'=>'Rising Star','gold'=>'High Flyer','master'=>'Lesson Master'] as $tier=>$label):$tierCount=(int)($game['emblems'][$tier]??0);?><span class="is-<?=$tier?> <?=$tierCount?'is-earned':''?>"><b><?=$tierCount?></b><?=e($label)?></span><?php endforeach;?></div><button class="trophy-unlock-continue" type="button">Amazing — continue my journey <?=qd_icon('arrow')?></button></div></div><?php endif;?>
<?php if($showAyubowan):?><div class="ayubowan-welcome" id="ayubowanWelcome" role="dialog" aria-modal="true" aria-labelledby="ayubowanTitle" data-voice-src="<?=url('assets/audio/ayubowan-recorded.mp3')?>"><div class="ayu-sky" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i></div><button class="ayu-skip" id="ayubowanSkip" type="button">Enter my world <?=qd_icon('arrow')?></button><div class="ayu-ceremony"><div class="ayu-lotus" aria-hidden="true"><span></span><span></span><span></span><span></span><span></span></div><div class="ayu-hands" aria-hidden="true"><span>🙏</span><i></i></div><span class="ayu-kicker">WELCOME TO YOUR LEARNING WORLD</span><h2 id="ayubowanTitle">ආයුබෝවන්</h2><strong>Ayubowan, <?=e($firstName)?>!</strong><p>May your day be filled with curiosity, courage and bright discoveries.</p><button class="ayu-voice" id="ayubowanVoice" type="button" aria-pressed="false" hidden><span>🔊</span><b>හඬ ඇසීමට ඔබන්න</b></button><div class="ayu-progress" aria-hidden="true"><span></span></div></div></div><?php endif;?>
<?php include __DIR__.'/student_dashboard_tour.php';?>
<script src="<?=url('assets/js/student-dashboard.js')?>?v=<?=filemtime(__DIR__.'/../assets/js/student-dashboard.js')?>" defer></script>
<script src="<?=url('assets/js/student-subject-wheel.js')?>?v=<?=filemtime(__DIR__.'/../assets/js/student-subject-wheel.js')?>" defer></script>
<script src="<?=url('assets/js/student-trail-3d.js')?>?v=<?=filemtime(__DIR__.'/../assets/js/student-trail-3d.js')?>" defer></script>
<script src="<?=url('assets/js/student-trophy-celebration.js')?>?v=<?=filemtime(__DIR__.'/../assets/js/student-trophy-celebration.js')?>" defer></script>
<?php if($showAyubowan):?><script src="<?=url('assets/js/student-ayubowan.js')?>?v=<?=filemtime(__DIR__.'/../assets/js/student-ayubowan.js')?>" defer></script><?php endif;?>
<?php include __DIR__.'/footer.php';?>
