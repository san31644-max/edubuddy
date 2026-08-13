<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') exit;
require_once __DIR__.'/../includes/db.php';

$db = db();
$sql = "UPDATE lessons l
        INNER JOIN grades g ON g.id=l.grade_id
        INNER JOIN subjects s ON s.id=l.subject_id
        SET l.title_si=REPLACE(l.title_si,'කදමින්','කරමින්'),
            l.short_description_si=REPLACE(l.short_description_si,'කදමින්','කරමින්'),
            l.content_si=REPLACE(l.content_si,'කදමින්','කරමින්'),
            l.short_notes_si=REPLACE(l.short_notes_si,'කදමින්','කරමින්'),
            l.learning_objectives_si=REPLACE(l.learning_objectives_si,'කදමින්','කරමින්'),
            l.key_terms_si=REPLACE(l.key_terms_si,'කදමින්','කරමින්'),
            l.examples_si=REPLACE(l.examples_si,'කදමින්','කරමින්'),
            l.summary_si=REPLACE(l.summary_si,'කදමින්','කරමින්')
        WHERE g.grade_number=8 AND l.medium='Sinhala' AND l.display_order=1
          AND (s.name_en LIKE '%Sinhala%' OR s.name_si LIKE '%සිංහල%')";
if (!$db->query($sql)) throw new RuntimeException($db->error);
echo "Grade 8 Sinhala lesson 1 wording corrected to කරමින්: {$db->affected_rows} row(s).\n";
