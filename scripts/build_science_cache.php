<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$source = $root . '/uploads/syllabus/curriculum.json';
$target = $root . '/uploads/syllabus/science-cache';
$items = json_decode((string) file_get_contents($source), true, 512, JSON_THROW_ON_ERROR);
if (!is_dir($target) && !mkdir($target, 0775, true) && !is_dir($target)) {
    throw new RuntimeException('Unable to create Science cache directory.');
}
$written = 0;
foreach ($items as $item) {
    if (stripos((string) ($item['subject'] ?? ''), 'Science') !== 0
        || !preg_match('/Lesson\s*0*(\d+)(?!\d)/i', (string) ($item['title'] ?? ''), $match)) continue;
    $number = (int) $match[1];
    if ($number < 1 || $number > 11) continue;
    $payload = ['title'=>$item['title'], 'chunks'=>$item['chunks'] ?? []];
    file_put_contents($target . '/lesson-' . $number . '.json', json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    $written++;
}
echo "Science lesson cache files written: {$written}\n";
