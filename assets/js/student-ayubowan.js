(() => {
  const welcome=document.getElementById('ayubowanWelcome');if(!welcome)return;
  const skip=document.getElementById('ayubowanSkip'),voiceButton=document.getElementById('ayubowanVoice');
  const reduce=matchMedia('(prefers-reduced-motion: reduce)').matches;
  const greeting=welcome.dataset.voiceSrc?new Audio(welcome.dataset.voiceSrc):null;
  const voiceText=welcome.dataset.voiceText||'',voiceLanguage=welcome.dataset.language||'en-GB';
  if(greeting)greeting.preload='auto';
  let timer=0,closed=false;document.body.style.overflow='hidden';
  const stopVoice=()=>{if(greeting){greeting.pause();greeting.currentTime=0}window.speechSynthesis?.cancel()};
  const close=()=>{if(closed)return;closed=true;clearTimeout(timer);stopVoice();welcome.classList.add('is-leaving');document.body.style.removeProperty('overflow');setTimeout(()=>{welcome.remove();document.dispatchEvent(new CustomEvent('kedu:ayubowan-complete'))},reduce?0:680)};
  const showVoiceHint=()=>{voiceButton.hidden=false;voiceButton.setAttribute('aria-pressed','false');voiceButton.querySelector('b').textContent=welcome.dataset.voiceHint||'';timer=setTimeout(close,6500)};
  const finishVoice=()=>{voiceButton.setAttribute('aria-pressed','false');timer=setTimeout(close,1200)};
  const speakWelcome=()=>{if(!('speechSynthesis'in window)||!voiceText.trim()){showVoiceHint();return}window.speechSynthesis.cancel();const utterance=new SpeechSynthesisUtterance(voiceText);utterance.lang=voiceLanguage;utterance.rate=.9;utterance.pitch=1.03;utterance.onend=finishVoice;utterance.onerror=showVoiceHint;window.speechSynthesis.speak(utterance)};
  const playGreeting=async()=>{clearTimeout(timer);voiceButton.setAttribute('aria-pressed','true');if(!greeting){speakWelcome();return}try{await greeting.play()}catch{showVoiceHint()}};
  skip.addEventListener('click',close);welcome.addEventListener('keydown',event=>{if(event.key==='Escape'){event.preventDefault();close()}});skip.focus();
  voiceButton.addEventListener('click',()=>{voiceButton.hidden=true;playGreeting()});
  if(greeting){greeting.addEventListener('ended',finishVoice);greeting.addEventListener('error',showVoiceHint);timer=setTimeout(close,reduce?7000:12000);setTimeout(playGreeting,reduce?250:1050)}
  else{timer=setTimeout(close,reduce?7000:12000);setTimeout(playGreeting,reduce?250:1050)}
})();
