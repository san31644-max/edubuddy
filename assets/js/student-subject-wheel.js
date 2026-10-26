(() => {
  const shell = document.querySelector('[data-subject-wheel]');
  if (!shell) return;
  const wheel = shell.querySelector('.qd-wheel');
  const items = [...shell.querySelectorAll('.qd-wheel-item')];
  const detail = shell.querySelector('.qd-wheel-detail');
  const open = detail.querySelector('.qd-wheel-open');
  const heading = detail.querySelector('h3');
  const description = detail.querySelector('.qd-wheel-description');
  const position = detail.querySelector('.qd-wheel-position b');
  const progressText = detail.querySelector('.qd-wheel-progress strong');
  const progressBar = detail.querySelector('.qd-wheel-progress .qd-progress-track>span');
  const previous = shell.querySelector('.qd-wheel-up');
  const next = shell.querySelector('.qd-wheel-down');
  const reduceMotion = matchMedia('(prefers-reduced-motion: reduce)');
  let selected = 0;
  let frame = 0;
  let settleTimer = 0;

  const padded = value => String(value).padStart(2, '0');
  const updateDetail = index => {
    selected = Math.max(0, Math.min(items.length - 1, index));
    const item = items[selected];
    items.forEach((entry, itemIndex) => entry.setAttribute('aria-selected', itemIndex === selected ? 'true' : 'false'));
    wheel.setAttribute('aria-activedescendant', item.id);
    heading.textContent = item.dataset.name;
    const lessons = Number(item.dataset.lessons || 0);
    const completed = Number(item.dataset.completed || 0);
    const progress = Number(item.dataset.progress || 0);
    description.textContent = `${lessons} lesson${lessons === 1 ? '' : 's'} to explore · ${completed} completed`;
    position.textContent = padded(selected + 1);
    progressText.textContent = `${progress}%`;
    progressBar.style.width = `${progress}%`;
    open.href = item.dataset.url;
    open.setAttribute('aria-label', `Open ${item.dataset.name}`);
    previous.disabled = selected === 0;
    next.disabled = selected === items.length - 1;
  };

  const paint = () => {
    frame = 0;
    const centre = wheel.getBoundingClientRect().top + wheel.clientHeight / 2;
    let closest = 0;
    let distance = Infinity;
    const flat = reduceMotion.matches || document.querySelector('.qd')?.classList.contains('qd-motion-paused');
    items.forEach((item, index) => {
      const rect = item.getBoundingClientRect();
      const delta = (rect.top + rect.height / 2 - centre) / rect.height;
      const absolute = Math.abs(delta);
      if (absolute < distance) { distance = absolute; closest = index; }
      if (flat) {
        item.style.transform = '';
        item.style.opacity = absolute < .55 ? '1' : String(Math.max(.24, 1 - absolute * .28));
      } else {
        const limited = Math.max(-2.45, Math.min(2.45, delta));
        const rotate = limited * -25;
        const depth = Math.min(125, absolute * 48);
        const scale = Math.max(.7, 1 - absolute * .105);
        item.style.transform = `rotateX(${rotate}deg) translateZ(${-depth}px) scale(${scale})`;
        item.style.opacity = String(Math.max(.16, 1 - absolute * .3));
      }
    });
    if (closest !== selected) updateDetail(closest);
  };
  const schedulePaint = () => { if (!frame) frame = requestAnimationFrame(paint); };
  const go = (index, behavior = 'smooth') => {
    const target = Math.max(0, Math.min(items.length - 1, index));
    updateDetail(target);
    items[target].scrollIntoView({ block: 'center', behavior: reduceMotion.matches || document.querySelector('.qd')?.classList.contains('qd-motion-paused') ? 'auto' : behavior });
    schedulePaint();
  };
  const settle = () => {
    clearTimeout(settleTimer);
    settleTimer = setTimeout(() => go(selected), 90);
    schedulePaint();
  };

  wheel.addEventListener('scroll', settle, { passive: true });
  wheel.addEventListener('keydown', event => {
    let destination = null;
    if (event.key === 'ArrowUp') destination = selected - 1;
    if (event.key === 'ArrowDown') destination = selected + 1;
    if (event.key === 'Home') destination = 0;
    if (event.key === 'End') destination = items.length - 1;
    if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); open.click(); return; }
    if (destination === null) return;
    event.preventDefault(); go(destination);
  });
  items.forEach((item, index) => item.addEventListener('click', () => go(index)));
  previous.addEventListener('click', () => go(selected - 1));
  next.addEventListener('click', () => go(selected + 1));
  addEventListener('resize', schedulePaint, { passive: true });
  reduceMotion.addEventListener?.('change', schedulePaint);
  new MutationObserver(schedulePaint).observe(document.querySelector('.qd'), { attributes: true, attributeFilter: ['class'] });
  updateDetail(0);
  requestAnimationFrame(() => go(0, 'auto'));
})();
