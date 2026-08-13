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
    if (query_one('SELECT id FROM users WHERE username=? OR phone=? LIMIT 1', 'ss', [$number, $phone])) {
        echo "Grade $gradeNumber account already exists; skipped.\n";
        continue;
    }
    $name = "Sinhala Grade $gradeNumber Demo Student";
    $hash = password_hash($number, PASSWORD_DEFAULT);
    $statement = $db->prepare("INSERT INTO users(full_name,username,email,phone,phone_verified_at,password_hash,grade_id,medium,preferred_language,status) VALUES(?,?,NULL,?,NOW(),?,?,'Sinhala','si','active')");
    if (!$statement) {
        throw new RuntimeException($db->error);
    }
    $gradeId = (int)$grade['id'];
    $statement->bind_param('ssssi', $name, $number, $phone, $hash, $gradeId);
    if (!$statement->execute()) {
        throw new RuntimeException($statement->error);
    }
    echo "Created Sinhala Grade $gradeNumber demo account: $number\n";
}
