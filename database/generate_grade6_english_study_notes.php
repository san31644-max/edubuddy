<?php
declare(strict_types=1);

ini_set('session.save_path', __DIR__.'/../includes/runtime');
require_once __DIR__.'/../includes/db.php';

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("Run this importer from the command line.\n");
}

function clean_study_point(string $text): string
{
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = str_replace(["\u{00AD}", "\u{FFFD}", 'For free distribution', 'For Free Distribution'], '', $text);
    $text = preg_replace('/^[\s\d.()\-–—]+(?=[A-Z])/u', '', trim($text)) ?? trim($text);
    $text = preg_replace('/\b(?:PB|UNIT)\s*\d*\b/iu', '', $text) ?? $text;
    $text = preg_replace('/\s+/u', ' ', trim($text)) ?? trim($text);
    return trim($text, " \t\n\r\0\x0B-–—");
}

function study_points(array $lesson): array
{
    $source = trim((string)($lesson['content_en'] ?? ''));
    $source = preg_replace('/^\s*SHORT NOTES\s*/iu', '', $source) ?? $source;
    $parts = preg_split('/(?:\r?\n){2,}|\r?\n(?=\s*\d+[.)]\s+)/u', $source) ?: [];
    if (count($parts) < 4) {
        $parts = preg_split('/(?<=[.!?])\s+(?=[A-Z])/u', $source) ?: [];
    }

    $points = [];
    foreach ($parts as $part) {
        $part = preg_replace('/^\s*\d+[.)]\s*/u', '', $part) ?? $part;
        $part = clean_study_point($part);
        if (mb_strlen($part) < 35 || mb_strlen($part) > 520) continue;
        if (preg_match('/^(activity|exercise|question|write |fill |match |draw |discuss |ask your teacher|group work)\b/iu', $part)) continue;
        $key = mb_strtolower(preg_replace('/[^\p{L}\p{N}]+/u', '', $part) ?? $part);
        if ($key === '' || isset($points[$key])) continue;
        $points[$key] = $part;
        if (count($points) === 8) break;
    }

    if (count($points) < 4) {
        $fallback = preg_split('/\r?\n|(?<=[.!?])\s+/u', (string)($lesson['summary_en'] ?? '')) ?: [];
        foreach ($fallback as $part) {
            $part = clean_study_point(ltrim($part, '- '));
            if (mb_strlen($part) < 25) continue;
            $key = mb_strtolower(preg_replace('/[^\p{L}\p{N}]+/u', '', $part) ?? $part);
            if ($key !== '' && !isset($points[$key])) $points[$key] = $part;
            if (count($points) === 8) break;
        }
    }
    return array_values($points);
}

function term_list(string $terms): array
{
    $items = preg_split('/\s*,\s*|\r?\n/u', trim($terms)) ?: [];
    $items = array_values(array_unique(array_filter(array_map(
        fn(string $item): string => trim($item, " \t\n\r\0\x0B-–—"),
        $items
    ), fn(string $item): bool => mb_strlen($item) >= 2)));
    return array_slice($items, 0, 12);
}

$db = db();
$query = $db->query(
    "SELECT l.id,l.display_order,l.title_en,l.content_en,l.summary_en,l.key_terms_en,s.name_en subject_name
     FROM lessons l
     JOIN subjects s ON s.id=l.subject_id
     JOIN grades g ON g.id=l.grade_id
     WHERE g.grade_number=6 AND l.medium='English'
       AND l.content_source='textbook' AND l.status='active'
     ORDER BY s.name_en,l.display_order,l.id"
);
if (!$query) throw new RuntimeException($db->error);

$update = $db->prepare('UPDATE lessons SET short_notes_en=? WHERE id=?');
if (!$update) throw new RuntimeException($db->error);

$updated = 0;
$subjects = [];
$db->begin_transaction();
try {
    while ($lesson = $query->fetch_assoc()) {
        $title = trim((string)$lesson['title_en']);
        $points = study_points($lesson);
        $terms = term_list((string)$lesson['key_terms_en']);

        $notes = "STUDY NOTES\n\n";
        $notes .= "Lesson: {$title}\n";
        $notes .= "Subject: {$lesson['subject_name']}\n\n";
        $notes .= "KEY STUDY POINTS\n";
        foreach ($points as $point) $notes .= "• {$point}\n";
        if ($terms) {
            $notes .= "\nIMPORTANT TERMS\n";
            foreach ($terms as $term) $notes .= "• ".ucfirst($term)."\n";
        }
        $notes .= "\nREVISION CHECKLIST\n";
        $notes .= "✓ Explain the main idea of {$title} in your own words.\n";
        $notes .= "✓ Remember the important facts, rules, examples and vocabulary above.\n";
        $notes .= "✓ Use the textbook activities and lesson quiz to check your understanding.";

        $id = (int)$lesson['id'];
        $update->bind_param('si', $notes, $id);
        $update->execute();
        $updated++;
        $subjects[(string)$lesson['subject_name']] = ($subjects[(string)$lesson['subject_name']] ?? 0) + 1;
    }
    $db->commit();
} catch (Throwable $error) {
    $db->rollback();
    throw $error;
}

echo "Updated {$updated} Grade 6 English study notes.\n";
foreach ($subjects as $subject => $count) echo "- {$subject}: {$count}\n";
