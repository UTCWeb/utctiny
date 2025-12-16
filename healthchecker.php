<?php
// Simple YOURLS DB + schema health check for both DDEV and Platform.sh.
header('Content-Type: text/plain; charset=UTF-8');

// YOURLS table prefix (must match your config).
$tablePrefix = 'yourls_';

// Resolve DB connection settings.

// DDEV defaults.
$host = 'db';
$db   = 'db';
$user = 'db';
$pass = 'db';

// 1. Try to override with Platform.sh relationship if present.
$relationshipsJson = getenv('PLATFORM_RELATIONSHIPS');
if ($relationshipsJson) {
    $relationships = json_decode(base64_decode($relationshipsJson), true);

    if (json_last_error() === JSON_ERROR_NONE && isset($relationships['database'][0])) {
        $dbConfig = $relationships['database'][0];

        $host = $dbConfig['host']     ?? $host;
        $db   = $dbConfig['path']     ?? $db;   // 'path' is the DB name
        $user = $dbConfig['username'] ?? $user;
        $pass = $dbConfig['password'] ?? $pass;
    }
}

$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_TIMEOUT            => 3,
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    // 2. Basic connectivity check.
    $stmt = $pdo->query('SELECT 1');
    $stmt->fetch();

    // 3. Lightweight YOURLS schema check: ensure yourls_options exists and is usable.
    $optionsTable = $tablePrefix . 'options';
    $stmt = $pdo->query("SELECT 1 FROM `$optionsTable` LIMIT 1");
    $stmt->fetch();

    http_response_code(200);
    echo "OK - Database & YOURLS Schema Healthy\n";
} catch (Throwable $e) {
    http_response_code(503);
    echo "Error - Database or YOURLS Schema Problem\n";

    // Optional logging for debugging:
    // error_log('Healthcheck error: ' . $e->getMessage());
}
