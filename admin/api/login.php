<?php
/**
 * VisionX Admin — Login API
 * File: admin/api/login.php
 * Method: POST | Body: { username, password }
 */

// Show PHP errors in response during debugging — remove in production
ini_set('display_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: same-origin');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200); exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed. Got: ' . $_SERVER['REQUEST_METHOD']]);
    exit;
}

// Try to load DB
$dbFile = __DIR__ . '/db.php';
if (!file_exists($dbFile)) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'db.php not found at: ' . $dbFile]);
    exit;
}

require_once $dbFile;

session_start();

$raw  = file_get_contents('php://input');
$b    = json_decode($raw, true);

if (!$b) {
    echo json_encode(['ok' => false, 'error' => 'Invalid JSON body. Received: ' . substr($raw, 0, 100)]);
    exit;
}

$user = trim($b['username'] ?? '');
$pass = $b['password'] ?? '';

if (!$user || !$pass) {
    echo json_encode(['ok' => false, 'error' => 'Username and password are required.']);
    exit;
}

try {
    $stmt = db()->prepare('SELECT id, name, password_hash FROM admin_users WHERE username = ? LIMIT 1');
    $stmt->execute([$user]);
    $row = $stmt->fetch();

    if (!$row) {
        echo json_encode(['ok' => false, 'error' => 'Incorrect username or password.']);
        exit;
    }

    if (!password_verify($pass, $row['password_hash'])) {
        echo json_encode(['ok' => false, 'error' => 'Incorrect username or password.']);
        exit;
    }

    session_regenerate_id(true);
    $_SESSION['admin'] = ['id' => $row['id'], 'name' => $row['name']];

    echo json_encode(['ok' => true, 'name' => $row['name']]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'ok'    => false,
        'error' => 'Database error: ' . $e->getMessage() .
                   ' | Check DB credentials in api/db.php'
    ]);
}