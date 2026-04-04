<?php
/**
 * VisionX — Gallery Upload Diagnostics
 * File: admin/api/gallery_debug.php
 *
 * DELETE THIS FILE after you've confirmed uploads are working.
 * Access: GET /admin/api/gallery_debug.php
 */

// Basic protection — only show to logged-in admins
// Uncomment once session is wired up:
// session_start();
// if (empty($_SESSION['vx_admin_logged_in'])) { http_response_code(403); exit('Forbidden'); }

header('Content-Type: application/json');

function projectRoot(): string
{
    return dirname(__DIR__, 2);
}

$uploadDir   = projectRoot() . '/visionx/assets/images/gallery/';
$docRoot     = $_SERVER['DOCUMENT_ROOT'] ?? '(not set)';
$scriptFile  = __FILE__;
$dirExists   = is_dir($uploadDir);
$dirWritable = $dirExists && is_writable($uploadDir);

// Try to create the directory if it doesn't exist
$mkdirResult = null;
if (!$dirExists) {
    $mkdirResult = mkdir($uploadDir, 0775, true) ? 'created OK' : 'mkdir FAILED';
    $dirExists   = is_dir($uploadDir);
    $dirWritable = $dirExists && is_writable($uploadDir);
}

// php.ini upload limits
$iniUpload  = ini_get('upload_max_filesize');
$iniPost    = ini_get('post_max_size');
$iniTmp     = ini_get('upload_tmp_dir') ?: sys_get_temp_dir();
$tmpWritable = is_writable($iniTmp);

// Try writing a test file to the gallery folder
$writeTest = null;
if ($dirWritable) {
    $testFile = $uploadDir . 'write_test_' . time() . '.txt';
    if (file_put_contents($testFile, 'ok') !== false) {
        unlink($testFile);
        $writeTest = 'PASSED — file written and deleted';
    } else {
        $writeTest = 'FAILED — file_put_contents returned false';
    }
} else {
    $writeTest = 'SKIPPED — directory not writable';
}

echo json_encode([
    'paths' => [
        'this_file'        => $scriptFile,
        'project_root'     => projectRoot(),
        'upload_dir'       => $uploadDir,
        'document_root'    => $docRoot,
    ],
    'directory' => [
        'exists'           => $dirExists,
        'writable'         => $dirWritable,
        'mkdir_attempted'  => $mkdirResult,
    ],
    'write_test'           => $writeTest,
    'php_ini' => [
        'upload_max_filesize' => $iniUpload,
        'post_max_size'       => $iniPost,
        'upload_tmp_dir'      => $iniTmp,
        'tmp_writable'        => $tmpWritable,
    ],
    'action' => 'DELETE this file after confirming uploads work.',
], JSON_PRETTY_PRINT);