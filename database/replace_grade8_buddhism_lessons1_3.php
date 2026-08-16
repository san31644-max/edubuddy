<?php
declare(strict_types=1);
ini_set('session.save_path',__DIR__.'/../includes/runtime');
require_once __DIR__.'/../includes/db.php';require_once __DIR__.'/../includes/helpers.php';
$file=__DIR__.'/data/grade8-buddhism-lessons1-3.json';if(!is_file($file))throw new RuntimeException('Grade 8 Buddhism lesson package is missing.');
$sets=json_decode((string)file_get_contents($file),true,512,JSON_THROW_ON_ERROR);if(count($sets)!==3)throw new RuntimeException('Expected three lesson sets.');
$db=db();$db->begin_transaction();
try{
 $update=$db->prepare('UPDATE lessons SET short_description_si=?,short_notes_si=?,content_si=?,learning_objectives_si=?,key_terms_si=?,examples_si=?,summary_si=? WHERE id=?');
 $insert=$db->prepare("INSERT INTO quiz_questions(quiz_id,activity_type,question_si,option_a_si,option_b_si,option_c_si,option_d_si,correct_option,explanation_si,display_order,status) VALUES(?,'challenge',?,?,?,?,?,?,?,?,'active')");
 $total=0;foreach([1,2,3] as $order){
  $set=$sets[(string)$order]??null;if(!$set||count($set['questions']??[])!==30)throw new RuntimeException("Lesson $order data invalid.");
  $lesson=query_one("SELECT id FROM lessons WHERE subject_id=40 AND medium='Sinhala' AND display_order=? AND status='active' LIMIT 1",'i',[$order]);if(!$lesson)throw new RuntimeException("Lesson $order missing.");$lessonId=(int)$lesson['id'];
  $update->bind_param('sssssssi',$set['short_description'],$set['short_notes'],$set['full_content'],$set['learning_objectives'],$set['key_terms'],$set['examples'],$set['summary'],$lessonId);$update->execute();
  $quiz=query_one("SELECT id FROM quizzes WHERE lesson_id=? AND status='active' LIMIT 1",'i',[$lessonId]);if(!$quiz)throw new RuntimeException("Quiz $order missing.");$quizId=(int)$quiz['id'];
  $old=$db->query("SELECT id FROM quiz_questions WHERE quiz_id=$quizId AND activity_type='challenge'");$deleteAnswer=$db->prepare('DELETE FROM quiz_answers WHERE question_id=?');while($oldQuestion=$old->fetch_assoc()){$oldId=(int)$oldQuestion['id'];$deleteAnswer->bind_param('i',$oldId);$deleteAnswer->execute();}
  $db->query("DELETE FROM quiz_questions WHERE quiz_id=$quizId AND activity_type='challenge'");
  foreach($set['questions'] as $i=>$q){$position=$i+1;$insert->bind_param('isssssssi',$quizId,$q['question'],$q['a'],$q['b'],$q['c'],$q['d'],$q['correct'],$q['explanation'],$position);$insert->execute();}
  $count=$db->query("SELECT COUNT(*) total FROM quiz_questions WHERE quiz_id=$quizId AND activity_type='challenge' AND status='active'")->fetch_assoc();if((int)$count['total']!==30)throw new RuntimeException("Lesson $order question verification failed.");$total+=(int)$count['total'];
  $title=$order.' වන පාඩම — MCQ ප්‍රශ්න 30';$qUpdate=$db->prepare('UPDATE quizzes SET title_si=?,timer_minutes=30,pass_mark=50 WHERE id=?');$qUpdate->bind_param('si',$title,$quizId);$qUpdate->execute();
 }
 $audit=$db->query("SELECT COUNT(*) lessons,SUM(CHAR_LENGTH(short_notes_si)>=1000 AND CHAR_LENGTH(content_si)>=1400) content_ready FROM lessons WHERE subject_id=40 AND medium='Sinhala' AND display_order IN(1,2,3) AND status='active'")->fetch_assoc();
 if((int)$audit['lessons']!==3||(int)$audit['content_ready']!==3||$total!==90)throw new RuntimeException('Grade 8 Buddhism verification failed.');
 $db->commit();echo "Grade 8 Buddhism lessons 1-3 deployed: summaries, short notes, full content and 90 MCQs.\n";
}catch(Throwable $e){$db->rollback();throw $e;}
