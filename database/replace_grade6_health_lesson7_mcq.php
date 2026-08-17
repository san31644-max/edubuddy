<?php
declare(strict_types=1);

ini_set('session.save_path', __DIR__ . '/../includes/runtime');
require_once __DIR__ . '/../includes/db.php';

$raw = [
    ['ශරීරයට ආහාර අවශ්‍ය වීමට ප්‍රධාන හේතුවක් වන්නේ කුමක් ද?', ['ශක්තිය ලබා ගැනීමට', 'නින්ද වැඩි කිරීමට', 'උස අඩු කිරීමට', 'පිපාසය වැඩි කිරීමට'], 'a'],
    ['ශරීරයට ශක්තිය ලබාදීමට ප්‍රධාන වශයෙන් උපකාරී වන පෝෂක වන්නේ,', ['කාබෝහයිඩ්‍රේට් හා මේද', 'ජලය පමණි', 'විටමින් පමණි', 'ඛනිජ ලවණ පමණි'], 'a'],
    ['ශරීරයේ වර්ධනයට විශේෂයෙන් වැදගත් පෝෂකය වන්නේ,', ['ප්‍රෝටීන්', 'ජලය', 'ලුණු', 'සීනි'], 'a'],
    ['රෝගවලින් ශරීරය ආරක්ෂා කරගැනීමට විශේෂයෙන් උපකාරී වන්නේ,', ['විටමින් හා ඛනිජ ලවණ', 'සීනි පමණි', 'මේද පමණි', 'පිෂ්ඨය පමණි'], 'a'],
    ['කාබෝහයිඩ්‍රේට් අයත් වන්නේ,', ['මහා පෝෂකවලට', 'ක්ෂුද්‍ර පෝෂකවලට', 'ඛනිජ ලවණවලට', 'විටමින්වලට'], 'a'],
    ['ප්‍රෝටීන් අයත් වන්නේ,', ['මහා පෝෂකවලට', 'විටමින්වලට', 'ඛනිජ ලවණවලට', 'ජලයට'], 'a'],
    ['මේද අයත් වන්නේ,', ['මහා පෝෂකවලට', 'ක්ෂුද්‍ර පෝෂකවලට පමණි', 'විටමින්වලට', 'ඛනිජ ලවණවලට'], 'a'],
    ['ක්ෂුද්‍ර පෝෂක සඳහා උදාහරණ වන්නේ,', ['විටමින් හා ඛනිජ ලවණ', 'ප්‍රෝටීන් හා මේද', 'පිෂ්ඨය හා සීනි', 'ජලය හා මේද'], 'a'],
    ['පහත සඳහන් ඒවායින් විටමිනයක් වන්නේ,', ['විටමින් C', 'කැල්සියම්', 'යකඩ', 'අයඩින්'], 'a'],
    ['පහත සඳහන් ඒවායින් ඛනිජ ලවණයක් වන්නේ,', ['කැල්සියම්', 'විටමින් A', 'විටමින් C', 'විටමින් D'], 'a'],
    ['සියලුම පෝෂක අවශ්‍ය ප්‍රමාණයෙන් ලබාගැනීමට කළ යුත්තේ,', ['විවිධත්වයෙන් යුතු ආහාර ගැනීම', 'එකම ආහාරය දිනපතා ගැනීම', 'ආහාර වේල් මඟහැරීම', 'රසකැවිලි පමණක් ගැනීම'], 'a'],
    ['ආහාර ප්‍රධාන වශයෙන් වර්ග කළ හැකි කාණ්ඩ ගණන වන්නේ,', ['3', '4', '6', '10'], 'c'],
    ['ධාන්‍ය හා අල වර්ග අයත් වන්නේ,', ['ප්‍රධාන ආහාර කාණ්ඩයකට', 'බීම වර්ගවලට පමණි', 'පලතුරු වර්ගවලට', 'කිරි නිෂ්පාදනවලට'], 'a'],
    ['එළවළු ආහාරයට එක්කර ගැනීමෙන් විශේෂයෙන් ලබාගත හැක්කේ,', ['විටමින් හා ඛනිජ ලවණ', 'සීනි පමණි', 'මේද පමණි', 'ලුණු පමණි'], 'a'],
    ['පලතුරු නිතිපතා ආහාරයට ගැනීම,', ['සෞඛ්‍යයට හිතකරය', 'සෑමවිටම අහිතකරය', 'පෝෂණයට අදාළ නොවේ', 'සම්පූර්ණයෙන් වැළකිය යුතුය'], 'a'],
    ['මස්, මාළු, බිත්තර හා පියලි වර්ගවලින් විශේෂයෙන් ලබාගත හැකි පෝෂකය වන්නේ,', ['ප්‍රෝටීන්', 'ජලය පමණි', 'සීනි පමණි', 'ලුණු පමණි'], 'a'],
    ['කිරි හා කිරි නිෂ්පාදන ආහාරයට එක්කර ගැනීමේ වැදගත්කමක් වන්නේ,', ['ශරීරයට අවශ්‍ය පෝෂක ලබාදීම', 'ආහාරයේ පෝෂණ අගය අඩු කිරීම', 'ශරීරය දුර්වල කිරීම', 'ආහාර අවශ්‍යතාව නැති කිරීම'], 'a'],
    ['සමබල ආහාර වේලක් යනු,', ['අවශ්‍ය පෝෂක නිසි ප්‍රමාණවලින් අඩංගු ආහාර වේලකි', 'සීනි පමණක් අඩංගු ආහාර වේලකි', 'මේද පමණක් අඩංගු ආහාර වේලකි', 'එක් ආහාර වර්ගයක් පමණක් අඩංගු වේලකි'], 'a'],
    ['සෞඛ්‍යවත් ආහාරයක් තෝරාගැනීමේදී වැදගත් කරුණක් වන්නේ,', ['නැවුම් බව', 'මිල වැඩි බව පමණි', 'වර්ණවත් ඇසුරුම', 'වෙළෙඳ දැන්වීම'], 'a'],
    ['සෞඛ්‍යවත් ආහාරයක්,', ['පිරිසිදු හා සෞඛ්‍යාරක්ෂිත විය යුතුය', 'අපිරිසිදු විය යුතුය', 'නරක් වූ එකක් විය යුතුය', 'කල් ඉකුත් වූ එකක් විය යුතුය'], 'a'],
    ['ස්වභාවික ආහාර තෝරාගැනීම,', ['යහපත් ආහාර පුරුද්දකි', 'අයහපත් පුරුද්දකි', 'පෝෂණයට අදාළ නොවේ', 'සෑමවිටම වැළකිය යුතුය'], 'a'],
    ['දිනපතා ආහාර වේල් නිසි වේලාවට ගැනීම,', ['යහපත් ආහාර පුරුද්දකි', 'අයහපත් පුරුද්දකි', 'සෞඛ්‍යයට කිසිදු සම්බන්ධයක් නැත', 'රෝග ඇති කරන පුරුද්දකි'], 'a'],
    ['පාසල් දරුවෙකුට වැදගත් ආහාර වේලක් වන්නේ,', ['උදෑසන ආහාරය', 'රසකැවිලි වේල', 'සිසිල් බීම වේල', 'අයිස්ක්‍රීම් වේල'], 'a'],
    ['අධික සීනි සහිත ආහාර නිතර ගැනීම,', ['සීමා කළ යුතුය', 'හැකි තරම් වැඩි කළ යුතුය', 'සෑම ආහාර වේලකටම අත්‍යවශ්‍යය', 'ජලය වෙනුවට භාවිත කළ යුතුය'], 'a'],
    ['අධික ලුණු සහිත ආහාර ගැනීම සම්බන්ධයෙන් සුදුසු පුරුද්ද වන්නේ,', ['සීමා කිරීම', 'වැඩිපුර ගැනීම', 'සෑම ආහාරයකටම වැඩිපුර ලුණු දැමීම', 'ලුණු පමණක් ආහාරයට ගැනීම'], 'a'],
    ['අධික මේද සහිත ආහාර සම්බන්ධයෙන් සුදුසු ක්‍රියාව වන්නේ,', ['සීමා කර ගැනීම', 'දිනපතා විශාල ප්‍රමාණයෙන් ගැනීම', 'අනෙක් ආහාර වෙනුවට ඒවා පමණක් ගැනීම', 'සෑම වේලකටම ඒවා ගැනීම'], 'a'],
    ['සෞඛ්‍ය සම්පන්න සුළු ආහාරයක් ලෙස වඩාත් සුදුසු වන්නේ,', ['නැවුම් පලතුරක්', 'අධික සීනි සහිත රසකැවිල්ලක්', 'අධික ලුණු සහිත ආහාරයක්', 'අධික තෙල් සහිත ආහාරයක්'], 'a'],
    ['ආහාර ගැනීමට පෙර කළ යුතු වැදගත් සෞඛ්‍ය පුරුද්ද වන්නේ,', ['සබන් යොදා දෑත් සේදීම', 'දෑත් අපිරිසිදුව තබාගැනීම', 'ආහාර බිම තැබීම', 'ආහාර විවෘතව තැබීම'], 'a'],
    ['නිවැරදි ආහාර පුරුදු ඇති කරගැනීමෙන්,', ['සෞඛ්‍යවත් ජීවිතයක් ගත කිරීමට උපකාරී වේ', 'සෑමවිටම රෝග වැඩි වේ', 'ශරීර වර්ධනය නතර වේ', 'ශරීරයට ශක්තිය නොලැබේ'], 'a'],
    ['7 වන පාඩමේ ප්‍රධාන අදහස වඩාත් හොඳින් දැක්වෙන්නේ,', ['නිවැරදි හා සෞඛ්‍යවත් ආහාර පුරුදු ඇති කරගෙන නිරෝගී ජීවිතයක් ගත කිරීම', 'එක් ආහාර වර්ගයක් පමණක් ගැනීම', 'ආහාර වේල් මඟහැරීම', 'රසකැවිලි හා සිසිල් බීම පමණක් ගැනීම'], 'a'],
];

if (count($raw) !== 30) throw new RuntimeException('Grade 6 Health lesson 7 must contain exactly 30 questions.');

$db = db();
$db->begin_transaction();
try {
    $lesson = $db->query(
        "SELECT l.id FROM lessons l JOIN grades g ON g.id=l.grade_id JOIN subjects s ON s.id=l.subject_id
         WHERE g.grade_number=6 AND s.name_en='Health and Physical Education' AND l.medium='Sinhala'
           AND l.display_order=7 AND l.status='active' LIMIT 1"
    )->fetch_assoc();
    if (!$lesson) throw new RuntimeException('Grade 6 Sinhala Health lesson 7 was not found.');
    $lessonId = (int) $lesson['id'];
    $quizQuery = $db->prepare("SELECT id FROM quizzes WHERE lesson_id=? AND status='active' LIMIT 1");
    $quizQuery->bind_param('i', $lessonId);
    $quizQuery->execute();
    $quiz = $quizQuery->get_result()->fetch_assoc();
    if (!$quiz) throw new RuntimeException('Grade 6 Sinhala Health lesson 7 quiz was not found.');
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
    $title = '7 වන පාඩම — MCQ ප්‍රශ්න 30';
    $update = $db->prepare('UPDATE quizzes SET title_si=?,timer_minutes=30,pass_mark=50 WHERE id=?');
    $update->bind_param('si', $title, $quizId);
    $update->execute();
    $verify = $db->prepare("SELECT COUNT(*) total FROM quiz_questions WHERE quiz_id=? AND activity_type='challenge' AND status='active'");
    $verify->bind_param('i', $quizId);
    $verify->execute();
    $count = (int) $verify->get_result()->fetch_assoc()['total'];
    if ($count !== 30) throw new RuntimeException("Grade 6 Health lesson 7 verification failed: $count questions found.");
    $db->commit();
    echo "Grade 6 Health lesson 7 replaced and verified with 30 exact MCQs.\n";
} catch (Throwable $error) {
    $db->rollback();
    throw $error;
}
