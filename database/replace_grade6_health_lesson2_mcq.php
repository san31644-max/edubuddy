<?php
declare(strict_types=1);

ini_set('session.save_path', __DIR__ . '/../includes/runtime');
require_once __DIR__ . '/../includes/db.php';

$raw = [
    ['මිනිසාට ජීවත් වීම සඳහා නැතිවම බැරි සාධක හඳුන්වන්නේ කුමක් ලෙස ද?', ['ආශාවන්', 'මූලික අවශ්‍යතා', 'විනෝදාංශ', 'සමාජ අවශ්‍යතා'], 'b'],
    ['මිනිසාගේ මූලික අවශ්‍යතාවක් වන්නේ කුමක් ද?', ['වාතය', 'රූපවාහිනිය', 'ජංගම දුරකථනය', 'මෝටර් රථය'], 'a'],
    ['මූලික අවශ්‍යතා තුන වන්නේ,', ['නිවාස, ඇඳුම්, අධ්‍යාපනය', 'වාතය, ජලය, ආහාර', 'ආදරය, විවේකය, නින්ද', 'වාහන, නිවාස, උපකරණ'], 'b'],
    ['මිනිසාට ශ්වසනය සඳහා අත්‍යවශ්‍ය වන්නේ,', ['ආහාර', 'ජලය', 'වාතය', 'ඇඳුම්'], 'c'],
    ['පිපාසය නිවා ගැනීමට අවශ්‍ය මූලික සාධකය කුමක් ද?', ['ජලය', 'වාතය', 'නිවාස', 'ඇඳුම්'], 'a'],
    ['කුසගින්න නිවා ගැනීමට අවශ්‍ය වන්නේ,', ['ජලය', 'ආහාර', 'නිවාස', 'අධ්‍යාපනය'], 'b'],
    ['මූලික අවශ්‍යතාවලින් එකක්වත් නොමැතිව මිනිසාට,', ['පහසුවෙන් ජීවත් විය හැක', 'ජීවත් විය නොහැක', 'වඩා සතුටින් සිටිය හැක', 'වැඩිපුර ක්‍රීඩා කළ හැක'], 'b'],
    ['සමාජය සංකීර්ණ වීමත් සමඟ මූලික අවශ්‍යතාවලට අමතරව ඇති වූයේ,', ['වෙනත් අවශ්‍යතා', 'වාතය', 'ජලය', 'ආහාර පමණි'], 'a'],
    ['පහත සඳහන් දේවලින් භෞතික අවශ්‍යතාවක් වන්නේ,', ['ආදරය', 'ආරක්ෂාව', 'නිවාස', 'විවේකය'], 'c'],
    ['වෙනත් භෞතික අවශ්‍යතාවකට උදාහරණයක් වන්නේ,', ['ඇඳුම් පැළඳුම්', 'ආදරය', 'නින්ද', 'විවේකය'], 'a'],
    ['පහත සඳහන් දේවලින් වෙනත් අවශ්‍යතාවක් වන්නේ කුමක් ද?', ['අධ්‍යාපනය', 'වාතය', 'ජලය', 'ආහාර'], 'a'],
    ['මානසික හා සමාජීය අවශ්‍යතාවක් ලෙස පෙළපොතේ සඳහන් වන්නේ,', ['ආදරය', 'වාතය', 'ජලය', 'ආහාර'], 'a'],
    ['පහත සඳහන් දේවලින් මානසික හා සමාජීය අවශ්‍යතාවක් වන්නේ,', ['විවේකය', 'ජලය', 'වාතය', 'ආහාර'], 'a'],
    ['නිවාස, ඇඳුම් පැළඳුම්, උපකරණ හා වාහන වර්ග කළ හැක්කේ,', ['මූලික අවශ්‍යතා ලෙස', 'භෞතික අවශ්‍යතා ලෙස', 'ආශාවන් පමණක් ලෙස', 'ආහාර අවශ්‍යතා ලෙස'], 'b'],
    ['ආරක්ෂාව, ආදරය, අධ්‍යාපනය, ව්‍යායාම, විවේකය හා නින්ද අයත් වන්නේ,', ['මානසික හා සමාජීය අවශ්‍යතාවලට', 'මූලික අවශ්‍යතා තුනට', 'ආහාර වර්ගවලට', 'වාහන අවශ්‍යතාවලට'], 'a'],
    ['“ආශාවන්” යන්නෙන් අදහස් කරන්නේ,', ['ජීවිතයට අත්‍යවශ්‍යම දේ පමණි', 'අත්‍යවශ්‍ය නොවුණත් ලබා ගැනීමට කැමැත්ත ඇති දේ', 'ආහාර පමණි', 'ජලය හා වාතය පමණි'], 'b'],
    ['අපගේ ආශාවන් පිළිබඳ නිවැරදි ප්‍රකාශය කුමක් ද?', ['ඒවාට සීමාවක් ඇත', 'ඒවාට සීමාවක් නැත', 'සියලු ආශාවන් මූලික අවශ්‍යතා වේ', 'සියලු ආශාවන් අත්‍යවශ්‍ය වේ'], 'b'],
    ['ආශාවන් නිසි ලෙස පාලනය කිරීමෙන්,', ['වඩාත් හොඳ ජීවිතයක් ගත කළ හැක', 'මූලික අවශ්‍යතා නැති වේ', 'ආහාර අවශ්‍ය නොවේ', 'ජලය අවශ්‍ය නොවේ'], 'a'],
    ['පෙළපොතේ මකන දෙකේ උදාහරණයෙන් පැහැදිලි කරන්නේ,', ['අවශ්‍යතාවට ගැළපෙන භාණ්ඩය තෝරාගැනීමේ වැදගත්කම', 'මිල වැඩිම භාණ්ඩය සැමවිටම මිලදී ගැනීම', 'සියලු භාණ්ඩ මිලදී ගැනීම', 'මුදල් ඉතිරි නොකිරීම'], 'a'],
    ['රු. 10ක මකනයෙන් අවශ්‍ය කාර්යය කළ හැකි විට රු. 20ක විසිතුරු මකනයක් ගැනීම ප්‍රධාන වශයෙන් සම්බන්ධ වන්නේ,', ['මූලික අවශ්‍යතාවකට', 'ආශාවකට', 'ජල අවශ්‍යතාවකට', 'ආහාර අවශ්‍යතාවකට'], 'b'],
    ['එක් පුද්ගලයෙකුගේ ආශාවක් තවත් පුද්ගලයෙකුගේ කුමක් විය හැකි ද?', ['අවශ්‍යතාවක්', 'ක්‍රීඩාවක්', 'රෝගයක්', 'විනෝදාංශයක් පමණි'], 'a'],
    ['හිම කබායක් ශ්‍රී ලංකාවේ සිටින කෙනෙකුට ආශාවක් විය හැකි නමුත් ඉතා සීතල රටක සිටින කෙනෙකුට එය,', ['අවශ්‍යතාවක් විය හැක', 'කිසිසේත් ප්‍රයෝජනවත් නොවේ', 'ආහාරයක් වේ', 'ව්‍යායාමයක් වේ'], 'a'],
    ['ඇතැම් ආශාවන් පසුපස අධික ලෙස යාමෙන්,', ['සෑමවිටම සෞඛ්‍යය වැඩි වේ', 'හානිදායක ප්‍රතිඵල ඇති විය හැක', 'මුදල් සැමවිටම ඉතිරි වේ', 'සියලු අවශ්‍යතා සපුරා ගත හැක'], 'b'],
    ['ටොෆි හා චොකලට් වැනි පැණිරස ආහාර නිතර ගැනීමෙන් ඇති විය හැකි ගැටලුවක් වන්නේ,', ['දත් දිරා යාම', 'උස වැඩි වීම', 'ඇස් පෙනීම වැඩි වීම', 'ශ්‍රවණය වැඩි වීම'], 'a'],
    ['පැණිරස ආහාර අධිකව ගැනීමෙන් පසුකාලීනව ඇති විය හැකි රෝගයක් ලෙස පාඩමේ සඳහන් වන්නේ,', ['දියවැඩියාව', 'සෙම්ප්‍රතිශ්‍යාව', 'ඇස් රතු වීම', 'කන් කැක්කුම'], 'a'],
    ['අවශ්‍යතා හා ආශාවන් සපුරා ගැනීමේදී ප්‍රමුඛත්වය ලබා දිය යුත්තේ,', ['ආශාවන්ට', 'අවශ්‍යතාවලට', 'මිල අධික භාණ්ඩවලට', 'වෙළෙඳ දැන්වීම්වලට'], 'b'],
    ['අවශ්‍යතා හා ආශාවන් සපුරා ගැනීමේදී සැලකිලිමත් විය යුතු වැදගත් කරුණක් වන්නේ,', ['තම සෞඛ්‍ය තත්ත්වය', 'වෙළෙඳ දැන්වීමේ වර්ණය පමණි', 'මිතුරන් මිලදී ගන්නා දේ පමණි', 'භාණ්ඩයේ හැඩය පමණි'], 'a'],
    ['අවශ්‍යතා හා ආශාවන් සපුරා ගැනීමේදී පවුල සම්බන්ධයෙන් සැලකිලිමත් විය යුත්තේ,', ['පවුලේ ආර්ථික තත්ත්වය', 'නිවසේ වර්ණය', 'මිතුරන්ගේ කැමැත්ත', 'වෙළෙඳ දැන්වීම් පමණි'], 'a'],
    ['අවශ්‍යතා හා ආශාවන් ඉටු කරගැනීමේදී සැලකිල්ලට ගත යුතු කරුණු අතරට අයත් වන්නේ,', ['තම සෞඛ්‍ය තත්ත්වය', 'ආර්ථික තත්ත්වය', 'අන් අයගේ අයිතිවාසිකම්, සමාජ සාරධර්ම හා නීති රීති', 'ඉහත සියල්ලම'], 'd'],
    ['සෞඛ්‍යවත් ජීවිතයක් ගත කිරීම සඳහා කුඩා කල සිටම පුරුදු විය යුත්තේ,', ['සියලු ආශාවන් වහාම ඉටු කරගැනීමට', 'අවශ්‍යතා හා ආශාවන් පාලනය කරගැනීමට', 'මිල අධික භාණ්ඩ පමණක් මිලදී ගැනීමට', 'අවශ්‍යතා නොසලකා හැරීමට'], 'b'],
];

if (count($raw) !== 30) {
    throw new RuntimeException('Grade 6 Health lesson 2 must contain exactly 30 questions.');
}

$db = db();
$db->begin_transaction();
try {
    $lesson = $db->query(
        "SELECT l.id FROM lessons l
         JOIN grades g ON g.id=l.grade_id
         JOIN subjects s ON s.id=l.subject_id
         WHERE g.grade_number=6 AND s.name_en='Health and Physical Education'
           AND l.medium='Sinhala' AND l.display_order=2 AND l.status='active' LIMIT 1"
    )->fetch_assoc();
    if (!$lesson) throw new RuntimeException('Grade 6 Sinhala Health lesson 2 was not found.');
    $lessonId = (int) $lesson['id'];

    $quizQuery = $db->prepare("SELECT id FROM quizzes WHERE lesson_id=? AND status='active' LIMIT 1");
    $quizQuery->bind_param('i', $lessonId);
    $quizQuery->execute();
    $quiz = $quizQuery->get_result()->fetch_assoc();
    if (!$quiz) throw new RuntimeException('Grade 6 Sinhala Health lesson 2 quiz was not found.');
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
         VALUES(?,'challenge',?,?,?,?,?,?,?,?, 'active')"
    );
    foreach ($raw as $index => [$question, $options, $answer]) {
        $order = $index + 1;
        $explanation = 'නිවැරදි පිළිතුර: ' . $options[ord($answer) - ord('a')] . '.';
        $insert->bind_param('isssssssi', $quizId, $question, $options[0], $options[1], $options[2], $options[3], $answer, $explanation, $order);
        $insert->execute();
    }

    $title = '2 වන පාඩම — MCQ ප්‍රශ්න 30';
    $update = $db->prepare('UPDATE quizzes SET title_si=?,timer_minutes=30,pass_mark=50 WHERE id=?');
    $update->bind_param('si', $title, $quizId);
    $update->execute();

    $verify = $db->prepare("SELECT COUNT(*) total FROM quiz_questions WHERE quiz_id=? AND activity_type='challenge' AND status='active'");
    $verify->bind_param('i', $quizId);
    $verify->execute();
    $count = (int) $verify->get_result()->fetch_assoc()['total'];
    if ($count !== 30) throw new RuntimeException("Grade 6 Health lesson 2 verification failed: $count questions found.");

    $db->commit();
    echo "Grade 6 Health lesson 2 replaced and verified with 30 exact MCQs.\n";
} catch (Throwable $error) {
    $db->rollback();
    throw $error;
}
