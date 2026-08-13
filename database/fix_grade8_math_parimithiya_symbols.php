<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit;
}

require_once __DIR__.'/../includes/db.php';

$notes = <<<'TEXT'
අධ්‍යයන සටහන්

පාඩම: පරිමිතිය
විෂය: ගණිතය — 8 ශ්‍රේණිය

පරිමිතිය යනු තල රූපයක පිටත මායිමේ මුළු දිගයි.

• සමචතුරස්‍රයක පැත්තේ දිග a නම්, පරිමිතිය P = 4 × a වේ.
  උදාහරණය: a = 6 cm නම් P = 4 × 6 = 24 cm.
• සෘජුකෝණාස්‍රයක දිග l සහ පළල w නම්, P = 2 × (l + w) වේ.
  උදාහරණය: l = 10 cm සහ w = 7 cm නම් P = 2 × (10 + 7) = 34 cm.
• ත්‍රිකෝණයක පරිමිතිය එහි පැති තුනේ දිගවල එකතුවයි.
• සංයුක්ත රූපයක පරිමිතිය සොයන විට පිටත මායිමේ ඇති සියලු පැති පමණක් එකතු කරන්න. ඇතුළත රේඛා එකතු නොකරන්න.
• සියලු දිග එකම ඒකකයකට පරිවර්තනය කර පසුව එකතු කරන්න. 1 m = 100 cm සහ 1 cm = 10 mm.
• පරිමිතිය සඳහා mm, cm, m හෝ km වැනි දිග ඒකක භාවිතා කරන්න; cm² සහ m² වැනි වර්ග ඒකක භාවිතා නොකරන්න.

මතක තබාගන්න: පරිමිතිය = පිටත මායිමේ මුළු දිග.
TEXT;

$db = db();
$column = $db->query("SHOW COLUMNS FROM lessons LIKE 'short_notes_si'");
if (!$column || $column->num_rows === 0) {
    $db->query("ALTER TABLE lessons ADD short_notes_si MEDIUMTEXT NULL AFTER short_description_si");
}

$sql = "UPDATE lessons l
        INNER JOIN grades g ON g.id = l.grade_id
        INNER JOIN subjects s ON s.id = l.subject_id
        SET l.title_si = 'පරිමිතිය',
            l.short_description_si = 'තල රූපයක පිටත මායිමේ මුළු දිග සොයමු.',
            l.short_notes_si = ?,
            l.content_si = ?,
            l.summary_si = 'පරිමිතිය යනු පිටත මායිමේ ඇති සියලු පැතිවල දිග එකතුවයි. සංයුක්ත රූපයක ඇතුළත රේඛා එකතු නොකරන්න.',
            l.key_terms_si = 'පරිමිතිය, පිටත මායිම, සමචතුරස්‍රය, සෘජුකෝණාස්‍රය, සංයුක්ත රූපය'
        WHERE g.grade_number = 8
          AND s.subject_code = 'MATHE8'
          AND l.medium = 'Sinhala'
          AND l.display_order = 2";
$statement = $db->prepare($sql);
if (!$statement) {
    throw new RuntimeException($db->error);
}
$statement->bind_param('ss', $notes, $notes);
if (!$statement->execute()) {
    throw new RuntimeException($statement->error);
}
echo "Grade 8 Sinhala Mathematics Parimithiya lesson repaired: {$statement->affected_rows} row(s).\n";
