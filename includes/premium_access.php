<?php
declare(strict_types=1);

/** One fixed preview lesson per subject, for the student's grade and medium. */
function free_subject_lesson(int $subjectId): ?array
{
    static $previews=[];
    $student=user();
    if(!$student)return null;
    $key=$student['grade_id'].':'.$student['medium'].':'.$subjectId;
    if(!array_key_exists($key,$previews)) {
        $previews[$key]=query_one("SELECT id,subject_id,display_order FROM lessons WHERE subject_id=? AND grade_id=? AND status='active' AND content_source='textbook' AND (medium='All' OR medium=?) ORDER BY display_order,id LIMIT 1",'iis',[$subjectId,(int)$student['grade_id'],(string)$student['medium']]);
    }
    return $previews[$key];
}

function can_access_lesson(int $lessonId): bool
{
    if(!user())return false;
    $lesson=query_one("SELECT id,subject_id FROM lessons WHERE id=? AND grade_id=? AND status='active' AND content_source='textbook' AND (medium='All' OR medium=?)",'iis',[$lessonId,(int)user()['grade_id'],(string)user()['medium']]);
    if(!$lesson)return false;
    return is_premium() || (int)(free_subject_lesson((int)$lesson['subject_id'])['id']??0)===$lessonId;
}

function require_lesson_access(int $lessonId): void
{
    if(!can_access_lesson($lessonId)) {
        flash('warning','Free accounts can view the first lesson in each subject. Unlock Premium for every lesson, quiz, practice activity and textbook.');
        redirect('subscription.php');
    }
}
