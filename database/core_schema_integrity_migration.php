<?php
declare(strict_types=1);

ini_set('session.save_path', __DIR__.'/../includes/runtime');
require_once __DIR__.'/../includes/db.php';

$db = db();

function core_schema_column(mysqli $db, string $table, string $column): ?array
{
    $s = $db->prepare('SELECT COLUMN_TYPE,EXTRA FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? AND COLUMN_NAME=?');
    $s->bind_param('ss', $table, $column);
    $s->execute();
    return $s->get_result()->fetch_assoc() ?: null;
}

function core_schema_primary_exists(mysqli $db, string $table): bool
{
    $s = $db->prepare("SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA=DATABASE() AND TABLE_NAME=? AND CONSTRAINT_TYPE='PRIMARY KEY'");
    $s->bind_param('s', $table);
    $s->execute();
    return (bool)$s->get_result()->fetch_row();
}

function core_schema_ensure_generated_id(mysqli $db, string $table): void
{
    if (!in_array($table, ['users', 'quizzes', 'subscriptions'], true)) throw new RuntimeException('Unsupported core table.');
    $column = core_schema_column($db, $table, 'id');
    if (!$column) throw new RuntimeException("$table.id is missing.");
    $counts = $db->query("SELECT COUNT(*) total,COUNT(id) populated,COUNT(DISTINCT id) unique_ids FROM `$table`")->fetch_assoc();
    if ((int)$counts['total'] !== (int)$counts['populated'] || (int)$counts['total'] !== (int)$counts['unique_ids']) throw new RuntimeException("$table.id contains null or duplicate values; automatic repair was stopped.");
    if (!core_schema_primary_exists($db, $table) && !$db->query("ALTER TABLE `$table` ADD PRIMARY KEY(id)")) throw new RuntimeException($db->error);
    if (!str_contains(strtolower((string)$column['EXTRA']), 'auto_increment')) {
        $type = strtolower((string)$column['COLUMN_TYPE']);
        if (!preg_match('/^(?:int|bigint)(?:\(\d+\))?(?: unsigned)?$/', $type)) throw new RuntimeException("Unexpected type for $table.id.");
        if (!$db->query("ALTER TABLE `$table` MODIFY id $type NOT NULL AUTO_INCREMENT")) throw new RuntimeException($db->error);
    }
    echo "$table.id is primary and auto-incrementing.\n";
}

foreach (['users', 'quizzes', 'subscriptions'] as $table) core_schema_ensure_generated_id($db, $table);
