<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli')exit;
$root=getenv('EDUCHAT_TEST_ROOT')?:dirname(__DIR__);
require_once $root.'/includes/db.php';
require_once $root.'/includes/student_history.php';
if(!ensure_student_account_events_table())throw new RuntimeException(db()->error);
echo "Student account history table ready.\n";
