(() => {
  const trail = document.querySelector('[data-trail-3d]');
  if (!trail) return;
  const stage = trail.querySelector('.qd-trail-stage');
  const items = [...trail.querySelectorAll('.qd-trail-orb')];
  const previous = trail.querySelector('.qd-trail-previous');
  const next = trail.querySelector('.qd-trail-next');
  const status = trail.querySelector('.qd-trail-status');
  const counter = trail.querySelector('.qd-trail-step b');
  const order = trail.querySelector('.qd-trail-focus-order');
  const title = trail.querySelector('.qd-trail-focus h3');
  const copy = trail.querySelector('.qd-trail-focus p');
  const open = trail.querySelector('.qd-trail-open');
  const openLabel = open.querySelector('span');
  const labels = {
    completed: ['Completed', 'Review this lesson whenever you want. Your completion is safely recorded.', 'Review lesson'],
    current: ['You are here', 'Continue your journey from this lesson and earn progress as you learn.', 'Continue lesson'],
    available: ['Ready to explore', 'This lesson is available and ready for your next learning session.', 'Start lesson'],
    locked: ['Premium path', 'Unlock Premium to continue along this part of the learning trail.', 'Unlock lesson']
  };
  let selected = Math.max(0, items.findIndex(item => item.getAttribute('aria-selected') === 'true'));
  let pointerStart = null;

  const paint = () => {
    const compact = stage.clientWidth < 480;
    const spacing = compact ? Math.min(91, stage.clientWidth * .245) : Math.min(126, stage.clientWidth * .205);
    items.forEach((item, index) => {
      const delta = index - selected;
      const absolute = Math.abs(delta);
      item.setAttribute('aria-selected', index === selected ? 'true' : 'false');
      item.style.setProperty('--trail-x', `${delta * spacing}px`);
      item.style.setProperty('--trail-y', `${Math.min(45, absolute * 20)}px`);
      item.style.setProperty('--trail-z', `${-Math.min(220, absolute * 72)}px`);
      item.style.setProperty('--trail-r', `${delta * -15}deg`);
      item.style.setProperty('--trail-scale', String(Math.max(.58, 1 - absolute * .15)));
      item.style.setProperty('--trail-opacity', String(Math.max(.14, 1 - absolute * .29)));
      item.style.pointerEvents = absolute > 2 ? 'none' : 'auto';
    });
  };
  const select = index => {
    selected = Math.max(0, Math.min(items.length - 1, index));
    const item = items[selected];
    const state = item.dataset.state;
    const words = labels[state] || labels.available;
    stage.setAttribute('aria-activedescendant', item.id);
    status.textContent = words[0];status.dataset.state = state;
    counter.textContent = String(selected + 1).padStart(2, '0');
    order.textContent = `LESSON ${item.dataset.order}`;
    title.textContent = item.dataset.title;
    copy.textContent = words[1];
    open.href = item.dataset.url;openLabel.textContent = words[2];
    open.classList.toggle('is-locked', state === 'locked');
    open.setAttribute('aria-label', `${words[2]}: ${item.dataset.title}`);
    previous.disabled = selected === 0;next.disabled = selected === items.length - 1;
    paint();
  };
  const move = amount => select(selected + amount);
  items.forEach((item,index) => item.addEventListener('click', () => select(index)));
  previous.addEventListener('click', () => move(-1));next.addEventListener('click', () => move(1));
  stage.addEventListener('keydown', event => {
    if (event.key === 'ArrowLeft' || event.key === 'ArrowUp') { event.preventDefault();move(-1); }
    else if (event.key === 'ArrowRight' || event.key === 'ArrowDown') { event.preventDefault();move(1); }
    else if (event.key === 'Home') { event.preventDefault();select(0); }
    else if (event.key === 'End') { event.preventDefault();select(items.length - 1); }
    else if (event.key === 'Enter' || event.key === ' ') { event.preventDefault();open.click(); }
  });
  stage.addEventListener('pointerdown', event => { pointerStart = {x:event.clientX,y:event.clientY,id:event.pointerId};stage.setPointerCapture?.(event.pointerId); });
  stage.addEventListener('pointerup', event => {
    if (!pointerStart || pointerStart.id !== event.pointerId) return;
    const x = event.clientX - pointerStart.x;const y = event.clientY - pointerStart.y;pointerStart = null;
    if (Math.abs(x) > 42 && Math.abs(x) > Math.abs(y) * 1.2) move(x < 0 ? 1 : -1);
  });
  stage.addEventListener('pointercancel', () => { pointerStart = null; });
  addEventListener('resize', paint, {passive:true});
  select(selected);
})();
