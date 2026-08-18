<?php
require_once __DIR__.'/../includes/auth.php';require_login();
// Progress is user-specific and must never be served from a stale browser or
// proxy cache after a lesson is completed.
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');header('Pragma: no-cache');
$current=user();$uid=(int)$current['id'];$grade=(int)$current['grade_id'];$gradeNumber=user_grade_number();$medium=(string)$current['medium'];$lang=medium_language($medium);
$s=db()->prepare("SELECT s.*,(SELECT COUNT(*) FROM lessons l WHERE l.subject_id=s.id AND l.grade_id=? AND l.status='active' AND l.content_source='textbook' AND (l.medium='All' OR l.medium=?)) lesson_count,(SELECT COUNT(DISTINCT lp.lesson_id) FROM lesson_progress lp JOIN lessons l ON l.id=lp.lesson_id WHERE lp.user_id=? AND lp.completed_at IS NOT NULL AND l.subject_id=s.id AND l.grade_id=? AND l.status='active' AND l.content_source='textbook' AND (l.medium='All' OR l.medium=?)) completed_count FROM subjects s WHERE s.grade_id=? AND s.status='active' HAVING lesson_count>0 ORDER BY s.name_en");
if(!$s){flash('error','Subjects could not be loaded. Please try again.');redirect('student/dashboard.php');}$s->bind_param('isiisi',$grade,$medium,$uid,$grade,$medium,$grade);$s->execute();$res=$s->get_result();
$copy=[
'en'=>['badge'=>'Official Grade '.$gradeNumber.' textbooks','title'=>'English medium','intro'=>'Choose a subject to access textbook lessons, short notes, AI help and revision quizzes.','lessons'=>'lessons','complete'=>'complete','empty'=>'No textbooks are available for Grade '.$gradeNumber.' in this medium yet.'],
'si'=>['badge'=>$gradeNumber.' ශ්‍රේණිය නිල පෙළපොත්','title'=>'සිංහල මාධ්‍ය','intro'=>'පෙළපොත් පාඩම්, කෙටි සටහන්, AI උපකාර සහ පුනරීක්ෂණ ප්‍රශ්න සඳහා විෂයක් තෝරන්න.','lessons'=>'පාඩම්','complete'=>'සම්පූර්ණයි','empty'=>$gradeNumber.' ශ්‍රේණියේ මෙම මාධ්‍යය සඳහා පෙළපොත් තවම ඇතුළත් කර නැත.'],
'ta'=>['badge'=>'தரம் '.$gradeNumber.' அதிகாரப்பூர்வ பாடநூல்கள்','title'=>'தமிழ் மூலம்','intro'=>'பாடங்கள், சுருக்கக் குறிப்புகள், AI உதவி மற்றும் வினாடி வினாக்களுக்கு ஒரு பாடத்தைத் தேர்ந்தெடுக்கவும்.','lessons'=>'பாடங்கள்','complete'=>'நிறைவு','empty'=>'தரம் '.$gradeNumber.' தமிழ் மொழிமூல பாடநூல்கள் இன்னும் சேர்க்கப்படவில்லை.']][$lang];
$pageTitle=tr('subjects');include __DIR__.'/../includes/header.php';?>
<style>.learn-hero{background:linear-gradient(135deg,#4637b8,#6554e8 50%,#168fc1);color:#fff}.learn-hero .muted{color:rgba(255,255,255,.8)}.subject-card{display:flex;flex-direction:column;text-decoration:none;color:inherit}.subject-icon{display:grid;place-items:center;width:62px;height:62px;border-radius:19px;background:linear-gradient(135deg,#efedff,#e4f7ff);font-size:2rem}.subject-card h2{margin:17px 0 6px}.subject-meta{display:flex;justify-content:space-between;gap:8px;margin-top:auto;padding-top:15px;font-size:.83rem;font-weight:800;color:var(--muted)}</style>
<style>
.subjects-page{--subject-color:#6655e8;--subject-soft:#ece9ff;perspective:1300px}.learn-hero{position:relative;isolation:isolate;min-height:270px;overflow:hidden;padding:35px;border:1px solid rgba(255,255,255,.3);background:radial-gradient(circle at 80% 25%,rgba(255,200,72,.32),transparent 24%),linear-gradient(125deg,#29206d 0%,#5949d1 48%,#078fae 100%);background-size:160% 160%;box-shadow:0 27px 60px rgba(51,40,143,.24),inset 0 1px rgba(255,255,255,.3);animation:subjectHeroFlow 12s ease infinite}.learn-hero:before{content:"";position:absolute;z-index:-1;inset:0;opacity:.13;background-image:linear-gradient(rgba(255,255,255,.22) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.22) 1px,transparent 1px);background-size:30px 30px;mask-image:linear-gradient(90deg,#000,transparent 80%)}.learn-hero:after{content:"";position:absolute;z-index:-1;width:280px;height:280px;right:-70px;bottom:-155px;border:45px solid rgba(255,255,255,.08);border-radius:50%;animation:subjectOrbit 9s linear infinite}.learn-hero .badge{border:1px solid rgba(255,255,255,.25);background:rgba(255,255,255,.14);color:#fff;box-shadow:0 8px 22px rgba(29,20,100,.15);backdrop-filter:blur(8px)}.learn-hero h1{max-width:720px;margin:18px 0 9px;color:#fff;font-size:clamp(2rem,5vw,3.25rem);line-height:1.08}.learn-hero p{max-width:700px;line-height:1.75}.hero-books{position:absolute;right:7%;top:39px;width:155px;height:155px;transform-style:preserve-3d;pointer-events:none}.hero-books span{position:absolute;display:grid;place-items:center;width:92px;height:116px;border:5px solid rgba(255,255,255,.68);border-radius:14px;background:linear-gradient(145deg,#ffbf42,#ff7d43);box-shadow:0 22px 38px rgba(33,24,108,.3);font-size:2.6rem}.hero-books span:first-child{left:0;top:23px;transform:rotate(-15deg) translateZ(-15px);background:linear-gradient(145deg,#17c4c6,#1472d1)}.hero-books span:last-child{right:0;top:0;transform:rotate(10deg) translateZ(20px);animation:bookFloat 4s ease-in-out infinite}.hero-books i{position:absolute;right:-12px;top:-8px;width:16px;height:16px;border-radius:50%;background:#ffe16f;box-shadow:0 0 20px #ffe16f;animation:bookSpark 2s ease-in-out infinite}
.subject-tools{position:relative;z-index:5;display:flex;align-items:center;gap:12px;margin:-28px 22px 27px;padding:14px 16px;border:1px solid rgba(255,255,255,.9);border-radius:20px;background:rgba(255,255,255,.91);box-shadow:0 17px 38px rgba(30,43,82,.13);backdrop-filter:blur(16px)}.subject-search{position:relative;flex:1}.subject-search span{position:absolute;left:14px;top:50%;transform:translateY(-50%);font-size:1.05rem}.subject-search input{min-height:48px;margin:0;padding-left:44px;border:1px solid #dce7f1;border-radius:14px;background:#f8fbfe}.subject-search input:focus{border-color:#6554e8;box-shadow:0 0 0 4px rgba(101,84,232,.11);background:#fff}.subject-result-count{flex:0 0 auto;padding:9px 12px;border-radius:12px;background:#eeeaff;color:#5948ca;font-size:.75rem;font-weight:900}
.subject-grid{counter-reset:subjectCards;grid-template-columns:repeat(auto-fit,minmax(245px,1fr));gap:18px;perspective:1300px}.subject-grid .subject-card{--subject-color:#6554e8;--subject-soft:#ebe7ff;counter-increment:subjectCards;position:relative;isolation:isolate;min-height:285px;overflow:hidden;padding:22px;border:1px solid color-mix(in srgb,var(--subject-color) 14%,#fff);background:linear-gradient(150deg,#fff 32%,color-mix(in srgb,var(--subject-soft) 55%,#fff));box-shadow:0 15px 38px rgba(37,53,88,.09);transform-style:preserve-3d;opacity:0;animation:subjectLaunch .65s cubic-bezier(.2,.8,.2,1) forwards;animation-delay:calc(var(--subject-index,0)*65ms + 100ms);transition:transform .16s ease-out,box-shadow .28s ease,border-color .28s ease}.subject-grid .subject-card:nth-child(6n+1){--subject-color:#e08b19;--subject-soft:#fff0ce}.subject-grid .subject-card:nth-child(6n+2){--subject-color:#168cc6;--subject-soft:#dcf3ff}.subject-grid .subject-card:nth-child(6n+3){--subject-color:#7154d8;--subject-soft:#ece5ff}.subject-grid .subject-card:nth-child(6n+4){--subject-color:#0b9d79;--subject-soft:#ddf8ef}.subject-grid .subject-card:nth-child(6n+5){--subject-color:#e14e78;--subject-soft:#ffe4eb}.subject-grid .subject-card:nth-child(6n){--subject-color:#d19a08;--subject-soft:#fff3c9}.subject-grid .subject-card:before{content:counter(subjectCards,decimal-leading-zero);position:absolute;z-index:-1;right:15px;top:5px;color:color-mix(in srgb,var(--subject-color) 10%,transparent);font-size:4.7rem;font-weight:1000;line-height:1}.subject-grid .subject-card:after{content:"";position:absolute;z-index:-2;width:145px;height:145px;right:-55px;bottom:-68px;border-radius:50%;background:var(--subject-soft);transition:transform .4s ease}.subject-grid .subject-card:hover{border-color:color-mix(in srgb,var(--subject-color) 35%,#fff);box-shadow:0 28px 55px color-mix(in srgb,var(--subject-color) 16%,transparent)}.subject-grid .subject-card:hover:after{transform:scale(1.45)}.subject-grid .subject-icon{position:relative;width:68px;height:68px;border:1px solid rgba(255,255,255,.85);border-radius:21px;background:linear-gradient(145deg,#fff,var(--subject-soft));box-shadow:0 13px 25px color-mix(in srgb,var(--subject-color) 18%,transparent);transform:translateZ(30px);transition:transform .3s}.subject-grid .subject-icon:after{content:"";position:absolute;inset:6px;border:1px solid color-mix(in srgb,var(--subject-color) 22%,transparent);border-radius:15px}.subject-grid .subject-card:hover .subject-icon{transform:translateZ(38px) rotate(-7deg) scale(1.08)}.subject-grid .subject-card h2{position:relative;margin:19px 0 7px;color:#1e2d41;font-size:1.12rem;line-height:1.35;transform:translateZ(20px)}.subject-grid .subject-card p{position:relative;line-height:1.6;transform:translateZ(14px)}.subject-grid .progress{height:9px;margin-top:16px;overflow:hidden;border-radius:99px;background:rgba(49,61,91,.08);box-shadow:inset 0 1px 3px rgba(31,45,70,.1)}.subject-grid .progress span{display:block;height:100%;border-radius:inherit;background:linear-gradient(90deg,var(--subject-color),color-mix(in srgb,var(--subject-color) 55%,#ffd75d));box-shadow:0 0 12px color-mix(in srgb,var(--subject-color) 35%,transparent);transition:width .8s ease}.subject-grid .subject-meta{position:relative;color:#68798c}.subject-grid .subject-meta span:last-child{color:var(--subject-color)}.subject-grid .subject-meta:after{content:"›";display:grid;width:29px;height:29px;margin-left:3px;place-items:center;border-radius:9px;background:var(--subject-color);color:#fff;font-size:1.25rem;transition:transform .25s}.subject-grid .subject-card:hover .subject-meta:after{transform:translateX(5px)}.subject-grid .subject-card:focus-visible{outline:3px solid color-mix(in srgb,var(--subject-color) 55%,#fff);outline-offset:4px}.subject-grid .subject-card.subject-hidden{display:none}.subjects-empty-search{display:none;padding:35px;text-align:center}.subjects-empty-search.show{display:block}
@keyframes subjectHeroFlow{0%,100%{background-position:0 50%}50%{background-position:100% 50%}}@keyframes subjectOrbit{to{transform:rotate(360deg)}}@keyframes bookFloat{50%{transform:rotate(5deg) translateY(-10px) translateZ(20px)}}@keyframes bookSpark{50%{transform:scale(1.5);opacity:.45}}@keyframes subjectLaunch{from{opacity:0;transform:translateY(30px) rotateX(-7deg) scale(.97)}to{opacity:1;transform:none}}
@media(max-width:780px){.learn-hero{min-height:250px;padding:28px}.hero-books{right:-20px;top:50px;opacity:.42;transform:scale(.75)}.learn-hero h1,.learn-hero p{max-width:78%}.subject-tools{margin-inline:12px}.subject-grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}.subject-grid .subject-card{min-height:265px;padding:18px}}
@media(max-width:520px){.learn-hero{min-height:290px}.learn-hero h1,.learn-hero p{max-width:100%}.hero-books{right:-40px;top:-5px;opacity:.25;transform:scale(.55)}.subject-tools{align-items:stretch;flex-direction:column;margin:-22px 8px 22px;padding:12px}.subject-result-count{text-align:center}.subject-grid{grid-template-columns:1fr}.subject-grid .subject-card{min-height:235px}.subject-meta{align-items:center}}
@media(prefers-reduced-motion:reduce){.learn-hero,.learn-hero:after,.hero-books span:last-child,.hero-books i,.subject-grid .subject-card{animation:none!important}.subject-grid .subject-card{opacity:1;transform:none!important;transition:none!important}}
</style>
<section class="card learn-hero"><span class="badge"><?=e($copy['badge'])?></span><h1><?=tr('learn')?> · <?=e($copy['title'])?></h1><p class="muted"><?=e($copy['intro'])?></p></section>
<?php if($res->num_rows===0):?><section class="card"><p><?=e($copy['empty'])?></p></section><?php else:?><section class="grid"><?php while($r=$res->fetch_assoc()):$pct=$r['lesson_count']?(int)round(100*$r['completed_count']/$r['lesson_count']):0;?><a class="card subject-card" href="units.php?subject_id=<?=intval($r['id'])?>"><div class="subject-icon"><?=e($r['icon'])?></div><h2><?=e(locale_value($r,'name'))?></h2><p class="muted"><?=e(locale_value($r,'description'))?></p><div class="progress"><span style="width:<?=$pct?>%"></span></div><div class="subject-meta"><span><?=intval($r['lesson_count'])?> <?=e($copy['lessons'])?></span><span><?=$pct?>% <?=e($copy['complete'])?></span></div></a><?php endwhile;?></section><?php endif;?>
<script>
(()=>{
    const hero=document.querySelector('.learn-hero');
    const grid=hero?.nextElementSibling?.classList.contains('grid')?hero.nextElementSibling:document.querySelector('section.grid');
    if(!hero||!grid)return;
    grid.classList.add('subject-grid');

    const books=document.createElement('div');
    books.className='hero-books';
    books.setAttribute('aria-hidden','true');
    books.innerHTML='<span>✦</span><span>📚</span><i></i>';
    hero.appendChild(books);

    const cards=[...grid.querySelectorAll('.subject-card')];
    const language=<?=json_encode($lang)?>;
    const labels=language==='si'
        ?{placeholder:'විෂයක් සොයන්න...',count:'විෂයයන්',empty:'ගැළපෙන විෂයක් හමු නොවීය.'}
        :language==='ta'
            ?{placeholder:'பாடத்தைத் தேடுங்கள்...',count:'பாடங்கள்',empty:'பொருந்தும் பாடம் கிடைக்கவில்லை.'}
            :{placeholder:'Search for a subject...',count:'subjects',empty:'No matching subject was found.'};

    const tools=document.createElement('div');
    tools.className='subject-tools';
    tools.innerHTML='<label class="subject-search"><span aria-hidden="true">🔎</span><input type="search" autocomplete="off"></label><span class="subject-result-count" aria-live="polite"></span>';
    tools.querySelector('input').placeholder=labels.placeholder;
    hero.insertAdjacentElement('afterend',tools);

    const empty=document.createElement('section');
    empty.className='card subjects-empty-search';
    empty.innerHTML='<div style="font-size:2.5rem">🔍</div><h3></h3>';
    empty.querySelector('h3').textContent=labels.empty;
    grid.insertAdjacentElement('afterend',empty);

    const count=tools.querySelector('.subject-result-count');
    const updateCount=visible=>count.textContent=visible+' '+labels.count;
    updateCount(cards.length);
    cards.forEach((card,index)=>{
        card.style.setProperty('--subject-index',index);
        const progress=card.querySelector('.progress span');
        if(progress){const target=progress.style.width;progress.style.width='0';requestAnimationFrame(()=>requestAnimationFrame(()=>progress.style.width=target))}
    });

    tools.querySelector('input').addEventListener('input',event=>{
        const query=event.target.value.trim().toLocaleLowerCase();
        let visible=0;
        cards.forEach(card=>{
            const match=!query||card.textContent.toLocaleLowerCase().includes(query);
            card.classList.toggle('subject-hidden',!match);
            if(match)visible++;
        });
        updateCount(visible);
        empty.classList.toggle('show',visible===0);
    });

    const reduceMotion=matchMedia('(prefers-reduced-motion: reduce)').matches;
    const finePointer=matchMedia('(hover: hover) and (pointer: fine)').matches;
    if(reduceMotion||!finePointer)return;
    cards.forEach(card=>{
        card.addEventListener('pointermove',event=>{
            const rect=card.getBoundingClientRect();
            const x=(event.clientX-rect.left)/rect.width-.5;
            const y=(event.clientY-rect.top)/rect.height-.5;
            card.style.transform=`rotateX(${-y*7}deg) rotateY(${x*9}deg) translateY(-7px)`;
        });
        card.addEventListener('pointerleave',()=>card.style.transform='');
    });
})();
</script>
<?php include __DIR__.'/../includes/footer.php';
