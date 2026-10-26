(() => {
  const root = document.querySelector('.qd');
  if (!root) return;
  // Keep the primary action readable while adding a subtle desktop-only glow.
  const hero = root.querySelector('.qd-hero');
  const art = hero?.querySelector('.qd-hero-art');
  const reduceMotion = matchMedia('(prefers-reduced-motion: reduce)');
  if (hero && art && !reduceMotion.matches && matchMedia('(hover: hover) and (pointer: fine)').matches) {
    hero.addEventListener('pointermove', event => {
      if (reduceMotion.matches || root.classList.contains('qd-motion-paused')) return;
      const bounds = hero.getBoundingClientRect();
      const x = (event.clientX - bounds.left) / bounds.width - 0.5;
      const y = (event.clientY - bounds.top) / bounds.height - 0.5;
      art.style.transform = `translate(${x * 5}px, ${y * 5}px)`;
    });
    hero.addEventListener('pointerleave', () => { art.style.transform = ''; });
  }

  // The native-looking dock uses the same vector icons as the desktop sidebar.
  const dockIcons = [
    root.querySelector('.qd-nav a:nth-child(1) svg'),
    root.querySelector('.qd-nav a:nth-child(2) svg'),
    root.querySelector('.qd-tool-ai .qd-tool-icon svg'),
    root.querySelector('.qd-nav a:nth-child(5) svg'),
    root.querySelector('.qd-utility-links a:last-child svg'),
  ];
  document.querySelectorAll('.bottom a b').forEach((element, index) => {
    if (dockIcons[index]) element.replaceChildren(dockIcons[index].cloneNode(true));
  });

  root.addEventListener('click', event => {
    const link = event.target.closest('a');
    if (!link) return;
    if (!reduceMotion.matches && navigator.vibrate) navigator.vibrate(6);
  });
  const tabs = [...root.querySelectorAll('.qd-app-tabs a[href^="#"]')];
  tabs.forEach(tab => tab.addEventListener('click', event => {
    const destination = document.getElementById(tab.hash.slice(1));
    if (!destination) return;
    event.preventDefault();
    destination.scrollIntoView({ behavior: reduceMotion.matches || root.classList.contains('qd-motion-paused') ? 'instant' : 'smooth', block: 'start' });
    tabs.forEach(item => item.classList.toggle('active', item === tab));
  }));

  // The atmosphere never changes XP, rewards, quotas, or lesson access.
  let paused = false;
  try { paused = localStorage.getItem('kedu-dashboard-motion') === 'paused'; } catch {}
  const motionBar = document.createElement('div');
  motionBar.className = 'qd-motion-bar';
  const status = document.createElement('span');status.className = 'qd-motion-status';
  const toggle = document.createElement('button');toggle.type = 'button';toggle.className = 'qd-motion-toggle';
  motionBar.append(status, toggle);root.querySelector('.qd-app-tabs')?.after(motionBar);
  root.classList.add('qd-motion-enabled');
  const running = new Set(), finishers = new Set();
  const motionAllowed = () => !paused && !reduceMotion.matches && !document.hidden;
  const settle = () => {
    for (const animation of running) { try { animation.finish(); } catch {} }
    running.clear();for (const finish of [...finishers]) finish();
  };
  const syncMotion = () => {
    const off = paused || reduceMotion.matches;
    root.classList.toggle('qd-motion-paused', off);
    toggle.disabled = reduceMotion.matches;
    toggle.textContent = reduceMotion.matches ? 'Motion reduced' : off ? '▶ Play motion' : 'Ⅱ Pause motion';
    toggle.setAttribute('aria-label', reduceMotion.matches ? 'Animations reduced by your device preference' : off ? 'Play dashboard animations' : 'Pause dashboard animations');
    toggle.setAttribute('aria-pressed', String(!off));
    status.textContent = off ? 'Explore at your own pace' : 'Your learning world is alive';
    if (off) { settle();if (art) art.style.transform = ''; }
  };
  toggle.addEventListener('click', () => { paused = !paused;try { localStorage.setItem('kedu-dashboard-motion', paused ? 'paused' : 'playing'); } catch {}syncMotion(); });
  reduceMotion.addEventListener('change', syncMotion);syncMotion();
  const visibility = () => { root.classList.toggle('qd-motion-background', document.hidden);if (document.hidden) settle(); };
  document.addEventListener('visibilitychange', visibility);visibility();

  if (hero) {
    const atmosphere = document.createElement('div');atmosphere.className = 'qd-atmosphere';atmosphere.setAttribute('aria-hidden', 'true');
    const ring = document.createElement('span');ring.className = 'qd-atmosphere-ring';atmosphere.append(ring);
    for (let i = 0; i < 16; i++) {
      const mote = document.createElement('i');mote.className = 'qd-mote';
      mote.style.cssText = `--mx:${8+(i*23)%87}%;--my:${7+(i*31)%75}%;--ms:${2+i%3}px;--md:${6+i%5}s;--ml:-${i*.9}s`;atmosphere.append(mote);
    }
    hero.append(atmosphere);
  }
  const animate = (element, frames, options) => {
    if (!motionAllowed() || !element.animate) return;
    const animation = element.animate(frames, options);running.add(animation);
    animation.finished.then(() => running.delete(animation), () => running.delete(animation));
  };
  const countUp = strong => {
    const node = [...strong.childNodes].find(node => node.nodeType === Node.TEXT_NODE && /^\s*[\d,]+%?\s*$/.test(node.textContent));
    if (!node || !motionAllowed()) return;
    const original = node.textContent, target = Number(original.replace(/[^\d]/g, ''));
    if (!target || !Number.isSafeInteger(target)) return;
    const visual = document.createElement('span');visual.className = 'qd-stat-visual';visual.setAttribute('aria-hidden', 'true');
    const accessible = document.createElement('span');accessible.className = 'qd-motion-sr';accessible.textContent = original;
    node.replaceWith(visual, accessible);
    let frame;const start = performance.now();
    const finish = () => { cancelAnimationFrame(frame);visual.textContent = original;finishers.delete(finish); };finishers.add(finish);
    const tick = now => {
      if (!motionAllowed()) { finish();return; }
      const progress = Math.min(1, (now-start)/1000);
      visual.textContent = Math.round(target*(1-Math.pow(1-progress,3))).toLocaleString('en-US')+(original.includes('%')?'%':'')+' ';
      if (progress < 1) frame = requestAnimationFrame(tick);else finish();
    };frame = requestAnimationFrame(tick);
  };
  const blocks = [...root.querySelectorAll('.qd-hero,.qd-stat,.qd-trail-panel,.qd-subject,.qd-tool,.qd-player,.qd-missions,.qd-rewards,.qd-upgrade')];
  const seen = new WeakSet();
  blocks.forEach((block,index) => block.style.setProperty('--qd-order', index%4));
  const reveal = block => {
    if (seen.has(block)) return;seen.add(block);
    animate(block, [{opacity:.25,transform:'translateY(18px)'},{opacity:1,transform:'translateY(0)'}], {duration:650,easing:'cubic-bezier(.2,.8,.2,1)'});
    block.querySelectorAll('.qd-progress-track>span,.qd-subject-progress>span').forEach(bar => animate(bar, [{transform:'scaleX(0)'},{transform:'scaleX(1)'}], {duration:1100,easing:'cubic-bezier(.2,.8,.2,1)'}));
    if (block.matches('.qd-stat')) block.querySelectorAll('strong').forEach(countUp);
  };
  if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver(entries => entries.forEach(entry => {
      entry.target.classList.toggle('qd-motion-offscreen', !entry.isIntersecting);
      if (entry.isIntersecting) reveal(entry.target);
    }), {threshold:.08});blocks.forEach(block => observer.observe(block));
    addEventListener('pagehide', () => { settle();observer.disconnect(); }, {once:true});
    addEventListener('pageshow', event => { if(event.persisted) blocks.forEach(block => observer.observe(block)); });
  } else blocks.forEach(reveal);
})();
