<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/core/DatabaseSchema.php';

// Database configuration.
// Defaults target the local XAMPP MySQL used on the shop's LAN box.
// Any value can be overridden by environment variable so credentials do not
// have to live in a tracked file when this is deployed elsewhere.
$host     = getenv('DB_HOST') ?: '127.0.0.1';
$dbname   = getenv('DB_NAME') ?: 'dinewithdin';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';

// Base URL — derived from the current request so the same code runs on a LAN
// box, on localhost, and behind any domain without editing anything.
// Set APP_BASE_URL in the environment to override (e.g. behind a proxy).
$baseUrl = getenv('APP_BASE_URL') ?: '';
if ($baseUrl === '') {
    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    $baseUrl = $script !== ''
        ? rtrim(strtr(dirname($script), chr(92), '/'), '/')
        : '';
}

$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbSchemaSync = ['ran' => false, 'changes' => [], 'error' => null];

    try {
        $dbSchemaSync = dbs_sync_database_schema_if_needed($pdo);
    } catch (Throwable $schemaError) {
        $dbSchemaSync = [
            'ran'     => false,
            'changes' => [],
            'error'   => $schemaError->getMessage(),
        ];
        error_log('[db schema sync] ' . $schemaError->getMessage());
    }
} catch (PDOException $e) {
    error_log('Database connection failed: ' . $e->getMessage());
    die('Database connection error. Please check your configuration or contact the administrator.');
}
