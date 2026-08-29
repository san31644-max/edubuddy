<?php
function ol_pdf_text(string $file):string{$raw=@file_get_contents($file);if($raw===false)return '';$out='';if(preg_match_all('/stream\r?\n(.*?)\r?\nendstream/s',$raw,$blocks)){foreach($blocks[1] as $block){$data=@gzuncompress($block);if($data===false)$data=$block;if(preg_match_all('/\(([^()]*)\)/s',$data,$parts))foreach($parts[1] as $part)$out.=' '.preg_replace('/\\\\([()\\\\])/','$1',$part);}}return trim($out);}
function ol_extract_questions(string $text):array{$out=[];$chunks=preg_split('/(?=^\s*\d+(?:[.)]|\s))/mu',$text,-1,PREG_SPLIT_NO_EMPTY)?:[];foreach($chunks as $chunk){if(!preg_match('/^\s*(\d+(?:[.)][a-z]?|\s*[a-z]\))?)\s*(.*)$/isu',$chunk,$m))continue;$q=trim(preg_replace('/\s+/u',' ',$m[2]));if(mb_strlen($q)>=3)$out[]=['number'=>trim($m[1]),'text'=>$q];}return $out;}

function ol_ai_extract_questions(string $file):array{
 require_once __DIR__.'/gemini_transport.php';
 if(!is_file($file)||GEMINI_API_KEY==='')return [];
 $schema=['type'=>'ARRAY','items'=>['type'=>'OBJECT','properties'=>['number'=>['type'=>'STRING'],'question'=>['type'=>'STRING']],'required'=>['number','question']]];
 $prompt='Read this O/L examination paper visually, including scanned Sinhala, Tamil or English pages. Extract every numbered question and subquestion in exact paper order. Include answer choices with MCQ question text. Preserve the original language. Do not answer questions. Return JSON only.';
 $payload=json_encode(['contents'=>[['parts'=>[['text'=>$prompt],['inlineData'=>['mimeType'=>'application/pdf','data'=>base64_encode((string)file_get_contents($file))]]]]],'generationConfig'=>['responseMimeType'=>'application/json','responseSchema'=>$schema,'temperature'=>0,'maxOutputTokens'=>32768]],JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR);
 $response=gemini_http_json(GEMINI_API_BASE.rawurlencode(GEMINI_MODEL).':generateContent?key='.rawurlencode(GEMINI_API_KEY),['Content-Type: application/json'],$payload);
 if($response['status']!==200)throw new RuntimeException('AI paper reading failed. '.$response['error']);
 $body=json_decode($response['body'],true,512,JSON_THROW_ON_ERROR);$text='';
 foreach($body['candidates'][0]['content']['parts']??[] as $part)$text.=(string)($part['text']??'');
 $text=trim(preg_replace('/^\x60\x60\x60(?:json)?\s*|\s*\x60\x60\x60$/i','',$text)??$text);
 $items=json_decode($text,true,512,JSON_THROW_ON_ERROR);$out=[];
 foreach(is_array($items)?$items:[] as $i=>$item){$number=trim((string)($item['number']??$i+1));$question=trim((string)($item['question']??''));if($question!=='')$out[]=['number'=>$number,'text'=>$question];}
 return $out;
}
function ol_read_questions(string $file):array{$items=ol_extract_questions(ol_pdf_text($file));return $items?:ol_ai_extract_questions($file);}
