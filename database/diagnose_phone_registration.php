<?php
declare(strict_types=1);
require_once __DIR__.'/../includes/db.php';
$db=db();$columns=[];foreach($db->query('SHOW COLUMNS FROM users') as $row)$columns[]=$row['Field'];
foreach(['phone','phone_verified_at','district'] as $required)echo $required.': '.(in_array($required,$columns,true)?'present':'MISSING').PHP_EOL;
$sql="INSERT INTO users(id,full_name,username,email,phone,phone_verified_at,school_name,district,password_hash,grade_id,medium,preferred_language,profile_image,subscription_expires_at,status,created_at,updated_at) VALUES(?,?,?,NULL,?,NOW(),?,?,?,?,?,?,NULL,NULL,'active',NOW(),NOW())";
$statement=$db->prepare($sql);echo $statement?'Registration statement: ready'.PHP_EOL:'Registration statement: FAILED - '.$db->error.PHP_EOL;
