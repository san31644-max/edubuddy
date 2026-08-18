<?php
declare(strict_types=1);
require_once __DIR__.'/../includes/db.php';
$db=db();
$column=$db->query("SHOW COLUMNS FROM subscriptions LIKE 'receipt_path'");
if(!$column || $column->num_rows===0){
    if(!$db->query("ALTER TABLE subscriptions ADD receipt_path VARCHAR(255) NULL AFTER payment_reference")) throw new RuntimeException($db->error);
    echo "Added subscriptions.receipt_path\n";
}else echo "subscriptions.receipt_path already exists\n";
