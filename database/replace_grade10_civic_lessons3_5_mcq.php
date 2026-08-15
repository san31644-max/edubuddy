<?php
declare(strict_types=1);
require_once __DIR__.'/../includes/db.php';
require_once __DIR__.'/../includes/helpers.php';
$file=__DIR__.'/data/grade10-civic-lessons3-5.json';
if(!is_file($file))throw new RuntimeException('Civic MCQ data is missing.');
$sets=json_decode(file_get_contents($file),true,512,JSON_THROW_ON_ERROR);
$titles=[3=>'බහු සංස්කෘතික සමාජය',4=>'ආර්ථික ක්‍රම හා ආර්ථික සබඳතා',5=>'ප්‍රජාතන්ත්‍රවාදී සමාජයක ගැටුම් නිරාකරණය'];
$db=db();$db->begin_transaction();
try{foreach($titles as $order=>$lessonTitle){
 $set=$sets[(string)$order]??null;if(!$set||count($set['questions']??[])!==30)throw new RuntimeException("Lesson $order data invalid");
 $l=query_one("SELECT l.id FROM lessons l JOIN grades g ON g.id=l.grade_id JOIN subjects s ON s.id=l.subject_id WHERE g.grade_number=10 AND s.name_en='Civic Education' AND l.medium='Sinhala' AND l.display_order=? AND l.status='active' LIMIT 1",'i',[$order]);if(!$l)throw new RuntimeException("Sinhala Civic lesson $order was not found.");$lessonId=(int)$l['id'];
 $fix=$db->prepare('UPDATE lessons SET title_si=? WHERE id=?');$fix->bind_param('si',$lessonTitle,$lessonId);$fix->execute();
 $q=query_one("SELECT id FROM quizzes WHERE lesson_id=? AND status='active' LIMIT 1",'i',[$lessonId]);if(!$q)throw new RuntimeException("Lesson $order quiz was not found.");$id=(int)$q['id'];
 $db->query("DELETE qa FROM quiz_answers qa JOIN quiz_questions qq ON qq.id=qa.question_id WHERE qq.quiz_id=$id AND qq.activity_type='challenge'");$db->query("DELETE FROM quiz_questions WHERE quiz_id=$id AND activity_type='challenge'");
 $s=$db->prepare("INSERT INTO quiz_questions(quiz_id,activity_type,question_si,option_a_si,option_b_si,option_c_si,option_d_si,correct_option,explanation_si,display_order,status) VALUES(?,'challenge',?,?,?,?,?,?,?,?,'active')");
 foreach($set['questions'] as $i=>$v){$n=$i+1;$s->bind_param('isssssssi',$id,$v['question'],$v['a'],$v['b'],$v['c'],$v['d'],$v['correct'],$v['explanation'],$n);if(!$s->execute())throw new RuntimeException($s->error);}
 $quizTitle="$order වන පාඩම — MCQ ප්‍රශ්න 30";$u=$db->prepare("UPDATE quizzes SET title_si=?,timer_minutes=30,pass_mark=50 WHERE id=?");$u->bind_param('si',$quizTitle,$id);$u->execute();
}$db->commit();echo "Grade 10 Civic Education lessons 3-5 replaced with 30 MCQs each.\n";}catch(Throwable $e){$db->rollback();throw $e;}
