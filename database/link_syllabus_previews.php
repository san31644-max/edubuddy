<?php
declare(strict_types=1);
ini_set('session.save_path',__DIR__.'/../includes/runtime');require_once __DIR__.'/../includes/db.php';require_once __DIR__.'/../includes/helpers.php';
if(PHP_SAPI!=='cli')exit("Run from the command line.\n");
$root=realpath(__DIR__.'/..');$syllabus=realpath($argv[1]??($root.'/uploads/syllabus'));if(!$syllabus)throw new RuntimeException('Syllabus folder not found.');$db=db();
$db->query("CREATE TABLE IF NOT EXISTS lesson_source_pdfs(lesson_id INT PRIMARY KEY,local_file VARCHAR(500) NOT NULL,start_page INT NOT NULL DEFAULT 1,updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,FOREIGN KEY(lesson_id) REFERENCES lessons(id) ON DELETE CASCADE)");
$rules=[
 'mathematics'=>['math'], 'science'=>['science'], 'history'=>['history','histoy','his '], 'geography'=>['geography','geo '],
 'civic'=>['civic'], 'information'=>['information communication','information-and-communication','ict '], 'health'=>['health','helth'],
 'english'=>['english'], 'sinhala'=>['sinhala'], 'tamil'=>['tamil'], 'buddh'=>['budd','bud '], 'practical'=>['practical','pts '], 'second'=>['second'],
];
$iterator=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($syllabus,FilesystemIterator::SKIP_DOTS));$groups=[];$explicit=[];
foreach($iterator as $file){
 if(!$file->isFile()||strtolower($file->getExtension())!=='pdf')continue;$path=$file->getRealPath();$relative=str_replace('\\','/',substr($path,strlen($root)+1));$search=strtolower(str_replace(['_','-'], ' ', $relative));
 if(!preg_match('/(?:grade\s*|g\s*[- ]?)(\d{1,2})/i',$search,$m))continue;$gradeNumber=(int)$m[1];
 $medium=str_contains($search,'sinhala medium')?'Sinhala':(str_contains($search,'tamil medium')?'Tamil':'English');$kind=null;
 foreach($rules as $candidate=>$needles){foreach($needles as $needle)if(str_contains($search,$needle)){$kind=$candidate;break 2;}}
 if(!$kind)continue;$grade=(int)(query_one('SELECT id FROM grades WHERE grade_number=?','i',[$gradeNumber])['id']??0);if(!$grade)continue;
 $subjects=$db->query("SELECT id,name_en FROM subjects WHERE grade_id=$grade AND status='active'")->fetch_all(MYSQLI_ASSOC);$subjectId=0;
 foreach($subjects as $subject)if(str_contains(strtolower($subject['name_en']),$kind)){$subjectId=(int)$subject['id'];break;}
 if(!$subjectId)continue;$key="$grade|$medium|$subjectId";$score=0;
 if(str_contains($search,'text book')||str_contains($search,'reading')||preg_match('/p\s*[- ]?(?:i|ii|iii)\b/i',$search))$score+=20;
 if(str_contains($search,'work book')||str_contains($search,' wb '))$score-=10;if(str_contains($search,'syllabus')||str_contains($search,'manual'))$score-=30;
 $candidate=['file'=>$relative,'score'=>$score];if(!isset($groups[$key])||$score>$groups[$key]['score'])$groups[$key]=$candidate;
 if(preg_match('/lesson\s*[- ]?0*(\d{1,2})/i',$search,$lessonMatch))$explicit[$key][(int)$lessonMatch[1]]=$relative;
}
$linked=0;
foreach($groups as $key=>$fallback){[$grade,$medium,$subjectId]=explode('|',$key);$q=$db->prepare("SELECT id,display_order FROM lessons WHERE grade_id=? AND medium=? AND subject_id=? AND content_source='textbook' ORDER BY display_order,id");$q->bind_param('isi',$grade,$medium,$subjectId);$q->execute();
 foreach($q->get_result()->fetch_all(MYSQLI_ASSOC) as $lesson){$order=(int)$lesson['display_order'];$file=$explicit[$key][$order]??$fallback['file'];$id=(int)$lesson['id'];$start=1;$save=$db->prepare('INSERT INTO lesson_source_pdfs(lesson_id,local_file,start_page) VALUES(?,?,?) ON DUPLICATE KEY UPDATE local_file=VALUES(local_file),start_page=IF(local_file=VALUES(local_file),start_page,VALUES(start_page))');$save->bind_param('isi',$id,$file,$start);$save->execute();$linked++;}
}
echo "$linked lesson preview links are ready.\n";
