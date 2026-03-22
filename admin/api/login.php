<?php
/**
 * VisionX Admin — Login API
 * File: admin/api/login.php
 * Method: POST  |  Body: { username, password }
 * Returns: { ok, name } or { ok: false, error }
 */
require_once __DIR__ . '/db.php';
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') fail('Method not allowed', 405);

$b    = body();
$user = trim($b['username'] ?? '');
$pass = $b['password'] ?? '';

if (!$user || !$pass) fail('Username and password are required.');

try {
    $row = db()->prepare('SELECT id, name, password_hash FROM admin_users WHERE username = ? LIMIT 1');
    $row->execute([$user]);
    $row = $row->fetch();

    if (!$row || !password_verify($pass, $row['password_hash'])) {
        fail('Incorrect username or password.');
    }

    session_regenerate_id(true);
    $_SESSION['admin'] = ['id' => $row['id'], 'name' => $row['name']];
    ok(['name' => $row['name']]);

} catch (PDOException $e) {
    error_log($e->getMessage());
    fail('Server error. Check DB credentials in api/db.php.', 500);
}