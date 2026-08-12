<?php
declare(strict_types=1);

ini_set('session.save_path', __DIR__.'/../includes/runtime');
require_once __DIR__.'/../includes/db.php';
$db = db();

function referral_column_exists(mysqli $db, string $table, string $column): bool
{
    $s = $db->prepare('SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? AND COLUMN_NAME=?');
    $s->bind_param('ss', $table, $column);
    $s->execute();
    return (bool)$s->get_result()->fetch_row();
}

function referral_index_exists(mysqli $db, string $table, string $index): bool
{
    $s = $db->prepare('SELECT 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? AND INDEX_NAME=?');
    $s->bind_param('ss', $table, $index);
    $s->execute();
    return (bool)$s->get_result()->fetch_row();
}

function referral_constraint_exists(mysqli $db, string $constraint): bool
{
    $s = $db->prepare('SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA=DATABASE() AND CONSTRAINT_NAME=?');
    $s->bind_param('s', $constraint);
    $s->execute();
    return (bool)$s->get_result()->fetch_row();
}

function referral_constraint_delete_rule(mysqli $db, string $constraint): string
{
    $s = $db->prepare('SELECT DELETE_RULE FROM information_schema.REFERENTIAL_CONSTRAINTS WHERE CONSTRAINT_SCHEMA=DATABASE() AND CONSTRAINT_NAME=?');
    $s->bind_param('s', $constraint);
    $s->execute();
    return (string)($s->get_result()->fetch_row()[0] ?? '');
}

$queries = [
    "CREATE TABLE IF NOT EXISTS referral_promoters(
        id INT AUTO_INCREMENT PRIMARY KEY,
        full_name VARCHAR(100) NOT NULL,
        email VARCHAR(190) NULL,
        phone VARCHAR(15) NULL,
        referral_code VARCHAR(24) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        status ENUM('active','inactive') NOT NULL DEFAULT 'active',
        last_login_at DATETIME NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY referral_promoters_email_unique(email),
        UNIQUE KEY referral_promoters_phone_unique(phone)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
];
foreach ($queries as $sql) if (!$db->query($sql)) throw new RuntimeException($db->error);

if (!referral_column_exists($db, 'users', 'referral_promoter_id') && !$db->query('ALTER TABLE users ADD referral_promoter_id INT NULL AFTER subscription_expires_at')) throw new RuntimeException($db->error);
if (!referral_column_exists($db, 'users', 'referral_code_used') && !$db->query('ALTER TABLE users ADD referral_code_used VARCHAR(24) NULL AFTER referral_promoter_id')) throw new RuntimeException($db->error);
if (!referral_index_exists($db, 'users', 'users_referral_promoter_idx') && !$db->query('ALTER TABLE users ADD INDEX users_referral_promoter_idx(referral_promoter_id)')) throw new RuntimeException($db->error);
if (!referral_constraint_exists($db, 'users_referral_promoter_fk') && !$db->query('ALTER TABLE users ADD CONSTRAINT users_referral_promoter_fk FOREIGN KEY(referral_promoter_id) REFERENCES referral_promoters(id) ON DELETE RESTRICT')) throw new RuntimeException($db->error);
if (referral_constraint_delete_rule($db, 'users_referral_promoter_fk') !== 'RESTRICT') {
    if (!$db->query('ALTER TABLE users DROP FOREIGN KEY users_referral_promoter_fk')) throw new RuntimeException($db->error);
    if (!$db->query('ALTER TABLE users ADD CONSTRAINT users_referral_promoter_fk FOREIGN KEY(referral_promoter_id) REFERENCES referral_promoters(id) ON DELETE RESTRICT')) throw new RuntimeException($db->error);
}

if (!referral_column_exists($db, 'subscriptions', 'base_amount_lkr') && !$db->query('ALTER TABLE subscriptions ADD base_amount_lkr DECIMAL(10,2) NULL AFTER amount_lkr')) throw new RuntimeException($db->error);
if (!referral_column_exists($db, 'subscriptions', 'discount_lkr') && !$db->query('ALTER TABLE subscriptions ADD discount_lkr DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER base_amount_lkr')) throw new RuntimeException($db->error);
if (!referral_column_exists($db, 'subscriptions', 'referral_promoter_id') && !$db->query('ALTER TABLE subscriptions ADD referral_promoter_id INT NULL AFTER discount_lkr')) throw new RuntimeException($db->error);
if (!$db->query('UPDATE subscriptions SET base_amount_lkr=amount_lkr WHERE base_amount_lkr IS NULL')) throw new RuntimeException($db->error);
if (!$db->query('ALTER TABLE subscriptions MODIFY base_amount_lkr DECIMAL(10,2) NOT NULL')) throw new RuntimeException($db->error);
if (!referral_index_exists($db, 'subscriptions', 'subscriptions_referral_promoter_idx') && !$db->query('ALTER TABLE subscriptions ADD INDEX subscriptions_referral_promoter_idx(referral_promoter_id)')) throw new RuntimeException($db->error);
if (!referral_constraint_exists($db, 'subscriptions_referral_promoter_fk') && !$db->query('ALTER TABLE subscriptions ADD CONSTRAINT subscriptions_referral_promoter_fk FOREIGN KEY(referral_promoter_id) REFERENCES referral_promoters(id) ON DELETE RESTRICT')) throw new RuntimeException($db->error);
if (referral_constraint_delete_rule($db, 'subscriptions_referral_promoter_fk') !== 'RESTRICT') {
    if (!$db->query('ALTER TABLE subscriptions DROP FOREIGN KEY subscriptions_referral_promoter_fk')) throw new RuntimeException($db->error);
    if (!$db->query('ALTER TABLE subscriptions ADD CONSTRAINT subscriptions_referral_promoter_fk FOREIGN KEY(referral_promoter_id) REFERENCES referral_promoters(id) ON DELETE RESTRICT')) throw new RuntimeException($db->error);
}

if (!$db->query("UPDATE subscription_plans SET name='K Education Premium 30 Days',price_lkr=300,duration_days=30 WHERE id=1")) throw new RuntimeException($db->error);
echo "Referral program schema and Rs. 300 Premium plan are ready.\n";
