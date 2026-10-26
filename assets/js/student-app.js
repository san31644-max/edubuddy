/* Shared student artwork and motion. No learning or payment state changes. */
(()=>{'use strict';
 const body=document.body;if(!body.classList.contains('student-game-app'))return;
 const assetBase=document.currentScript.src;
 if(body.classList.contains('game-screen-subjects')){
  const hero=document.querySelector('.learn-hero');
  if(hero){
   const scene=document.createElement('div');scene.className='subject-world-scene';scene.setAttribute('aria-hidden','true');
   const halo=document.createElement('div');halo.className='subject-world-halo';scene.append(halo);
   const companion=document.createElement('img');companion.className='subject-world-companion';companion.src=new URL('../images/study-companion-v2.webp',assetBase).href;companion.alt='';companion.width=220;companion.height=260;scene.append(companion);
   for(let i=0;i<12;i++){const star=document.createElement('i');star.className='subject-world-star';star.style.cssText=`--sx:${14+(i*29)%83}%;--sy:${12+(i*19)%76}%;--ss:${2+i%3}px;--sd:${5+i%5}s;--delay:-${i*.8}s`;scene.append(star)}
   hero.append(scene);
   const toggle=document.createElement('button');toggle.type='button';toggle.className='subject-motion-toggle';hero.append(toggle);
   const reduced=matchMedia('(prefers-reduced-motion: reduce)');let paused=false;try{paused=localStorage.getItem('kedu-subject-motion')==='paused'}catch{}
   const sync=()=>{const off=paused||reduced.matches;body.classList.toggle('subject-motion-paused',off);toggle.textContent=reduced.matches?'Motion reduced':off?'▶ Play motion':'Ⅱ Pause motion';toggle.setAttribute('aria-label',reduced.matches?'Animations reduced by your device preference':off?'Play subject animations':'Pause subject animations');toggle.setAttribute('aria-pressed',String(!off));toggle.disabled=reduced.matches};
   toggle.addEventListener('click',()=>{paused=!paused;try{localStorage.setItem('kedu-subject-motion',paused?'paused':'playing')}catch{}sync()});reduced.addEventListener('change',sync);sync();
   const visibility=()=>body.classList.toggle('motion-page-hidden',document.hidden);document.addEventListener('visibilitychange',visibility);visibility();
   document.querySelectorAll('.subject-card').forEach(card=>{const shine=document.createElement('span');shine.className='subject-card-sheen';shine.setAttribute('aria-hidden','true');card.prepend(shine)});
  }
 }
 document.querySelectorAll('.lesson-row').forEach((row,index)=>row.style.setProperty('--lesson-motion-index',index));
 document.querySelectorAll('.tutor-avatar').forEach(el=>{if(el.querySelector('img'))return;const image=document.createElement('img');image.src=new URL('../images/study-companion-v2.webp',document.currentScript.src).href;image.alt='K Education study companion';image.className='game-companion-avatar';el.replaceChildren(image)});
})();
