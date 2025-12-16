<?php
// Simple DB health check for both DDEV and Platform.sh, independent of YOURLS.
header('Content-Type: text/plain; charset=UTF-8');

$host = null;
$db   = null;
$user = null;
$pass = null;

// 1. Check if we're on Platform.sh (PLATFORM_RELATIONSHIPS is set).
$relationshipsJson = getenv('PLATFORM_RELATIONSHIPS');

if ($relationshipsJson) {
    $relationships = json_decode(base64_decode($relationshipsJson), true);

    if (json_last_error() === JSON_ERROR_NONE && isset($relationships['database'][0])) {
        $dbConfig = $relationships['database'][0];

        $host = $dbConfig['host'] ?? null;
        $db   = $dbConfig['path'] ?? null;   // 'path' is the DB name on Platform.sh
        $user = $dbConfig['username'] ?? null;
        $pass = $dbConfig['password'] ?? null;
    }
}

// 2. If Platform.sh detection failed or missing info, fall back to DDEV defaults.
if (!$host || !$db || !$user) {
    $host = 'db';
    $db   = 'db';
    $user = 'db';
    $pass = 'db';
}

$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_TIMEOUT            => 3,
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    // Simple query to validate the connection.
    $stmt = $pdo->query('SELECT 1');
    $stmt->fetch();

    http_response_code(200);
    echo "OK - Database Connected\n";
} catch (Throwable $e) {
    http_response_code(503);
    echo "Error - Database Connection Failed\n";

    // Optional: log details for debugging (they won't be visible to the health checker client).
    // error_log('Healthcheck DB error: ' . $e->getMessage());
}
