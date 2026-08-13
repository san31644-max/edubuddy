<?php
declare(strict_types=1);
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
require_once __DIR__.'/includes/auth.php';
require_guest();
require_once __DIR__.'/includes/textlk.php';

$errors = [];
$notice = '';
$phoneInput = trim((string)($_POST['phone'] ?? ($_GET['phone'] ?? '')));
$phone = normalize_sri_lankan_phone($phoneInput);
$stage = (string)($_POST['stage'] ?? 'send');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    if ($stage === 'send') {
        if (!$phone) {
            $errors[] = 'Enter the Sri Lankan mobile number used for your account.';
        } elseif (query_one('SELECT id FROM users WHERE phone=? AND status="active" LIMIT 1', 's', [$phone]) === null) {
            $errors[] = 'We could not find an active student account with that number.';
        } else {
            $last = (int)($_SESSION['password_reset']['sent_at'] ?? 0);
            if (time() - $last < 60) {
                $errors[] = 'Please wait 60 seconds before requesting another code.';
            } else {
                $code = (string)random_int(100000, 999999);
                [$sent, $message] = send_textlk_password_reset($phone, $code);
                if ($sent) {
                    $_SESSION['password_reset'] = [
                        'phone' => $phone,
                        'hash' => password_hash($code, PASSWORD_DEFAULT),
                        'expires' => time() + 300,
                        'sent_at' => time(),
                        'attempts' => 0,
                        'verified' => false,
                    ];
                    $notice = 'A verification code was sent to your phone. It expires in 5 minutes.';
                    $stage = 'verify';
                } else {
                    $errors[] = $message;
                }
            }
        }
    } elseif ($stage === 'verify') {
        $reset = $_SESSION['password_reset'] ?? null;
        $code = trim((string)($_POST['otp_code'] ?? ''));
        if (!$reset || ($reset['phone'] ?? '') !== $phone || time() > (int)($reset['expires'] ?? 0)) {
            $errors[] = 'The verification code expired. Request a new one.';
        } elseif ((int)($reset['attempts'] ?? 0) >= 5) {
            $errors[] = 'Too many incorrect attempts. Request a new code.';
        } elseif (!preg_match('/^\d{6}$/', $code) || !password_verify($code, (string)$reset['hash'])) {
            $_SESSION['password_reset']['attempts'] = (int)($reset['attempts'] ?? 0) + 1;
            $errors[] = 'Incorrect verification code.';
        } else {
            $_SESSION['password_reset']['verified'] = true;
            $notice = 'Phone verified. Choose a new password.';
            $stage = 'reset';
        }
    } elseif ($stage === 'reset') {
        $reset = $_SESSION['password_reset'] ?? null;
        $new = (string)($_POST['password'] ?? '');
        $confirm = (string)($_POST['confirm_password'] ?? '');
        if (!$reset || empty($reset['verified']) || ($reset['phone'] ?? '') !== $phone || time() > (int)($reset['expires'] ?? 0)) {
            $errors[] = 'Your verification expired. Start again.';
        } elseif (strlen($new) < 8 || $new !== $confirm) {
            $errors[] = 'Use a password of at least 8 characters and make both entries match.';
        } else {
            $hash = password_hash($new, PASSWORD_DEFAULT);
            $statement = db()->prepare('UPDATE users SET password_hash=? WHERE phone=? AND status="active"');
            $statement->bind_param('ss', $hash, $phone);
            if ($statement->execute() && $statement->affected_rows >= 1) {
                unset($_SESSION['password_reset']);
                flash('success', 'Your password was changed. You can now log in.');
                redirect('login.php');
            }
            $errors[] = 'The password could not be changed. Please request a new code.';
        }
    }
}

$pageTitle = 'Forgot password';
include __DIR__.'/includes/header.php';
?>
<section class="card" style="max-width:580px;margin:auto">
    <span class="badge">Account recovery</span>
    <h1>Forgot your password?</h1>
    <p class="muted">We will send a one-time verification code to the mobile number on your K Education account.</p>
    <?php foreach ($errors as $error): ?><div class="alert error"><?= e($error) ?></div><?php endforeach; ?>
    <?php if ($notice): ?><div class="alert"><?= e($notice) ?></div><?php endif; ?>
    <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="stage" value="<?= e($stage) ?>">
        <?php if ($stage === 'send'): ?>
            <label>Mobile number<input type="tel" name="phone" value="<?= e($phoneInput) ?>" placeholder="0712345678" autocomplete="tel" required></label>
            <button type="submit">Send verification code</button>
        <?php elseif ($stage === 'verify'): ?>
            <input type="hidden" name="phone" value="<?= e($phoneInput) ?>">
            <label>Verification code<input name="otp_code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" placeholder="6-digit code" required></label>
            <button type="submit">Verify code</button>
            <p><a href="forgot-password.php">Request a new code</a></p>
        <?php else: ?>
            <input type="hidden" name="phone" value="<?= e($phoneInput) ?>">
            <label>New password<input type="password" name="password" minlength="8" autocomplete="new-password" required></label>
            <label>Confirm new password<input type="password" name="confirm_password" minlength="8" autocomplete="new-password" required></label>
            <button type="submit">Change password</button>
        <?php endif; ?>
    </form>
    <p><a href="login.php">&larr; Back to login</a></p>
</section>
<?php include __DIR__.'/includes/footer.php';
