<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';

$conn = db();

$result = $conn->query(
    "SELECT p.id, p.title, p.total_marks,
            COUNT(q.id) AS question_count,
            COALESCE(SUM(q.marks), 0) AS allocated_marks
       FROM practice_papers p
       LEFT JOIN paper_questions q ON q.paper_id = p.id
      GROUP BY p.id, p.title, p.total_marks
      ORDER BY p.id"
);

if (!$result) {
    fwrite(STDERR, "Verification query failed: {$conn->error}\n");
    exit(1);
}

if ($result->num_rows === 0) {
    fwrite(STDERR, "No imported practice papers found.\n");
    exit(1);
}

while ($paper = $result->fetch_assoc()) {
    printf(
        "Paper %d: %s | questions=%d | total_marks=%s | allocated_marks=%s\n",
        (int) $paper['id'],
        $paper['title'],
        (int) $paper['question_count'],
        $paper['total_marks'],
        $paper['allocated_marks']
    );
}
