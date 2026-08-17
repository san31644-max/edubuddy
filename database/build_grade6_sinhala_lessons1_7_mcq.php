<?php
declare(strict_types=1);
$source=$argv[1]??'';$target=$argv[2]??'';
if(!is_file($source)||$target==='')throw new RuntimeException('Usage: php build_grade6_sinhala_lessons1_7_mcq.php source.txt target.json');
$text=(string)file_get_contents($source);$sets=[];
preg_match_all('/^6 ශ්‍රේණිය\s*–\s*සිංහල භාෂාව හා සාහිත්‍යය\R(.*?)(?=^6 ශ්‍රේණිය\s*–\s*සිංහල භාෂාව හා සාහිත්‍යය|\z)/msu',$text,$chunks);
foreach($chunks[1] as $chunk){
 if(!preg_match('/^(\d+) වන පාඩම\s*–\s*(.+)$/mu',$chunk,$head))continue;$lesson=(int)$head[1];$title=trim($head[2]);
 preg_match_all('/^(\d+)\.\s*(.+?)\R\s*\R?A\)\s*(.+?)\R\s*B\)\s*(.+?)\R\s*C\)\s*(.+?)\R\s*D\)\s*(.+?)\R\s*\R?පිළිතුර:\s*([ABCD])/msu',$chunk,$matches,PREG_SET_ORDER);
 $questions=[];foreach($matches as $m)$questions[]=['question'=>trim($m[2]),'a'=>trim($m[3]),'b'=>trim($m[4]),'c'=>trim($m[5]),'d'=>trim($m[6]),'correct'=>strtolower($m[7])];
 if(count($questions)!==30)throw new RuntimeException("Lesson $lesson parsed ".count($questions).' questions, expected 30.');
 $sets[(string)$lesson]=['title'=>$title,'questions'=>$questions];
}
if(array_map('intval',array_keys($sets))!==range(1,7))throw new RuntimeException('Expected lessons 1 through 7. Found: '.implode(',',array_keys($sets)));
file_put_contents($target,json_encode($sets,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR));
echo "Built 7 lessons and 210 exact MCQs.\n";
