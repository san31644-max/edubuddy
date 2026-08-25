<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../includes/db.php';
$db=db();$fullName='Manula';$username='Manula';$email='manula@keducation.local';
$passwordHash='$2y$10$.x9rphtbb1Gui2Mncz9C5.Sice8nZxMNqVRhK7l8zhoyUrxiT4y8a';$role='admin';$status='active';
$stmt=$db->prepare("INSERT INTO admins(full_name,username,email,password_hash,role,status) VALUES(?,?,?,?,?,?) ON DUPLICATE KEY UPDATE full_name=VALUES(full_name),email=VALUES(email),password_hash=VALUES(password_hash),role=VALUES(role),status=VALUES(status)");
if(!$stmt)throw new RuntimeException('Could not prepare Manula admin provisioning: '.$db->error);
$stmt->bind_param('ssssss',$fullName,$username,$email,$passwordHash,$role,$status);
if(!$stmt->execute())throw new RuntimeException('Could not provision Manula admin: '.$stmt->error);
$stmt->close();echo "Manula admin account is ready.".PHP_EOL;
