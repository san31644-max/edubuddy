<?php
declare(strict_types=1);

ini_set('session.save_path', __DIR__ . '/../includes/runtime');
require_once __DIR__ . '/../includes/db.php';

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("Run this importer from the command line.\n");
}

$guides = [
    1 => ['Hello', 'Meeting and introducing classmates on the first day of school.', ['Use greetings and introduce yourself confidently.', 'Ask and answer questions with am, is and are.', 'Use polite expressions such as Thank you and You are welcome.', 'Write and speak about personal details, hobbies and ambitions.', 'Recognize rhyming words and similar vowel sounds.'], ['greetings', 'introductions', 'am/is/are', 'polite expressions', 'rhyming words']],
    2 => ['Leisure', 'Talking about leisure activities and actions happening now.', ['Use the present continuous tense for actions happening now.', 'Describe activities at home, in a park and at a sports meet.', 'Ask questions with what, where, who and why.', 'Use suitable expressions in a library, classroom and sports event.', 'Distinguish the short /i/ and long /i:/ vowel sounds.'], ['present continuous', 'leisure', 'question words', 'public expressions', 'vowel sounds']],
    3 => ["Where's Everything?", 'Finding places, objects and directions.', ['Use prepositions such as on, in, near, next to and between.', 'Give and follow simple directions to places.', 'Use there is and there are to describe surroundings.', 'Ask and answer questions about the location of objects.', 'Follow spoken instructions and complete practical making activities.'], ['prepositions', 'directions', 'there is/are', 'locations', 'instructions']],
    4 => ['What Can You See?', 'Observing and describing people, animals, objects and places.', ['Use suitable vocabulary to describe what you can see.', 'Build correct descriptive sentences from pictures and texts.', 'Identify details by reading, listening and comparing pictures.', 'Use singular and plural forms appropriately in descriptions.', 'Develop speaking and writing through observation activities.'], ['description', 'observation', 'vocabulary', 'singular/plural', 'picture reading']],
    5 => ["Aunt Minoli's Kitchen", 'Food, healthy eating and language used in the kitchen.', ['Read and discuss texts about healthy food choices.', 'Use food and kitchen vocabulary accurately.', 'Follow and write simple instructions and recipes.', 'Practise speaking, listening and writing through food activities.', 'Understand how good food habits support health.'], ['healthy food', 'kitchen', 'instructions', 'recipes', 'health']],
    6 => ['What We Do', 'Talking about occupations, routines and practical tasks.', ['Describe what people do in daily life and at work.', 'Use suitable verbs and sentence patterns for routines and actions.', 'Read, listen, speak and write about jobs and activities.', 'Follow sequenced instructions to make a lion mask.', 'Organize information and present it clearly.'], ['occupations', 'routines', 'verbs', 'sequencing', 'lion mask']],
    7 => ['A Fine Day', 'Weather, daily events and possibilities.', ['Read and talk about a fine day and changing weather.', 'Use can and may for ability, permission and possibility.', 'Describe events using suitable vocabulary and sentence patterns.', 'Develop comprehension through listening and reading activities.', 'Enjoy and respond to the poem Rain.'], ['weather', 'can/may', 'possibility', 'comprehension', 'Rain']],
    8 => ['A Visit to the Zoo', 'Zoo animals, permission, vowels and opposites.', ['Read and act out language used during a visit to the zoo.', 'Use can and may to ask for permission.', 'Recognize ordinal numbers and use them correctly.', 'Identify vowel sounds and group words by sound.', 'Learn antonyms and respond to The Little Red Hen.'], ['zoo animals', 'permission', 'ordinal numbers', 'vowels', 'antonyms']],
    9 => ['Sugar or Sand', 'Stories, pronouns, contractions, adverbs and gender words.', ['Understand and sequence the story about Andare and the sugar.', 'Use subject and object pronouns correctly.', 'Form and use common contractions.', 'Use adverbs to explain how an action is done.', 'Identify masculine, feminine and neuter words.'], ['Andare', 'pronouns', 'contractions', 'adverbs', 'gender words']],
    10 => ['Was It Yesterday?', 'Talking and writing about completed actions in the past.', ['Use regular and irregular verbs in the simple past tense.', 'Form past-tense statements, negatives and questions.', 'Read a diary and describe what happened yesterday.', 'Recognize descriptive words and enjoy the poem Gorilla.', 'Practise words containing the /p/ and /f/ sounds.'], ['simple past', 'irregular verbs', 'diary', 'Gorilla', '/p/ and /f/']],
    11 => ['Eco Friends', 'Caring for nature and expressing responsible choices.', ['Describe environmentally friendly and harmful actions.', 'Use should and should not when giving advice.', 'Read and write about hobbies, gardens and useful activities.', 'Build vocabulary through comprehension, games and riddles.', 'Plan personal actions that protect parks and the environment.'], ['environment', 'should/should not', 'hobbies', 'gardening', 'eco-friendly actions']],
];

function lesson_payload(int $number): array
{
    $file = __DIR__ . '/../uploads/syllabus/textbook-cache/grade-6/en/english/lesson-' . $number . '.json';
    if (!is_file($file)) throw new RuntimeException("Missing Grade 6 English cache for lesson $number.");
    return json_decode((string) file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
}

function lesson_text(array $payload): string
{
    $pages = [];
    foreach ($payload['chunks'] ?? [] as $chunk) {
        $text = preg_replace('/^\s*\d+\s*$/m', '', (string) ($chunk['text'] ?? '')) ?? '';
        $text = preg_replace('/\n{3,}/', "\n\n", trim($text)) ?? trim($text);
        if ($text !== '') $pages[] = $text;
    }
    return implode("\n\n", $pages);
}

$db = db();
$gradeRow = $db->query('SELECT id FROM grades WHERE grade_number=6 LIMIT 1')->fetch_assoc();
if (!$gradeRow) throw new RuntimeException('Grade 6 is missing.');
$gradeId = (int) $gradeRow['id'];
$subjectStatement = $db->prepare("SELECT id FROM subjects WHERE grade_id=? AND name_en='English' AND status='active' LIMIT 1");
$subjectStatement->bind_param('i', $gradeId);
$subjectStatement->execute();
$subjectRow = $subjectStatement->get_result()->fetch_assoc();
if (!$subjectRow) throw new RuntimeException('Grade 6 English subject is missing.');
$subjectId = (int) $subjectRow['id'];

$findLesson = $db->prepare("SELECT id,unit_id FROM lessons WHERE grade_id=? AND subject_id=? AND medium IN ('English','All') AND display_order=? ORDER BY (medium='English') DESC,id LIMIT 1");
$updateUnit = $db->prepare("UPDATE units SET name_en=?,description_en=?,display_order=?,status='active' WHERE id=?");
$updateLesson = $db->prepare("UPDATE lessons SET medium='English',content_source='textbook',title_en=?,short_description_en=?,content_en=?,short_notes_en=?,learning_objectives_en=?,key_terms_en=?,examples_en=?,summary_en=?,display_order=?,status='active' WHERE id=?");
$findQuiz = $db->prepare('SELECT id FROM quizzes WHERE lesson_id=? ORDER BY id LIMIT 1');
$addQuiz = $db->prepare("INSERT INTO quizzes(grade_id,subject_id,unit_id,lesson_id,title_en,timer_minutes,pass_mark,status) VALUES(?,?,?,?,?,15,50,'active')");
$updateQuiz = $db->prepare("UPDATE quizzes SET title_en=?,timer_minutes=15,pass_mark=50,status='active' WHERE id=?");
$oldIds = $db->prepare('SELECT id FROM quiz_questions WHERE quiz_id=?');
$deleteAnswers = $db->prepare('DELETE FROM quiz_answers WHERE question_id=?');
$deleteQuestions = $db->prepare('DELETE FROM quiz_questions WHERE quiz_id=?');
$addQuestion = $db->prepare("INSERT INTO quiz_questions(quiz_id,activity_type,question_en,option_a_en,option_b_en,option_c_en,option_d_en,correct_option,explanation_en,display_order,status) VALUES(?,?,?,?,?,?,?,?,?,?,'active')");
$sourcePdf = $db->prepare("INSERT INTO lesson_source_pdfs(lesson_id,local_file,start_page) VALUES(?,?,?) ON DUPLICATE KEY UPDATE local_file=VALUES(local_file),start_page=VALUES(start_page)");

$db->begin_transaction();
try {
    foreach ($guides as $number => [$title, $overview, $points, $terms]) {
        $payload = lesson_payload($number);
        $fullContent = lesson_text($payload);
        if (mb_strlen($fullContent) < 2500) throw new RuntimeException("Lesson $number extraction is incomplete.");
        $firstPage = (int) ($payload['chunks'][0]['page'] ?? 1);

        $notes = "STUDY NOTES\n\nLesson: $title\n\nWHAT YOU LEARN\n";
        foreach ($points as $point) $notes .= "• $point\n";
        $notes .= "\nIMPORTANT TERMS\n";
        foreach ($terms as $term) $notes .= '• ' . ucfirst($term) . "\n";
        $notes .= "\nTEXTBOOK PRACTICE\n• Complete the reading, role-play, speaking, listening and writing activities shown in this unit.\n• Check your answers against the language examples and learning points in the full lesson.\n• Use the three Practice Lab activities to revise this unit.\n";
        $objectives = implode("\n", array_map(fn(string $point): string => '• ' . $point, $points));
        $summary = $overview . "\n" . implode("\n", array_map(fn(string $point): string => '• ' . $point, $points));
        $examples = "The Full Lesson tab contains the official role plays, reading passages, grammar examples and numbered activities from the pupil's book.";
        $termText = implode(', ', $terms);

        $findLesson->bind_param('iii', $gradeId, $subjectId, $number);
        $findLesson->execute();
        $lessonRow = $findLesson->get_result()->fetch_assoc();
        if (!$lessonRow) throw new RuntimeException("Existing Grade 6 English lesson $number is missing.");
        $lessonId = (int) $lessonRow['id'];
        $unitId = (int) $lessonRow['unit_id'];
        $updateUnit->bind_param('ssii', $title, $overview, $number, $unitId);
        $updateUnit->execute();
        $updateLesson->bind_param('ssssssssii', $title, $overview, $fullContent, $notes, $objectives, $termText, $examples, $summary, $number, $lessonId);
        $updateLesson->execute();

        $findQuiz->bind_param('i', $lessonId);
        $findQuiz->execute();
        $quizRow = $findQuiz->get_result()->fetch_assoc();
        $quizTitle = $title . ' – Textbook Practice';
        if ($quizRow) {
            $quizId = (int) $quizRow['id'];
            $updateQuiz->bind_param('si', $quizTitle, $quizId);
            $updateQuiz->execute();
        } else {
            $addQuiz->bind_param('iiiis', $gradeId, $subjectId, $unitId, $lessonId, $quizTitle);
            $addQuiz->execute();
            $quizId = (int) $db->insert_id;
        }

        $oldIds->bind_param('i', $quizId);
        $oldIds->execute();
        foreach ($oldIds->get_result() as $old) {
            $questionId = (int) $old['id'];
            $deleteAnswers->bind_param('i', $questionId);
            $deleteAnswers->execute();
        }
        $deleteQuestions->bind_param('i', $quizId);
        $deleteQuestions->execute();

        foreach ($points as $index => $point) {
            $other = [];
            for ($offset = 1; count($other) < 3; $offset++) {
                $otherLesson = (($number - 1 + $offset) % count($guides)) + 1;
                $other[] = $guides[$otherLesson][2][$index];
            }
            $options = array_merge([$point], $other);
            $correctIndex = abs(crc32($title . $index)) % 4;
            [$options[0], $options[$correctIndex]] = [$options[$correctIndex], $options[0]];
            $correct = ['a', 'b', 'c', 'd'][$correctIndex];
            foreach (['challenge', 'missing', 'matching'] as $typeIndex => $type) {
                $question = match ($type) {
                    'challenge' => 'Which learning point belongs to Unit ' . $number . ', “' . $title . '”?',
                    'missing' => 'Choose the correct study point to complete your notes for “' . $title . '”.',
                    default => 'Match “' . $title . '” with the correct textbook learning point.',
                };
                $position = ($index + 1) + ($typeIndex * 5);
                $addQuestion->bind_param('issssssssi', $quizId, $type, $question, $options[0], $options[1], $options[2], $options[3], $correct, $point, $position);
                $addQuestion->execute();
            }
        }

        $pdfPath = 'uploads/syllabus/GRADE 6 SINHALA MEDIUM/english PB G-6.pdf';
        $sourcePdf->bind_param('isi', $lessonId, $pdfPath, $firstPage);
        $sourcePdf->execute();
        echo "English lesson $number: full content, notes and 15 activities updated.\n";
    }
    $db->commit();
} catch (Throwable $error) {
    $db->rollback();
    fwrite(STDERR, $error->getMessage() . "\n");
    exit(1);
}
