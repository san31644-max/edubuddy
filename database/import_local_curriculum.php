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
$subjectPatterns=['history'=>'history','histoy'=>'history','geo'=>'geography','civic'=>'civic','ict'=>'ict','math'=>'mathematics','science'=>'science','literary'=>'english','english'=>'english'];
$grade=(int)(query_one('SELECT id FROM grades WHERE grade_number=?','i',[$gradeNumber])['id']??0);
if(!$grade)throw new RuntimeException("Grade $gradeNumber does not exist.");
$python=PHP_OS_FAMILY==='Windows'?(glob((getenv('LOCALAPPDATA')?:'').'/Programs/Python/Python*/python.exe')?:['python'])[0]:'python3';
$extractor=realpath(__DIR__.'/../scripts/extract_curriculum_pdf.py');$seen=[];$imported=[];$db=db();
$db->query("CREATE TABLE IF NOT EXISTS lesson_source_pdfs(lesson_id INT PRIMARY KEY,local_file VARCHAR(500) NOT NULL,start_page INT NOT NULL DEFAULT 1,updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,FOREIGN KEY(lesson_id) REFERENCES lessons(id) ON DELETE CASCADE)");
foreach(glob($folder.'/*.pdf')?:[] as $book){
    $hash=hash_file('sha256',$book);if(isset($seen[$hash]))continue;$seen[$hash]=true;
    $lower=strtolower(basename($book));$code=null;foreach($subjectPatterns as $pattern=>$candidate){if(str_contains($lower,$pattern)){$code=$candidate;break;}}
    if(!$code){echo 'Skipped unknown subject: '.basename($book).PHP_EOL;continue;}
    $subject=(int)(query_one('SELECT id FROM subjects WHERE grade_id=? AND subject_code=?','is',[$grade,$code])['id']??0);
    if(!$subject){echo "Skipped missing subject $code: ".basename($book).PHP_EOL;continue;}
    $pipes=[];$process=proc_open([$python,$extractor,$book,$medium==='Sinhala'?'auto':'unicode'],[['pipe','r'],['pipe','w'],['pipe','w']],$pipes,null,null,['bypass_shell'=>true]);
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
            if(mb_strlen($content)<100)continue;$title=trim((string)$section['title']);
            $unit=query_one('SELECT id FROM units WHERE grade_id=? AND subject_id=? AND unit_number=?','iii',[$grade,$subject,$number]);
            if($unit)$unitId=(int)$unit['id'];else{$q=$db->prepare("INSERT INTO units(grade_id,subject_id,unit_number,name_en,name_si,name_ta,display_order,status) VALUES(?,?,?,?,?,?,?,'active')");$q->bind_param('iiisssi',$grade,$subject,$number,$title,$title,$title,$number);$q->execute();$unitId=(int)$db->insert_id;}
            $summary=implode("\n",array_slice(array_values(array_filter(array_map('trim',preg_split('/\R+/u',$content)?:[]),fn($line)=>mb_strlen($line)>30)),0,6));$description="Official textbook chapter $number.";
            $lesson=query_one('SELECT id FROM lessons WHERE grade_id=? AND subject_id=? AND medium=? AND display_order=? ORDER BY id LIMIT 1','iisi',[$grade,$subject,$medium,$number]);
            if($lesson){$lessonId=(int)$lesson['id'];$q=$db->prepare("UPDATE lessons SET unit_id=?,title_$field=?,short_description_$field=?,content_$field=?,short_notes_$field=?,summary_$field=?,content_source='textbook',status='active' WHERE id=?");$q->bind_param('isssssi',$unitId,$title,$description,$content,$content,$summary,$lessonId);$q->execute();}
            else{$q=$db->prepare("INSERT INTO lessons(grade_id,medium,content_source,subject_id,unit_id,title_$field,short_description_$field,content_$field,short_notes_$field,summary_$field,display_order,status) VALUES(?,?,'textbook',?,?,?,?,?,?,?,?,'active')");$q->bind_param('isiisssssi',$grade,$medium,$subject,$unitId,$title,$description,$content,$content,$summary,$number);$q->execute();$lessonId=(int)$db->insert_id;}
            $root=realpath(__DIR__.'/..');$relative=str_replace('\\','/',substr(realpath($book),strlen($root)+1));$source=$db->prepare('INSERT INTO lesson_source_pdfs(lesson_id,local_file,start_page) VALUES(?,?,?) ON DUPLICATE KEY UPDATE local_file=VALUES(local_file),start_page=VALUES(start_page)');$source->bind_param('isi',$lessonId,$relative,$start);$source->execute();
            $imported[$code][$number]=$title;
        }
        $db->commit();echo basename($book).': '.count($sections).' chapters imported.'.PHP_EOL;
    }catch(Throwable $e){$db->rollback();throw $e;}
}
foreach($imported as $code=>$lessons)echo ucfirst($code).': '.count($lessons).' unique lessons ready.'.PHP_EOL;
