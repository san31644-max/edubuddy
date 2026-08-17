<?php
declare(strict_types=1);

ini_set('session.save_path', __DIR__ . '/../includes/runtime');
require_once __DIR__ . '/../includes/db.php';

$raw = [
    ['සෞඛ්‍යවත් බව යනු,', ['රෝග නොමැතිව සිටීම පමණි', 'ශාරීරිකව ශක්තිමත්ව සිටීම පමණි', 'කායික, මානසික, සමාජීය හා ආධ්‍යාත්මික වශයෙන් යහපත්ව සිටීමයි', 'හොඳින් ආහාර ගැනීම පමණි'], 'c'],
    ['සෞඛ්‍යයේ ප්‍රධාන පැතිකඩක් නොවන්නේ,', ['කායික යහපැවැත්ම', 'මානසික යහපැවැත්ම', 'සමාජීය යහපැවැත්ම', 'ආර්ථික යහපැවැත්ම'], 'd'],
    ['කායික යහපැවැත්මට වැදගත් සාධකයක් වන්නේ,', ['පිරිසිදු වාතය', 'වැඩිපුර මුදල් තිබීම', 'මිල අධික ඇඳුම් ඇඳීම', 'වැඩිපුර රූපවාහිනිය නැරඹීම'], 'a'],
    ['වාතය පිරිසිදුව තබා ගැනීමට කළ හැකි ක්‍රියාවක් වන්නේ,', ['පොලිතීන් පිළිස්සීම', 'ගස් වැවීම', 'කසළ පිළිස්සීම', 'වාහනවලින් වැඩිපුර දුම් පිට කිරීම'], 'b'],
    ['වායු දූෂණය අඩු කිරීම සඳහා වැළකිය යුත්තේ,', ['ගස් සිටුවීමෙන්', 'කසළ නිසි ලෙස බැහැර කිරීමෙන්', 'පොලිතීන් පිළිස්සීමෙන්', 'වාහන නිසි ලෙස නඩත්තු කිරීමෙන්'], 'c'],
    ['පිරිසිදු ජලය අවශ්‍ය නොවන කාර්යය වන්නේ,', ['පානය කිරීම', 'ස්නානය කිරීම', 'ඇඳුම් පිරිසිදු කිරීම', 'රූපවාහිනිය නැරඹීම'], 'd'],
    ['පානය සඳහා වඩාත් සුදුසු ජලය වන්නේ,', ['අපිරිසිදු ජලය', 'උතුරවා නිවාගත් ජලය', 'කාණු ජලය', 'ලුණු ජලය'], 'b'],
    ['ජලය පිරිසිදු කරගැනීමට භාවිත කළ හැක්කේ,', ['පෙරණයක්', 'පොලිතීන් බෑගයක්', 'රෙදි සෝදන කුඩු', 'තීන්ත'], 'a'],
    ['පාසල් යන වයසේ දරුවෙකු සාමාන්‍යයෙන් දිනකට පානය කළ යුතු ජල ප්‍රමාණය වන්නේ,', ['ලීටර් 0.5ක් පමණ', 'ලීටර් 1.5–2ක් පමණ', 'ලීටර් 5–6ක් පමණ', 'ලීටර් 10ක් පමණ'], 'b'],
    ['ස්වස්ථතාව (Hygiene) යනු,', ['පෞද්ගලික පිරිසිදුකම රැක ගැනීමයි', 'ක්‍රීඩා කිරීමයි', 'ආහාර පිසීමයි', 'නිදා ගැනීමයි'], 'a'],
    ['WASH සංකල්පයේ “WA” යන්නෙන් අදහස් කරන්නේ,', ['Walking', 'Water', 'Washing', 'Warm'], 'b'],
    ['WASH සංකල්පයේ “S” යන්නෙන් අදහස් කරන්නේ,', ['Safety', 'Sport', 'Sanitation', 'Sleep'], 'c'],
    ['WASH සංකල්පයේ “H” යන්නෙන් අදහස් කරන්නේ,', ['Health', 'Hygiene', 'Happiness', 'Home'], 'b'],
    ['දත් මැදීම සුදුසු වන්නේ,', ['සතියකට වරක්', 'දිනකට අවම වශයෙන් දෙවරක්', 'මාසයකට වරක්', 'ආහාර ගැනීමට පෙර පමණි'], 'b'],
    ['වැසිකිළි යාමෙන් පසු කළ යුතු වැදගත් ක්‍රියාව වන්නේ,', ['අත් සබන් යොදා සේදීම', 'ආහාර ගැනීම', 'ව්‍යායාම කිරීම', 'නිදා ගැනීම'], 'a'],
    ['ආහාර පිළියෙල කිරීමට පෙර කළ යුත්තේ,', ['දෑත් සබන් යොදා සේදීම', 'නිදා ගැනීම', 'ක්‍රීඩා කිරීම', 'රූපවාහිනිය නැරඹීම'], 'a'],
    ['නියපොතු සම්බන්ධයෙන් ඇති හොඳ පුරුද්ද වන්නේ,', ['දිගටම වවා ගැනීම', 'කොටට කපා පිරිසිදුව තබා ගැනීම', 'නියපොතු සැපීම', 'අපිරිසිදුව තබා ගැනීම'], 'b'],
    ['සෞඛ්‍යවත් ආහාර වේලක ලක්ෂණයක් වන්නේ,', ['සියලු පෝෂ්‍ය පදාර්ථ අවශ්‍ය ප්‍රමාණයෙන් තිබීම', 'සීනි පමණක් අඩංගු වීම', 'තෙල් පමණක් අඩංගු වීම', 'රසකැවිලි පමණක් අඩංගු වීම'], 'a'],
    ['සෞඛ්‍යවත් ආහාරයක් තෝරාගැනීමේදී සැලකිලිමත් විය යුතු කරුණක් වන්නේ,', ['පිරිසිදු බව', 'ස්වාභාවික බව', 'නැවුම් බව', 'ඉහත සියල්ලම'], 'd'],
    ['ක්‍රීඩා හා ව්‍යායාම මගින්,', ['අස්ථි හා පේශි ශක්තිමත් වේ', 'ශරීරය දුර්වල වේ', 'ක්‍රීඩා කුසලතා අඩු වේ', 'ශරීර බර අනිවාර්යයෙන් වැඩි වේ'], 'a'],
    ['ක්‍රීඩා හා ව්‍යායාම මගින් නිරෝගී වීමට උපකාර වන පද්ධතියක් වන්නේ,', ['හෘදය හා රුධිර සංසරණ පද්ධතිය', 'නිවසේ ජල පද්ධතිය', 'පාසල් පරිපාලන පද්ධතිය', 'ප්‍රවාහන පද්ධතිය'], 'a'],
    ['දිනකට ව්‍යායාම හෝ ක්‍රීඩා සඳහා යෙදිය යුතු කාලය ලෙස පාඩමේ දක්වා ඇත්තේ,', ['විනාඩි 5–10', 'විනාඩි 10–20', 'විනාඩි 30–60', 'පැය 5–6'], 'c'],
    ['දෛනික ජීවිතයේ ශාරීරික ක්‍රියාකාරීත්වය වැඩි කරගැනීමට සුදුසු ක්‍රියාව වන්නේ,', ['විදුලි සෝපානය වෙනුවට තරප්පු භාවිත කිරීම', 'සෑමවිටම වාහනයක ගමන් කිරීම', 'දවස පුරා වාඩි වී සිටීම', 'ව්‍යායාම නොකිරීම'], 'a'],
    ['ක්‍රීඩා හා ව්‍යායාම මගින් ලැබෙන ප්‍රතිලාභයක් නොවන්නේ,', ['ශරීර බර පාලනය', 'විනෝදය ලැබීම', 'ක්‍රීඩා කුසලතා වර්ධනය', 'ශරීරය අක්‍රිය වීම'], 'd'],
    ['පිරිසිදු වාතය සහිත පරිසරයක ජීවත් වීම වැදගත් වන්නේ,', ['කායික යහපැවැත්මට', 'මුදල් ඉපයීමට', 'විභාග ලකුණු පමණක් වැඩි කිරීමට', 'ඇඳුම් මිලදී ගැනීමට'], 'a'],
    ['සනීපාරක්ෂාවට අදාළ උදාහරණයක් වන්නේ,', ['ප්‍රමාණවත් පිරිසිදු ජලය තිබීම', 'වැඩිපුර රූපවාහිනිය නැරඹීම', 'මිල අධික සපත්තු පැළඳීම', 'දවස පුරා නිදා ගැනීම'], 'a'],
    ['අපද්‍රව්‍ය නිසි ලෙස කළමනාකරණය කිරීම වැදගත් වන්නේ,', ['සනීපාරක්ෂාව පවත්වා ගැනීමට', 'පරිසරය අපිරිසිදු කිරීමට', 'ජලය දූෂණය කිරීමට', 'වාතය දූෂණය කිරීමට'], 'a'],
    ['නිවැරදිව අත් සේදීමේදී භාවිත කළ යුත්තේ,', ['පිරිසිදු ජලය හා සබන්', 'තෙල් හා ජලය', 'තීන්ත හා ජලය', 'වැලි පමණි'], 'a'],
    ['හොඳ කායික යහපැවැත්මක් ඇති පුද්ගලයෙකුගේ ලක්ෂණයක් වන්නේ,', ['හොඳ ශාරීරික යෝග්‍යතාවක් තිබීම', 'නිතරම අක්‍රියව සිටීම', 'පෞද්ගලික පිරිසිදුකම නොසලකා හැරීම', 'ව්‍යායාමවලින් සම්පූර්ණයෙන් වැළකීම'], 'a'],
    ['“සුවෙන් සතුටින් ජීවත් වෙමු” පාඩමෙන් ප්‍රධාන වශයෙන් අවධානය යොමු කරන්නේ,', ['සෞඛ්‍යවත් ජීවිතයක් පවත්වා ගැනීමට අවශ්‍ය යහපුරුදු වර්ධනය කිරීම', 'මුදල් ඉපයීම', 'පරිගණක භාවිතය', 'ව්‍යාපාර ආරම්භ කිරීම'], 'a'],
];

if (count($raw) !== 30) {
    throw new RuntimeException('Grade 6 Health lesson 1 must contain exactly 30 questions.');
}

$db = db();
$db->begin_transaction();

try {
    $lessonStatement = $db->prepare(
        "SELECT l.id
         FROM lessons l
         JOIN grades g ON g.id = l.grade_id
         JOIN subjects s ON s.id = l.subject_id
         WHERE g.grade_number = 6
           AND s.name_en = 'Health and Physical Education'
           AND l.medium = 'Sinhala'
           AND l.display_order = 1
           AND l.status = 'active'
         LIMIT 1"
    );
    $lessonStatement->execute();
    $lesson = $lessonStatement->get_result()->fetch_assoc();
    if (!$lesson) {
        throw new RuntimeException('Grade 6 Sinhala Health lesson 1 was not found.');
    }

    $lessonId = (int) $lesson['id'];
    $quizStatement = $db->prepare("SELECT id FROM quizzes WHERE lesson_id = ? AND status = 'active' LIMIT 1");
    $quizStatement->bind_param('i', $lessonId);
    $quizStatement->execute();
    $quiz = $quizStatement->get_result()->fetch_assoc();
    if (!$quiz) {
        throw new RuntimeException('Grade 6 Sinhala Health lesson 1 quiz was not found.');
    }
    $quizId = (int) $quiz['id'];

    $questionIds = $db->prepare("SELECT id FROM quiz_questions WHERE quiz_id = ? AND activity_type = 'challenge'");
    $questionIds->bind_param('i', $quizId);
    $questionIds->execute();
    $existing = $questionIds->get_result();
    while ($row = $existing->fetch_assoc()) {
        $questionId = (int) $row['id'];
        $db->query("DELETE FROM quiz_answers WHERE question_id = $questionId");
    }

    $deleteQuestions = $db->prepare("DELETE FROM quiz_questions WHERE quiz_id = ? AND activity_type = 'challenge'");
    $deleteQuestions->bind_param('i', $quizId);
    $deleteQuestions->execute();

    $insert = $db->prepare(
        "INSERT INTO quiz_questions
         (quiz_id, activity_type, question_si, option_a_si, option_b_si, option_c_si, option_d_si,
          correct_option, explanation_si, display_order, status)
         VALUES (?, 'challenge', ?, ?, ?, ?, ?, ?, ?, ?, 'active')"
    );
    foreach ($raw as $index => [$question, $options, $answer]) {
        $displayOrder = $index + 1;
        $explanation = 'නිවැරදි පිළිතුර: ' . $options[ord($answer) - ord('a')] . '.';
        $insert->bind_param(
            'isssssssi',
            $quizId,
            $question,
            $options[0],
            $options[1],
            $options[2],
            $options[3],
            $answer,
            $explanation,
            $displayOrder
        );
        $insert->execute();
    }

    $title = '1 වන පාඩම — MCQ ප්‍රශ්න 30';
    $updateQuiz = $db->prepare('UPDATE quizzes SET title_si = ?, timer_minutes = 30, pass_mark = 50 WHERE id = ?');
    $updateQuiz->bind_param('si', $title, $quizId);
    $updateQuiz->execute();

    $verify = $db->prepare(
        "SELECT COUNT(*) AS total
         FROM quiz_questions
         WHERE quiz_id = ? AND activity_type = 'challenge' AND status = 'active'"
    );
    $verify->bind_param('i', $quizId);
    $verify->execute();
    $count = (int) $verify->get_result()->fetch_assoc()['total'];
    if ($count !== 30) {
        throw new RuntimeException("Grade 6 Health lesson 1 verification failed: $count questions found.");
    }

    $db->commit();
    echo "Grade 6 Health lesson 1 replaced and verified with 30 exact MCQs.\n";
} catch (Throwable $error) {
    $db->rollback();
    throw $error;
}
