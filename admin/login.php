<?php
/**
 * VisionX Admin — Login
 * File: admin/login.php
 */
session_start();
if (!empty($_SESSION['admin'])) { header('Location: index.php'); exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login — VisionX Repairs</title>
  <meta name="robots" content="noindex,nofollow">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="login-wrap">
  <div class="login-card">
    <div class="login-logo">
      <div class="logo-icon">V</div>
      <div>
        <div class="brand">Vision<span>X</span></div>
        <small>Admin Panel</small>
      </div>
    </div>

    <div class="login-head">Welcome back 👋</div>
    <p class="login-sub">Sign in to manage your website</p>

    <div class="login-err" id="err"></div>

    <div class="form-group">
      <label class="label">Username</label>
      <input class="input" id="usr" type="text" placeholder="admin" autocomplete="username">
    </div>
    <div class="form-group">
      <label class="label">Password</label>
      <input class="input" id="pw" type="password" placeholder="••••••••" autocomplete="current-password">
    </div>

    <button class="btn-login" id="login-btn" onclick="doLogin()">Sign In →</button>
    <p style="text-align:center;font-size:12px;color:rgba(255,255,255,.25);margin-top:14px;">
      Default: admin / visionx2025
    </p>

    <!-- Debug info box — remove in production -->
    <div id="debug-box" style="display:none;margin-top:16px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.1);border-radius:8px;padding:14px;font-size:11px;font-family:monospace;color:rgba(255,255,255,.5);word-break:break-all;line-height:1.8;"></div>
  </div>
</div>

<div class="toasts" id="toasts"></div>

<script>
  const DEBUG = false;

document.querySelectorAll('#usr,#pw').forEach(el =>
  el.addEventListener('keydown', e => { if(e.key==='Enter') doLogin(); })
);

// Detect the correct API base path from current page URL
function getApiBase() {
  // If URL is  /admin/login  or  /admin/login.php  → base is /admin/api
  // Works regardless of whether .htaccess is hiding extensions or not
  const path = window.location.pathname;           // e.g. /admin/login or /admin/login.php
  const dir  = path.substring(0, path.lastIndexOf('/') + 1); // e.g. /admin/
  return dir + 'api';                              // e.g. /admin/api
}

function showDebug(info) {
  if (!DEBUG) return;

  const box = document.getElementById('debug-box');
  box.style.display = 'block';
  box.innerHTML = Object.entries(info).map(([k,v]) =>
    `<div><b style="color:rgba(255,255,255,.7)">${k}:</b> ${v}</div>`
  ).join('');
}

async function doLogin() {
  const usr = document.getElementById('usr').value.trim();
  const pw  = document.getElementById('pw').value;
  const err = document.getElementById('err');
  const btn = document.getElementById('login-btn');

  err.classList.remove('show');
  if (!usr || !pw) {
    err.textContent = 'Enter username and password.';
    err.classList.add('show');
    return;
  }

  btn.disabled = true;
  btn.textContent = 'Signing in…';

  const apiUrl = getApiBase() + '/login.php';

  showDebug({
    'Page URL':    window.location.href,
    'API URL':     apiUrl,
    'Method':      'POST',
  });

  try {
    const r = await fetch(apiUrl, {
      method:      'POST',
      credentials: 'same-origin',
      headers:     { 'Content-Type': 'application/json' },
      body:        JSON.stringify({ username: usr, password: pw })
    });

    showDebug({
      'Page URL':    window.location.href,
      'API URL':     apiUrl,
      'HTTP Status': r.status + ' ' + r.statusText,
      'Content-Type': r.headers.get('content-type') || 'none',
    });

    // Check if response is actually JSON before parsing
    const contentType = r.headers.get('content-type') || '';
    if (!contentType.includes('application/json')) {
      const text = await r.text();
      showDebug({
        'Page URL':     window.location.href,
        'API URL':      apiUrl,
        'HTTP Status':  r.status + ' ' + r.statusText,
        'Error':        'Server returned non-JSON response',
        'Raw response': text.substring(0, 300),
      });
      err.textContent = 'Server error — see debug info below.';
      err.classList.add('show');
      btn.disabled = false;
      btn.textContent = 'Sign In →';
      return;
    }

    const d = await r.json();

    if (d.ok) {
      window.location.href = 'index.php';
    } else {
      err.textContent = d.error || 'Incorrect username or password.';
      err.classList.add('show');
      btn.disabled = false;
      btn.textContent = 'Sign In →';
    }

  } catch (e) {
    showDebug({
      'Page URL':  window.location.href,
      'API URL':   apiUrl,
      'JS Error':  e.message || String(e),
      'Tip':       'Check browser console (F12) for more details',
    });
    err.textContent = 'Request failed: ' + (e.message || 'Network error');
    err.classList.add('show');
    btn.disabled = false;
    btn.textContent = 'Sign In →';
  }
}
</script>
</body>
</html>