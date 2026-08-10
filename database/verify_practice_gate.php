<?php
declare(strict_types=1);
require_once __DIR__.'/../includes/db.php';
$sql="SELECT COUNT(DISTINCT CASE WHEN detail LIKE 'Quick Challenge - %' THEN 'challenge' WHEN detail LIKE 'Fill the Missing Answer - %' THEN 'missing' WHEN detail LIKE 'Matching Mission - %' THEN 'matching' END) completed FROM student_activity_events WHERE user_id=? AND lesson_id=? AND event_type='quiz_completed'";
$s=db()->prepare($sql);if(!$s){fwrite(STDERR,"Practice gate query failed: ".db()->error.PHP_EOL);exit(1);}echo "Practice completion gate query passed.\n";
