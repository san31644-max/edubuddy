(()=>{'use strict';const shell=document.querySelector('.tutor-shell');if(!shell)return;
 const reduced=matchMedia('(prefers-reduced-motion: reduce)'),button=document.querySelector('#tutorMotion');let paused=false;try{paused=localStorage.getItem('kedu-tutor-motion')==='paused'}catch{}
 shell.classList.add('tutor-animated');const sync=()=>{const off=paused||reduced.matches;shell.classList.toggle('tutor-motion-paused',off);button.disabled=reduced.matches;button.textContent=reduced.matches?'Motion reduced':off?'▶ Play motion':'Ⅱ Pause motion';button.setAttribute('aria-pressed',String(!off));button.setAttribute('aria-label',reduced.matches?'Animations reduced by your device preference':off?'Play companion animations':'Pause companion animations')};sync();button.addEventListener('click',()=>{paused=!paused;try{localStorage.setItem('kedu-tutor-motion',paused?'paused':'playing')}catch{}sync()});reduced.addEventListener('change',sync);
 const visibility=()=>shell.classList.toggle('tutor-background',document.hidden);document.addEventListener('visibilitychange',visibility);visibility();
 const toggle=document.querySelector('#mobileContextToggle'),side=shell.querySelector('.tutor-side');
 const closeContext=()=>{if(matchMedia('(max-width:800px)').matches){side.classList.remove('context-open');toggle.setAttribute('aria-expanded','false')}};
 document.addEventListener('keydown',event=>{if(event.key==='Escape'&&side.classList.contains('context-open')){closeContext();toggle.focus()}});
 document.querySelector('#message').addEventListener('focus',closeContext);
 // Use the visible screen, including when browser chrome or the keyboard moves.
 const input=document.querySelector('#message'),mobile=matchMedia('(max-width:800px)');
 let viewportFrame;
 const fitScreen=()=>{
  cancelAnimationFrame(viewportFrame);viewportFrame=requestAnimationFrame(()=>{
   if(!mobile.matches){document.body.style.removeProperty('--tutor-screen-height');document.body.style.removeProperty('--tutor-screen-top');document.body.classList.remove('tutor-keyboard');return}
   const viewport=window.visualViewport,height=viewport?.height||innerHeight;
   document.body.style.setProperty('--tutor-screen-height',Math.round(height)+'px');
   document.body.style.setProperty('--tutor-screen-top',Math.round(viewport?.offsetTop||0)+'px');
   document.body.classList.toggle('tutor-keyboard',document.activeElement===input&&(height<500||height<innerHeight-120));
  });
 };
 fitScreen();addEventListener('resize',fitScreen);mobile.addEventListener('change',fitScreen);
 window.visualViewport?.addEventListener('resize',fitScreen);window.visualViewport?.addEventListener('scroll',fitScreen);
 input.addEventListener('focus',fitScreen);input.addEventListener('blur',fitScreen);addEventListener('pageshow',fitScreen);
 // Keep the phone header small; secondary controls live in its overflow menu.
 const options=document.querySelector('#tutorOptions'),panel=options.querySelector('.tutor-options-panel');
 const setupOptions=()=>{
  const controls=[document.querySelector('.game-sound-toggle'),button,document.querySelector('#mobileClear'),document.querySelector('.tutor-top .plan-chip')].filter(Boolean);
  const homes=controls.map(control=>{const marker=document.createComment('Tutor control position');control.before(marker);return {control,marker}});
  const arrange=()=>{options.open=false;homes.forEach(({control,marker})=>{if(mobile.matches)panel.append(control);else marker.after(control)})};arrange();mobile.addEventListener('change',arrange);
 };
 if(document.readyState==='complete')setupOptions();else document.addEventListener('DOMContentLoaded',setupOptions,{once:true});
 options.addEventListener('toggle',()=>{if(options.open)closeContext()});toggle.addEventListener('click',()=>options.open=false);
 input.addEventListener('focus',()=>options.open=false);
 document.addEventListener('click',event=>{if(options.open&&!options.contains(event.target))options.open=false});
 document.addEventListener('keydown',event=>{if(event.key==='Escape'&&options.open){options.open=false;options.querySelector('summary').focus()}});
 const brandNote=shell.querySelector('.tutor-brand p'),defaultNote=brandNote.textContent;
 const showContext=()=>{const lesson=document.querySelector('#lesson');brandNote.textContent=lesson.value?lesson.selectedOptions[0].textContent.trim():defaultNote;brandNote.title=brandNote.textContent};
 document.querySelector('#lesson').addEventListener('change',showContext);document.querySelector('#subject').addEventListener('change',showContext);showContext();
})();
