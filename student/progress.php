<?php
require_once __DIR__.'/../includes/auth.php';
require_login();

$current=user();$uid=(int)$current['id'];$gradeId=(int)$current['grade_id'];$medium=(string)($current['medium']??'English');
$db=db();

$overall=$db->prepare("SELECT COUNT(*) total,COUNT(lp.completed_at) done FROM lessons l LEFT JOIN lesson_progress lp ON lp.lesson_id=l.id AND lp.user_id=? WHERE l.grade_id=? AND l.status='active' AND l.content_source='textbook' AND (l.medium='All' OR l.medium=?)");
$overall->bind_param('iis',$uid,$gradeId,$medium);$overall->execute();$totals=$overall->get_result()->fetch_assoc();
$total=(int)$totals['total'];$done=(int)$totals['done'];$percent=$total?(int)round(100*$done/$total):0;

$stats=$db->prepare("SELECT COALESCE((SELECT SUM(points) FROM student_points WHERE user_id=?),0) points,(SELECT COUNT(*) FROM student_emblems WHERE user_id=?) emblems,COALESCE((SELECT ROUND(AVG(best_percentage)) FROM (SELECT MAX(percentage) best_percentage FROM quiz_attempts WHERE user_id=? GROUP BY quiz_id) best),0) quiz_average");
$stats->bind_param('iii',$uid,$uid,$uid);$stats->execute();$summary=$stats->get_result()->fetch_assoc();

$rows=$db->prepare("SELECT s.id,s.name_en,s.name_si,s.name_ta,s.icon,COUNT(DISTINCT l.id) total,COUNT(DISTINCT CASE WHEN lp.completed_at IS NOT NULL THEN l.id END) done FROM subjects s JOIN lessons l ON l.subject_id=s.id AND l.grade_id=? AND l.status='active' AND l.content_source='textbook' AND (l.medium='All' OR l.medium=?) LEFT JOIN lesson_progress lp ON lp.lesson_id=l.id AND lp.user_id=? WHERE s.grade_id=? GROUP BY s.id ORDER BY s.name_en");
$rows->bind_param('isii',$gradeId,$medium,$uid,$gradeId);$rows->execute();$subjectRows=$rows->get_result();

$attempts=$db->prepare("SELECT qa.id,qa.score,qa.total_questions,qa.percentage,qa.passed,qa.completed_at,q.title_en,q.title_si,q.title_ta FROM quiz_attempts qa JOIN quizzes q ON q.id=qa.quiz_id JOIN lessons l ON l.id=q.lesson_id WHERE qa.user_id=? AND l.content_source='textbook' ORDER BY qa.completed_at DESC LIMIT 8");
$attempts->bind_param('i',$uid);$attempts->execute();

$pageTitle=tr('progress');include __DIR__.'/../includes/header.php';
?>
<h1>📈 <?=tr('progress')?></h1>
<section class="card">
 <h2>Overall textbook progress</h2>
 <p style="font-size:2rem;margin:.25rem 0"><strong><?=$percent?>%</strong></p>
 <div class="progress"><span style="width:<?=$percent?>%"></span></div>
 <p class="muted"><?=$done?> of <?=$total?> lessons completed · <?=e($medium)?> medium</p>
</section>
<div class="grid">
 <section class="card"><h3><?=number_format((int)$summary['points'])?></h3><p class="muted">Total points</p></section>
 <section class="card"><h3><?=number_format((int)$summary['emblems'])?></h3><p class="muted">Emblems earned</p></section>
 <section class="card"><h3><?=intval($summary['quiz_average'])?>%</h3><p class="muted">Average best quiz score</p></section>
</div>
<h2>Progress by subject</h2>
<div class="grid">
<?php while($r=$subjectRows->fetch_assoc()):$p=$r['total']?(int)round(100*$r['done']/$r['total']):0;?>
 <a class="card" style="display:block;text-decoration:none;color:inherit" href="lessons.php?subject_id=<?=intval($r['id'])?>">
  <h3><?=e($r['icon'])?> <?=e(locale_value($r,'name'))?> · <?=$p?>%</h3>
  <div class="progress"><span style="width:<?=$p?>%"></span></div>
  <p class="muted"><?=intval($r['done'])?> / <?=intval($r['total'])?> lessons completed</p>
 </a>
<?php endwhile;?>
</div>
<section class="card">
 <h2>Recent quiz results</h2>
 <?php $attemptRows=$attempts->get_result();if(!$attemptRows->num_rows):?><p class="muted">No textbook quiz attempts yet. Complete a lesson and try its quiz.</p><?php endif;?>
 <?php while($r=$attemptRows->fetch_assoc()):?><p><a href="results.php?id=<?=intval($r['id'])?>"><?=e(locale_value($r,'title'))?></a> — <strong><?=intval($r['percentage'])?>%</strong> (<?=intval($r['score'])?>/<?=intval($r['total_questions'])?>) <?=intval($r['passed'])?'✓':''?></p><?php endwhile;?>
</section>
<?php include __DIR__.'/../includes/footer.php';
