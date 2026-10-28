<?php
declare(strict_types=1);
require_once __DIR__.'/../includes/auth.php';require_once __DIR__.'/../includes/ai_quota.php';require_login();
$gradeId=(int)user()['grade_id'];$subjectRows=[];$lessonRows=[];$contentError=false;
$medium=(string)user()['medium'];$s=db()->prepare("SELECT DISTINCT s.id,s.name_en,s.name_si,s.name_ta FROM subjects s JOIN lessons l ON l.subject_id=s.id AND l.grade_id=s.grade_id AND l.status='active' AND l.content_source='textbook' AND (l.medium='All' OR l.medium=?) WHERE s.grade_id=? AND s.status='active' ORDER BY s.name_en");if($s){$s->bind_param('si',$medium,$gradeId);if($s->execute())$subjectRows=$s->get_result()->fetch_all(MYSQLI_ASSOC);else$contentError=true;$s->close();}else$contentError=true;
$s=db()->prepare("SELECT id,subject_id,title_en,title_si,title_ta FROM lessons WHERE grade_id=? AND status='active' AND content_source='textbook' AND (medium='All' OR medium=?) ORDER BY display_order");if($s){$s->bind_param('is',$gradeId,$medium);if($s->execute())$lessonRows=$s->get_result()->fetch_all(MYSQLI_ASSOC);else$contentError=true;$s->close();}else$contentError=true;
if(!is_premium())$lessonRows=array_values(array_filter($lessonRows,static fn($lesson)=>(int)(free_subject_lesson((int)$lesson['subject_id'])['id']??0)===(int)$lesson['id']));
$selectedLesson=filter_input(INPUT_GET,'lesson_id',FILTER_VALIDATE_INT)?:0;$olPaperId=filter_input(INPUT_GET,'ol_paper',FILTER_VALIDATE_INT)?:0;
$chatLanguage=['Sinhala'=>'si','Tamil'=>'ta','English'=>'en'][user()['medium']]??'en';
$gradeNumber=user_grade_number();
$freeAiRemaining=null;if(!is_premium()){$usedToday=free_ai_used_today((int)user()['id']);$freeAiRemaining=max(0,FREE_AI_DAILY_LIMIT-$usedToday);}
$chatText=['en'=>['title'=>'AI Tutor','clear'=>'Clear chat','free'=>'Free: 1 message per day','paid'=>'Unlimited Premium','error'=>'Lesson choices could not be loaded. You can still send a question.','subjects'=>'All subjects','lesson'=>'Select lesson','typing'=>'EduBuddy is thinking…','simple'=>'Explain more simply','example'=>'Give me an example','quiz'=>'Create a quiz','translate'=>'Translate answer','placeholder'=>'Ask your Grade '.$gradeNumber.' question…','send'=>'Send'],'si'=>['title'=>'අලුත් සර්','clear'=>'සංවාදය මකන්න','free'=>'නොමිලේ: දිනකට පණිවිඩ 1ක්','paid'=>'අසීමිත වාරික සේවාව','error'=>'පාඩම් තේරීම් පූරණය කළ නොහැකි විය. ඔබට තවමත් ප්‍රශ්නයක් යැවිය හැක.','subjects'=>'සියලු විෂයයන්','lesson'=>'පාඩමක් තෝරන්න','typing'=>'EduBuddy සිතමින් සිටී…','simple'=>'තවත් සරලව පැහැදිලි කරන්න','example'=>'උදාහරණයක් දෙන්න','quiz'=>'කෙටි ප්‍රශ්නාවලියක් සාදන්න','translate'=>'පිළිතුර පරිවර්තනය කරන්න','placeholder'=>'ඔබේ '.$gradeNumber.' ශ්‍රේණියේ ප්‍රශ්නය අසන්න…','send'=>'යවන්න'],'ta'=>['title'=>'AI ஆசிரியர்','clear'=>'உரையாடலை அழி','free'=>'இலவசம்: நாளுக்கு 1 செய்தி','paid'=>'வரம்பற்ற பிரீமியம்','error'=>'பாடத் தேர்வுகளை ஏற்ற முடியவில்லை. நீங்கள் இன்னும் கேள்வியை அனுப்பலாம்.','subjects'=>'அனைத்து பாடங்களும்','lesson'=>'பாடத்தைத் தேர்ந்தெடுக்கவும்','typing'=>'EduBuddy சிந்திக்கிறது…','simple'=>'இன்னும் எளிமையாக விளக்கவும்','example'=>'ஓர் உதாரணம் தரவும்','quiz'=>'சிறு வினாடி வினா உருவாக்கவும்','translate'=>'பதிலை மொழிபெயர்க்கவும்','placeholder'=>'உங்கள் தரம் '.$gradeNumber.' கேள்வியைக் கேளுங்கள்…','send'=>'அனுப்பு']][$chatLanguage];
$chatText['typing']=str_replace('EduBuddy','K Education',$chatText['typing']);
if($chatLanguage==='en')$chatText['placeholder']='Ask your Grade '.$gradeNumber.' question…';
$pageTitle=$chatText['title'];include __DIR__.'/../includes/header.php';
?>
<link rel="stylesheet" href="<?=url('assets/css/tutor-premium.css')?>?v=<?=filemtime(__DIR__.'/../assets/css/tutor-premium.css')?>">
<link rel="stylesheet" href="<?=url('assets/css/student-tutor.css')?>?v=<?=filemtime(__DIR__.'/../assets/css/student-tutor.css')?>">
<link rel="stylesheet" href="<?=url('assets/css/tutor-clean-controls.css')?>?v=20261029a">
<section class="tutor-shell" aria-label="AI study workspace">
 <aside class="tutor-side" aria-label="Study context">
  <a class="tutor-back" href="<?=url('student/dashboard.php')?>" aria-label="Back to dashboard"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m14 5-7 7 7 7M7 12h13"/></svg></a>
  <div class="tutor-brand"><div class="tutor-avatar"><img class="game-companion-avatar" src="<?=url('assets/images/study-companion-v2.webp')?>" alt=""></div><div><span class="tutor-status">YOUR STUDY COMPANION</span><h1><?=e($chatText['title'])?></h1><p>Grade <?=$gradeNumber?> · <?=e(user()['medium'])?></p></div></div>
  <button class="mobile-context-toggle" id="mobileContextToggle" type="button" aria-expanded="false" aria-controls="tutorContext"><?=game_icon('book')?> <span>Choose lesson</span></button>
  <details class="tutor-options" id="tutorOptions"><summary aria-label="More Tutor options"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><circle cx="5" cy="12" r="1.7"/><circle cx="12" cy="12" r="1.7"/><circle cx="19" cy="12" r="1.7"/></svg></summary><div class="tutor-options-panel"><strong>Tutor options</strong></div></details>
  <div class="picker" id="tutorContext"><h2 class="picker-title"><?=game_icon('book')?> Choose your learning path</h2>
   <label>Subject<select id="subject"><option value=""><?=e($chatText['subjects'])?></option><?php foreach($subjectRows as $subject):?><option value="<?=(int)$subject['id']?>"><?=e(locale_value($subject,'name'))?></option><?php endforeach;?></select></label>
   <label>Textbook lesson<select id="lesson"><option value=""><?=e($chatText['lesson'])?></option><?php foreach($lessonRows as $lesson):?><option data-subject="<?=(int)$lesson['subject_id']?>" data-lesson-id="<?=(int)$lesson['id']?>" value="lesson-<?=(int)$lesson['id']?>"<?=$selectedLesson===(int)$lesson['id']?' selected':''?>><?=e(locale_value($lesson,'title'))?></option><?php endforeach;?></select></label>
   <div class="selection-note"><?=game_icon('shield')?><span><b>Bring your textbook along.</b>Select a lesson for answers grounded in that Grade <?=$gradeNumber?> book, or ask a general study question.</span></div>
  </div>
  <div class="tutor-side-foot"><div class="tutor-side-tip"><?=game_icon('star')?><strong>There’s no question too small.</strong><p>Ask for an example, break a tricky idea into steps, or practise what you’ve learned.</p></div><button class="clear-chat" id="clearChat" type="button"><?=game_icon('chat')?> <?=e($chatText['clear'])?></button></div>
 </aside>
 <div class="tutor-main">
  <header class="tutor-top"><div class="tutor-conversation"><?=game_icon('chat')?><div><strong id="conversationTitle">General education chat</strong><small id="conversationMeta">Ask any education-related question</small></div></div><?php if(is_premium()):?><span class="plan-chip"><?=game_icon('star')?> <?=e($chatText['paid'])?></span><?php else:$chatOffer=premium_offer_for_user((int)user()['id']);?><a class="plan-chip quota-chip" id="aiQuota" href="<?=url('subscription.php')?>"><strong id="aiRemaining"><?=(int)$freeAiRemaining?></strong> / 1 free today</a><?php endif;?><button class="mobile-clear" id="mobileClear" type="button" aria-label="Clear chat"><?=game_icon('chat')?></button></header>
  <?php if($contentError):?><div class="alert error"><?=e($chatText['error'])?></div><?php endif;?>
  <div id="messages" role="log" aria-label="Conversation" aria-relevant="additions" aria-live="polite"><div class="chat-welcome" id="chatWelcome">
   <div class="tutor-welcome-art tutor-teaching-robot" aria-hidden="true"><span class="tutor-welcome-halo"></span><span class="robot-lesson-symbol robot-symbol-one">A+</span><span class="robot-lesson-symbol robot-symbol-two">✦</span><span class="robot-lesson-symbol robot-symbol-three">?</span><img src="<?=url('assets/images/ai-teaching-robot-v2.png')?>" alt="" width="180" height="220" decoding="async"><i>✦</i></div>
   <span class="tutor-eyebrow">A LITTLE CURIOSITY. A WORLD OF POSSIBILITIES.</span><h2>Big questions.<br><em>Brighter ideas.</em></h2><p>Your study companion for the “how”, the “why”, and the “can you explain that again?”</p>
   <div class="starter-grid">
    <?php foreach([['science','Explain a topic','Make a tricky idea click','Explain a useful topic for my grade'],['book','Give me an example','See how it works','Give me a simple educational example']] as $prompt):?><button type="button" class="starter" data-prompt="<?=e($prompt[3])?>"><span><?=game_icon($prompt[0])?></span><span><b><?=e($prompt[1])?></b><small><?=e($prompt[2])?></small></span><?=game_icon('arrow')?></button><?php endforeach;?>
   </div><small class="tutor-prompt-hint">Choose an idea to draft a question. Send when you’re ready.</small>
  </div></div>
  <div id="typing" hidden role="status"><span class="typing-bubble" aria-hidden="true"><i></i><i></i><i></i></span><span><?=e($chatText['typing'])?></span></div>
  <div class="composer"><details class="tutor-followups"><summary>More ways to learn</summary><div class="suggestions" id="suggestions"><?php foreach(['simple'=>'star','example'=>'book','quiz'=>'target','translate'=>'globe'] as $key=>$icon):?><button type="button" data-prompt="<?=e($chatText[$key])?>"><?=game_icon($icon)?> <?=e($chatText[$key])?></button><?php endforeach;?></div></details><form id="chatForm" class="chat-send"><textarea id="message" maxlength="1000" rows="1" placeholder="<?=e($chatText['placeholder'])?>" aria-label="Your question" required></textarea><button class="send-button" type="submit" aria-label="<?=e($chatText['send'])?>"><?=game_icon('arrow')?></button></form><div class="composer-hint"><span>AI can make mistakes. Check important answers with your textbook.</span><span id="charCount">0 / 1000</span></div></div>
 </div>
</section>
<div class="tutor-motion"><span>Your questions. Your pace. Your next discovery.</span><button id="tutorMotion" type="button" aria-pressed="true">Ⅱ Pause motion</button></div>
<?php if(!is_premium()):?>
<div class="premium-gate" id="premiumGate" hidden role="dialog" aria-modal="true" aria-labelledby="premiumGateTitle">
 <div class="premium-gate-card">
  <button class="premium-gate-close" id="premiumGateClose" type="button" aria-label="Close">×</button>
  <span class="premium-gate-badge">⭐ FREE LIMIT COMPLETED</span>
  <h2 id="premiumGateTitle">Keep learning without limits.</h2>
  <p class="premium-gate-lead">You used today’s one free AI message. Your next free message is available tomorrow (Sri Lanka time). Unlock the complete K Education learning experience with Premium.</p>
  <div class="premium-gate-features">
   <div class="premium-gate-feature"><span>🤖</span><div><strong>Unlimited AI Tutor</strong><small>Ask questions and get explanations without a daily cap.</small></div></div>
   <div class="premium-gate-feature"><span>📚</span><div><strong>Premium O/L Kuppiya</strong><small>Past papers, answer practice and AI exam support.</small></div></div>
   <div class="premium-gate-feature"><span>📝</span><div><strong>All lesson quizzes</strong><small>Practise every available textbook lesson.</small></div></div>
   <div class="premium-gate-feature"><span>📈</span><div><strong>Answers & progress</strong><small>Review explanations and understand your improvement.</small></div></div>
  </div>
  <div class="premium-gate-action"><div class="premium-gate-price"><strong>Rs. <?=e(number_format((float)$chatOffer['total'],0))?></strong><small>30 days · no automatic renewal</small></div><a class="premium-gate-buy" href="<?=url('subscription.php')?>">Unlock Premium →</a></div>
  <button class="premium-gate-later" id="premiumGateLater" type="button">Maybe later</button>
 </div>
</div>
<?php endif;?><script>window.EDU={send:<?=json_encode(url('chatbot/send.php'))?>,history:<?=json_encode(url('chatbot/history.php'))?>,csrf:<?=json_encode(csrf_token())?>,remaining:<?=json_encode($freeAiRemaining)?>};</script><script src="<?=url('assets/js/chatbot.js')?>?v=<?=filemtime(__DIR__.'/../assets/js/chatbot.js')?>"></script>

<script src="<?=url('assets/js/student-tutor.js')?>?v=<?=filemtime(__DIR__.'/../assets/js/student-tutor.js')?>" defer></script>
<?php include __DIR__.'/../includes/footer.php';?>
