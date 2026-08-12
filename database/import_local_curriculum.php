<?php
declare(strict_types=1);
ini_set('session.save_path',__DIR__.'/../includes/runtime');
require_once __DIR__.'/../includes/db.php';
require_once __DIR__.'/../includes/helpers.php';

if(PHP_SAPI!=='cli')exit("Run this importer from the command line.\n");
$folder=$argv[1]??(__DIR__.'/../uploads/syllabus/grade 10 english medium');
$folder=realpath($folder)?:'';
if(!$folder||!is_dir($folder))throw new RuntimeException('Curriculum folder was not found.');
if(!preg_match('/grade\s*(\d+)/i',$folder,$gradeMatch))throw new RuntimeException('Put files in a folder named with the grade, for example: grade 10 english medium.');
$gradeNumber=(int)$gradeMatch[1];
$medium=stripos($folder,'sinhala')!==false?'Sinhala':(stripos($folder,'tamil')!==false?'Tamil':'English');
$field=['Sinhala'=>'si','Tamil'=>'ta','English'=>'en'][$medium];
$subjectPatterns=['book.pdf'=>'sinhala','second lang tamil'=>'second language tamil','katholika'=>'catholic','kristhu'=>'christianity','agri food'=>'agriculture','jalaja jeewa'=>'aquatic resources','art craft'=>'art and craft','design electr'=>'electrical and electronic technology','design mahanic'=>'mechanical technology','busines'=>'business','business'=>'business','comm.media'=>'communication and media','comm media'=>'communication and media','media'=>'communication and media','entre pher'=>'entrepreneurship','entrep'=>'entrepreneurship','home sci'=>'home economics','home econ'=>'home economics','creation, elec'=>'technology','history'=>'history','histoy'=>'history','his '=>'history','geo'=>'geography','civic'=>'civic','ict'=>'information','math'=>'mathematics','science'=>'science','literary'=>'english','english'=>'english','bud'=>'buddhism','health'=>'health','pts'=>'practical','sin rasaswadaya'=>'sinhala','sinhala la'=>'sinhala','sinhala'=>'sinhala'];
$subjectNames=['buddhism'=>['Buddhism','බුද්ධ ධර්මය','Buddhism'],'history'=>['History','ඉතිහාසය','History'],'practical'=>['Practical and Technical Skills','ප්‍රායෝගික හා තාක්ෂණික කුසලතා','Practical and Technical Skills'],'sinhala'=>['Sinhala','සිංහල','Sinhala'],'catholic'=>['Catholicism','කතෝලික ධර්මය','Catholicism'],'second language tamil'=>['Second Language Tamil','දෙවන භාෂාව දෙමළ','இரண்டாம் மொழி தமிழ்'],'agriculture'=>['Agriculture and Food Technology','කෘෂිකර්මය හා ආහාර තාක්ෂණය','Agriculture and Food Technology'],'business'=>['Business & Accounting Studies','ව්‍යාපාර හා ගිණුම්කරණ අධ්‍යයනය','Business & Accounting Studies'],'communication and media'=>['Communication and Media Studies','සන්නිවේදනය හා මාධ්‍ය අධ්‍යයනය','Communication and Media Studies'],'entrepreneurship'=>['Entrepreneurship Studies','ව්‍යවසායකත්ව අධ්‍යයනය','Entrepreneurship Studies'],'home economics'=>['Home Economics','ගෘහ ආර්ථික විද්‍යාව','Home Economics'],'technology'=>['Technology Studies','තාක්ෂණවේදය','Technology Studies']];
$subjectNames += [
 'mathematics'=>['Mathematics','ගණිතය','Mathematics'],
 'science'=>['Science','විද්‍යාව','Science'],
 'geography'=>['Geography','භූගෝල විද්‍යාව','Geography'],
 'health'=>['Health & Physical Education','සෞඛ්‍ය හා ශාරීරික අධ්‍යාපනය','Health & Physical Education'],
 'information'=>['Information & Communication Technology','තොරතුරු හා සන්නිවේදන තාක්ෂණය','Information & Communication Technology'],
 'business'=>['Business & Accounting Studies','ව්‍යාපාර හා ගිණුම්කරණ අධ්‍යයනය','Business & Accounting Studies'],
 'christianity'=>['Christianity','ක්‍රිස්තු ධර්මය','Christianity'],
 'aquatic resources'=>['Aquatic Resources Technology','ජලජ සම්පත් තාක්ෂණවේදය','Aquatic Resources Technology'],
 'art and craft'=>['Art and Craft','චිත්‍ර හා ශිල්ප කලා','Art and Craft'],
 'electrical and electronic technology'=>['Electrical and Electronic Technology','විදුලි හා ඉලෙක්ට්‍රොනික තාක්ෂණවේදය','Electrical and Electronic Technology'],
 'mechanical technology'=>['Mechanical Technology','යාන්ත්‍රික තාක්ෂණවේදය','Mechanical Technology'],
];
$db=db();
$existingGrade=query_one('SELECT id FROM grades WHERE grade_number=?','i',[$gradeNumber]);
if(!$existingGrade){
 $gradeId=(int)(query_one('SELECT COALESCE(MAX(id),0)+1 next_id FROM grades')['next_id']??1);
 $addGrade=$db->prepare("INSERT INTO grades(id,grade_number,name,status) VALUES(?,?,?,'active')");
 $gradeName='Grade '.$gradeNumber;$addGrade->bind_param('iis',$gradeId,$gradeNumber,$gradeName);
 if(!$addGrade->execute())throw new RuntimeException('Grade creation failed: '.$addGrade->error);
}else{
 $activate=$db->prepare("UPDATE grades SET name=?,status='active' WHERE id=?");$gradeName='Grade '.$gradeNumber;$gradeId=(int)$existingGrade['id'];$activate->bind_param('si',$gradeName,$gradeId);$activate->execute();
}
$grade=(int)(query_one('SELECT id FROM grades WHERE grade_number=?','i',[$gradeNumber])['id']??0);
if(!$grade)throw new RuntimeException("Grade $gradeNumber could not be created.");
$serverPython=__DIR__.'/../.venv/bin/python';
$python=PHP_OS_FAMILY==='Windows'?(glob((getenv('LOCALAPPDATA')?:'').'/Programs/Python/Python*/python.exe')?:['python'])[0]:(is_executable($serverPython)?$serverPython:'python3');
$extractor=realpath(__DIR__.'/../scripts/extract_curriculum_pdf.py');$seen=[];$imported=[];
$db->query("CREATE TABLE IF NOT EXISTS lesson_source_pdfs(lesson_id INT PRIMARY KEY,local_file VARCHAR(500) NOT NULL,start_page INT NOT NULL DEFAULT 1,updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,FOREIGN KEY(lesson_id) REFERENCES lessons(id) ON DELETE CASCADE)");
foreach(glob($folder.'/*.pdf')?:[] as $book){
    $hash=hash_file('sha256',$book);if(isset($seen[$hash]))continue;$seen[$hash]=true;
    $lower=strtolower(basename($book));$code=null;foreach($subjectPatterns as $pattern=>$candidate){if(str_contains($lower,$pattern)){$code=$candidate;break;}}
    if(!$code){echo 'Skipped unknown subject: '.basename($book).PHP_EOL;continue;}
    $subject=$code==='technology'
        ?(int)(query_one('SELECT id FROM subjects WHERE grade_id=? AND LOWER(name_en)=? ORDER BY id LIMIT 1','is',[$grade,'technology studies'])['id']??0)
        :(int)(query_one('SELECT id FROM subjects WHERE grade_id=? AND LOWER(name_en) LIKE CONCAT("%",?,"%") ORDER BY id LIMIT 1','is',[$grade,$code])['id']??0);
    if(!$subject&&isset($subjectNames[$code])){$names=$subjectNames[$code];$subject=(int)(query_one('SELECT COALESCE(MAX(id),0)+1 next_id FROM subjects')['next_id']??1);$subjectCode=preg_replace('/[^A-Z]/','',strtoupper(substr($names[0],0,8))).$gradeNumber;$description=$names[0].' textbook learning for Grade '.$gradeNumber.'.';$icon='📘';$add=$db->prepare("INSERT INTO subjects(id,grade_id,subject_code,name_en,name_si,name_ta,description_en,icon,status) VALUES(?,?,?,?,?,?,?,?,'active')");if(!$add)throw new RuntimeException($db->error);$add->bind_param('iissssss',$subject,$grade,$subjectCode,$names[0],$names[1],$names[2],$description,$icon);if(!$add->execute())throw new RuntimeException($add->error);echo 'Created subject: '.$names[0].PHP_EOL;}
    if(!$subject){echo "Skipped missing subject $code: ".basename($book).PHP_EOL;continue;}
    if(str_contains($lower,'work book')||preg_match('/\bwb\b/i',$lower)){echo 'Skipped workbook preview: '.basename($book).PHP_EOL;continue;}
    $pipes=[];$process=proc_open([$python,$extractor,$book,$medium==='Sinhala'?'fm_abhaya':'unicode'],[['pipe','r'],['pipe','w'],['pipe','w']],$pipes,null,null,['bypass_shell'=>true]);
    if(!is_resource($process))throw new RuntimeException('PDF extractor did not start.');
    fclose($pipes[0]);$json=stream_get_contents($pipes[1]);$error=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);
    if(proc_close($process)!==0)throw new RuntimeException(trim($error));$data=json_decode($json,true,512,JSON_THROW_ON_ERROR);
    $sections=$data['sections']??[];$pages=$data['page_texts']??[];if(count($sections)<1){echo 'Skipped; no chapters detected: '.basename($book).PHP_EOL;continue;}
    $firstPage=(int)($sections[0]['pdf_page']??1);$bookText=trim(implode("\n\n",array_column(array_values(array_filter($pages,fn($page)=>(int)$page['page']>=$firstPage)),'text')));$textStarts=[];$cursor=0;
    foreach($sections as $section){$searchTitle=trim((string)(preg_split('/\s+-\s+/',(string)$section['title'],2)[0]??$section['title']));$position=stripos($bookText,$searchTitle,$cursor);$textStarts[]=$position===false?null:$position;if($position!==false)$cursor=$position+strlen($searchTitle);}
    $db->begin_transaction();try{
        foreach($sections as $index=>$section){
            $number=(int)$section['number'];$start=(int)$section['pdf_page'];$end=(int)($sections[$index+1]['pdf_page']??PHP_INT_MAX);
            $content=trim(implode("\n\n",array_column(array_values(array_filter($pages,fn($page)=>(int)$page['page']>=$start&&(int)$page['page']<$end)),'text')));
            if(mb_strlen($content)<100&&$textStarts[$index]!==null){$finish=$textStarts[$index+1]??strlen($bookText);$content=trim(substr($bookText,$textStarts[$index],$finish-$textStarts[$index]));}
            if(mb_strlen($content)<100)continue;$content=mb_substr($content,0,200000);$title=trim((string)$section['title']);
            $unit=query_one('SELECT id FROM units WHERE grade_id=? AND subject_id=? AND unit_number=?','iii',[$grade,$subject,$number]);
            if($unit)$unitId=(int)$unit['id'];else{$unitId=(int)(query_one('SELECT COALESCE(MAX(id),0)+1 next_id FROM units')['next_id']??1);$q=$db->prepare("INSERT INTO units(id,grade_id,subject_id,unit_number,name_en,name_si,name_ta,display_order,status) VALUES(?,?,?,?,?,?,?,?,'active')");$q->bind_param('iiiisssi',$unitId,$grade,$subject,$number,$title,$title,$title,$number);if(!$q->execute())throw new RuntimeException($q->error);}
            $summary=implode("\n",array_slice(array_values(array_filter(array_map('trim',preg_split('/\R+/u',$content)?:[]),fn($line)=>mb_strlen($line)>30)),0,6));$description="Official textbook chapter $number.";
            $lesson=query_one('SELECT id FROM lessons WHERE grade_id=? AND subject_id=? AND medium=? AND display_order=? ORDER BY id LIMIT 1','iisi',[$grade,$subject,$medium,$number]);
            if($lesson){$lessonId=(int)$lesson['id'];$q=$db->prepare("UPDATE lessons SET unit_id=?,title_$field=?,short_description_$field=?,content_$field=?,short_notes_$field=?,summary_$field=?,content_source='textbook',status='active' WHERE id=?");$q->bind_param('isssssi',$unitId,$title,$description,$content,$summary,$summary,$lessonId);if(!$q->execute())throw new RuntimeException($q->error);}
            else{$lessonId=(int)(query_one('SELECT COALESCE(MAX(id),0)+1 next_id FROM lessons')['next_id']??1);$q=$db->prepare("INSERT INTO lessons(id,grade_id,medium,content_source,subject_id,unit_id,title_$field,short_description_$field,content_$field,short_notes_$field,summary_$field,display_order,status) VALUES(?,?,?,'textbook',?,?,?,?,?,?,?,?,'active')");if(!$q)throw new RuntimeException($db->error);$q->bind_param('iisiisssssi',$lessonId,$grade,$medium,$subject,$unitId,$title,$description,$content,$summary,$summary,$number);if(!$q->execute())throw new RuntimeException($q->error);}
            $root=realpath(__DIR__.'/..');$relative=str_replace('\\','/',substr(realpath($book),strlen($root)+1));$source=$db->prepare('INSERT INTO lesson_source_pdfs(lesson_id,local_file,start_page) VALUES(?,?,?) ON DUPLICATE KEY UPDATE local_file=VALUES(local_file),start_page=VALUES(start_page)');$source->bind_param('isi',$lessonId,$relative,$start);if(!$source->execute())throw new RuntimeException($source->error);
            $imported[$code][$number]=$title;
        }
        $db->commit();echo basename($book).': '.count($sections).' chapters imported.'.PHP_EOL;
    }catch(Throwable $e){$db->rollback();throw $e;}
}
foreach($imported as $code=>$lessons)echo ucfirst($code).': '.count($lessons).' unique lessons ready.'.PHP_EOL;
