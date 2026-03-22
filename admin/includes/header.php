<?php
/**
 * VISIONX ADMIN — SHARED HEADER
 * File: admin/includes/header.php
 * Usage: include at top of every admin PHP page
 *
 * PHP Integration:
 *   require_once __DIR__ . '/header.php';
 *   // Pass $pageTitle and $activePage before including
 */

// ── Session check ──
// session_start();
// if (!isset($_SESSION['vx_admin_logged_in']) || $_SESSION['vx_admin_logged_in'] !== true) {
//     header('Location: /visionX/admin/login.php');
//     exit;
// }

$pageTitle  = $pageTitle  ?? 'Dashboard';
$activePage = $activePage ?? 'dashboard';
$adminUser  = $_SESSION['vx_admin_user'] ?? ['name' => 'Admin', 'role' => 'Super Admin'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle) ?> — VisionX Admin</title>
  <meta name="robots" content="noindex, nofollow">
  <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css"
        integrity="sha384-AYmEC3Yw5cVb3ZcuHtOA93w35dYTsvhLPVnYs9eStHfGJvOvKxVfELGroGkvsg+p"
        crossorigin="anonymous"/>
  <link rel="stylesheet" href="css/admin.css">
</head>
<body>
<div class="admin-layout">

<!-- ══════════════ SIDEBAR ══════════════ -->
<aside class="sidebar" id="sidebar">
  <div class="sidebar-head">
    <div class="s-mark">V</div>
    <span class="s-brand">Vision<span>X</span></span>
    <span class="s-badge">Admin</span>
  </div>

  <nav class="sidebar-nav">
    <div class="nav-section-label">Main</div>
    <a href="index.php"
       class="nav-item <?= $activePage === 'dashboard' ? 'active' : '' ?>"
       data-page="index">
      <span class="ni-icon">📊</span> Dashboard
    </a>
    <a href="gallery.php"
       class="nav-item <?= $activePage === 'gallery' ? 'active' : '' ?>"
       data-page="gallery">
      <span class="ni-icon">🖼️</span> Gallery
      <span class="nav-badge" id="badge-gallery">0</span>
    </a>
    <a href="reviews.php"
       class="nav-item <?= $activePage === 'reviews' ? 'active' : '' ?>"
       data-page="reviews">
      <span class="ni-icon">⭐</span> Reviews
      <span class="nav-badge" id="badge-reviews">0</span>
    </a>
    <a href="/admin/blog.php"
       class="nav-item <?= $activePage === 'blog' ? 'active' : '' ?>"
       data-page="blog">
      <span class="ni-icon">✍️</span> Blog Posts
      <span class="nav-badge" id="badge-blog">0</span>
    </a>

    <div class="nav-section-label">Content</div>
    <a href="/admin/areas.php"
       class="nav-item <?= $activePage === 'areas' ? 'active' : '' ?>"
       data-page="areas">
      <span class="ni-icon">📍</span> Areas &amp; Brands
    </a>
    <a href="/admin/services.php"
       class="nav-item <?= $activePage === 'services' ? 'active' : '' ?>"
       data-page="services">
      <span class="ni-icon">🔧</span> Services
    </a>
    <a href="/admin/media.php"
       class="nav-item <?= $activePage === 'media' ? 'active' : '' ?>"
       data-page="media">
      <span class="ni-icon">📁</span> Media Library
    </a>
    <a href="faqs.php"
       class="nav-item <?= $activePage === 'faqs' ? 'active' : '' ?>"
       data-page="faqs">
      <span class="ni-icon">❓</span> FAQs
    </a>

    <div class="nav-section-label">System</div>
    <a href="settings.php"
       class="nav-item <?= $activePage === 'settings' ? 'active' : '' ?>"
       data-page="settings">
      <span class="ni-icon">⚙️</span> Site Settings
    </a>
    <a href="/admin/activity.php"
       class="nav-item <?= $activePage === 'activity' ? 'active' : '' ?>"
       data-page="activity">
      <span class="ni-icon">🕐</span> Activity Log
    </a>
    <a href="/" target="_blank" class="nav-item" data-page="">
      <span class="ni-icon">🌐</span> View Site
    </a>
  </nav>

  <div class="sidebar-footer">
    <div class="sidebar-user">
      <div class="su-avatar" id="sb-avatar">
        <?= strtoupper(substr($adminUser['name'], 0, 1)) ?>
      </div>
      <div class="su-info">
        <div class="su-name" id="sb-user-name"><?= htmlspecialchars($adminUser['name']) ?></div>
        <div class="su-role" id="sb-user-role"><?= htmlspecialchars($adminUser['role']) ?></div>
      </div>
      <button class="su-logout" onclick="doLogout()" title="Sign Out">⏻</button>
    </div>
  </div>
</aside>

<!-- ══════════════ MAIN AREA ══════════════ -->
<main class="admin-main">
  <!-- Top Header -->
  <header class="admin-header">
    <button class="hamburger-admin" id="ham-btn" aria-label="Toggle sidebar">☰</button>
    <div>
      <div class="header-page-title"><?= htmlspecialchars($pageTitle) ?></div>
      <div class="header-breadcrumb">VisionX Admin › <?= htmlspecialchars($pageTitle) ?></div>
    </div>
    <div class="header-actions">
      <a href="/" target="_blank" class="btn btn-ghost btn-sm">
        <i class="fas fa-external-link-alt"></i> View Site
      </a>
    </div>
  </header>

  <!-- Page body starts here -->
  <div class="page-content">