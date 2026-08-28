<?php
declare(strict_types=1);

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
    $statement = db()->prepare('SELECT response_count FROM ai_daily_usage WHERE user_id=? AND usage_date=CURDATE()');
    if (!$statement) return 0;
    $statement->bind_param('i', $userId);
    $statement->execute();
    $row = $statement->get_result()->fetch_assoc();
    $statement->close();
    return (int) ($row['response_count'] ?? 0);
}

function record_free_ai_response(int $userId): int
{
    if (!ensure_ai_daily_usage_table()) return free_ai_used_today($userId);
    $statement = db()->prepare(
        'INSERT INTO ai_daily_usage(user_id,usage_date,response_count) VALUES(?,CURDATE(),1)
         ON DUPLICATE KEY UPDATE response_count=response_count+1'
    );
    if ($statement) {
        $statement->bind_param('i', $userId);
        $statement->execute();
        $statement->close();
    }
    return free_ai_used_today($userId);
}