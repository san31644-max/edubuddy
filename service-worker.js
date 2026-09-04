const CACHE='k-education-app-v8';
const ROOT=new URL('./',self.registration.scope);
const PUBLIC=['','index.php','home.php','login.php','register.php','manifest.json','logo/k-transparent.png','logo/k-landing-transparent.png','logo/k.png','assets/js/app.js'];
const urls=PUBLIC.map(path=>new URL(path,ROOT).href);
self.addEventListener('install',event=>event.waitUntil(caches.open(CACHE).then(cache=>cache.addAll(urls)).then(()=>self.skipWaiting())));
self.addEventListener('activate',event=>event.waitUntil(caches.keys().then(keys=>Promise.all(keys.filter(key=>key!==CACHE).map(key=>caches.delete(key)))).then(()=>self.clients.claim())));
self.addEventListener('fetch',event=>{
 const request=event.request;
 if(request.method!=='GET'||new URL(request.url).origin!==location.origin)return;
 if(!urls.includes(request.url))return;
 event.respondWith(fetch(request).then(response=>{if(response.ok){const copy=response.clone();caches.open(CACHE).then(cache=>cache.put(request,copy));}return response;}).catch(()=>caches.match(request)));
});
self.addEventListener('notificationclick',event=>{event.notification.close();event.waitUntil(clients.openWindow(new URL('student/dashboard.php',ROOT).href));});
