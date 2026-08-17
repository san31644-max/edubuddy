<?php
declare(strict_types=1);

$source = $argv[1] ?? '';
$target = $argv[2] ?? '';
if (!is_file($source) || $target === '') {
    throw new RuntimeException('Source and target required.');
}

$text = (string) file_get_contents($source);
preg_match_all('/^([1-5]) [^\r\n]+$/m', $text, $heads, PREG_OFFSET_CAPTURE);
$sets = [];
foreach ($heads[0] as $index => $heading) {
    $lesson = (int) $heads[1][$index][0];
    $start = $heading[1];
    $end = $heads[0][$index + 1][1] ?? strlen($text);
    $chunk = substr($text, $start, $end - $start);
    preg_match_all('/^(\d+)\.\s*(.+?)\R\s*\R?A\)\s*(.+?)\R\s*B\)\s*(.+?)\R\s*C\)\s*(.+?)\R\s*D\)\s*(.+?)\R\s*\R?[^:\r\n]+:\s*([ABCD])/msu', $chunk, $matches, PREG_SET_ORDER);
    $questions = [];
    foreach ($matches as $match) {
        $questions[] = [
            'question' => trim($match[2]),
            'a' => trim($match[3]),
            'b' => trim($match[4]),
            'c' => trim($match[5]),
            'd' => trim($match[6]),
            'correct' => strtolower($match[7]),
        ];
    }
    if (count($questions) !== 30) {
        throw new RuntimeException("Lesson $lesson parsed " . count($questions) . ' questions.');
    }
    $sets[(string) $lesson] = ['questions' => $questions];
}
if (array_map('intval', array_keys($sets)) !== range(1, 5)) {
    throw new RuntimeException('Expected lessons 1-5; found ' . implode(',', array_keys($sets)));
}
file_put_contents($target, json_encode($sets, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
echo "Built 5 lessons and 150 exact MCQs.\n";
