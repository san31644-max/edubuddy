<?php
declare(strict_types=1);
require_once __DIR__.'/../includes/db.php';
$db=db();
if(!$db->query("SHOW COLUMNS FROM users LIKE 'phone'")->num_rows&&!$db->query("ALTER TABLE users ADD phone VARCHAR(15) NULL UNIQUE AFTER email"))throw new RuntimeException($db->error);
if(!$db->query("SHOW COLUMNS FROM users LIKE 'phone_verified_at'")->num_rows&&!$db->query("ALTER TABLE users ADD phone_verified_at DATETIME NULL AFTER phone"))throw new RuntimeException($db->error);
if(!$db->query("SHOW COLUMNS FROM users LIKE 'district'")->num_rows&&!$db->query("ALTER TABLE users ADD district VARCHAR(80) NULL AFTER school_name"))throw new RuntimeException($db->error);
if(!$db->query("ALTER TABLE users MODIFY email VARCHAR(190) NULL"))throw new RuntimeException($db->error);
echo "Phone verification schema ready.\n";
