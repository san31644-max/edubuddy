(()=>{
  const loader=document.querySelector('#pageLoader'),emoji=document.querySelector('#loaderEmoji');
  const icons=['📚','✏️','🧠','🔬','📐','🌟'];let timer;
  const show=()=>{if(!loader)return;emoji.textContent=icons[Math.floor(Math.random()*icons.length)];loader.classList.add('show');loader.setAttribute('aria-hidden','false');timer=setInterval(()=>emoji.textContent=icons[Math.floor(Math.random()*icons.length)],650)};
  const hide=()=>{clearInterval(timer);loader?.classList.remove('show');loader?.setAttribute('aria-hidden','true')};
  window.addEventListener('pageshow',()=>{
    hide();
    if(!document.querySelector('.tour-overlay,.celebration')) document.body.style.removeProperty('overflow');
  });
  document.querySelectorAll('a[href]').forEach(a=>a.addEventListener('click',event=>{const href=a.getAttribute('href');if(!href||href.startsWith('#')||href.startsWith('javascript:')||a.target==='_blank'||event.ctrlKey||event.metaKey||event.shiftKey)return;show()}));
  document.querySelectorAll('form').forEach(form=>form.addEventListener('submit',event=>{if(form.id==='chatForm')return;show();const button=event.submitter;if(button)setTimeout(()=>button.disabled=true,0)}));
  setTimeout(hide,8000);
})();
(()=>{let audio;document.addEventListener('click',e=>{if(!e.target.closest('button,.btn,a.card'))return;try{const C=window.AudioContext||window.webkitAudioContext;if(!C)return;audio=audio||new C();const o=audio.createOscillator(),g=audio.createGain();o.frequency.value=520;g.gain.setValueAtTime(.025,audio.currentTime);g.gain.exponentialRampToValueAtTime(.001,audio.currentTime+.055);o.connect(g).connect(audio.destination);o.start();o.stop(audio.currentTime+.06)}catch(_){}})})();
