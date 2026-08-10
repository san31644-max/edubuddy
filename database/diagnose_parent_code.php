<?php
declare(strict_types=1);require_once __DIR__.'/../includes/db.php';$db=db();
$student=$db->query("SELECT id FROM users WHERE status='active' ORDER BY id LIMIT 1")->fetch_assoc();
if(!$student)exit("No active student account exists.\n");
$db->begin_transaction();
try{$id=(int)$student['id'];$code=strtoupper(substr(bin2hex(random_bytes(6)),0,8));$s=$db->prepare('INSERT INTO student_parent_codes(student_id,link_code,expires_at) VALUES(?,?,DATE_ADD(NOW(),INTERVAL 24 HOUR)) ON DUPLICATE KEY UPDATE link_code=VALUES(link_code),expires_at=VALUES(expires_at),created_at=NOW()');if(!$s)throw new RuntimeException($db->error);$s->bind_param('is',$id,$code);if(!$s->execute())throw new RuntimeException($s->error);$s=$db->prepare('SELECT link_code,expires_at FROM student_parent_codes WHERE student_id=? AND expires_at>NOW()');$s->bind_param('i',$id);$s->execute();$row=$s->get_result()->fetch_assoc();if(!$row)throw new RuntimeException('Generated code could not be read back.');echo "PASS: generated and read code {$row['link_code']}, expiring {$row['expires_at']}.\n";$db->rollback();}catch(Throwable $e){$db->rollback();fwrite(STDERR,"FAILED: {$e->getMessage()}\n");exit(1);}
