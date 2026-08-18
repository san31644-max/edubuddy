<?php
require_once __DIR__.'/../includes/auth.php';require_login();
$id=filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT);
$a=$id?query_one('SELECT qa.*,q.title_en,q.title_si,q.title_ta,q.lesson_id FROM quiz_attempts qa JOIN quizzes q ON q.id=qa.quiz_id WHERE qa.id=? AND qa.user_id=?','ii',[$id,user()['id']]):null;
if(!$a)redirect('student/progress.php');
$canReview=is_premium();
$answers=[];
if($canReview){$s=db()->prepare('SELECT qq.*,ans.selected_option,ans.is_correct FROM quiz_answers ans JOIN quiz_questions qq ON qq.id=ans.question_id WHERE ans.attempt_id=? ORDER BY qq.display_order,qq.id');$s->bind_param('i',$id);$s->execute();$answers=$s->get_result()->fetch_all(MYSQLI_ASSOC);}
$perfect=(int)$a['total_questions']>0&&(int)$a['score']===(int)$a['total_questions'];$pageTitle=tr('results');include __DIR__.'/../includes/header.php';
?>
<section class="card">
 <h1><?=e(locale_value($a,'title'))?></h1>
 <div style="font-size:3rem;font-weight:900"><?=intval($a['percentage'])?>%</div>
 <p><?=intval($a['score'])?> / <?=intval($a['total_questions'])?> · <?=$perfect?'Lesson completed 🎉':'Review your answers and retry 💪'?></p>
 <div class="row">
  <?php if(!$perfect):?><a class="btn" href="quiz.php?id=<?=$a['quiz_id']?>">Retry quiz</a><?php endif;?>
  <a class="btn good" href="lesson.php?id=<?=intval($a['lesson_id'])?>&amp;finish=1"><?=$perfect?'✓ View completed lesson':'← Back to lesson'?></a>
 </div>
</section>
<?php if(!$canReview):?><section class="card premium-review-lock"><div class="lock-icon">🔒</div><span class="badge">Premium review</span><h2>Unlock answer review</h2><p class="muted">Your score is saved. Premium members can review every selected answer, the correct answer, and the explanation.</p><a class="btn warn" href="../subscription.php">✨ Unlock Premium</a></section><?php else:?>
<?php foreach($answers as $x):?>
<section class="card">
 <h3><?=e(locale_value($x,'question'))?></h3>
 <p><?=$x['is_correct']?'✅':'❌'?> Your answer: <?=e(strtoupper((string)($x['selected_option']??'Not answered')))?> · Correct: <?=e(strtoupper($x['correct_option']))?></p>
 <?php if(trim((string)locale_value($x,'explanation'))!==''):?><p><?=e(locale_value($x,'explanation'))?></p><?php endif;?>
</section>
<?php endforeach;?>
<?php endif;?><style>.premium-review-lock{position:relative;overflow:hidden;border:1px solid #f0d58d;background:linear-gradient(135deg,#fffaf0,#fff)}.premium-review-lock:after{content:"";position:absolute;width:180px;height:180px;right:-90px;top:-90px;border-radius:50%;background:rgba(255,190,57,.16)}.premium-review-lock .lock-icon{display:grid;width:58px;height:58px;margin-bottom:15px;place-items:center;border-radius:18px;background:#fff0c9;font-size:1.7rem}.premium-review-lock h2{margin:12px 0 6px}.premium-review-lock p{max-width:570px}</style>
<?php include __DIR__.'/../includes/footer.php';?>
