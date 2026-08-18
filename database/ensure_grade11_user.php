<?php
declare(strict_types=1);

// Run from the hosting/server CLI once: php database/ensure_grade11_user.php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit('Not available over HTTP.'); }
require_once __DIR__.'/../includes/db.php';

$username='1111111111';
$password='1111111111';
$db=db();
$g=$db->query("SELECT id FROM grades WHERE grade_number=11 LIMIT 1");
$grade=$g?$g->fetch_assoc():null;
if(!$grade){
    $s=$db->prepare("INSERT INTO grades(grade_number,name,status) VALUES(11,'Grade 11','active')");
    $s->execute();$grade=['id'=>$s->insert_id];
}else{
    $db->query('UPDATE grades SET status=\'active\' WHERE id='.(int)$grade['id']);
}
$gradeId=(int)$grade['id'];
$s=$db->prepare('SELECT id FROM users WHERE username=? LIMIT 1');$s->bind_param('s',$username);$s->execute();$existing=$s->get_result()->fetch_assoc();
$hash=password_hash($password,PASSWORD_DEFAULT);
if($existing){
    $s=$db->prepare("UPDATE users SET full_name='Grade 11 Sinhala Student',phone=?,phone_verified_at=NOW(),password_hash=?,grade_id=?,medium='Sinhala',preferred_language='si',status='active',updated_at=NOW() WHERE id=?");
    $s->bind_param('ssii',$username,$hash,$gradeId,$existing['id']);$s->execute();echo "Updated Grade 11 user {$username}.\n";
}else{
    $s=$db->prepare("INSERT INTO users(full_name,username,phone,phone_verified_at,password_hash,grade_id,medium,preferred_language,status,created_at,updated_at) VALUES('Grade 11 Sinhala Student',?,?,NOW(),?,?,'Sinhala','si','active',NOW(),NOW())");
    $s->bind_param('sssi',$username,$username,$hash,$gradeId);$s->execute();echo "Created Grade 11 user {$username}.\n";
}
