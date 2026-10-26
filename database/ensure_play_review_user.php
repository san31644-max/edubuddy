<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';

$phone = trim((string) getenv('PLAY_REVIEW_PHONE'));
$password = (string) getenv('PLAY_REVIEW_PASSWORD');

if (!preg_match('/^0\d{9}$/', $phone)) {
    throw new RuntimeException('PLAY_REVIEW_PHONE must be a 10-digit local phone number.');
}
if (strlen($password) < 8) {
    throw new RuntimeException('PLAY_REVIEW_PASSWORD must contain at least 8 characters.');
}

$grade = query_one("SELECT id FROM grades WHERE grade_number=6 AND status='active' LIMIT 1");
if (!$grade) {
    throw new RuntimeException('No active Grade 6 record is available for the review account.');
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$existing = query_one('SELECT id FROM users WHERE username=? OR phone=? LIMIT 1', 'ss', [$phone, $phone]);

if ($existing) {
    $statement = db()->prepare("UPDATE users SET full_name='Google Play Reviewer',username=?,phone=?,phone_verified_at=NOW(),password_hash=?,grade_id=?,medium='English',preferred_language='en',subscription_expires_at=DATE_ADD(NOW(),INTERVAL 10 YEAR),status='active' WHERE id=?");
    $id = (int) $existing['id'];
    $gradeId = (int) $grade['id'];
    $statement->bind_param('sssii', $phone, $phone, $hash, $gradeId, $id);
    $action = 'updated';
} else {
    $statement = db()->prepare("INSERT INTO users(full_name,username,email,phone,phone_verified_at,school_name,district,password_hash,grade_id,medium,preferred_language,profile_image,subscription_expires_at,status,created_at,updated_at) VALUES('Google Play Reviewer',?,NULL,?,NOW(),'Google Play Review','Colombo',?,?,'English','en',NULL,DATE_ADD(NOW(),INTERVAL 10 YEAR),'active',NOW(),NOW())");
    $gradeId = (int) $grade['id'];
    $statement->bind_param('sssi', $phone, $phone, $hash, $gradeId);
    $action = 'created';
}

if (!$statement->execute()) {
    throw new RuntimeException('Could not provision review account: ' . $statement->error);
}

$verified = query_one(
    'SELECT u.password_hash,u.medium,u.status,g.grade_number FROM users u JOIN grades g ON g.id=u.grade_id WHERE u.username=? AND u.phone=? LIMIT 1',
    'ss',
    [$phone, $phone]
);
if (
    !$verified
    || !password_verify($password, (string) $verified['password_hash'])
    || (int) $verified['grade_number'] !== 6
    || $verified['medium'] !== 'English'
    || $verified['status'] !== 'active'
) {
    throw new RuntimeException('The review account did not pass post-provisioning verification.');
}

echo "Play review account {$action} and verified successfully.\n";
