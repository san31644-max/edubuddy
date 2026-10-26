<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__.'/../includes/db.php';
if (!in_array(DB_HOST, ['localhost','127.0.0.1','::1'], true)) {
    throw new RuntimeException('This demo seeder only supports the local database.');
}
$db=db();
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$target=6477;
$before=(int)$db->query('SELECT COUNT(*) FROM users')->fetch_row()[0];
$grades=$db->query("SELECT id,grade_number FROM grades WHERE status='active' AND grade_number BETWEEN 6 AND 11 ORDER BY grade_number")->fetch_all(MYSQLI_ASSOC);
if (!$grades) throw new RuntimeException('No active grades 6–11 are available.');
if (!in_array('--apply',$argv,true)) {
    echo json_encode(['database'=>DB_NAME,'existing_students'=>$before,'students_to_add'=>max(0,$target-$before),'grades'=>$grades],JSON_PRETTY_PRINT).PHP_EOL;
    $summary=$db->query("SELECT COUNT(*) demo_students,COUNT(DISTINCT school_name) demo_schools,COUNT(DISTINCT medium) mediums,COUNT(DISTINCT grade_id) grades,SUM(subscription_expires_at>NOW()) premium FROM users WHERE LEFT(username,8)='demo_lk_'")->fetch_assoc();
    $summary['demo_point_records']=(int)$db->query("SELECT COUNT(*) FROM student_points p JOIN users u ON u.id=p.user_id WHERE LEFT(u.username,8)='demo_lk_' AND p.award_key='demo-profile-points'")->fetch_row()[0];
    echo json_encode($summary,JSON_PRETTY_PRINT).PHP_EOL;
    exit;
}
$firstNames=[
    ['Kasun','Nipun','Dinuka','Tharindu','Kavindu','Pasindu','Chamod','Lahiru','Isuru','Sahan','Dulith','Yasiru','Mineth','Ravindu','Pavithra','Sachini','Nethmi','Tharushi','Dinithi','Kavindi','Hiruni','Dewmini','Imasha','Sanduni','Senuri','Chamodi','Oshadi','Ama','Nimasha','Hansani'],
    ['Arun','Kavin','Tharshan','Praveen','Sanjay','Dinesh','Vijay','Niroshan','Akash','Sathish','Janani','Kavitha','Abirami','Dharshini','Nithya','Shalini','Anjali','Priya','Karthika','Thivya'],
    ['Ahamed','Imran','Fazil','Rizwan','Afnan','Irfan','Nawaz','Farhan','Ayesha','Fathima','Safiya','Hafsa','Zainab','Nusra','Rifka','Shakira']
];
$lastNames=[
    ['Perera','Fernando','Silva','Dissanayake','Bandara','Wijesinghe','Jayasinghe','Gunawardena','Herath','Senanayake','Karunaratne','Wickramasinghe','Ratnayake','Madushanka','Abeysekera','Samarasinghe','Ekanayake','Rajapaksha','Weerasinghe','Ranasinghe'],
    ['Sivakumar','Balasubramaniam','Rajendran','Shanmugam','Tharmalingam','Sivapalan','Kanagaratnam','Nadarajah','Ravichandran','Selvarajah'],
    ['Mohamed','Ahamed','Hassan','Hussain','Ibrahim','Ismail','Farook','Rahman','Nazeer','Rasheed']
];
$schoolCatalog=json_decode(file_get_contents(__DIR__.'/data/sri_lanka_demo_schools.json'),true,512,JSON_THROW_ON_ERROR)['schools'];
$premium=(int)$db->query('SELECT COUNT(*) FROM users WHERE subscription_expires_at>NOW()')->fetch_row()[0];
$premiumNeeded=max(0,23-$premium);
$insert=$db->prepare('INSERT INTO users(full_name,username,email,phone,school_name,district,password_hash,grade_id,medium,preferred_language,subscription_expires_at,status,created_at) VALUES(?,?,?,?,?,?,?,?,?,?,?,\'active\',?)');
$pointsInsert=$db->prepare("INSERT INTO student_points(user_id,grade_id,activity_type,points,award_key,description,awarded_at) VALUES(?,?,'demo_seed',?,'demo-profile-points','Synthetic demo learning points',?)");
$exists=$db->prepare('SELECT id FROM users WHERE username=? OR email=? OR phone=?');
$added=0;
$db->begin_transaction();
try {
    for ($serial=1;$added<max(0,$target-$before);$serial++) {
        $username=sprintf('demo_lk_%05d',$serial);
        $email=$username.'@example.invalid';
        // Deliberately non-dialable: no real person's mobile number is assigned.
        $phone=sprintf('DEMO-%010d',$serial);
        $exists->bind_param('sss',$username,$email,$phone);$exists->execute();
        if ($exists->get_result()->num_rows) continue;
        $group=random_int(0,99);$group=$group<70?0:($group<90?1:2);
        $first=$firstNames[$group][array_rand($firstNames[$group])];
        $middle=$firstNames[$group][array_rand($firstNames[$group])];
        $last=$lastNames[$group][array_rand($lastNames[$group])];
        $name="$first $middle $last";
        $grade=$grades[array_rand($grades)];$gradeId=(int)$grade['id'];
        $medium=random_int(1,100)<=20?'English':($group===1?'Tamil':($group===2?(random_int(0,1)?'Tamil':'Sinhala'):'Sinhala'));
        $language=['English'=>'en','Sinhala'=>'si','Tamil'=>'ta'][$medium];
        $eligibleSchools=array_values(array_filter($schoolCatalog,static fn($school)=>in_array($medium,$school['mediums'],true)));
        $schoolRecord=$eligibleSchools[($serial-1)%count($eligibleSchools)];
        $district=$schoolRecord['district'];
        $school=$schoolRecord['name'];
        // No known password exists for generated profiles.
        $hash=password_hash(bin2hex(random_bytes(24)),PASSWORD_BCRYPT,['cost'=>4]);
        $expires=$added<$premiumNeeded?date('Y-m-d H:i:s',strtotime('+30 days')):null;
        $created=date('Y-m-d H:i:s',time()-random_int(0,150*86400));
        $insert->bind_param('sssssssissss',$name,$username,$email,$phone,$school,$district,$hash,$gradeId,$medium,$language,$expires,$created);
        $insert->execute();$userId=(int)$db->insert_id;
        $points=random_int(0,600)*5;
        $awarded=date('Y-m-d H:i:s',random_int(strtotime($created),time()));
        $pointsInsert->bind_param('iiis',$userId,$gradeId,$points,$awarded);$pointsInsert->execute();
        $added++;
    }
    $total=(int)$db->query('SELECT COUNT(*) FROM users')->fetch_row()[0];
    if ($before<=$target && $total!==$target) throw new RuntimeException('Unexpected final student count.');
    $db->commit();
    echo "Added $added synthetic student profiles. Total students: $total.\n";
    echo "Demo usernames start with demo_lk_; emails use example.invalid; phones are non-dialable DEMO identifiers.\n";
} catch (Throwable $error) { $db->rollback(); throw $error; }
