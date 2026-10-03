<?php
require __DIR__.'/includes/config.php';
$installed = $pdo->query("SHOW TABLES LIKE 'settings'")->fetchColumn();
if ($installed) {
    http_response_code(410);
    exit('Installation has already run.');
}

$sql=file_get_contents(__DIR__.'/schema.sql');
try { $pdo->exec($sql); $hash=password_hash('admin123',PASSWORD_DEFAULT); $st=$pdo->prepare('INSERT INTO admins(username,password_hash) VALUES (?,?) ON DUPLICATE KEY UPDATE username=username'); $st->execute(['admin',$hash]); echo '<h1>Installation complete</h1><p>Change the default admin password after first login.</p><p><a href="admin/login.php">Open admin panel</a></p>'; } catch(Throwable $e){ http_response_code(500); echo '<h1>Install failed</h1><pre>'.htmlspecialchars($e->getMessage()).'</pre>'; }
