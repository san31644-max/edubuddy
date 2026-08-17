<?php
declare(strict_types=1);

ini_set('session.save_path', __DIR__ . '/../includes/runtime');
require_once __DIR__ . '/../includes/db.php';

$raw = [
    ['සුළු ක්‍රීඩා කිරීමෙන් ප්‍රධාන වශයෙන් ලැබෙන්නේ කුමක් ද?', ['විනෝදය හා ශාරීරික ක්‍රියාකාරීත්වය', 'අලසකම', 'නින්ද පමණක්', 'කුසගින්න පමණක්'], 'a'],
    ['සුළු ක්‍රීඩාවල යෙදීමෙන් වර්ධනය කරගත හැක්කේ,', ['ශාරීරික කුසලතා', 'අක්‍රියතාව', 'අලසකම', 'රෝගී බව'], 'a'],
    ['සුළු ක්‍රීඩා කළ හැක්කේ,', ['තනිව පමණි', 'දෙදෙනෙකු පමණි', 'කණ්ඩායමක් සමඟ පමණි', 'තනිව, දෙදෙනෙකු හෝ කණ්ඩායමක් ලෙස'], 'd'],
    ['පෙළපොතේ සඳහන් දෙදෙනෙකු සමඟ කළ හැකි ක්‍රීඩාවක් වන්නේ,', ['කොටු පැනීම', 'ක්‍රිකට්', 'පාපන්දු', 'වොලිබෝල්'], 'a'],
    ['කොටු පැනීමේ ක්‍රීඩාව සඳහා අවශ්‍ය විය හැක්කේ,', ['වියළි අඹ ඇටයක් හෝ පැතලි කුඩා ගල් කැබැල්ලක්', 'ක්‍රිකට් පිත්තක්', 'පාපන්දුවක්', 'දැලක්'], 'a'],
    ['කොටු පැනීමේ ක්‍රීඩාව ආරම්භයේදී ගල් කැබැල්ල දමන්නේ,', ['අංක 1 කොටුවට', 'අංක 3 කොටුවට', 'අංක 5 කොටුවට', 'අංක 6 කොටුවට'], 'a'],
    ['කොටු පැනීමේදී අංක 1 කොටුවට පැනිය යුත්තේ,', ['තනි පාදයෙන්', 'අත් දෙකෙන්', 'දණහිස් දෙකෙන්', 'වාඩි වී'], 'a'],
    ['කොටු පැනීමේදී 1, 2, 3 හා 5 යන කොටුවලට පැනිය යුත්තේ,', ['තනි පාදයෙන්', 'පාද දෙකෙන්', 'අත් දෙකෙන්', 'දණහිස් මත'], 'a'],
    ['කොටු පැනීමේදී 4 හා 6 කොටුවලට පැනිය යුත්තේ,', ['තනි පාදයෙන්', 'පාද දෙකෙන්', 'එක් අතකින්', 'දණහිසෙන්'], 'b'],
    ['කොටු පැනීමේදී ක්‍රීඩාවෙන් ඉවත් වීමට හේතුවක් වන්නේ,', ['රේඛාව පෑගීම', 'නිවැරදිව පැනීම', 'සමබරතාව රැකගැනීම', 'නීති පිළිපැදීම'], 'a'],
    ['ගල් කැබැල්ල කොටුවට දැමීමේදී එය රේඛාව මත වැටුණහොත්,', ['ක්‍රීඩාවෙන් ඉවත් වීමට හේතු වේ', 'අමතර ලකුණු ලැබේ', 'ජයග්‍රහණය ලැබේ', 'කිසිවක් සිදු නොවේ'], 'a'],
    ['පැනීමේදී ශරීරයේ සමබරතාව නැති වී නිදහස් පාදය බිම තැබුවහොත්,', ['ක්‍රීඩාවෙන් ඉවත් වීමට හේතු වේ', 'ජයග්‍රහණය ලැබේ', 'අමතර වාරයක් ලැබේ', 'ලකුණු දෙකක් ලැබේ'], 'a'],
    ['කොටු පැනීමේ ක්‍රීඩාවේ ජයග්‍රාහකයා වන්නේ,', ['මුලින්ම සියලු කොටු පැන අවසන් කරන ක්‍රීඩකයා', 'වැඩිම උස ඇති ක්‍රීඩකයා', 'වැඩිම බර ඇති ක්‍රීඩකයා', 'අවසානයට පැමිණෙන ක්‍රීඩකයා'], 'a'],
    ['කොටු පැනීමේ ක්‍රීඩාවට සහභාගී විය හැක්කේ,', ['දෙදෙනෙකුට පමණි', 'දෙදෙනෙකුට වැඩි ගණනකට ද හැකිය', 'එක් අයෙකුට පමණි', 'ගුරුවරුන්ට පමණි'], 'b'],
    ['කොටු පැනීමෙන් විශේෂයෙන් වර්ධනය වන හැකියාවක් වන්නේ,', ['ශරීර සමබරතාව', 'නිදාගැනීම', 'ලිවීම', 'කියවීම'], 'a'],
    ['පෙළපොතේ සඳහන් තවත් සුළු ක්‍රීඩාවක් වන්නේ,', ['කවුද රජා', 'ටෙනිස්', 'හොකී', 'රග්බි'], 'a'],
    ['“කවුද රජා” ක්‍රීඩාවේ ක්‍රීඩකයන් සිටින්නේ,', ['සලකුණු කළ රවුම් තුළ', 'ගස් මත', 'මේස මත', 'වතුර තුළ'], 'a'],
    ['“කවුද රජා” ක්‍රීඩාවේදී ප්‍රතිවාදියා සීමාවෙන් පිටතට යැවීමට භාවිත කරන්නේ,', ['අත්ල', 'පාදය', 'හිස', 'පන්දුවක්'], 'a'],
    ['“කවුද රජා” ක්‍රීඩාවේ සීමාවෙන් පිටතට යන ක්‍රීඩකයා,', ['පරාජය වේ', 'ජයගනී', 'අමතර ලකුණු ලබයි', 'නායකයා වේ'], 'a'],
    ['“කවුද රජා” ක්‍රීඩාවේ ජයගන්නා ක්‍රීඩකයා හඳුන්වන්නේ,', ['රජා ලෙස', 'විනිසුරු ලෙස', 'පුහුණුකරු ලෙස', 'නායකයා ලෙස පමණි'], 'a'],
    ['කණ්ඩායමක් සමඟ කළ හැකි සුළු ක්‍රීඩාවක් ලෙස පාඩමේ සඳහන් වන්නේ,', ['ගස් මාරු කිරීම', 'පිහිනීම', 'ක්‍රිකට්', 'බැඩ්මින්ටන්'], 'a'],
    ['“ගස් මාරු කිරීම” සඳහා තෝරාගන්නා ගස් හෝ කණු ගණන, කණ්ඩායමේ සිටින සංඛ්‍යාවට වඩා,', ['එකකින් අඩුය', 'එකකින් වැඩිය', 'දෙකකින් වැඩිය', 'සමානය'], 'a'],
    ['කණ්ඩායම් සුළු ක්‍රීඩා කිරීමෙන් වර්ධනය වන ගුණයක් වන්නේ,', ['සහයෝගීතාව', 'ආත්මාර්ථකාමී බව', 'අසමගිය', 'අලසකම'], 'a'],
    ['ක්‍රීඩාවේ නීති පිළිපැදීමෙන් වර්ධනය වන්නේ,', ['විනය', 'අවිනය', 'කෝපය', 'බිය'], 'a'],
    ['කණ්ඩායම් ක්‍රීඩාවකදී අන් අය සමඟ කටයුතු කිරීමෙන් වර්ධනය වන්නේ,', ['කණ්ඩායම් හැඟීම', 'හුදකලා බව', 'අසමගිය', 'අක්‍රියතාව'], 'a'],
    ['සුළු ක්‍රීඩාවලදී ජය හා පරාජය පිළිගැනීමට පුරුදු වීමෙන් වර්ධනය වන්නේ,', ['යහපත් ක්‍රීඩාශීලීත්වය', 'වෛරය', 'අසමගිය', 'අවිනය'], 'a'],
    ['පැනීම වැනි ක්‍රියාකාරකම් මගින් වර්ධනය කරගත හැකි ශාරීරික ගුණයක් වන්නේ,', ['සමබරතාව හා සම්බන්ධීකරණය', 'අලසකම', 'නිදිමත', 'අක්‍රියතාව'], 'a'],
    ['සුළු ක්‍රීඩා කිරීමේදී වැදගත්ම කරුණක් වන්නේ,', ['ක්‍රීඩාවේ නීති පිළිපැදීම', 'නීති නොසලකා හැරීම', 'අනෙක් අයට බාධා කිරීම', 'ජයගැනීමට ඕනෑම දෙයක් කිරීම'], 'a'],
    ['සුළු ක්‍රීඩාවලට සහභාගී වීමෙන් දරුවන්ට,', ['විනෝදය සමඟ ශාරීරික හැකියාවන් වර්ධනය කරගත හැක', 'ශාරීරික ක්‍රියාකාරීත්වය සම්පූර්ණයෙන් අඩු වේ', 'ක්‍රීඩා කුසලතා නැති වේ', 'කණ්ඩායම් හැඟීම අඩු වේ'], 'a'],
    ['4 වන පාඩමේ ප්‍රධාන අදහස වඩාත් හොඳින් දැක්වෙන්නේ කුමක් ද?', ['සුළු ක්‍රීඩාවල සතුටින් හා නීතිගරුකව යෙදෙමින් ශාරීරික හා සමාජීය කුසලතා වර්ධනය කරගැනීම', 'ක්‍රීඩාවලින් සම්පූර්ණයෙන් වැළකීම', 'ජයග්‍රහණය පමණක් වැදගත් ලෙස සැලකීම', 'තනිවම කටයුතු කිරීමට පුරුදු වීම'], 'a'],
];

if (count($raw) !== 30) throw new RuntimeException('Grade 6 Health lesson 4 must contain exactly 30 questions.');

$db = db();
$db->begin_transaction();
try {
    $lesson = $db->query(
        "SELECT l.id FROM lessons l JOIN grades g ON g.id=l.grade_id JOIN subjects s ON s.id=l.subject_id
         WHERE g.grade_number=6 AND s.name_en='Health and Physical Education' AND l.medium='Sinhala'
           AND l.display_order=4 AND l.status='active' LIMIT 1"
    )->fetch_assoc();
    if (!$lesson) throw new RuntimeException('Grade 6 Sinhala Health lesson 4 was not found.');
    $lessonId = (int) $lesson['id'];
    $quizQuery = $db->prepare("SELECT id FROM quizzes WHERE lesson_id=? AND status='active' LIMIT 1");
    $quizQuery->bind_param('i', $lessonId);
    $quizQuery->execute();
    $quiz = $quizQuery->get_result()->fetch_assoc();
    if (!$quiz) throw new RuntimeException('Grade 6 Sinhala Health lesson 4 quiz was not found.');
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
    $title = '4 වන පාඩම — MCQ ප්‍රශ්න 30';
    $update = $db->prepare('UPDATE quizzes SET title_si=?,timer_minutes=30,pass_mark=50 WHERE id=?');
    $update->bind_param('si', $title, $quizId);
    $update->execute();
    $verify = $db->prepare("SELECT COUNT(*) total FROM quiz_questions WHERE quiz_id=? AND activity_type='challenge' AND status='active'");
    $verify->bind_param('i', $quizId);
    $verify->execute();
    $count = (int) $verify->get_result()->fetch_assoc()['total'];
    if ($count !== 30) throw new RuntimeException("Grade 6 Health lesson 4 verification failed: $count questions found.");
    $db->commit();
    echo "Grade 6 Health lesson 4 replaced and verified with 30 exact MCQs.\n";
} catch (Throwable $error) {
    $db->rollback();
    throw $error;
}
