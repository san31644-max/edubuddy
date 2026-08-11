<?php
declare(strict_types=1);

ini_set('session.save_path', __DIR__.'/../includes/runtime');
require_once __DIR__.'/../includes/db.php';

if (PHP_SAPI !== 'cli') { http_response_code(403); exit("Run from the command line.\n"); }

function note_clean(string $text): string
{
    $text=html_entity_decode($text,ENT_QUOTES|ENT_HTML5,'UTF-8');
    $text=str_replace(["\u{00AD}","\u{FFFD}",'For free distribution','For Free Distribution'],'',$text);
    $text=preg_replace('/^[\s\d.()\-–—]+(?=[\p{L}])/u','',trim($text))??trim($text);
    $text=preg_replace('/\b(?:PB|UNIT)\s*\d*\b/iu','',$text)??$text;
    return trim(preg_replace('/\s+/u',' ',$text)??$text," \t\n\r\0\x0B-–—");
}

function note_points(string $content,string $summary): array
{
    $content=preg_replace('/^\s*(?:SHORT NOTES|කෙටි සටහන්|சுருக்கக் குறிப்புகள்)\s*/iu','',$content)??$content;
    $parts=preg_split('/(?:\r?\n){2,}|\r?\n(?=\s*\d+[.)]\s+)/u',$content)?:[];
    if(count($parts)<4)$parts=preg_split('/(?<=[.!?।])\s+(?=[\p{Lu}\x{0D80}-\x{0DFF}\x{0B80}-\x{0BFF}])/u',$content)?:[];
    $points=[];
    foreach(array_merge($parts,preg_split('/\r?\n/u',$summary)?:[]) as $part){
        $part=note_clean(preg_replace('/^\s*(?:\d+[.)]|[-•])\s*/u','',$part)??$part);
        if(mb_strlen($part)<25||mb_strlen($part)>520)continue;
        $key=mb_strtolower(preg_replace('/[^\p{L}\p{N}]+/u','',$part)??$part);
        if($key===''||isset($points[$key]))continue;
        $points[$key]=$part;if(count($points)===8)break;
    }
    return array_values($points);
}

function note_terms(string $terms): array
{
    $items=preg_split('/\s*,\s*|\r?\n/u',trim($terms))?:[];
    $items=array_values(array_unique(array_filter(array_map(fn($x)=>trim((string)$x),$items),fn($x)=>mb_strlen($x)>=2)));
    return array_slice($items,0,12);
}

$labels=[
 'English'=>['heading'=>'STUDY NOTES','lesson'=>'Lesson','subject'=>'Subject','points'=>'KEY STUDY POINTS','terms'=>'IMPORTANT TERMS','revision'=>'REVISION CHECKLIST','checks'=>['Explain the main idea in your own words.','Remember the important facts, rules, examples and vocabulary above.','Use the textbook activities and lesson quiz to check your understanding.']],
 'Sinhala'=>['heading'=>'අධ්‍යයන සටහන්','lesson'=>'පාඩම','subject'=>'විෂය','points'=>'ප්‍රධාන අධ්‍යයන කරුණු','terms'=>'වැදගත් පද','revision'=>'පුනරීක්ෂණ ලැයිස්තුව','checks'=>['ප්‍රධාන අදහස ඔබේම වචනවලින් පැහැදිලි කරන්න.','ඉහත වැදගත් කරුණු, නීති, උදාහරණ සහ පද මතක තබා ගන්න.','ඔබේ අවබෝධය පරීක්ෂා කිරීමට පෙළපොත් ක්‍රියාකාරකම් සහ පාඩම් ප්‍රශ්නාවලිය භාවිත කරන්න.']],
 'Tamil'=>['heading'=>'கற்றல் குறிப்புகள்','lesson'=>'பாடம்','subject'=>'பாடப்பிரிவு','points'=>'முக்கிய கற்றல் கருத்துகள்','terms'=>'முக்கிய சொற்கள்','revision'=>'மீளாய்வுப் பட்டியல்','checks'=>['முக்கிய கருத்தை உங்கள் சொந்த வார்த்தைகளில் விளக்குங்கள்.','மேலுள்ள முக்கிய தகவல்கள், விதிகள், உதாரணங்கள் மற்றும் சொற்களை நினைவில் கொள்ளுங்கள்.','உங்கள் புரிதலைச் சோதிக்க பாடநூல் செயற்பாடுகளையும் பாட வினாடிவினாவையும் பயன்படுத்துங்கள்.']]
];
$fields=['English'=>'en','Sinhala'=>'si','Tamil'=>'ta'];$db=db();$updated=0;$stats=[];
$q=$db->query("SELECT l.*,s.name_en,s.name_si,s.name_ta,g.grade_number FROM lessons l JOIN subjects s ON s.id=l.subject_id JOIN grades g ON g.id=l.grade_id WHERE l.content_source='textbook' AND l.status='active' AND l.medium IN ('English','Sinhala','Tamil') ORDER BY g.grade_number,l.medium,s.name_en,l.display_order,l.id");
if(!$q)throw new RuntimeException($db->error);
$statements=[];foreach($fields as $field)$statements[$field]=$db->prepare("UPDATE lessons SET short_notes_$field=? WHERE id=?");
$db->begin_transaction();
try{
 while($row=$q->fetch_assoc()){
  $medium=(string)$row['medium'];$field=$fields[$medium];$label=$labels[$medium];
  $title=trim((string)($row["title_$field"]?:$row['title_en']?:('Lesson '.$row['display_order'])));
  $subject=trim((string)($row["name_$field"]?:$row['name_en']));
  $content=(string)($row["content_$field"]?:$row['content_en']);$summary=(string)($row["summary_$field"]?:$row['summary_en']);
  $points=note_points($content,$summary);$terms=note_terms((string)($row["key_terms_$field"]?:$row['key_terms_en']));
  if(!$points)$points=[$medium==='English'?'Review the full textbook lesson and identify its central ideas.':($medium==='Sinhala'?'සම්පූර්ණ පෙළපොත් පාඩම සමාලෝචනය කර එහි ප්‍රධාන අදහස් හඳුනා ගන්න.':'முழுப் பாடநூல் பாடத்தையும் மீளாய்வு செய்து அதன் முக்கிய கருத்துகளை அடையாளம் காணுங்கள்.')];
  $notes=$label['heading']."\n\n{$label['lesson']}: $title\n{$label['subject']}: $subject\n\n{$label['points']}\n";
  foreach($points as $point)$notes.="• $point\n";
  if($terms){$notes.="\n{$label['terms']}\n";foreach($terms as $term)$notes.="• ".($medium==='English'?ucfirst($term):$term)."\n";}
  $notes.="\n{$label['revision']}\n";foreach($label['checks'] as $check)$notes.="✓ $check\n";$notes=rtrim($notes);
  $id=(int)$row['id'];$statements[$field]->bind_param('si',$notes,$id);$statements[$field]->execute();$updated++;
  $key='Grade '.$row['grade_number'].' / '.$medium;$stats[$key]=($stats[$key]??0)+1;
 }
 $db->commit();
}catch(Throwable $error){$db->rollback();throw $error;}
echo "Updated $updated textbook lesson study notes.\n";foreach($stats as $group=>$count)echo "- $group: $count\n";
