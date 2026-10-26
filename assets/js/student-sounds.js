/* One small synthesizer for the student app: no downloads or autoplay. */
(()=>{'use strict';
 if(!document.body.classList.contains('student-game-app'))return;
 const key='kedu-dashboard-sound',Audio=window.AudioContext||window.webkitAudioContext;
 let enabled=true,context,master,lastPlayed=-Infinity,voices=0;
 try{const saved=localStorage.getItem(key);if(saved!==null)enabled=saved==='on'}catch{}
 const patterns={tap:[[640,0,.045]],navigate:[[520,0,.05],[740,.035,.06]],select:[[760,0,.055]],action:[[620,0,.06],[880,.055,.08]],locked:[[360,0,.075],[300,.055,.075]]};
 const play=kind=>{
  if(!enabled||!Audio||document.hidden||performance.now()-lastPlayed<65||voices>5)return;
  lastPlayed=performance.now();
  try{
   if(!context||context.state==='closed'){context=new Audio();master=context.createGain();master.gain.value=enabled?1:0;master.connect(context.destination)}
   if(context.state==='suspended')context.resume().catch(()=>{});
   for(const [frequency,delay,duration] of patterns[kind]||patterns.tap){
    const oscillator=context.createOscillator(),gain=context.createGain(),start=context.currentTime+delay;
    oscillator.type='sine';oscillator.frequency.setValueAtTime(frequency,start);
    gain.gain.setValueAtTime(0,start);gain.gain.linearRampToValueAtTime(.035,start+.006);gain.gain.exponentialRampToValueAtTime(.0001,start+duration);
    oscillator.connect(gain);gain.connect(master);voices++;oscillator.onended=()=>{voices--;oscillator.disconnect();gain.disconnect()};oscillator.start(start);oscillator.stop(start+duration+.01);
   }
  }catch{/* Optional feedback never blocks a control. */}
 };
 let toggle=document.querySelector('#qdSound');
 if(!toggle){
  toggle=document.createElement('button');toggle.type='button';toggle.className='game-sound-toggle';
  toggle.innerHTML='<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m11 4-6 5H2v6h3l6 5V4Zm4 4a6 6 0 0 1 0 8m3-11a10 10 0 0 1 0 14"/></svg><span></span>';
  const route=document.querySelector('.game-routebar');
  if(route)route.insertBefore(toggle,route.lastElementChild);else document.querySelector('.topin')?.append(toggle);
 }
 toggle.dataset.soundToggle='true';toggle.disabled=!Audio;
 const sync=()=>{if(master&&context.state!=='closed')master.gain.setValueAtTime(enabled?1:0,context.currentTime);toggle.setAttribute('aria-pressed',String(enabled&&!!Audio));toggle.setAttribute('aria-label',!Audio?'Interface sounds unavailable':enabled?'Mute interface sounds':'Turn on interface sounds');toggle.title=!Audio?'Interface sounds unavailable':enabled?'Sound on — click to mute':'Sound off — click to enable';toggle.querySelector('span').textContent=enabled&&Audio?'Sound on':'Sound off';document.body.classList.toggle('game-sound-on',enabled&&!!Audio)};
 toggle.addEventListener('click',()=>{enabled=!enabled;try{localStorage.setItem(key,enabled?'on':'off')}catch{}sync();if(enabled){lastPlayed=-Infinity;play('action')}});sync();
 addEventListener('storage',event=>{if(event.key===key){enabled=event.newValue===null||event.newValue==='on';sync()}});
 addEventListener('pageshow',()=>{try{const saved=localStorage.getItem(key);enabled=saved===null||saved==='on'}catch{}sync()});
 document.addEventListener('click',event=>{
  if(event.ctrlKey||event.metaKey||event.shiftKey||event.altKey)return;
  const control=event.target.closest('button,a[href],summary,input[type="submit"],input[type="button"],input[type="reset"],[role="button"]');
  if(!control||control===toggle||control.disabled||control.getAttribute('aria-disabled')==='true'||control.closest('[inert]')||control.dataset.sound==='off')return;
  let kind='tap';
  if(control.matches('a[href]')){
   const href=control.getAttribute('href');if(!href||href.startsWith('javascript:')||control.hasAttribute('download'))return;
   kind=href.startsWith('#')?'select':'navigate';
   if(/(?:^|\/)subscription\.php(?:[?#]|$)/.test(href))kind='locked';
  }else if(control.matches('button[type="submit"],input[type="submit"],.send-button'))kind='action';
  play(kind);
 });
 document.addEventListener('change',event=>{if(event.target.matches('select,input[type="radio"],input[type="checkbox"]')&&!event.target.disabled)play('select')});
})();
