<?php
declare(strict_types=1);

require_once __DIR__.'/../includes/db.php';
require_once __DIR__.'/../includes/helpers.php';

$db = db();
$lesson = query_one(
    "SELECT l.id,l.grade_id,l.subject_id FROM lessons l JOIN grades g ON g.id=l.grade_id JOIN subjects s ON s.id=l.subject_id WHERE g.grade_number=6 AND l.medium='Sinhala' AND s.name_en='Buddhism' AND l.display_order=1 AND l.title_si='අසිරිමත් බෝසත් කුමරු' LIMIT 1"
);
if (!$lesson) {
    throw new RuntimeException('Grade 6 Sinhala Buddhism lesson 1 was not found.');
}

$title = 'පාඩම 1: අසිරිමත් බෝසත් කුමරු — විභාග පුහුණු ප්‍රශ්න';
$paper = query_one(
    'SELECT id FROM practice_papers WHERE grade_id=? AND subject_id=? AND medium="Sinhala" AND title=? LIMIT 1',
    'iis',
    [(int)$lesson['grade_id'], (int)$lesson['subject_id'], $title]
);

if ($paper) {
    $paperId = (int)$paper['id'];
    $update = $db->prepare("UPDATE practice_papers SET total_marks=50,duration_minutes=45,status='active' WHERE id=?");
    $update->bind_param('i', $paperId);
    $update->execute();
} else {
    $insertPaper = $db->prepare("INSERT INTO practice_papers(grade_id,subject_id,medium,title,total_marks,duration_minutes,status) VALUES(?,?,'Sinhala',?,50,45,'active')");
    $gradeId = (int)$lesson['grade_id'];
    $subjectId = (int)$lesson['subject_id'];
    $insertPaper->bind_param('iis', $gradeId, $subjectId, $title);
    $insertPaper->execute();
    $paperId = (int)$db->insert_id;
}

$questions = [
    [
        'බෝසතාණන් වහන්සේ මනුලොව උපත ලැබීමට පෙර විමසා බැලූ “පස් මහ බැලුම්” (පංච මහා විලෝකනය) පහ මොනවාද?',
        "කාලය, දීපය, දේශය, කුලය හා මවු යන කරුණු පහයි.",
    ],
    [
        'බෝසත් කුමරුගේ උපතේදී සිදු වූ අසිරිමත් සිදුවීම් 3ක් සඳහන් කරන්න.',
        "1. සතරවරම් දෙවිවරු සාධුකාර පැවැත්වීම.\n2. අහසින් සිසිල් හා උණුසුම් දිය දහරා දෙකක් මතුවීම.\n3. පොළොවෙන් මතු වූ නෙළුම් මල් මත පියවර හතක් ගමන් කර සිංහනාද පැවැත්වීම.",
    ],
    [
        'බෝසත් කුමරු උපත ලබා සත් පියුම් මත යමින් පැවැත්වූ සිංහනාද ගාථාව සහ එහි තේරුම ලියන්න.',
        "ගාථාව:\nඅග්ගෝ’හමස්මි ලෝකස්ස\nජෙට්ඨෝ’හමස්මි ලෝකස්ස\nසෙට්ඨෝ’හමස්මි ලෝකස්ස\nඅයමන්තිමා ජාති\nනත්ථිදානි පුනබ්භවෝ\n\nතේරුම: මම ලොවට අග්‍ර වෙමි. මම ලොවට ජ්‍යෙෂ්ඨ වෙමි. මම ලොවට ශ්‍රේෂ්ඨ වෙමි. මේ මගේ අන්තිම උපතයි. දැන් නැවත උපතක් නැත.",
    ],
    [
        '“බුද්ධ කෝලාහලය” සහ “දේවාරාධනාව” යන්න පැහැදිලි කරන්න.',
        "බුද්ධ කෝලාහලය: ලෝකයෙහි බුදු කෙනෙකු උපදින්නට අවුරුදු දහසකට පෙර, ‘මෙයින් අවුරුදු දහසක් ඉකුත් වූ කල බුදු කෙනෙකු උපදිනවා’ යැයි හඬ නඟා ප්‍රකාශ වීමයි.\n\nදේවාරාධනාව: එය ඇසූ දහසක් සක්වල දෙවි බඹුන් මඟුල් සක්වලට රැස් වී, සහම්පති මහා බ්‍රහ්මයා ප්‍රමුඛව තුසිත දෙව්ලොව වැඩ සිටි බෝසතුන්ට මනුලොව උපදින්නට ආරාධනා කිරීමයි.",
    ],
    [
        'දෙවි බඹුන් විසින් තුසිත දෙව්ලොව වැඩ සිටි බෝසතුන්ට මනුලොව උපදින්නට කළ දේවාරාධනා ගාථාව ලියන්න.',
        "කාලෝ’යං තේ මහාවීර\nඋප්පජ්ජ මාතුකුච්ඡියං\nසදේවකං තාරයන්තෝ\nබුජ්ඣස්සු අමතං පදං",
    ],
];

$db->begin_transaction();
try {
    $delete = $db->prepare('DELETE FROM paper_questions WHERE paper_id=?');
    $delete->bind_param('i', $paperId);
    $delete->execute();

    $insert = $db->prepare('INSERT INTO paper_questions(paper_id,question_number,question_text,answer_text,marks,display_order) VALUES(?,?,?,?,10,?)');
    foreach ($questions as $index => [$question, $answer]) {
        $number = (string)($index + 1);
        $order = $index + 1;
        $insert->bind_param('isssi', $paperId, $number, $question, $answer, $order);
        $insert->execute();
    }
    $db->commit();
} catch (Throwable $error) {
    $db->rollback();
    throw $error;
}

echo "Added ".count($questions)." written questions to paper $paperId.\n";
