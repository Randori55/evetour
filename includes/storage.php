<?php
require_once __DIR__.'/../vendor/autoload.php';
function bucket_config(){
 $cfg=['bucket'=>getenv('EVE_S3_BUCKET')?:'','key'=>getenv('EVE_S3_ACCESS_KEY_ID')?:'','secret'=>getenv('EVE_S3_SECRET_ACCESS_KEY')?:'','region'=>getenv('EVE_S3_REGION')?:'auto','endpoint'=>rtrim(getenv('EVE_S3_ENDPOINT')?:'','/')];
 if(!$cfg['bucket']||!$cfg['key']||!$cfg['secret']||!$cfg['endpoint'])throw new RuntimeException('Image bucket is not configured.');
 return $cfg;
}
function bucket_client(){
 static $client=null;
 if($client)return $client;
 $cfg=bucket_config();
 $client=new Aws\S3\S3Client(['version'=>'latest','region'=>$cfg['region'],'endpoint'=>$cfg['endpoint'],'use_path_style_endpoint'=>false,'signature_version'=>'v4','credentials'=>['key'=>$cfg['key'],'secret'=>$cfg['secret']]]);
 return $client;
}
function valid_bucket_image_key($key){return is_string($key)&&preg_match('~^(reviews|tours|hero)/[a-z0-9_-]+\.(jpg|png|webp)$~i',$key)===1;}
function bucket_request($method,$key,$body='',$contentType='application/octet-stream'){
 if(!in_array($method,['PUT','DELETE'],true)||!valid_bucket_image_key($key))throw new RuntimeException('Invalid image bucket operation.');
 $cfg=bucket_config();$args=['Bucket'=>$cfg['bucket'],'Key'=>$key];
 try{
  if($method==='PUT')bucket_client()->putObject($args+['Body'=>$body,'ContentType'=>$contentType,'CacheControl'=>'public, max-age=3600']);
  else bucket_client()->deleteObject($args);
 }catch(Throwable $e){$code=$e instanceof Aws\Exception\AwsException?$e->getAwsErrorCode():'';throw new RuntimeException('Image bucket request failed'.($code?' ('.$code.')':'.'),0,$e);}
 return true;
}
function delete_stored_image($path){
 $path=str_replace('\\','/',trim((string)$path));
 if(str_starts_with($path,'s3://')){ $key=substr($path,5);if(!valid_bucket_image_key($key))throw new RuntimeException('Invalid bucket image key.');return bucket_request('DELETE',$key); }
 if(!str_starts_with($path,'uploads/'))return false;
 $root=realpath(dirname(__DIR__).'/uploads');$file=realpath(dirname(__DIR__).'/'.$path);
 if(!$root||!$file||!str_starts_with($file,$root.DIRECTORY_SEPARATOR)||!is_file($file))return false;
 if(!unlink($file))throw new RuntimeException('Uploaded image could not be deleted.');
 return true;
}
