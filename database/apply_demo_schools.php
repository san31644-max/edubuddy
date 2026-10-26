<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__.'/../includes/db.php';
if (!in_array(DB_HOST,['localhost','127.0.0.1','::1'],true)) throw new RuntimeException('Local database only.');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$db=db();
$catalog=json_decode(file_get_contents(__DIR__.'/data/sri_lanka_demo_schools.json'),true,512,JSON_THROW_ON_ERROR);
$schools=$catalog['schools'];
if(count($schools)!==291) throw new RuntimeException('Expected 291 official schools.');
$students=$db->query("SELECT id,medium FROM users WHERE LEFT(username,8)='demo_lk_' AND email LIKE '%@example.invalid' ORDER BY id")->fetch_all(MYSQLI_ASSOC);
$usage=array_fill(0,count($schools),0);
$update=$db->prepare('UPDATE users SET school_name=?,district=? WHERE id=?');
$db->begin_transaction();
try {
    foreach($students as $student) {
        $chosen=null;
        foreach($schools as $index=>$school) {
            if(in_array($student['medium'],$school['mediums'],true) && ($chosen===null || $usage[$index]<$usage[$chosen])) $chosen=$index;
        }
        if($chosen===null) throw new RuntimeException('No school for medium '.$student['medium']);
        $school=$schools[$chosen];$id=(int)$student['id'];
        $update->bind_param('ssi',$school['name'],$school['district'],$id);$update->execute();
        $usage[$chosen]++;
    }
    if(count($students)>=291 && in_array(0,$usage,true)) throw new RuntimeException('Some schools were not assigned.');
    $actual=$db->query("SELECT id,school_name,district,medium FROM users WHERE LEFT(username,8)='demo_lk_' AND email LIKE '%@example.invalid'")->fetch_all(MYSQLI_ASSOC);
    $byName=array_column($schools,null,'name');
    foreach($actual as $student) {
        $school=$byName[$student['school_name']]??null;
        if(!$school || $school['district']!==$student['district'] || !in_array($student['medium'],$school['mediums'],true)) throw new RuntimeException('School validation failed.');
    }
    $db->commit();
    echo 'Updated '.count($students).' synthetic profiles with '.count(array_filter($usage))." verified schools. Districts and mediums validated.\n";
} catch(Throwable $error) { $db->rollback(); throw $error; }
