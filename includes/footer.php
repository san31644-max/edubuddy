</main>
<?php if(!$isAdminPage):?><button type="button" id="pwaInstall" class="pwa-install" hidden>⬇ Install app</button><?php endif;?>
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
let deferredInstall=null;const installButton=document.querySelector('#pwaInstall');
const standalone=matchMedia('(display-mode: standalone)').matches||navigator.standalone===true;
addEventListener('beforeinstallprompt',event=>{event.preventDefault();deferredInstall=event;if(installButton&&!standalone)installButton.hidden=false});
installButton?.addEventListener('click',async()=>{
 if(deferredInstall){deferredInstall.prompt();await deferredInstall.userChoice;deferredInstall=null;installButton.hidden=true;return;}
 if(/iphone|ipad|ipod/i.test(navigator.userAgent))alert('To install K Education: tap the Share button in Safari, then choose “Add to Home Screen”.');
 else alert('Open this page in Chrome or Microsoft Edge, then choose “Install app” from the browser menu.');
});
if(installButton&&!standalone&&/iphone|ipad|ipod/i.test(navigator.userAgent))installButton.hidden=false;
addEventListener('appinstalled',()=>{deferredInstall=null;if(installButton)installButton.hidden=true});
</script>
<script>window.MathJax={tex:{inlineMath:[['$','$'],['\\(','\\)']],displayMath:[['$$','$$'],['\\[','\\]']],processEscapes:true},options:{skipHtmlTags:['script','noscript','style','textarea','pre','code']},chtml:{scale:1,matchFontHeight:true}};</script>
<script defer src="https://cdn.jsdelivr.net/npm/mathjax@3.2.2/es5/tex-chtml.js"></script>
<script src="<?= url('assets/js/app.js') ?>?v=<?=filemtime(__DIR__.'/../assets/js/app.js')?>"></script>
</body></html>
