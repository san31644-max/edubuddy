<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
$gradeNumber=6;foreach(array_slice($argv??[],1) as $arg)if(str_starts_with($arg,'--grade='))$gradeNumber=(int)substr($arg,8);
$grade=query_one("SELECT id FROM grades WHERE grade_number=? AND status='active'",'i',[$gradeNumber]);if(!$grade)die("Active Grade $gradeNumber was not found. Run database/add_grade10.php first.\n");$gradeId=(int)$grade['id'];
$file = __DIR__ . '/../uploads/syllabus/grade-'.$gradeNumber.'/curriculum.json';
if($gradeNumber===6&&!is_file($file))$file=__DIR__.'/../uploads/syllabus/curriculum.json';
if (!is_file($file)) die('curriculum.json not found');
$items = json_decode(file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
$db = db();
$source = $db->prepare("INSERT INTO curriculum_sources(grade_id,subject_name,language,title,source_url,local_file,file_hash) VALUES(?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id),grade_id=VALUES(grade_id),subject_name=VALUES(subject_name),language=VALUES(language),title=VALUES(title),source_url=VALUES(source_url),local_file=VALUES(local_file),status='active'");
$clear = $db->prepare('DELETE FROM curriculum_chunks WHERE source_id=?');
$chunk = $db->prepare('INSERT INTO curriculum_chunks(source_id,page_number,chunk_index,content) VALUES(?,?,?,?)');
$sources = $chunks = 0;
foreach ($items as $item) {
    $source->bind_param('issssss',$gradeId,$item['subject'],$item['language'],$item['title'],$item['url'],$item['file'],$item['sha256']);
    if (!$source->execute()) continue;
    $sourceId = $db->insert_id;
    $clear->bind_param('i',$sourceId); $clear->execute();
    foreach ($item['chunks'] as $entry) {
        $page=(int)$entry['page'];$index=(int)$entry['index'];$text=$entry['text'];
        $chunk->bind_param('iiis',$sourceId,$page,$index,$text);$chunk->execute();$chunks++;
    }
    $sources++;
}
echo json_encode(['grade'=>$gradeNumber,'sources'=>$sources,'chunks'=>$chunks]);
