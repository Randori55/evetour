<?php
require_once __DIR__.'/config.php';
function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function setting($key,$default=''){
 global $pdo; static $cache=[]; if(array_key_exists($key,$cache)) return $cache[$key];
 $st=$pdo->prepare('SELECT value FROM settings WHERE name=? LIMIT 1'); $st->execute([$key]); return $cache[$key]=$st->fetchColumn() ?: $default;
}
function admin_required(){ if(empty($_SESSION['admin_id'])){ header('Location: login.php'); exit; } }
function upload_image($field,$old=null){
 if(empty($_FILES[$field]['name'])) return $old;
 if(!isset($_FILES[$field]) || !is_array($_FILES[$field])) throw new Exception('Image upload data was not received.');
 $f=$_FILES[$field];
 if($f['error']!==UPLOAD_ERR_OK){
  $errors=[
   UPLOAD_ERR_INI_SIZE=>'The image exceeds the server upload limit.',
   UPLOAD_ERR_FORM_SIZE=>'Image is larger than the allowed size.',
   UPLOAD_ERR_PARTIAL=>'The image was only partially uploaded. Please try again.',
   UPLOAD_ERR_NO_FILE=>'Please choose an image file.',
   UPLOAD_ERR_NO_TMP_DIR=>'Server temporary upload folder is missing.',
   UPLOAD_ERR_CANT_WRITE=>'Server could not write the uploaded image.',
   UPLOAD_ERR_EXTENSION=>'Image upload was stopped by a server extension.'
  ];
  throw new Exception($errors[$f['error']] ?? 'Image upload failed.');
 }
 if(!is_uploaded_file($f['tmp_name'])) throw new Exception('The uploaded image is invalid. Please try again.');
 $mime=(new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']); $map=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
 if(!isset($map[$mime])) throw new Exception('Only JPG, PNG and WebP images are allowed.');
 $name=bin2hex(random_bytes(8)).'.'.$map[$mime]; $dir=__DIR__.'/../uploads';
 if(!is_dir($dir) && !mkdir($dir,0755,true) && !is_dir($dir)) throw new Exception('Upload folder could not be created.');
 if(!is_writable($dir)) throw new Exception('Upload folder is not writable. Please check the uploads folder permissions.');
 if(!move_uploaded_file($f['tmp_name'],$dir.'/'.$name)) throw new Exception('Image could not be saved. Please check uploads folder permissions.');
 return 'uploads/'.$name;
}
