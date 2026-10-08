<?php
require __DIR__.'/includes/functions.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: index.php#contact'); exit; }
$name=trim((string)($_POST['name']??''));
$message=trim((string)($_POST['message']??''));
$locale=strtolower((string)($_POST['lang']??'en'));
if(!array_key_exists($locale,supported_locales())) $locale='en';
$nameCharacters=preg_split('//u',$name,-1,PREG_SPLIT_NO_EMPTY);
if($nameCharacters===false){ header('Location:index.php?lang='.rawurlencode($locale).'&error=contact#contact'); exit; }
$name=implode('',array_slice($nameCharacters,0,150));
if($name==='' || $message===''){ header('Location:index.php?lang='.rawurlencode($locale).'&error=contact#contact'); exit; }
$pdo->exec('CREATE TABLE IF NOT EXISTS contact_messages (id INT AUTO_INCREMENT PRIMARY KEY,name VARCHAR(150),phone VARCHAR(30),message TEXT,locale VARCHAR(10),created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)');
try{$pdo->exec('ALTER TABLE contact_messages ADD COLUMN phone VARCHAR(30) NULL');}catch(Throwable $ignored){}
try{$pdo->exec('ALTER TABLE contact_messages ADD COLUMN locale VARCHAR(10) NULL');}catch(Throwable $ignored){}
$pdo->prepare('INSERT INTO contact_messages(name,phone,message,locale) VALUES(?,?,?,?)')->execute([$name,'',$message,$locale]);
$whatsappText="New EVE TOUR request\n\nName: {$name}\nMessage: {$message}";
$whatsappPhone=preg_replace('/\D+/', '', (string)setting('phone','994556458369'));
if($whatsappPhone==='') $whatsappPhone='994556458369';
$whatsappTextEncoded=rawurlencode($whatsappText);
$isMobile=preg_match('/Android|iPhone|iPad|iPod/i',(string)($_SERVER['HTTP_USER_AGENT']??''))===1 || ($_POST['device']??'')==='mobile';
$whatsappUrl=$isMobile
    ? 'whatsapp://send?phone='.$whatsappPhone.'&text='.$whatsappTextEncoded
    : 'https://web.whatsapp.com/send?phone='.$whatsappPhone.'&text='.$whatsappTextEncoded;
header('Location: '.$whatsappUrl,true,303);
exit;
