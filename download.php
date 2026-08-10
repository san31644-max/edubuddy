<?php
declare(strict_types=1);require_once __DIR__.'/includes/auth.php';$db=db();
$db->query("CREATE TABLE IF NOT EXISTS app_downloads(id BIGINT AUTO_INCREMENT PRIMARY KEY,user_id INT NULL,ip_address VARCHAR(45),user_agent VARCHAR(500),downloaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,INDEX(downloaded_at),INDEX(user_id))");
$file=__DIR__.'/K-Education-Share-Package.zip';if(!is_file($file)){http_response_code(404);exit('The K Education package is being prepared. Please try again later.');}
$userId=user()?(int)user()['id']:null;$ip=mb_substr((string)($_SERVER['REMOTE_ADDR']??''),0,45);$agent=mb_substr((string)($_SERVER['HTTP_USER_AGENT']??''),0,500);$s=$db->prepare('INSERT INTO app_downloads(user_id,ip_address,user_agent) VALUES(?,?,?)');if($s){$s->bind_param('iss',$userId,$ip,$agent);$s->execute();}
session_write_close();header('Content-Type: application/zip');header('Content-Disposition: attachment; filename="K-Education-App.zip"');header('Content-Length: '.filesize($file));header('Cache-Control: private, no-store');readfile($file);exit;
