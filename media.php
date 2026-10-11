<?php
require_once __DIR__.'/includes/storage.php';
$key=(string)($_GET['key']??'');
if(!valid_bucket_image_key($key)){http_response_code(404);exit;}
try{
 $cfg=bucket_config();$object=bucket_client()->getObject(['Bucket'=>$cfg['bucket'],'Key'=>$key]);
 header('Content-Type: '.($object['ContentType']?:'application/octet-stream'));
 header('Cache-Control: public, max-age=300, stale-while-revalidate=600');
 echo (string)$object['Body'];
}catch(Throwable $e){http_response_code(404);header('Cache-Control: no-store');}
