(() => {
  const modal = document.getElementById('trophyUnlock');
  if (!modal) return;
  const key = `kedu-trophy-seen-${modal.dataset.user}`;
  const latest = Number(modal.dataset.latest || 0);
  let seen = 0;
  try { seen = Number(localStorage.getItem(key) || 0); } catch {}
  if (!latest || seen >= latest) return;
  const reduceMotion = matchMedia('(prefers-reduced-motion: reduce)').matches;
  const close = () => {
    modal.hidden = true;document.body.style.removeProperty('overflow');
    document.querySelectorAll('.trophy-confetti').forEach(piece => piece.remove());
    try { localStorage.setItem(key, String(latest)); } catch {}
  };
  modal.hidden = false;document.body.style.overflow = 'hidden';
  const closeButton = modal.querySelector('.trophy-unlock-close');
  const continueButton = modal.querySelector('.trophy-unlock-continue');
  closeButton.addEventListener('click', close);continueButton.addEventListener('click', close);
  modal.addEventListener('click', event => { if (event.target === modal) close(); });
  modal.addEventListener('keydown', event => {
    if (event.key === 'Escape') close();
    if (event.key !== 'Tab') return;
    const controls = [closeButton, continueButton];const index = controls.indexOf(document.activeElement);
    if (event.shiftKey && index <= 0) { event.preventDefault();continueButton.focus(); }
    else if (!event.shiftKey && index === controls.length - 1) { event.preventDefault();closeButton.focus(); }
  });
  closeButton.focus();
  if (navigator.vibrate) navigator.vibrate([35,45,70]);
  if (!reduceMotion) {
    const colours = ['#b58aff','#cafa96','#ffd36d','#6bd9ff','#ff82ac'];
    for (let i=0;i<64;i++) {
      const piece=document.createElement('i');piece.className='trophy-confetti';piece.style.left=`${Math.random()*100}vw`;
      piece.style.background=colours[i%colours.length];piece.style.setProperty('--fall-delay',`${Math.random()*.75}s`);
      piece.style.setProperty('--fall-time',`${2.2+Math.random()*1.8}s`);piece.style.setProperty('--fall-x',`${-80+Math.random()*160}px`);document.body.append(piece);
    }
  }
})();
