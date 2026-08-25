<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../includes/db.php';

$db = db();
if (!$db->query("ALTER TABLE admins MODIFY role ENUM('admin','super_admin','operation_manager') NOT NULL DEFAULT 'admin'")) {
    throw new RuntimeException('Could not add the Operations Manager role: ' . $db->error);
}

$fullName = 'Manula';
$username = 'Manula';
$email = 'manula@keducation.local';
$passwordHash = '$2y$10$.x9rphtbb1Gui2Mncz9C5.Sice8nZxMNqVRhK7l8zhoyUrxiT4y8a';
$role = 'operation_manager';
$status = 'active';

$sql = "INSERT INTO admins(full_name,username,email,password_hash,role,status)
        VALUES(?,?,?,?,?,?)
        ON DUPLICATE KEY UPDATE
            full_name=VALUES(full_name),
            email=VALUES(email),
            password_hash=VALUES(password_hash),
            role=VALUES(role),
            status=VALUES(status)";
$stmt = $db->prepare($sql);
if (!$stmt) {
    throw new RuntimeException('Could not prepare Operations Manager provisioning: ' . $db->error);
}
$stmt->bind_param('ssssss', $fullName, $username, $email, $passwordHash, $role, $status);
if (!$stmt->execute()) {
    throw new RuntimeException('Could not provision Operations Manager: ' . $stmt->error);
}
$stmt->close();

echo "Operations Manager account is ready." . PHP_EOL;
