const CACHE='k-education-public-v1';
const PUBLIC=['/educhat/','/educhat/index.php','/educhat/home.php','/educhat/login.php','/educhat/register.php','/educhat/manifest.json','/educhat/logo/k-transparent.png','/educhat/assets/js/app.js'];
self.addEventListener('install',e=>e.waitUntil(caches.open(CACHE).then(c=>c.addAll(PUBLIC))));
self.addEventListener('activate',e=>e.waitUntil(caches.keys().then(xs=>Promise.all(xs.filter(x=>x!==CACHE).map(x=>caches.delete(x))))));
self.addEventListener('fetch',e=>{const u=new URL(e.request.url);if(e.request.method!=='GET'||u.origin!==location.origin)return;if(!PUBLIC.includes(u.pathname)&&u.pathname!=='/educhat/')return;e.respondWith(fetch(e.request).then(r=>{const copy=r.clone();caches.open(CACHE).then(c=>c.put(e.request,copy));return r}).catch(()=>caches.match(e.request))) });
self.addEventListener('notificationclick',e=>{e.notification.close();e.waitUntil(clients.matchAll({type:'window',includeUncontrolled:true}).then(windows=>{for(const client of windows){if(client.url.includes('/educhat/parent/')&&'focus'in client)return client.focus();}return clients.openWindow('/educhat/parent/dashboard.php');}));});
