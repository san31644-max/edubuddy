<?php
declare(strict_types=1);
require_once __DIR__.'/../includes/db.php';
require_once __DIR__.'/../includes/helpers.php';
$phone=$argv[1]??'';$row=query_one('SELECT id,full_name,phone,phone_verified_at,status FROM users WHERE phone=? LIMIT 1','s',[$phone]);
echo $row?'Account exists and is '.($row['status']??'unknown').'; phone verified: '.(!empty($row['phone_verified_at'])?'yes':'no').PHP_EOL:"Account not found.\n";
