<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('APP_ROOT', dirname(__DIR__));
define('UPLOAD_DIR', APP_ROOT . '/uploads/');

$docRoot = str_replace('\\', '/', rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/'));
$appRoot = str_replace('\\', '/', APP_ROOT);
$base    = ($docRoot && strpos($appRoot, $docRoot) === 0)
    ? substr($appRoot, strlen($docRoot))
    : '';
define('BASE_URL', rtrim($base, '/'));

require_once __DIR__ . '/database.php';
require_once APP_ROOT . '/includes/functions.php';