<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/core/DatabaseSchema.php';

use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__ . '/');
$dotenv->load();

$host = $_ENV['DB_HOST'];
$dbname = $_ENV['DB_NAME'];
$username = $_ENV['DB_USER'];
$password = $_ENV['DB_PASS'];
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
    die('Connection failed: ' . $e->getMessage());
}
