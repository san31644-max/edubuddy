<?php
declare(strict_types=1);
require_once __DIR__.'/../includes/db.php';
require_once __DIR__.'/../includes/helpers.php';

$expected=[[7,'Tamil','ta',9,12],[8,'Sinhala','si',10,128]];
$failures=[];
foreach($expected as [$gradeNumber,$medium,$field,$minimumSubjects,$expectedLessons]){
    $row=query_one("SELECT COUNT(DISTINCT l.subject_id) subjects,COUNT(DISTINCT l.id) lessons,COUNT(DISTINCT q.id) quizzes,COUNT(qq.id) questions,COUNT(DISTINCT CASE WHEN COALESCE(l.title_$field,'')<>'' AND COALESCE(l.content_$field,'')<>'' THEN l.id END) localized FROM grades g JOIN lessons l ON l.grade_id=g.id AND l.medium=? AND l.content_source='textbook' AND l.status='active' LEFT JOIN quizzes q ON q.lesson_id=l.id AND q.status='active' LEFT JOIN quiz_questions qq ON qq.quiz_id=q.id AND qq.status='active' WHERE g.grade_number=?",'si',[$medium,$gradeNumber]);
    if((int)$row['subjects']<$minimumSubjects)$failures[]="Grade $gradeNumber $medium has too few subjects";
    if((int)$row['lessons']!==$expectedLessons)$failures[]="Grade $gradeNumber $medium expected $expectedLessons lessons, found {$row['lessons']}";
    if((int)$row['localized']!==$expectedLessons)$failures[]="Grade $gradeNumber $medium has missing localized content";
    if((int)$row['quizzes']!==$expectedLessons||(int)$row['questions']!==$expectedLessons*5)$failures[]="Grade $gradeNumber $medium quiz data is incomplete";
    echo "Grade $gradeNumber $medium: {$row['subjects']} subjects, {$row['lessons']} lessons, {$row['quizzes']} quizzes, {$row['questions']} questions\n";
}
if($failures){fwrite(STDERR,implode("\n",$failures)."\n");exit(1);}
echo "New medium verification passed.\n";
