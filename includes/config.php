<?php
$databaseUrl = getenv('DATABASE_URL');
$databaseParts = $databaseUrl ? parse_url($databaseUrl) : false;

$host = ($databaseParts['host'] ?? null) ?: getenv('MYSQLHOST') ?: getenv('DB_HOST') ?: '127.0.0.1';
$db   = (isset($databaseParts['path']) ? ltrim($databaseParts['path'], '/') : '') ?: getenv('MYSQLDATABASE') ?: getenv('DB_NAME') ?: 'travel_site';
$user = (isset($databaseParts['user']) ? rawurldecode($databaseParts['user']) : '') ?: getenv('MYSQLUSER') ?: getenv('DB_USER') ?: 'root';
$pass = (isset($databaseParts['pass']) ? rawurldecode($databaseParts['pass']) : '') ?: getenv('MYSQLPASSWORD') ?: getenv('DB_PASS') ?: '';
$port = ($databaseParts['port'] ?? null) ?: getenv('MYSQLPORT') ?: getenv('DB_PORT') ?: '3306';
$charset = 'utf8mb4';
$dsn = "mysql:host={$host};port={$port};dbname={$db};charset={$charset}";
$options = [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES=>false];
try { $pdo = new PDO($dsn, $user, $pass, $options); } catch (PDOException $e) { http_response_code(500); exit('Database connection failed. Check Railway MySQL variables.'); }
if (session_status() === PHP_SESSION_NONE) session_start();