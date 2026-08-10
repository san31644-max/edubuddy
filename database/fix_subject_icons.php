<?php
declare(strict_types=1);
require_once __DIR__.'/../includes/db.php';

// Use UTF-8 byte sequences explicitly so Windows console encodings cannot
// corrupt emoji while this maintenance script is executed.
$icons = [
    'Mathematics' => 'F09F9390',
    'Science' => 'F09F94AC',
    'History' => 'F09F8FBA',
    'Geography' => 'F09F8C8D',
    'Civic Education' => 'F09FA49D',
    'Health and Physical Education' => 'F09F8F83',
    'ICT' => 'F09F92BB',
    'English' => 'F09F9398',
    'Sinhala' => 'F09F9395',
    'Tamil' => 'F09F9397',
    'Religion' => 'F09F958AEFB88F',
    'Buddhism' => 'E298B8EFB88F',
    'Practical and Technical Skills' => 'F09F9BA0EFB88F',
];

$statement = db()->prepare('UPDATE subjects SET icon=CONVERT(UNHEX(?) USING utf8mb4) WHERE name_en=?');
$updated = 0;
foreach ($icons as $subject => $hex) {
    $statement->bind_param('ss', $hex, $subject);
    $statement->execute();
    $updated += $statement->affected_rows;
}
echo "Corrected $updated subject icons.\n";
