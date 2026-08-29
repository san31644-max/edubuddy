<?php
declare(strict_types=1);
require_once __DIR__.'/../includes/auth.php';
require_admin();

$id=filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT);$requestedPath=str_replace('\\','/',ltrim((string)($_GET['path']??''),'/'));
$subscription=$id?query_one('SELECT receipt_path FROM subscriptions WHERE id=?','i',[$id]):($requestedPath!==''?query_one('SELECT receipt_path FROM subscriptions WHERE receipt_path=?','s',[$requestedPath]):null);
if(!$subscription||empty($subscription['receipt_path'])){
 http_response_code(404);
 exit('Receipt not found.');
}

$relative=str_replace('\\','/',ltrim((string)$subscription['receipt_path'],'/'));
$allowedPrefixes=['uploads/receipts/','includes/runtime/receipts/'];
if(str_starts_with($relative,'receipt-db/')){$blob=query_one('SELECT mime_type,image_data FROM subscription_receipt_blobs WHERE receipt_key=?','s',[$relative]);if(!$blob){http_response_code(404);exit('Receipt file not found.');}$blobMime=(string)$blob['mime_type'];if(!in_array($blobMime,['image/jpeg','image/png','image/webp'],true)){http_response_code(415);exit('Unsupported receipt file.');}header('Content-Type: '.$blobMime);header('Content-Length: '.strlen((string)$blob['image_data']));header('Content-Disposition: inline; filename="receipt-'.($id?:(int)sprintf('%u',crc32($relative))).'"');header('Cache-Control: private, no-store, max-age=0');header('X-Content-Type-Options: nosniff');echo $blob['image_data'];exit;}
$allowedRoot=null;
foreach($allowedPrefixes as $prefix)if(str_starts_with($relative,$prefix)){$candidateRoot=realpath(__DIR__.'/../'.rtrim($prefix,'/'));if($candidateRoot!==false)$allowedRoot=$candidateRoot;break;}
if($allowedRoot===null){
 http_response_code(403);
 exit('Invalid receipt path.');
}

$file=realpath(__DIR__.'/../'.$relative);
if($file===false||!is_file($file)||!str_starts_with($file,$allowedRoot.DIRECTORY_SEPARATOR)){
 http_response_code(404);
 exit('Receipt file not found.');
}
$mime=(new finfo(FILEINFO_MIME_TYPE))->file($file);
if(!in_array($mime,['image/jpeg','image/png','image/webp'],true)){
 http_response_code(415);
 exit('Unsupported receipt file.');
}

header('Content-Type: '.$mime);
header('Content-Length: '.filesize($file));
header('Content-Disposition: inline; filename=receipt-'.($id?:(int)sprintf('%u',crc32($relative))).'.'.pathinfo($file,PATHINFO_EXTENSION).'');
header('Cache-Control: private, no-store, max-age=0');
header('X-Content-Type-Options: nosniff');
readfile($file);
