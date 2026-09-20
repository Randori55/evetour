<?php
require __DIR__.'/includes/functions.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: index.php#contact'); exit; }
$name=trim((string)($_POST['name']??'')); $phone=trim((string)($_POST['phone']??'')); $message=trim((string)($_POST['message']??''));
$locale=strtolower((string)($_POST['lang']??'en')); if(!array_key_exists($locale,supported_locales())) $locale='en';
$phoneDigits=preg_replace('/\D+/', '', $phone);
if($name==='' || strlen($phoneDigits)<7 || strlen($phoneDigits)>15 || $message===''){ header('Location:index.php?lang='.rawurlencode($locale).'&error=contact#contact'); exit; }
$pdo->exec('CREATE TABLE IF NOT EXISTS contact_messages (id INT AUTO_INCREMENT PRIMARY KEY,name VARCHAR(150),phone VARCHAR(30),message TEXT,locale VARCHAR(10),created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)');
try{$pdo->exec('ALTER TABLE contact_messages ADD COLUMN phone VARCHAR(30) NULL');}catch(Throwable $ignored){} try{$pdo->exec('ALTER TABLE contact_messages ADD COLUMN locale VARCHAR(10) NULL');}catch(Throwable $ignored){}
$pdo->prepare('INSERT INTO contact_messages(name,phone,message,locale) VALUES(?,?,?,?)')->execute([$name,'+'.$phoneDigits,$message,$locale]);
$whatsappText="New EVE TOUR request\n\nName: {$name}\nPhone: +{$phoneDigits}\nMessage: {$message}";
header('Location: https://wa.me/994556458369?text='.rawurlencode($whatsappText),true,303); exit;
