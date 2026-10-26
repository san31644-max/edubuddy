<?php
if(!$showOnboarding)return;
$tourLanguage=medium_language($medium);$isSinhala=$tourLanguage==='si';$name=(string)(user()['full_name']??'Student');
$copy=$isSinhala?['eyebrow'=>'ඔබේ ඉගෙනුම් මාර්ගෝපදේශකයා','skip'=>'මඟ පෙන්වීම මඟ හරින්න','back'=>'ආපසු','next'=>'ඊළඟ','start'=>'ඉගෙනීම අරඹන්න','voiceOn'=>'හඬ ක්‍රියාත්මකයි','voiceOff'=>'හඬ අක්‍රියයි','step'=>'පියවර']:['eyebrow'=>'YOUR LEARNING GUIDE','skip'=>'Skip guide','back'=>'Back','next'=>'Next','start'=>'Start learning','voiceOn'=>'Voice on','voiceOff'=>'Voice off','step'=>'STEP'];
$steps=$isSinhala?[
 ['ඔබේ ඉගෙනුම් ලෝකයට සාදරයෙන් පිළිගනිමු!','ආයුබෝවන් '.$name.'. ඔබේ dashboard එකේ වැදගත් සියලුම දේ මම පියවරෙන් පියවර පෙන්වන්නම්.','✨',''],
 ['ඕනෑම පාඩමක් ඉක්මනින් සොයන්න','පාඩමක නමක් හෝ මාතෘකාවක් මෙහි ලියා Search කරන්න.','🔎','search'],
 ['විෂයක් තෝරන්න','ඔබේ ශ්‍රේණියට සහ මාධ්‍යයට අදාළ සියලුම විෂයන් මෙතැනින් බලන්න.','📚','subjects'],
 ['ඔබේ පාඩම් බලන්න','කෙටි සටහන්, සම්පූර්ණ පාඩම්, ක්‍රියාකාරකම් සහ ප්‍රශ්නාවලි මෙතැන ඇත.','📖','lessons'],
 ['AI ගුරුවරයාගෙන් අහන්න','ඕනෑම අධ්‍යාපනික ප්‍රශ්නයක් අසන්න. ඔබේ පාඩම තේරුම් ගැනීමට AI ගුරුවරයා උදව් කරයි.','🤖','chat'],
 ['දැනුම පරීක්ෂා කරන්න','ප්‍රශ්නාවලි සම්පූර්ණ කර ලකුණු සහ ත්‍යාග ලබාගන්න.','🎯','quiz'],
 ['Leaderboard එකට යන්න','ඔබේ ලකුණු, ශ්‍රේණිගත කිරීම සහ ලබාගත් කුසලාන මෙතැනින් බලන්න.','🏆','leaderboard'],
 ['ඔබේ ප්‍රගතිය බලන්න','සම්පූර්ණ කළ පාඩම් සහ විෂය අනුව වර්ධනය මෙතැන පෙන්වයි.','📈','progress'],
 ['දෙමාපියන් සම්බන්ධ කරන්න','ආරක්ෂිත කේතයක් සාදා ඔබේ දෙමාපියන්ට පමණක් ලබා දෙන්න.','👨‍👩‍👧','parent_access'],
 ['ඔබේ Profile එක','ඡායාරූපය සහ මුරපදය වෙනස් කරන්න. මෙම guide එක නැවතත් මෙතැනින් බලන්න.','👤','profile'],
 ['ඔබ සූදානම්!','දැන් විෂයක් තෝරා ඔබේ ඉගෙනුම් ගමන ආරම්භ කරන්න.','🚀','subjects']
]:[
 ['Welcome to your learning world!','Hello '.$name.'. I will show you every important part of your dashboard, one simple step at a time.','✨',''],
 ['Find any lesson fast','Type a lesson name or topic here and search across your textbook lessons.','🔎','search'],
 ['Choose a subject','Open every subject available for your grade and learning medium.','📚','subjects'],
 ['Continue your lessons','Find short notes, full lessons, activities, quizzes, and rewards in one place.','📖','lessons'],
 ['Ask your AI Tutor','Ask any educational question and get help understanding your lesson.','🤖','chat'],
 ['Test your knowledge','Complete quizzes, improve your scores, and earn new rewards.','🎯','quiz'],
 ['Climb the leaderboard','See your points, class rank, and the trophies you have earned.','🏆','leaderboard'],
 ['Track your progress','Follow completed lessons, quiz results, and growth by subject.','📈','progress'],
 ['Connect your parent','Create a secure access code and share it only with your parent or guardian.','👨‍👩‍👧','parent_access'],
 ['Make it yours','Update your picture and password, or replay this guide from your profile.','👤','profile'],
 ['You are ready!','Choose a subject and begin your next learning adventure.','🚀','subjects']
];
$recordingNames=['01-welcome-new.mp3','02-search.mp3','03-subjects.mp3','04-lessons.mp3','05-ai-tutor.mp3','06-quizzes.mp3','07-leaderboard.mp3','08-progress.mp3','09-parent-access.mp3','10-profile.mp3','11-ready.mp3'];$recordings=[];foreach($recordingNames as $file){$path=__DIR__.'/../assets/audio/tour/si/'.$file;$recordings[]=is_file($path)?url('assets/audio/tour/si/'.$file).'?v='.filemtime($path):null;}
$config=['copy'=>$copy,'language'=>$isSinhala?'si-LK':'en-GB','recordings'=>$isSinhala?$recordings:[],'subjectsUrl'=>url('student/subjects.php'),'waitForWelcome'=>$showAyubowan];
?>
<div class="tour-overlay is-waiting" id="studentTour" role="dialog" aria-modal="true" aria-labelledby="tourTitle" aria-hidden="true">
 <div class="tour-aurora" aria-hidden="true"><i></i><i></i><i></i></div><div class="tour-box">
  <div class="tour-visual" aria-hidden="true"><span class="tour-orbit tour-orbit-one"></span><span class="tour-orbit tour-orbit-two"></span><span class="tour-emoji" id="tourEmoji">✨</span><img src="<?=url('assets/images/study-companion-v2.webp')?>" alt="" class="tour-guide-avatar"><span class="tour-guide-status"><i></i>K EDU GUIDE</span></div>
  <div class="tour-content"><div class="tour-meta"><span><?=e($copy['eyebrow'])?></span><strong id="tourCounter"><?=e($copy['step'])?> 1 / <?=count($steps)?></strong></div><div class="tour-progress" aria-hidden="true"><i id="tourProgress"></i></div><div class="tour-step-frame">
   <?php foreach($steps as $i=>$step):?><section class="tour-step<?=$i===0?' active':''?>" data-emoji="<?=e($step[2])?>" data-target="<?=e($step[3])?>"><h2<?=$i===0?' id="tourTitle"':''?>><?=e($step[0])?></h2><p><?=e($step[1])?></p></section><?php endforeach;?>
  </div><div class="tour-dots" aria-hidden="true"><?php foreach($steps as $i=>$step):?><i class="tour-dot<?=$i===0?' active':''?>"></i><?php endforeach;?></div><div class="tour-controls"><button class="tour-quiet" id="tourSkip" type="button"><?=e($copy['skip'])?></button><button class="tour-voice" id="tourVoice" type="button" aria-pressed="true"><span>🔊</span><b><?=e($copy['voiceOn'])?></b></button><button class="tour-back" id="tourBack" type="button" hidden>← <?=e($copy['back'])?></button><button class="tour-next" id="tourNext" type="button"><span><?=e($copy['next'])?></span> →</button></div></div>
 </div>
</div>
<script>window.kEducationTour=<?=json_encode($config,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)?>;</script>
<script src="<?=url('assets/js/student-dashboard-tour.js')?>?v=<?=filemtime(__DIR__.'/../assets/js/student-dashboard-tour.js')?>" defer></script>
