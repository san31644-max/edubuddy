<?php
declare(strict_types=1);
require_once __DIR__.'/../includes/db.php';
require_once __DIR__.'/../includes/helpers.php';

$db=db();$db->begin_transaction();
try{
    $db->query("INSERT INTO grades(grade_number,name,status) VALUES(10,'Grade 10','active') ON DUPLICATE KEY UPDATE name=VALUES(name),status='active'");
    $grade=(int)(query_one('SELECT id FROM grades WHERE grade_number=10','')['id']??0);
    if(!$grade)throw new RuntimeException('Grade 10 could not be created.');
    $subjects=[
        ['mathematics','Mathematics','ගණිතය','கணிதம்','➗'],['science','Science','විද්‍යාව','விஞ்ஞானம்','🔬'],
        ['sinhala','Sinhala Language & Literature','සිංහල භාෂාව හා සාහිත්‍යය','சிங்கள மொழி மற்றும் இலக்கியம்','📖'],
        ['tamil','Tamil Language & Literature','දෙමළ භාෂාව හා සාහිත්‍යය','தமிழ் மொழியும் இலக்கியமும்','📖'],
        ['english','English','ඉංග්‍රීසි','ஆங்கிலம்','🔤'],['history','History','ඉතිහාසය','வரலாறு','🏛️'],
        ['geography','Geography','භූගෝල විද්‍යාව','புவியியல்','🌍'],['ict','Information & Communication Technology','තොරතුරු හා සන්නිවේදන තාක්ෂණය','தகவல் தொடர்பாடல் தொழில்நுட்பம்','💻'],
        ['health','Health & Physical Education','සෞඛ්‍ය හා ශාරීරික අධ්‍යාපනය','சுகாதாரமும் உடற்கல்வியும்','🏃'],
        ['civic','Civic Education','පුරවැසි අධ්‍යාපනය','குடியியற் கல்வி','🤝'],
        ['business-accounting','Business & Accounting Studies','ව්‍යාපාර හා ගිණුම්කරණ අධ්‍යයනය','வணிகமும் கணக்கீட்டுக் கல்வியும்','📊']
    ];
    $s=$db->prepare("INSERT INTO subjects(grade_id,subject_code,name_en,name_si,name_ta,icon,status) VALUES(?,?,?,?,?,?,'active') ON DUPLICATE KEY UPDATE name_en=VALUES(name_en),name_si=VALUES(name_si),name_ta=VALUES(name_ta),icon=VALUES(icon),status='active'");
    foreach($subjects as [$code,$en,$si,$ta,$icon]){$s->bind_param('isssss',$grade,$code,$en,$si,$ta,$icon);$s->execute();}
    $db->commit();echo "Grade 10 and ".count($subjects)." subjects are ready.\n";
}catch(Throwable $e){$db->rollback();fwrite(STDERR,$e->getMessage()."\n");exit(1);}
