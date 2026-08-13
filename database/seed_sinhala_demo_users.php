<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit;
}

require_once __DIR__.'/../includes/db.php';

// Local/demo Sinhala-medium accounts. The phone number is also the username and password.
$accounts = [
    6 => '0666666666',
    7 => '0777777777',
    8 => '0888888888',
    9 => '0999999999',
    10 => '0000000000',
];

$db = db();
foreach ($accounts as $gradeNumber => $number) {
    $grade = query_one("SELECT id FROM grades WHERE grade_number=? AND status='active' LIMIT 1", 'i', [$gradeNumber]);
    if (!$grade) {
        echo "Grade $gradeNumber is not available; skipped.\n";
        continue;
    }
    $phone = '94'.substr($number, 1);
    $gradeId = (int)$grade['id'];
    $name = "Sinhala Grade $gradeNumber Demo Student";
    $hash = password_hash($number, PASSWORD_DEFAULT);
    $existing = query_one('SELECT id FROM users WHERE username=? OR phone=? LIMIT 1', 'ss', [$number, $phone]);
    if ($existing) {
        $userId = (int)$existing['id'];
        $update = $db->prepare("UPDATE users SET full_name=?,phone=?,phone_verified_at=NOW(),password_hash=?,grade_id=?,medium='Sinhala',preferred_language='si',status='active' WHERE id=?");
        if (!$update) {
            throw new RuntimeException($db->error);
        }
        $update->bind_param('sssii', $name, $phone, $hash, $gradeId, $userId);
        if (!$update->execute()) {
            throw new RuntimeException($update->error);
        }
        echo "Updated Sinhala Grade $gradeNumber demo account: $number\n";
        continue;
    }
    $statement = $db->prepare("INSERT INTO users(full_name,username,email,phone,phone_verified_at,password_hash,grade_id,medium,preferred_language,status) VALUES(?,?,NULL,?,NOW(),?,?,'Sinhala','si','active')");
    if (!$statement) {
        throw new RuntimeException($db->error);
    }
    $statement->bind_param('ssssi', $name, $number, $phone, $hash, $gradeId);
    if (!$statement->execute()) {
        throw new RuntimeException($statement->error);
    }
    echo "Created Sinhala Grade $gradeNumber demo account: $number\n";
}
