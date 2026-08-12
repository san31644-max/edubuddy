<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli')exit;
$admin=@new mysqli('localhost','root','');if($admin->connect_errno)throw new RuntimeException('Local MySQL administrator connection failed.');
$password=bin2hex(random_bytes(24));$quoted="'".$admin->real_escape_string($password)."'";
foreach(["CREATE USER IF NOT EXISTS 'keducation_app'@'localhost' IDENTIFIED BY $quoted","ALTER USER 'keducation_app'@'localhost' IDENTIFIED BY $quoted","GRANT ALL PRIVILEGES ON educhat.* TO 'keducation_app'@'localhost'","FLUSH PRIVILEGES"] as $sql)if(!$admin->query($sql))throw new RuntimeException($admin->error);
$values=['EDUCHAT_DB_HOST'=>'localhost','EDUCHAT_DB_NAME'=>'educhat','EDUCHAT_DB_USER'=>'keducation_app','EDUCHAT_DB_PASS'=>$password];$target=__DIR__.'/../includes/runtime/database-secret.php';
if(file_put_contents($target,"<?php\nreturn ".var_export($values,true).";\n")===false)throw new RuntimeException('Could not write database secret.');chmod($target,0640);echo "Production database connection configured.\n";
