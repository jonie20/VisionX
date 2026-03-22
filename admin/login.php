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
      Default: admin / visionx2025 — change after first login
    </p>
  </div>
</div>

<div class="toasts" id="toasts"></div>

<script>
document.querySelectorAll('#usr,#pw').forEach(el =>
  el.addEventListener('keydown', e => { if(e.key==='Enter') doLogin(); })
);

async function doLogin() {
  const usr = document.getElementById('usr').value.trim();
  const pw  = document.getElementById('pw').value;
  const err = document.getElementById('err');
  const btn = document.getElementById('login-btn');

  err.classList.remove('show');
  if (!usr || !pw) { err.textContent='Enter username and password.'; err.classList.add('show'); return; }

  btn.disabled = true; btn.textContent = 'Signing in…';

  try {
    const r = await fetch('api/login.php', {
      method:'POST', credentials:'same-origin',
      headers:{'Content-Type':'application/json'},
      body: JSON.stringify({ username:usr, password:pw })
    });
    const d = await r.json();
    if (d.ok) {
      window.location.href = 'index.php';
    } else {
      err.textContent = d.error || 'Incorrect username or password.';
      err.classList.add('show');
      btn.disabled = false; btn.textContent = 'Sign In →';
    }
  } catch {
    err.textContent = 'Connection error. Is the server running?';
    err.classList.add('show');
    btn.disabled = false; btn.textContent = 'Sign In →';
  }
}
</script>
</body>
</html>