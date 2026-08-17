<?php
declare(strict_types=1);

ini_set('session.save_path', __DIR__ . '/../includes/runtime');
require_once __DIR__ . '/../includes/db.php';

$raw = [
    ['යෝග්‍යතාව යනු කුමක්ද?', ['දවස පුරා නිදාගැනීම', 'එදිනෙදා ක්‍රියාකාරකම් සාර්ථකව කරගැනීමට ඇති හැකියාව', 'ආහාර නොගැනීම', 'ක්‍රීඩා නොකිරීම'], 'b'],
    ['යෝග්‍යතාවේ ප්‍රධාන කොටස් ගණන කීයද?', ['2', '3', '4', '5'], 'b'],
    ['පහත කවරක් යෝග්‍යතාවේ ප්‍රධාන කොටසක් නොවේද?', ['ශාරීරික යෝග්‍යතාව', 'මානසික යෝග්‍යතාව', 'සමාජීය යෝග්‍යතාව', 'ආර්ථික යෝග්‍යතාව'], 'd'],
    ['ශාරීරික යෝග්‍යතාව යනු,', ['ශාරීරික ක්‍රියාකාරකමක් නියමිත හා උපරිම අන්දමින් කිරීමට ඇති හැකියාව', 'දිගු වේලාවක් නිදාගැනීම', 'අන් අය සමඟ කතා කිරීම', 'ආහාර ගැනීමේ හැකියාව'], 'a'],
    ['ශාරීරික යෝග්‍යතාවට බලපාන කරුණක් වන්නේ,', ['ක්‍රීඩා හා ව්‍යායාම', 'නින්ද නොලැබීම', 'ආහාර වේල් මඟහැරීම', 'දවස පුරා වාඩි වී සිටීම'], 'a'],
    ['ශාරීරික යෝග්‍යතාවට බලපාන තවත් කරුණක් වන්නේ,', ['ප්‍රමාණවත් විවේකය හා නින්ද', 'අධික රූපවාහිනී නැරඹීම', 'අක්‍රියව සිටීම', 'නිතර රාත්‍රිය පුරා අවදිව සිටීම'], 'a'],
    ['ශාරීරික යෝග්‍යතාව සඳහා වැදගත් ආහාර වන්නේ,', ['සෞඛ්‍යවත් ආහාර', 'පැණිරස පමණක්', 'කෘත්‍රිම ආහාර පමණක්', 'සිසිල් බීම පමණක්'], 'a'],
    ['මානසික යෝග්‍යතාව යනු,', ['අභියෝගවලට සාර්ථකව මුහුණ දෙමින් සතුටින් හා ඵලදායීව ජීවත් වීමේ හැකියාව', 'කිසිවෙකු සමඟ කතා නොකිරීම', 'දවස පුරා ක්‍රීඩා කිරීම', 'හැමවිටම තරහ ගැනීම'], 'a'],
    ['මානසික යෝග්‍යතාව ඇති පුද්ගලයෙකුගේ ලක්ෂණයක් වන්නේ,', ['සෑමවිටම දුකෙන් සිටීම', 'නිවැරදි තීරණ ගැනීම', 'සෑමවිටම කෝප වීම', 'අන් අය නොසලකා හැරීම'], 'b'],
    ['මානසික යෝග්‍යතාව ඇති පුද්ගලයෙකු,', ['ජය පරාජය සමසේ භාර ගනී', 'පරාජය කිසිවිටෙක භාර නොගනී', 'අන් අයට දොස් පවරයි', 'සැමවිටම බිය වේ'], 'a'],
    ['මානසික යෝග්‍යතාව වර්ධනය කර ගැනීමට උපකාරී ක්‍රියාවක් වන්නේ,', ['භාවනාව', 'රණ්ඩු කිරීම', 'නින්ද අඩු කිරීම', 'අන් අය සමඟ ගැටීම'], 'a'],
    ['සමාජීය යෝග්‍යතාව යනු,', ['යහපත් සමාජ සම්බන්ධතා ගොඩනඟාගෙන එදිනෙදා කටයුතු කිරීම', 'තනිවම සිටීම', 'අන් අය නොසලකා හැරීම', 'නීති කඩ කිරීම'], 'a'],
    ['සමාජීය යෝග්‍යතාව ඇති පුද්ගලයෙකු,', ['අන් අයට ගරු කරයි', 'අන් අයට අපහාස කරයි', 'නීති නොසලකා හරියි', 'උදව් කිරීමෙන් වැළකෙයි'], 'a'],
    ['ශාරීරික යෝග්‍යතා ගුණාංග ගණන කීයද?', ['3', '4', '5', '8'], 'c'],
    ['පහත කවරක් ශාරීරික යෝග්‍යතා ගුණාංගයකි?', ['ශක්තිය', 'ඊර්ෂ්‍යාව', 'කෝපය', 'බිය'], 'a'],
    ['ශක්තිය (Strength) යනු,', ['ප්‍රතිරෝධයකට විරුද්ධව කාර්යයක් කිරීමට ඇති හැකියාව', 'වේගයෙන් නිදාගැනීම', 'වැඩි වේලාවක් කතා කිරීම', 'හොඳින් ඇසීම'], 'a'],
    ['ශක්තියට උදාහරණයක් වන්නේ,', ['බරක් එසවීම', 'ගීතයක් ඇසීම', 'පොතක් කියවීම', 'නිදාගැනීම'], 'a'],
    ['වේගය (Speed) යනු,', ['කාර්යයක් අඩු කාලයකින් සිදු කිරීමට ඇති හැකියාව', 'කාර්යයක් නතර කිරීම', 'වැඩි කාලයක් නිදාගැනීම', 'හෙමින් කතා කිරීම'], 'a'],
    ['වේගයට උදාහරණයක් වන්නේ,', ['මීටර් 100 අඩු කාලයකින් දිවීම', 'පැයක් වාඩි වී සිටීම', 'ගීතයක් ගායනා කිරීම', 'නිදාගැනීම'], 'a'],
    ['දරා ගැනීමේ හැකියාව (Endurance) යනු,', ['කාර්යයක් වැඩි වේලාවක් පහසුවෙන් කරගෙන යාමේ හැකියාව', 'ඉක්මනින් නතර කිරීම', 'බරක් එක්වරක් එසවීම', 'කෙටි දුරක් පමණක් දිවීම'], 'a'],
    ['නම්‍යතාව (Flexibility) යනු,', ['සන්ධි වැඩි පරාසයක් තුළ ක්‍රියාත්මක කිරීමට ඇති හැකියාව', 'ඉතා වේගයෙන් දිවීම', 'බරක් එසවීම', 'දිගු කාලයක් දිවීම'], 'a'],
    ['නම්‍යතාවට උදාහරණයක් වන්නේ,', ['ජිම්නාස්ටික් ව්‍යායාම', 'නිදාගැනීම', 'ගීතයක් ඇසීම', 'පොතක් කියවීම'], 'a'],
    ['සමායෝජනය (Coordination) යනු,', ['ස්නායු හා පේශි අතර මනා සම්බන්ධතාවයෙන් චලන දැක්වීමේ හැකියාව', 'බරක් පමණක් එසවීම', 'නිදාගැනීම', 'ආහාර ගැනීම'], 'a'],
    ['සමායෝජනයට උදාහරණයක් වන්නේ,', ['පන්දුවක් උඩ දමා ඇල්ලීම', 'නිදාගැනීම', 'වාඩි වී සිටීම', 'පොතක් කියවීම'], 'a'],
    ['ශාරීරික යෝග්‍යතාව වර්ධනය කරගැනීමට සුදුසු ක්‍රියාවක් වන්නේ,', ['වේගයෙන් ඇවිදීම', 'දවස පුරා වාඩි වී සිටීම', 'ව්‍යායාම නොකිරීම', 'නින්ද නොලබා සිටීම'], 'a'],
    ['පහත කවරක් ශාරීරික යෝග්‍යතාව වර්ධනය කරන ක්‍රියාවකි?', ['බයිසිකල් පැදීම', 'දවස පුරා රූපවාහිනිය නැරඹීම', 'දවස පුරා නිදාගැනීම', 'අක්‍රියව සිටීම'], 'a'],
    ['රිද්මය (Rhythm) යනු,', ['කාල පරතරයක් සහිතව තාලයකට අනුව ක්‍රමානුකූලව ක්‍රියා කිරීම', 'කිසිදු පිළිවෙළක් නොමැති ක්‍රියාව', 'නිදාගැනීම', 'ඉතා වේගයෙන් කෑම'], 'a'],
    ['රිද්මයානුකූල ක්‍රියාකාරකමකට උදාහරණයක් වන්නේ,', ['ස්කිපින් රෝප් පැනීම', 'නිදාගැනීම', 'රූපවාහිනිය නැරඹීම', 'දිගු වේලාවක් වාඩි වී සිටීම'], 'a'],
    ['පහත සඳහන් කවරක් ප්‍රසන්න චිත්තවේගයකි?', ['සතුට', 'ඊර්ෂ්‍යාව', 'බිය', 'තරහ'], 'a'],
    ['චිත්තවේග සමබරව පවත්වා ගැනීමට සුදුසු ක්‍රියාවක් වන්නේ,', ['තම හැඟීම් හඳුනාගෙන ප්‍රතිචාර පාලනය කිරීම', 'තරහ ඇති වූ විට වහාම රණ්ඩු කිරීම', 'අන් අය සමඟ සම්බන්ධතා නතර කිරීම', 'සියලු හැඟීම් සඟවා තැබීම'], 'a'],
];

if (count($raw) !== 30) throw new RuntimeException('Grade 6 Health lesson 9 must contain exactly 30 questions.');

$db = db();
$db->begin_transaction();
try {
    $lesson = $db->query(
        "SELECT l.id FROM lessons l JOIN grades g ON g.id=l.grade_id JOIN subjects s ON s.id=l.subject_id
         WHERE g.grade_number=6 AND s.name_en='Health and Physical Education' AND l.medium='Sinhala'
           AND l.display_order=9 AND l.status='active' LIMIT 1"
    )->fetch_assoc();
    if (!$lesson) throw new RuntimeException('Grade 6 Sinhala Health lesson 9 was not found.');
    $lessonId = (int) $lesson['id'];
    $quizQuery = $db->prepare("SELECT id FROM quizzes WHERE lesson_id=? AND status='active' LIMIT 1");
    $quizQuery->bind_param('i', $lessonId);
    $quizQuery->execute();
    $quiz = $quizQuery->get_result()->fetch_assoc();
    if (!$quiz) throw new RuntimeException('Grade 6 Sinhala Health lesson 9 quiz was not found.');
    $quizId = (int) $quiz['id'];
    $ids = $db->prepare("SELECT id FROM quiz_questions WHERE quiz_id=? AND activity_type='challenge'");
    $ids->bind_param('i', $quizId);
    $ids->execute();
    $result = $ids->get_result();
    while ($row = $result->fetch_assoc()) {
        $questionId = (int) $row['id'];
        $db->query("DELETE FROM quiz_answers WHERE question_id=$questionId");
    }
    $delete = $db->prepare("DELETE FROM quiz_questions WHERE quiz_id=? AND activity_type='challenge'");
    $delete->bind_param('i', $quizId);
    $delete->execute();
    $insert = $db->prepare(
        "INSERT INTO quiz_questions
         (quiz_id,activity_type,question_si,option_a_si,option_b_si,option_c_si,option_d_si,correct_option,explanation_si,display_order,status)
         VALUES(?,'challenge',?,?,?,?,?,?,?,?,'active')"
    );
    foreach ($raw as $index => [$question, $options, $answer]) {
        $order = $index + 1;
        $explanation = 'නිවැරදි පිළිතුර: ' . $options[ord($answer) - ord('a')] . '.';
        $insert->bind_param('isssssssi', $quizId, $question, $options[0], $options[1], $options[2], $options[3], $answer, $explanation, $order);
        $insert->execute();
    }
    $title = '9 වන පාඩම — MCQ ප්‍රශ්න 30';
    $update = $db->prepare('UPDATE quizzes SET title_si=?,timer_minutes=30,pass_mark=50 WHERE id=?');
    $update->bind_param('si', $title, $quizId);
    $update->execute();
    $verify = $db->prepare("SELECT COUNT(*) total FROM quiz_questions WHERE quiz_id=? AND activity_type='challenge' AND status='active'");
    $verify->bind_param('i', $quizId);
    $verify->execute();
    $count = (int) $verify->get_result()->fetch_assoc()['total'];
    if ($count !== 30) throw new RuntimeException("Grade 6 Health lesson 9 verification failed: $count questions found.");
    $db->commit();
    echo "Grade 6 Health lesson 9 replaced and verified with 30 exact MCQs.\n";
} catch (Throwable $error) {
    $db->rollback();
    throw $error;
}
