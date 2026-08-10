</main>
<?php if (!$isAdminPage && function_exists('user') && user()): ?>
<nav class="bottom" aria-label="Student navigation">
<?php foreach ([['student/dashboard.php','🏠',tr('home')],['student/subjects.php','📚',tr('learn')],['chatbot/chat.php','🤖',tr('chat')],['student/progress.php','📈',tr('progress')],['profile.php','👤',tr('profile')]] as $nav): $active=str_ends_with($currentPath,'/'.basename($nav[0])); ?>
<a class="<?= $active?'active':'' ?>" href="<?= url($nav[0]) ?>"><b><?= $nav[1] ?></b><?= e($nav[2]) ?></a>
<?php endforeach; ?>
</nav>
<?php endif; ?>
<?php $loaderLanguage=function_exists('user')&&user()?(['Sinhala'=>'si','Tamil'=>'ta','English'=>'en'][user()['medium']]??'en'):($_SESSION['lang']??'en');$loaderText=['en'=>'Loading your next learning adventure…','si'=>'ඔබේ ඊළඟ ඉගෙනුම් ගමන පූරණය වෙමින්…','ta'=>'உங்கள் அடுத்த கற்றல் பயணம் ஏற்றப்படுகிறது…'][$loaderLanguage];?>
<div class="page-loader" id="pageLoader" aria-hidden="true"><div class="loader-box"><span class="loader-emoji" id="loaderEmoji">📚</span><strong><?=e($loaderText)?></strong><span class="loader-dots"><i></i><i></i><i></i></span></div></div>
<script>
document.querySelectorAll('.card').forEach((el,i)=>{el.classList.add('reveal');el.style.animationDelay=Math.min(i*55,330)+'ms'});
if('serviceWorker'in navigator){addEventListener('load',()=>navigator.serviceWorker.register('<?= url('service-worker.js') ?>'))}
</script>
<script src="<?= url('assets/js/app.js') ?>"></script>
</body></html>
