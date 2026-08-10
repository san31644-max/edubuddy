<?php
require_once __DIR__.'/../includes/db.php';require_once __DIR__.'/../includes/helpers.php';$db=db();$grade=query_one('SELECT id FROM grades WHERE grade_number=7');if(!$grade){fwrite(STDERR,"Grade 7 missing\n");exit(1);}$id=(int)$grade['id'];
$row=query_one("SELECT COUNT(*) lessons,COUNT(DISTINCT subject_id) subjects FROM lessons WHERE grade_id=? AND medium='English' AND content_source='textbook' AND status='active'",'i',[$id]);
$quizzes=(int)query_one('SELECT COUNT(*) n FROM quizzes q JOIN lessons l ON l.id=q.lesson_id WHERE l.grade_id=? AND l.medium="English" AND l.content_source="textbook" AND q.status="active"','i',[$id])['n'];
$questions=(int)query_one('SELECT COUNT(*) n FROM quiz_questions qq JOIN quizzes q ON q.id=qq.quiz_id JOIN lessons l ON l.id=q.lesson_id WHERE l.grade_id=? AND l.medium="English" AND l.content_source="textbook" AND qq.status="active"','i',[$id])['n'];
$missing=(int)query_one("SELECT COUNT(*) n FROM lessons WHERE grade_id=? AND medium='English' AND content_source='textbook' AND COALESCE(content_en,'')=''",'i',[$id])['n'];
echo "Grade 7 English: {$row['subjects']} subjects, {$row['lessons']} lessons, $quizzes quizzes, $questions questions, $missing missing notes\n";
exit((int)$row['subjects']===6&&(int)$row['lessons']===78&&$quizzes===78&&$questions===390&&$missing===0?0:1);
