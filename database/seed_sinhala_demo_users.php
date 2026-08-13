<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit;
}

require_once __DIR__.'/../includes/db.php';

function seed_one(mysqli $db, string $sql, string $types, array $values): ?array {
    $statement = $db->prepare($sql);
    if (!$statement) throw new RuntimeException($db->error);
    $statement->bind_param($types, ...$values);
    if (!$statement->execute()) throw new RuntimeException($statement->error);
    return $statement->get_result()->fetch_assoc() ?: null;
}

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
    $grade = seed_one($db, 'SELECT id FROM grades WHERE grade_number=? LIMIT 1', 'i', [$gradeNumber]);
    if (!$grade) {
        echo "Grade $gradeNumber is not available; skipped.\n";
        continue;
    }
    $phone = '94'.substr($number, 1);
    $gradeId = (int)$grade['id'];
    $name = "Sinhala Grade $gradeNumber Demo Student";
    $hash = password_hash($number, PASSWORD_DEFAULT);
    $existing = seed_one($db, 'SELECT id FROM users WHERE phone=? LIMIT 1', 's', [$phone]);
    if ($existing) {
        $userId = (int)$existing['id'];
        $update = $db->prepare("UPDATE users SET full_name=?,phone_verified_at=NOW(),password_hash=?,grade_id=?,medium='Sinhala',preferred_language='si',status='active' WHERE id=?");
        if (!$update) throw new RuntimeException($db->error);
        $update->bind_param('ssii', $name, $hash, $gradeId, $userId);
        if (!$update->execute()) throw new RuntimeException($update->error);
        echo "Created or reset Sinhala Grade $gradeNumber demo account: $number\n";
        continue;
    }
    if (seed_one($db, 'SELECT id FROM users WHERE username=? LIMIT 1', 's', [$number])) {
        echo "Grade $gradeNumber username conflict; skipped without changing the account.\n";
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
    echo "Created or reset Sinhala Grade $gradeNumber demo account: $number\n";
}
