<?php
/**
 * VisionX Admin — Logout API
 * File: admin/api/logout.php  |  Method: POST
 */
session_start();
session_destroy();
header('Content-Type: application/json');
echo json_encode(['ok' => true]);