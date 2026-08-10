<?php
declare(strict_types=1);
require_once __DIR__.'/../includes/auth.php';
require_admin();
header('Content-Type: application/json; charset=utf-8');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new RuntimeException('Invalid upload request.');
    verify_csrf();
    $uploadId = (string)($_POST['upload_id'] ?? '');
    $index = filter_input(INPUT_POST, 'index', FILTER_VALIDATE_INT);
    $last = (string)($_POST['last'] ?? '') === '1';
    if (!preg_match('/^[a-f0-9]{32}$/', $uploadId) || $index === false || $index === null || $index < 0) throw new RuntimeException('Invalid upload information.');
    if (!isset($_FILES['chunk']) || $_FILES['chunk']['error'] !== UPLOAD_ERR_OK) throw new RuntimeException('A PDF chunk could not be uploaded.');

    $directory = sys_get_temp_dir().'/educhat_pdf_'.hash('sha256',session_id());
    if (!is_dir($directory) && !mkdir($directory,0700,true) && !is_dir($directory)) throw new RuntimeException('Could not prepare PDF upload storage.');
    $partPath = $directory.'/'.$uploadId.'.part';
    $metaPath = $directory.'/'.$uploadId.'.index';
    $expected = is_file($metaPath) ? (int)file_get_contents($metaPath) : 0;
    if ($index !== $expected) throw new RuntimeException('PDF chunks arrived out of order. Please retry.');
    $mode = $index === 0 ? 'wb' : 'ab';
    $input = fopen($_FILES['chunk']['tmp_name'],'rb');$output = fopen($partPath,$mode);
    if (!$input || !$output || stream_copy_to_stream($input,$output) === false) throw new RuntimeException('Could not rebuild the PDF.');
    if (is_resource($input)) fclose($input);if(is_resource($output)) fclose($output);
    if (filesize($partPath) > 30*1024*1024) throw new RuntimeException('Use a PDF no larger than 30 MB.');
    file_put_contents($metaPath,(string)($index+1),LOCK_EX);

    if ($last) {
        if (file_get_contents($partPath,false,null,0,5) !== '%PDF-') throw new RuntimeException('The selected file is not a valid PDF.');
        $finalPath = $directory.'/'.$uploadId.'.pdf';
        if (!rename($partPath,$finalPath)) throw new RuntimeException('Could not finish the PDF upload.');
        @unlink($metaPath);
        echo json_encode(['ok'=>true,'token'=>$uploadId]);
    } else echo json_encode(['ok'=>true]);
} catch (Throwable $e) {
    http_response_code(422);
    echo json_encode(['ok'=>false,'error'=>$e->getMessage()]);
}
