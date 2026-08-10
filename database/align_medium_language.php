<?php
require_once __DIR__.'/../includes/db.php';
$sql="UPDATE users SET preferred_language=CASE medium WHEN 'Sinhala' THEN 'si' WHEN 'Tamil' THEN 'ta' ELSE 'en' END WHERE preferred_language<>CASE medium WHEN 'Sinhala' THEN 'si' WHEN 'Tamil' THEN 'ta' ELSE 'en' END";
if(!db()->query($sql)){fwrite(STDERR,db()->error.PHP_EOL);exit(1);}
echo 'Aligned '.db()->affected_rows.' existing account(s).'.PHP_EOL;
