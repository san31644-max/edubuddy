(() => {
  const welcome=document.getElementById('ayubowanWelcome');if(!welcome)return;
  const skip=document.getElementById('ayubowanSkip'),voiceButton=document.getElementById('ayubowanVoice');const reduce=matchMedia('(prefers-reduced-motion: reduce)').matches;
  let timer=0,closed=false;const greeting=new Audio(welcome.dataset.voiceSrc);greeting.preload='auto';document.body.style.overflow='hidden';
  const close=()=>{if(closed)return;closed=true;clearTimeout(timer);greeting.pause();greeting.currentTime=0;welcome.classList.add('is-leaving');document.body.style.removeProperty('overflow');setTimeout(()=>{welcome.remove();document.dispatchEvent(new CustomEvent('kedu:ayubowan-complete'))},reduce?0:680)};
  skip.addEventListener('click',close);welcome.addEventListener('keydown',event=>{if(event.key==='Escape'){event.preventDefault();close()}});skip.focus();
  const playGreeting=async()=>{clearTimeout(timer);voiceButton.setAttribute('aria-pressed','true');try{await greeting.play()}catch{voiceButton.hidden=false;voiceButton.setAttribute('aria-pressed','false');voiceButton.querySelector('b').textContent='හඬ ඇසීමට ඔබන්න';timer=setTimeout(close,6500)}};
  voiceButton.addEventListener('click',()=>{voiceButton.hidden=true;playGreeting()});
  greeting.addEventListener('ended',()=>{voiceButton.setAttribute('aria-pressed','false');timer=setTimeout(close,1300)});
  greeting.addEventListener('error',()=>{voiceButton.hidden=false;voiceButton.setAttribute('aria-pressed','false');voiceButton.querySelector('b').textContent='නැවත උත්සාහ කරන්න'});
  timer=setTimeout(close,reduce?5000:10000);setTimeout(playGreeting,reduce?250:1050);
})();
