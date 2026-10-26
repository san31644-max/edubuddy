<?php
declare(strict_types=1);
const FREE_AI_DAILY_LIMIT = 1;

function ensure_ai_daily_usage_table(): bool
{
    static $ready = null;
    if ($ready !== null) return $ready;
    $ready = (bool) db()->query(
        'CREATE TABLE IF NOT EXISTS ai_daily_usage (
            user_id INT NOT NULL,
            usage_date DATE NOT NULL,
            response_count INT UNSIGNED NOT NULL DEFAULT 0,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (user_id, usage_date),
            INDEX (usage_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
    );
    return $ready;
}

function free_ai_used_today(int $userId): int
{
    if (!ensure_ai_daily_usage_table()) return 0;
    $statement = db()->prepare('SELECT response_count FROM ai_daily_usage WHERE user_id=? AND usage_date=?');
    if (!$statement) return 0;
    $day=date('Y-m-d');
    $statement->bind_param('is', $userId, $day);
    $statement->execute();
    $row = $statement->get_result()->fetch_assoc();
    $statement->close();
    return (int) ($row['response_count'] ?? 0);
}

/** Atomically claim today's free message before contacting a provider. */
function reserve_free_ai_message(int $userId): bool
{
    if (!ensure_ai_daily_usage_table()) throw new RuntimeException('AI usage tracking is unavailable.');
    $day=date('Y-m-d');
    $insert=db()->prepare('INSERT IGNORE INTO ai_daily_usage(user_id,usage_date,response_count) VALUES(?,?,0)');
    if(!$insert)throw new RuntimeException('AI usage tracking is unavailable.');
    $insert->bind_param('is',$userId,$day);
    if(!$insert->execute())throw new RuntimeException('AI usage tracking is unavailable.');
    $insert->close();
    $claim=db()->prepare('UPDATE ai_daily_usage SET response_count=response_count+1 WHERE user_id=? AND usage_date=? AND response_count<?');
    if(!$claim)throw new RuntimeException('AI usage tracking is unavailable.');
    $limit=FREE_AI_DAILY_LIMIT;
    $claim->bind_param('isi',$userId,$day,$limit);
    if(!$claim->execute())throw new RuntimeException('AI usage tracking is unavailable.');
    $allowed=$claim->affected_rows===1;
    $claim->close();
    return $allowed;
}
