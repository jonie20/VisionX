<?php
/**
 * VisionX Admin — Database & Shared Helpers
 * File: admin/api/db.php
 * Include at top of every API file: require_once __DIR__ . '/db.php';
 */

// ── DB CREDENTIALS ──────────────────────────────────
// Change these to match your cPanel MySQL database
define('DB_HOST', 'localhost');
define('DB_NAME', 'visionx_db');     // your database name
define('DB_USER', 'root');   // your database user
define('DB_PASS', '');  // your database password
define('DB_CHARSET', 'utf8mb4');
// ────────────────────────────────────────────────────

function db(): PDO {
    static $pdo;
    if (!$pdo) {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
            DB_USER, DB_PASS,
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]
        );
    }
    return $pdo;
}

// ── JSON RESPONSES ───────────────────────────────────
function ok(array $extra = []): void {
    header('Content-Type: application/json');
    echo json_encode(['ok' => true] + $extra);
    exit;
}
function fail(string $msg, int $code = 400): void {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode(['ok' => false, 'error' => $msg]);
    exit;
}

// ── AUTH GUARD ───────────────────────────────────────
function guard(): void {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['admin'])) fail('Not authenticated', 401);
}

// ── PARSE JSON BODY ──────────────────────────────────
function body(): array {
    $raw = file_get_contents('php://input');
    return json_decode($raw, true) ?? [];
}

// ── UPLOAD IMAGE ─────────────────────────────────────
// Saves uploaded file, returns relative path or null
function saveImage(string $field, string $prefix = 'img'): ?string {
    if (empty($_FILES[$field]) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) return null;

    $file    = $_FILES[$field];
    $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];

    if (!in_array($ext, $allowed))       fail('Invalid file type. Use JPG, PNG or WebP.');
    if ($file['size'] > 3 * 1024 * 1024) fail('File too large. Max 3 MB.');

    $dir  = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/assets/images/gallery/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);

    $name = $prefix . '-' . time() . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
    move_uploaded_file($file['tmp_name'], $dir . $name);
    return 'assets/images/gallery/' . $name;
}