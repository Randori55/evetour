<?php
require_once __DIR__.'/config.php';
require_once __DIR__.'/i18n.php';
function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function setting($key,$default=''){
 global $pdo; static $cache=[]; if(array_key_exists($key,$cache)) return $cache[$key];
 $st=$pdo->prepare('SELECT value FROM settings WHERE name=? LIMIT 1'); $st->execute([$key]); return $cache[$key]=$st->fetchColumn() ?: $default;
}
function save_setting($key,$value){
 global $pdo;
 $pdo->prepare('INSERT INTO settings(name,value) VALUES(?,?) ON DUPLICATE KEY UPDATE value=VALUES(value)')->execute([$key,$value]);
}
require_once __DIR__.'/storage.php';
function site_image($path,$fallback='assets/img/placeholder.svg'){
 $relative=str_replace('\\','/',trim((string)$path));
 $asset=static fn($value)=>str_starts_with((string)$value,'/')?(string)$value:'/'.ltrim((string)$value,'/');
 if(str_starts_with($relative,'s3://')){
  $key=substr($relative,5);if(!valid_bucket_image_key($key))return $asset($fallback);
  return '/media.php?key='.rawurlencode($key);
 }
 if($relative==='' || str_starts_with($relative,'/') || preg_match('~(?:^|/)\.\.(?:/|$)~',$relative)) return $asset($fallback);
 return is_file(dirname(__DIR__).'/'.$relative) ? $asset($relative) : $asset($fallback);
}
function ensure_generated_id_columns(){
 global $pdo;
 static $checked=false;
 if($checked) return;
 $checked=true;

 $migration='generated_ids_auto_increment_v1';
 $lock='evetour_generated_ids_auto_increment_v1';
 $lockHeld=false;
 try{
  $tableExists=$pdo->prepare("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='settings'");
  $tableExists->execute();
  if((int)$tableExists->fetchColumn()===0) return;

  $done=$pdo->prepare('SELECT value FROM settings WHERE name=? LIMIT 1');
  $done->execute([$migration]);
  if($done->fetchColumn()==='1') return;

  $lockHeld=(int)$pdo->query("SELECT GET_LOCK('{$lock}',10)")->fetchColumn()===1;
  if(!$lockHeld) throw new RuntimeException('Could not acquire the database schema migration lock.');

  $done->execute([$migration]);
  if($done->fetchColumn()==='1') return;

  $tables=['admins','settings','hero_images','tours','reviews','contact_messages'];
  $marks=implode(',',array_fill(0,count($tables),'?'));
  $query=$pdo->prepare("SELECT TABLE_NAME,COLUMN_TYPE,EXTRA,COLUMN_KEY FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND COLUMN_NAME='id' AND TABLE_NAME IN ({$marks})");
  $query->execute($tables);
  foreach($query->fetchAll() as $column){
   if(stripos($column['EXTRA'],'auto_increment')!==false) continue;
   $type=strtolower($column['COLUMN_TYPE']);
   if(!preg_match('/^(tinyint|smallint|mediumint|int|bigint)(?:\\(\\d+\\))?(?: unsigned)?$/',$type)){
    throw new RuntimeException('Unsupported id column type in table '.$column['TABLE_NAME'].'.');
   }
   $table=str_replace('`','',$column['TABLE_NAME']);
   if(in_array($column['COLUMN_KEY'],['PRI','UNI'],true)){
    $pdo->exec("ALTER TABLE `{$table}` MODIFY `id` {$type} NOT NULL AUTO_INCREMENT");
   }else{
    if($pdo->query("SELECT 1 FROM `{$table}` WHERE `id` IS NULL LIMIT 1")->fetchColumn()){
     throw new RuntimeException('Cannot safely migrate the id column in table '.$table.': it contains NULL values.');
    }
    if($pdo->query("SELECT 1 FROM `{$table}` GROUP BY `id` HAVING COUNT(*)>1 LIMIT 1")->fetchColumn()){
     throw new RuntimeException('Cannot safely migrate the id column in table '.$table.': it contains duplicate values.');
    }
    $index='uq_evetour_'.$table.'_id';
    $pdo->exec("ALTER TABLE `{$table}` MODIFY `id` {$type} NOT NULL AUTO_INCREMENT, ADD UNIQUE KEY `{$index}` (`id`)");
   }
  }

  $pdo->prepare('INSERT INTO settings(name,value) VALUES(?,?) ON DUPLICATE KEY UPDATE value=VALUES(value)')->execute([$migration,'1']);
 }finally{
  if($lockHeld) $pdo->query("SELECT RELEASE_LOCK('{$lock}')");
 }
}
function admin_required(){ if(empty($_SESSION['admin_id'])){ header('Location: login.php'); exit; } }
function save_uploaded_image($f,$section='misc',$slot=null){
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
 $section=preg_match('/^(reviews|tours|hero)$/',$section)?$section:'misc';
 $suffix=$slot!==null?preg_replace('/[^a-zA-Z0-9_-]/','',(string)$slot):bin2hex(random_bytes(8));
 $name=$section.'_'.$suffix.'.'.$map[$mime];
 if(getenv('EVE_S3_BUCKET')){
  $key=$section.'/'.$name;$bytes=file_get_contents($f['tmp_name']);if($bytes===false)throw new Exception('Uploaded image could not be read.');
  bucket_request('PUT',$key,$bytes,$mime);
  foreach(['jpg','png','webp'] as $ext)if($ext!==$map[$mime])try{bucket_request('DELETE',$section.'/'.$section.'_'.$suffix.'.'.$ext);}catch(Throwable $e){}
  return 's3://'.$key;
 }
 $dir=__DIR__.'/../uploads/'.$section;
 if(!is_dir($dir) && !mkdir($dir,0755,true) && !is_dir($dir)) throw new Exception('Upload folder could not be created.');
 if(!is_writable($dir)) throw new Exception('Upload folder is not writable. Please check the uploads folder permissions.');
 if(!move_uploaded_file($f['tmp_name'],$dir.'/'.$name)) throw new Exception('Image could not be saved. Please check uploads folder permissions.');
 return 'uploads/'.$section.'/'.$name;
}
function upload_image($field,$old=null,$section='misc',$slot=null){
 if(empty($_FILES[$field]['name'])) return $old;
 if(!isset($_FILES[$field]) || !is_array($_FILES[$field])) throw new Exception('Image upload data was not received.');
 return save_uploaded_image($_FILES[$field],$section,$slot);
}
function upload_images($field,$section='hero',$startSlot=1){
 if(empty($_FILES[$field]['name']) || !is_array($_FILES[$field]['name'])) return [];
 $files=$_FILES[$field]; $images=[];
 foreach($files['name'] as $i=>$name){
  if($name==='') continue;
  $images[]=save_uploaded_image(['name'=>$name,'type'=>$files['type'][$i]??'', 'tmp_name'=>$files['tmp_name'][$i]??'', 'error'=>$files['error'][$i]??UPLOAD_ERR_NO_FILE, 'size'=>$files['size'][$i]??0],$section,($startSlot+$i));
 }
 return $images;
}
ensure_generated_id_columns();
