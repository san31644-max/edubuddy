<?php
declare(strict_types=1);
require_once __DIR__.'/includes/auth.php';
$supportEmail=defined('SUPPORT_EMAIL')?SUPPORT_EMAIL:'support@keducation.site';
$pageTitle='Help & Support';
$pageDescription='Get help with your K Education account, verification code, lessons, AI Tutor and Premium access.';
include __DIR__.'/includes/header.php';
?>
<style>
.support-shell{max-width:960px;margin:auto}.support-hero{position:relative;overflow:hidden;padding:clamp(25px,5vw,48px);color:#fff;background:radial-gradient(circle at 88% 8%,rgba(68,219,232,.36),transparent 27%),linear-gradient(125deg,#21175f,#5743d4 58%,#188ab3)}.support-hero:after{content:'?';position:absolute;right:7%;top:50%;transform:translateY(-55%) rotate(10deg);font-size:clamp(8rem,24vw,15rem);font-weight:1000;line-height:1;color:rgba(255,255,255,.08);pointer-events:none}.support-hero>*{position:relative;z-index:1}.support-hero .badge{background:rgba(255,255,255,.15);color:#fff}.support-hero h1{max-width:650px;margin:17px 0 9px;color:#fff;font-size:clamp(2rem,6vw,3.4rem);letter-spacing:-.045em}.support-hero p{max-width:600px;margin:0;color:rgba(255,255,255,.83);font-size:1.02rem}.support-actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:23px}.support-actions .btn{min-height:46px}.support-actions .btn.alt{background:rgba(255,255,255,.14);color:#fff;border:1px solid rgba(255,255,255,.26)}.support-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px;margin:18px 0}.support-topic{padding:20px;border:1px solid #e3e8f4;border-radius:22px;background:rgba(255,255,255,.92);box-shadow:0 10px 28px rgba(45,56,110,.07)}.support-topic h2{margin:0 0 7px;font-size:1.08rem}.support-topic p{margin:0;color:var(--muted);font-size:.92rem}.support-topic a{font-weight:800}.support-icon{display:grid;place-items:center;width:42px;height:42px;margin-bottom:13px;border-radius:14px;background:#eeedff;font-size:1.25rem}.support-topic:nth-child(2) .support-icon{background:#e7f8ff}.support-topic:nth-child(3) .support-icon{background:#e7faf3}.support-topic:nth-child(4) .support-icon{background:#fff4df}.support-faq{padding:clamp(18px,4vw,28px)}.support-faq h2{margin:0 0 12px}.support-faq details{padding:15px 0;border-bottom:1px solid var(--line)}.support-faq details:last-child{border-bottom:0}.support-faq summary{display:flex;justify-content:space-between;gap:12px;cursor:pointer;font-weight:850;list-style:none}.support-faq summary::-webkit-details-marker{display:none}.support-faq summary:after{content:'+';color:var(--violet);font-size:1.2rem;line-height:1}.support-faq details[open] summary:after{content:'−'}.support-faq details p{margin:10px 0 0;color:var(--muted)}.support-safety{margin-top:16px;padding:16px 18px;border:1px solid #ffe4aa;border-radius:17px;background:#fff8e8;color:#6b4b12}.support-safety strong{display:block;margin-bottom:3px}@media(max-width:620px){.support-grid{grid-template-columns:1fr}.support-hero:after{right:-2%;top:15%;transform:none;font-size:8rem}.support-actions{display:grid;grid-template-columns:1fr}.support-actions .btn{width:100%}}
</style>
<div class="support-shell">
 <section class="card support-hero">
  <span class="badge">K Education help desk</span>
  <h1>How can we help?</h1>
  <p>Find quick help for your account, verification code, learning tools and Premium access. If you’re still stuck, email our support team.</p>
  <div class="support-actions"><a class="btn" href="mailto:<?=e($supportEmail)?>?subject=K%20Education%20support">Email K Education support</a><a class="btn alt" href="<?=url('home.php')?>">Back to K Education</a></div>
 </section>

 <section class="support-grid" aria-label="Popular support topics">
  <article class="support-topic"><span class="support-icon" aria-hidden="true">📱</span><h2>Account and verification</h2><p>Can’t register or sign in? <a href="<?=url('register.php')?>">Register</a> or <a href="<?=url('forgot-password.php')?>">reset your password</a>. If a verification code doesn’t arrive, check that your number is correct and request a new code.</p></article>
  <article class="support-topic"><span class="support-icon" aria-hidden="true">📚</span><h2>Lessons and AI Tutor</h2><p>Open <a href="<?=url('subjects.php')?>">Subjects</a> to find learning materials. For an AI Tutor issue, email us with the page or lesson name and what happened.</p></article>
  <article class="support-topic"><span class="support-icon" aria-hidden="true">⭐</span><h2>Premium and payments</h2><p>For a payment or Premium access issue, email the transaction reference, date and the phone number on your K Education account. Never send card or banking credentials.</p></article>
  <article class="support-topic"><span class="support-icon" aria-hidden="true">🔒</span><h2>Privacy and account deletion</h2><p>Read our <a href="<?=url('privacy-policy.php')?>">Privacy Policy</a> or visit the <a href="<?=url('delete-account.php')?>">account deletion page</a>.</p></article>
 </section>

 <section class="card support-faq">
  <h2>Quick answers</h2>
  <details><summary>My verification code didn’t arrive. What should I do?</summary><p>Confirm the phone number is correct, wait briefly, then request a new code. A code expires after five minutes. If it still doesn’t arrive, email support and include the number used and the error shown—never include the code itself.</p></details>
  <details><summary>I forgot my password. How do I get back in?</summary><p>Use the <a href="<?=url('forgot-password.php')?>">password reset page</a> and follow the instructions sent to your registered phone.</p></details>
  <details><summary>What should I include when I email support?</summary><p>Describe what you were trying to do, the page or feature, your account phone number, and any error text. For payment questions, include the transaction reference and date.</p></details>
  <div class="support-safety"><strong>Keep your account safe</strong>Never email your password, verification code, API token, card number or online banking details.</div>
 </section>
</div>
<?php include __DIR__.'/includes/footer.php'; ?>
