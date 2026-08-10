<?php
require_once __DIR__.'/../includes/auth.php';require_login();
$gradeId=(int)user()['grade_id'];$gradeNumber=user_grade_number();$uid=(int)user()['id'];
$period=($_GET['period']??'all')==='month'?'month':'all';
$dateClause=$period==='month'?" AND sp.awarded_at>=DATE_FORMAT(CURDATE(),'%Y-%m-01') ":'';
$sql="SELECT u.id,u.username,u.profile_image,COALESCE(SUM(sp.points),0) points,
 COUNT(DISTINCT CASE WHEN sp.activity_type='lesson_complete' THEN sp.lesson_id END) completed,
 (SELECT ROUND(AVG(best_score)) FROM (SELECT qa2.user_id,qa2.quiz_id,MAX(qa2.percentage) best_score FROM quiz_attempts qa2 GROUP BY qa2.user_id,qa2.quiz_id) scores WHERE scores.user_id=u.id) quiz_average
 FROM users u LEFT JOIN student_points sp ON sp.user_id=u.id $dateClause
 WHERE u.grade_id=? AND u.status='active' GROUP BY u.id
 ORDER BY points DESC,completed DESC,quiz_average DESC,u.id LIMIT 100";
$s=db()->prepare($sql);$s->bind_param('i',$gradeId);$s->execute();$leaders=$s->get_result()->fetch_all(MYSQLI_ASSOC);
$myPoints=(int)(query_one('SELECT COALESCE(SUM(points),0) points FROM student_points WHERE user_id=?'.($period==='month'?" AND awarded_at>=DATE_FORMAT(CURDATE(),'%Y-%m-01')":''),'i',[$uid])['points']??0);
$emblems=[];$es=db()->prepare("SELECT se.tier,l.title_en,l.title_si,l.title_ta,s.name_en,s.name_si,s.name_ta,se.earned_at FROM student_emblems se JOIN lessons l ON l.id=se.lesson_id JOIN subjects s ON s.id=se.subject_id WHERE se.user_id=? ORDER BY FIELD(se.tier,'master','gold','silver','bronze'),se.earned_at DESC");$es->bind_param('i',$uid);$es->execute();$emblems=$es->get_result()->fetch_all(MYSQLI_ASSOC);
$pageTitle='Grade '.$gradeNumber.' Leaderboard';include __DIR__.'/../includes/header.php';
?>
<style>.leader{display:grid;grid-template-columns:60px 1fr auto;gap:12px;align-items:center;padding:13px;border-bottom:1px solid var(--line)}.leader.me{background:#f1efff;border-radius:16px}.rank{font-size:1.25rem;font-weight:900;text-align:center}.score{text-align:right}.score strong{display:block;font-size:1.15rem;color:var(--violet)}.emblems{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:12px}.emblem{text-align:center;padding:16px}.emblem-icon{font-size:2.4rem}.tabs{display:flex;gap:8px;margin-bottom:16px}</style>
<section class="card" style="background:linear-gradient(135deg,#5545d5,#2588f6);color:#fff"><span class="badge">🏅 Grade <?=$gradeNumber?></span><h1>Leaderboard</h1><p>Earn points by completing lessons and improving quiz scores.</p><strong style="font-size:2rem"><?=$myPoints?> points</strong></section>
<div class="tabs"><a class="btn <?=$period==='all'?'':'alt'?>" href="?period=all">All time</a><a class="btn <?=$period==='month'?'':'alt'?>" href="?period=month">This month</a></div>
<section class="card"><h2>🏆 Grade <?=$gradeNumber?> rankings</h2>
<?php foreach($leaders as $i=>$student):$rank=$i+1;?><div class="leader <?=$student['id']===$uid?'me':''?>"><div class="rank"><?=$rank===1?'🥇':($rank===2?'🥈':($rank===3?'🥉':'#'.$rank))?></div><div><strong>@<?=e($student['username'])?><?=$student['id']===$uid?' · You':''?></strong><small class="muted"><?=intval($student['completed'])?> lessons · <?=is_null($student['quiz_average'])?'No quizzes':intval($student['quiz_average']).'% quiz average'?></small></div><div class="score"><strong><?=intval($student['points'])?></strong><small>points</small></div></div><?php endforeach;?>
</section>
<section class="card"><h2>🎖️ My lesson emblems</h2><div class="emblems"><?php if(!$emblems):?><p class="muted">Complete a lesson to earn your first Bronze emblem.</p><?php endif;?><?php foreach($emblems as $emblem):?><div class="card emblem"><div class="emblem-icon"><?=['bronze'=>'🥉','silver'=>'🥈','gold'=>'🥇','master'=>'🏆'][$emblem['tier']]?></div><strong><?=e(ucfirst($emblem['tier']))?></strong><small><?=e(locale_value($emblem,'title'))?><br><?=e(locale_value($emblem,'name'))?></small></div><?php endforeach;?></div></section>
<section class="card"><h2>How points work</h2><p>Lesson complete: <strong>20</strong> · First quiz attempt: <strong>10</strong> · Quiz thresholds at 50%, 70%, 85% and 95%: <strong>10 each</strong>.</p><p class="muted">Every reward can be earned only once, so repeating the same action cannot inflate the ranking.</p></section>
<?php include __DIR__.'/../includes/footer.php';?>
