<?php
declare(strict_types=1);
require_once __DIR__.'/../includes/db.php';
$email='grade8@gmail.com';
$s=db()->prepare("SELECT u.id,u.email,u.username,u.status,u.medium,g.grade_number,CHAR_LENGTH(u.password_hash) hash_length FROM users u LEFT JOIN grades g ON g.id=u.grade_id WHERE LOWER(TRIM(u.email))=LOWER(?) OR LOWER(u.email) LIKE '%grade8%' OR LOWER(u.username) LIKE '%grade8%'");
$s->bind_param('s',$email);$s->execute();$rows=$s->get_result()->fetch_all(MYSQLI_ASSOC);
echo json_encode($rows,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE).PHP_EOL;
