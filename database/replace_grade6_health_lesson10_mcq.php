<?php
declare(strict_types=1);

ini_set('session.save_path', __DIR__ . '/../includes/runtime');
require_once __DIR__ . '/../includes/db.php';

$raw = [
    ['රෝග ප්‍රධාන වශයෙන් බෙදා දැක්විය හැක්කේ,', ['කොටස් දෙකකට', 'කොටස් තුනකට', 'කොටස් හතරකට', 'කොටස් පහකට'], 'a'],
    ['රෝග ප්‍රධාන වශයෙන් බෙදෙන්නේ,', ['බෝවන හා බෝ නොවන රෝග ලෙස', 'උණ හා කැස්ස ලෙස', 'සුළු හා විශාල රෝග ලෙස', 'දිවා හා රාත්‍රී රෝග ලෙස'], 'a'],
    ['පහත කවරක් බෝවන රෝගයකි?', ['සෙම්ප්‍රතිශ්‍යාව', 'දියවැඩියාව', 'අධි රුධිර පීඩනය', 'පිළිකාව'], 'a'],
    ['සෙම්ප්‍රතිශ්‍යාව බෝ විය හැක්කේ,', ['කිවිසුම් හා කැස්සෙන් පිටවන බිඳිති මඟින්', 'ව්‍යායාම කිරීමෙන්', 'පොත් කියවීමෙන්', 'නින්දෙන්'], 'a'],
    ['සෙම්ප්‍රතිශ්‍යාව වැනි රෝග පැතිරීම අවම කිරීමට,', ['කිවිසුම් යන විට මුඛය හා නාසය ආවරණය කළ යුතුය', 'අත් නොසේදිය යුතුය', 'කවුළු වසා තැබිය යුතුය', 'රෝගී තත්ත්වයේ සමාජයේ වැඩිපුර ගැවසිය යුතුය'], 'a'],
    ['පාචනය හා උණසන්නිපාතය බෝ වීමට ප්‍රධාන මාර්ගයක් වන්නේ,', ['අපිරිසිදු ජලය හා ආහාර', 'ව්‍යායාම', 'හිරු එළිය', 'පිරිසිදු වාතය'], 'a'],
    ['පාචනය වැනි රෝග වැළැක්වීමට සුදුසු ක්‍රියාවක් වන්නේ,', ['උතුරවා නිවාගත් ජලය පානය කිරීම', 'අපිරිසිදු ජලය පානය කිරීම', 'ආහාර විවෘතව තැබීම', 'අත් නොසේදීම'], 'a'],
    ['ඩෙංගු රෝගය බෝ කරන්නේ,', ['රෝග වාහක මදුරුවෙකු', 'මැස්සෙකු', 'බළලෙකු', 'පක්ෂියෙකු'], 'a'],
    ['ඩෙංගු වැළැක්වීමට වඩාත් සුදුසු ක්‍රියාව වන්නේ,', ['මදුරුවන් බෝවන ස්ථාන විනාශ කිරීම', 'ජලය එකතු වන භාජන තබා ගැනීම', 'පරිසරය අපිරිසිදු කිරීම', 'දොර ජනෙල් සෑමවිටම විවෘතව තැබීම'], 'a'],
    ['පහත කවරක් බෝ නොවන රෝගයකි?', ['දියවැඩියාව', 'සෙම්ප්‍රතිශ්‍යාව', 'ක්ෂය රෝගය', 'උණසන්නිපාතය'], 'a'],
    ['පහත කවරක් බෝ නොවන රෝගයක් නොවේද?', ['අධි රුධිර පීඩනය', 'හෘද රෝග', 'පිළිකාව', 'සෙම්ප්‍රතිශ්‍යාව'], 'd'],
    ['බෝ නොවන රෝග ඇතිවීමේ අවදානම වැඩි කරන ආහාර පුරුද්දක් වන්නේ,', ['ලුණු, සීනි හා මේද අධික ආහාර ගැනීම', 'එළවළු හා පලතුරු ගැනීම', 'සමබල ආහාර ගැනීම', 'පිරිසිදු ආහාර ගැනීම'], 'a'],
    ['බෝ නොවන රෝග ඇතිවීමේ අවදානම වැඩි කරන ජීවන රටාවක් වන්නේ,', ['අක්‍රිය ජීවිතයක් ගත කිරීම', 'දිනපතා ව්‍යායාම කිරීම', 'ක්‍රීඩා කිරීම', 'ඇවිදීම'], 'a'],
    ['පහත කවරක් බෝ නොවන රෝග ඇතිවීමේ අවදානම් සාධකයකි?', ['මානසික ආතතිය', 'ප්‍රමාණවත් විවේකය', 'සෞඛ්‍යවත් ආහාර', 'ව්‍යායාම'], 'a'],
    ['පෞද්ගලික ස්වස්ථතාව යනු,', ['පෞද්ගලික පිරිසිදුකම රැක ගැනීම', 'පරිසරය පමණක් පිරිසිදු කිරීම', 'නිවස පමණක් අලංකාර කිරීම', 'ආහාර පමණක් පිසීම'], 'a'],
    ['පෞද්ගලික ස්වස්ථතාව රැකගැනීමට කළ යුතු දෙයක් වන්නේ,', ['දිනපතා ස්නානය කිරීම', 'අපිරිසිදු ඇඳුම් ඇඳීම', 'නිය දිගට තබාගැනීම', 'අත් නොසේදීම'], 'a'],
    ['ආහාර ගැනීමට පෙර කළ යුත්තේ,', ['සබන් යොදා අත් සේදීම', 'අත් නොසේදීම', 'දුවන්නට යාම', 'නිදාගැනීම'], 'a'],
    ['පරිසර පවිත්‍රතාව රැකීමෙන්,', ['රෝග වාහක සතුන් බෝවීම අඩු කළ හැක', 'මදුරුවන් වැඩි කළ හැක', 'රෝග පැතිරීම වැඩි කළ හැක', 'අපද්‍රව්‍ය වැඩි කළ හැක'], 'a'],
    ['පරිසර පවිත්‍රතාව සඳහා කළ යුතු දෙයක් වන්නේ,', ['කසළ නිසි ලෙස බැහැර කිරීම', 'කසළ තැන තැන දැමීම', 'කාණු අවහිර කිරීම', 'ජලය රැඳෙන භාජන තැබීම'], 'a'],
    ['ප්‍රතිශක්තිකරණය මඟින්,', ['ඇතැම් රෝග වැළැක්වීමට ශරීරයට ආරක්ෂාව ලබා දේ', 'රෝග වැඩි කරයි', 'ශරීරය දුර්වල කරයි', 'සෑම රෝගයක්ම වහා සුව කරයි'], 'a'],
    ['බෝ නොවන රෝග වැළැක්වීමට වැදගත් වන්නේ,', ['නිවැරදි ජීවන රටාවක්', 'අක්‍රිය ජීවිතයක්', 'දුම්වැටි භාවිතය', 'අධික මේද සහිත ආහාර'], 'a'],
    ['සෞඛ්‍යවත් ජීවන රටාවකට අයත් දෙයක් වන්නේ,', ['ක්‍රියාකාරී ජීවිතයක් හා ව්‍යායාම', 'නින්ද අඩු කිරීම', 'මත්ද්‍රව්‍ය භාවිතය', 'දවස පුරා වාඩි වී සිටීම'], 'a'],
    ['රෝග වැළඳීම නිසා ඇති විය හැකි අහිතකර තත්ත්වයක් වන්නේ,', ['අධ්‍යාපන කටයුතුවලට බාධා වීම', 'ශරීර ශක්තිය අනිවාර්යයෙන් වැඩි වීම', 'ආදායම වැඩි වීම', 'ක්‍රීඩා කුසලතාව වැඩි වීම'], 'a'],
    ['පහත කවරක් එදිනෙදා ජීවිතයේ අභියෝගයක් ලෙස පාඩමේ සඳහන් වේද?', ['අනතුරු', 'ආපදා', 'අපචාර හා අපයෝජන', 'ඉහත සියල්ලම'], 'd'],
    ['මාර්ග අනතුරු වැළැක්වීමට කළ යුතු දෙයක් වන්නේ,', ['මාර්ග නීති පිළිපැදීම', 'බීමත්ව රිය පැදවීම', 'පාරේ ඕනෑම ස්ථානයකින් මාරු වීම', 'ගෙවී ගිය ටයර් සහිත වාහන භාවිත කිරීම'], 'a'],
    ['පදිකයන් පාරේ ගමන් කළ යුත්තේ,', ['දකුණු පැත්තෙන්', 'වම් පැත්තෙන් පමණක්', 'පාර මැදින්', 'ඕනෑම පැත්තකින්'], 'a'],
    ['ස්වභාවික ආපදාවකට උදාහරණයක් වන්නේ,', ['ගංවතුර', 'නායයෑම්', 'සුනාමි', 'ඉහත සියල්ලම'], 'd'],
    ['ආපදා අවස්ථාවකදී කළ යුතු වැදගත් දෙයක් වන්නේ,', ['කලබල නොවී ආරක්ෂිත ස්ථානයකට යාම', 'අනතුරුදායක ස්ථානයේම සිටීම', 'තොරතුරු නොසලකා හැරීම', 'තුවාලකරුවන් අත්හැර දැමීම'], 'a'],
    ['නොහඳුනන පුද්ගලයෙකු පාසලෙන් වෙනත් ස්ථානයකට රැගෙන යාමට උත්සාහ කළහොත්,', ['එය ප්‍රතික්ෂේප කර වැඩිහිටියෙකුට දැනුම් දිය යුතුය', 'ඔහු සමඟ වහාම යා යුතුය', 'කිසිවෙකුට නොකියා සිටිය යුතුය', 'පාසලෙන් සැඟවී පිටවිය යුතුය'], 'a'],
    ['ජීවිතයේ අභියෝගවලට සාර්ථකව මුහුණ දීමට වැදගත් වන්නේ,', ['ජීවන නිපුණතා වර්ධනය කර ගැනීම', 'ගැටලු මඟහැරීම', 'අන් අයට දොස් පැවරීම', 'බියෙන් කටයුතු කිරීම'], 'a'],
];

if (count($raw) !== 30) throw new RuntimeException('Grade 6 Health lesson 10 must contain exactly 30 questions.');

$db = db();
$db->begin_transaction();
try {
    $lesson = $db->query(
        "SELECT l.id FROM lessons l JOIN grades g ON g.id=l.grade_id JOIN subjects s ON s.id=l.subject_id
         WHERE g.grade_number=6 AND s.name_en='Health and Physical Education' AND l.medium='Sinhala'
           AND l.display_order=10 AND l.status='active' LIMIT 1"
    )->fetch_assoc();
    if (!$lesson) throw new RuntimeException('Grade 6 Sinhala Health lesson 10 was not found.');
    $lessonId = (int) $lesson['id'];
    $quizQuery = $db->prepare("SELECT id FROM quizzes WHERE lesson_id=? AND status='active' LIMIT 1");
    $quizQuery->bind_param('i', $lessonId);
    $quizQuery->execute();
    $quiz = $quizQuery->get_result()->fetch_assoc();
    if (!$quiz) throw new RuntimeException('Grade 6 Sinhala Health lesson 10 quiz was not found.');
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
    $title = '10 වන පාඩම — MCQ ප්‍රශ්න 30';
    $update = $db->prepare('UPDATE quizzes SET title_si=?,timer_minutes=30,pass_mark=50 WHERE id=?');
    $update->bind_param('si', $title, $quizId);
    $update->execute();
    $verify = $db->prepare("SELECT COUNT(*) total FROM quiz_questions WHERE quiz_id=? AND activity_type='challenge' AND status='active'");
    $verify->bind_param('i', $quizId);
    $verify->execute();
    $count = (int) $verify->get_result()->fetch_assoc()['total'];
    if ($count !== 30) throw new RuntimeException("Grade 6 Health lesson 10 verification failed: $count questions found.");
    $db->commit();
    echo "Grade 6 Health lesson 10 replaced and verified with 30 exact MCQs.\n";
} catch (Throwable $error) {
    $db->rollback();
    throw $error;
}
