<?php
if(!$showOnboarding)return;
$tourLanguage=medium_language($medium);$name=(string)(user()['full_name']??'Student');
$copyByLanguage=[
 'si'=>['eyebrow'=>'ඔබේ ඉගෙනුම් මඟපෙන්වීම','skip'=>'මඟපෙන්වීම මඟහරින්න','back'=>'ආපසු','next'=>'ඊළඟ','start'=>'ඉගෙනීම අරඹන්න','voiceOn'=>'හඬ ක්‍රියාත්මකයි','voiceOff'=>'හඬ අක්‍රියයි','step'=>'පියවර','guideLabel'=>'K EDU මඟපෙන්වන්නා'],
 'en'=>['eyebrow'=>'YOUR LEARNING GUIDE','skip'=>'Skip guide','back'=>'Back','next'=>'Next','start'=>'Start learning','voiceOn'=>'Voice on','voiceOff'=>'Voice off','step'=>'STEP','guideLabel'=>'K EDU GUIDE'],
 'ta'=>['eyebrow'=>'உங்கள் கற்றல் வழிகாட்டி','skip'=>'வழிகாட்டியைத் தவிர்க்கவும்','back'=>'பின்னால்','next'=>'அடுத்து','start'=>'கற்றலைத் தொடங்கு','voiceOn'=>'குரல் இயக்கத்தில்','voiceOff'=>'குரல் முடக்கப்பட்டுள்ளது','step'=>'படி','guideLabel'=>'K EDU வழிகாட்டி'],
];
$copy=$copyByLanguage[$tourLanguage]??$copyByLanguage['en'];
$stepsByLanguage=[
 'si'=>[
  ["ඔබේ ඉගෙනුම් ලෝකයට සාදරයෙන් පිළිගනිමු!","ආයුබෝවන්, {$name}. ඔබේ ඉගෙනුම් තිරයේ වැදගත් සියල්ල මම පියවරෙන් පියවර පෙන්වන්නම්.",'✨',''],
  ['පාඩමක් ඉක්මනින් සොයන්න','පාඩමේ නමක් හෝ මාතෘකාවක් මෙහි ලියා පෙළපොත් පාඩම් සොයන්න.','🔎','search'],
  ['විෂයක් තෝරන්න','ඔබේ ශ්‍රේණියට සහ ඉගෙනුම් මාධ්‍යයට ඇති විෂයන් මෙතැනින් විවෘත කරන්න.','📚','subjects'],
  ['පාඩම් දිගටම කරගෙන යන්න','කෙටි සටහන්, සම්පූර්ණ පාඩම්, ක්‍රියාකාරකම්, ප්‍රශ්නාවලි සහ ත්‍යාග මෙතැනින් සොයාගන්න.','📖','lessons'],
  ['AI ගුරුතුමාගෙන් අසන්න','අධ්‍යාපනික ප්‍රශ්නයක් අසා පාඩම තේරුම් ගැනීමට උදවු ලබාගන්න.','🤖','chat'],
  ['ඔබේ දැනුම පරීක්ෂා කරන්න','ප්‍රශ්නාවලි සම්පූර්ණ කර ලකුණු වැඩි කරගෙන නව ත්‍යාග දිනාගන්න.','🎯','quiz'],
  ['ශ්‍රේණිගත කිරීම් බලන්න','ඔබේ ලකුණු, පන්තියේ ස්ථානය සහ දිනාගත් කුසලාන බලන්න.','🏆','leaderboard'],
  ['ඔබේ ප්‍රගතිය බලන්න','සම්පූර්ණ කළ පාඩම්, ප්‍රශ්නාවලි ප්‍රතිඵල සහ එක් එක් විෂයේ දියුණුව බලන්න.','📈','progress'],
  ['දෙමාපියන් සම්බන්ධ කරන්න','ආරක්ෂිත ප්‍රවේශ කේතයක් සාදා එය ඔබේ දෙමාපියාට හෝ භාරකරුට පමණක් දෙන්න.','👨‍👩‍👧','parent_access'],
  ['ඔබේ ගිණුම ඔබට ගැළපෙන්න සකසන්න','ඔබේ ඡායාරූපය සහ මුරපදය වෙනස් කරන්න. මඟපෙන්වීම නැවත බැලීමට පැතිකඩට යන්න.','👤','profile'],
  ['දැන් ඔබ සූදානම්!','විෂයක් තෝරා ඔබේ ඊළඟ ඉගෙනුම් ගමන අරඹන්න.','🚀','subjects'],
 ],
 'en'=>[
  ['Welcome to your learning world!',"Hello, {$name}. I’ll show you the important parts of your learning dashboard, one step at a time.",'✨',''],
  ['Find a lesson quickly','Type a lesson name or topic here to search your textbook lessons.','🔎','search'],
  ['Choose a subject','Open the subjects available for your grade and learning medium here.','📚','subjects'],
  ['Continue your lessons','Find short notes, full lessons, activities, quizzes, and rewards here.','📖','lessons'],
  ['Ask your AI Tutor','Ask an education question and get help understanding your lesson.','🤖','chat'],
  ['Check what you know','Complete quizzes, improve your scores, and earn new rewards.','🎯','quiz'],
  ['See the leaderboard','Check your points, class position, and the trophies you have earned.','🏆','leaderboard'],
  ['Follow your progress','See completed lessons, quiz results, and your progress in each subject.','📈','progress'],
  ['Connect a parent or guardian','Create a secure access code and share it only with your parent or guardian.','👨‍👩‍👧','parent_access'],
  ['Make your account yours','Change your picture and password. Visit your profile to replay this guide.','👤','profile'],
  ['You’re ready!','Choose a subject and begin your next learning adventure.','🚀','subjects'],
 ],
 'ta'=>[
  ['உங்கள் கற்றல் உலகிற்கு வரவேற்கிறோம்!',"வணக்கம், {$name}. உங்கள் கற்றல் முகப்பின் முக்கிய பகுதிகளை ஒவ்வொன்றாகக் காட்டுகிறேன்.",'✨',''],
  ['பாடத்தை விரைவாகக் கண்டறியுங்கள்','பாடத்தின் பெயர் அல்லது தலைப்பை இங்கே தட்டச்சு செய்து பாடநூல் பாடங்களைத் தேடுங்கள்.','🔎','search'],
  ['ஒரு பாடத்தைத் தேர்ந்தெடுங்கள்','உங்கள் தரத்திற்கும் கற்றல் மொழிக்கும் கிடைக்கும் பாடங்களை இங்கே திறக்கலாம்.','📚','subjects'],
  ['பாடங்களைத் தொடருங்கள்','சுருக்கக் குறிப்புகள், முழுப் பாடங்கள், செயல்பாடுகள், வினாடி வினாக்கள் மற்றும் பரிசுகளை இங்கே காணலாம்.','📖','lessons'],
  ['AI ஆசிரியரிடம் கேளுங்கள்','கல்வி தொடர்பான கேள்வியைக் கேட்டு பாடத்தைப் புரிந்துகொள்ள உதவி பெறுங்கள்.','🤖','chat'],
  ['உங்கள் அறிவைச் சோதியுங்கள்','வினாடி வினாக்களை முடித்து மதிப்பெண்களை உயர்த்தி புதிய பரிசுகளைப் பெறுங்கள்.','🎯','quiz'],
  ['தரவரிசையைப் பாருங்கள்','உங்கள் புள்ளிகள், வகுப்பு நிலை மற்றும் பெற்ற கோப்பைகளைக் காணுங்கள்.','🏆','leaderboard'],
  ['உங்கள் முன்னேற்றத்தைப் பாருங்கள்','முடித்த பாடங்கள், வினாடி வினா முடிவுகள் மற்றும் ஒவ்வொரு பாடத்திலும் உள்ள முன்னேற்றத்தைக் காணுங்கள்.','📈','progress'],
  ['பெற்றோர் அல்லது பாதுகாவலரை இணைக்கவும்','பாதுகாப்பான அணுகல் குறியீட்டை உருவாக்கி பெற்றோர் அல்லது பாதுகாவலருடன் மட்டும் பகிருங்கள்.','👨‍👩‍👧','parent_access'],
  ['உங்கள் கணக்கை உங்களுக்கேற்றவாறு அமைக்கவும்','படத்தையும் கடவுச்சொல்லையும் மாற்றுங்கள். வழிகாட்டியை மீண்டும் பார்க்க உங்கள் சுயவிவரத்திற்குச் செல்லுங்கள்.','👤','profile'],
  ['நீங்கள் தயாராகிவிட்டீர்கள்!','ஒரு பாடத்தைத் தேர்ந்தெடுத்து உங்கள் அடுத்த கற்றல் பயணத்தைத் தொடங்குங்கள்.','🚀','subjects'],
 ],
];
$steps=$stepsByLanguage[$tourLanguage]??$stepsByLanguage['en'];
$recordingNames=['01-welcome-new.mp3','02-search.mp3','03-subjects.mp3','04-lessons.mp3','05-ai-tutor.mp3','06-quizzes.mp3','07-leaderboard.mp3','08-progress.mp3','09-parent-access.mp3','10-profile.mp3','11-ready.mp3'];$recordings=[];foreach($recordingNames as $file){$path=__DIR__.'/../assets/audio/tour/si/'.$file;$recordings[]=is_file($path)?url('assets/audio/tour/si/'.$file).'?v='.filemtime($path):null;}
$speechLanguage=['si'=>'si-LK','ta'=>'ta-LK','en'=>'en-GB'][$tourLanguage]??'en-GB';$config=['copy'=>$copy,'language'=>$speechLanguage,'recordings'=>$tourLanguage==='si'?$recordings:[],'subjectsUrl'=>url('student/subjects.php'),'waitForWelcome'=>$showAyubowan];
?>
<div class="tour-overlay is-waiting" id="studentTour" role="dialog" aria-modal="true" aria-labelledby="tourTitle" aria-hidden="true">
 <div class="tour-aurora" aria-hidden="true"><i></i><i></i><i></i></div><div class="tour-box">
  <div class="tour-visual" aria-hidden="true"><span class="tour-orbit tour-orbit-one"></span><span class="tour-orbit tour-orbit-two"></span><span class="tour-emoji" id="tourEmoji">✨</span><img src="<?=url('assets/images/study-companion-v2.webp')?>" alt="" class="tour-guide-avatar"><span class="tour-guide-status"><i></i><?=e($copy['guideLabel'])?></span></div>
  <div class="tour-content"><div class="tour-meta"><span><?=e($copy['eyebrow'])?></span><strong id="tourCounter"><?=e($copy['step'])?> 1 / <?=count($steps)?></strong></div><div class="tour-progress" aria-hidden="true"><i id="tourProgress"></i></div><div class="tour-step-frame">
   <?php foreach($steps as $i=>$step):?><section class="tour-step<?=$i===0?' active':''?>" data-emoji="<?=e($step[2])?>" data-target="<?=e($step[3])?>"><h2<?=$i===0?' id="tourTitle"':''?>><?=e($step[0])?></h2><p><?=e($step[1])?></p></section><?php endforeach;?>
  </div><div class="tour-dots" aria-hidden="true"><?php foreach($steps as $i=>$step):?><i class="tour-dot<?=$i===0?' active':''?>"></i><?php endforeach;?></div><div class="tour-controls"><button class="tour-quiet" id="tourSkip" type="button"><?=e($copy['skip'])?></button><button class="tour-voice" id="tourVoice" type="button" aria-pressed="true"><span>🔊</span><b><?=e($copy['voiceOn'])?></b></button><button class="tour-back" id="tourBack" type="button" hidden>← <?=e($copy['back'])?></button><button class="tour-next" id="tourNext" type="button"><span><?=e($copy['next'])?></span> →</button></div></div>
 </div>
</div>
<script>window.kEducationTour=<?=json_encode($config,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)?>;</script>
<script src="<?=url('assets/js/student-dashboard-tour.js')?>?v=<?=filemtime(__DIR__.'/../assets/js/student-dashboard-tour.js')?>" defer></script>
