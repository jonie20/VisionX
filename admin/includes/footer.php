<?php
/**
 * VISIONX ADMIN — SHARED FOOTER
 * File: admin/includes/footer.php
 * Usage: include at bottom of every admin PHP page before </body>
 */
?>
  </div><!-- /page-content -->
</main>
</div><!-- /admin-layout -->

<!-- ══ SHARED CONFIRM DIALOG ══ -->
<div class="confirm-overlay" id="confirm-overlay">
  <div class="confirm-box">
    <div class="confirm-icon">⚠️</div>
    <div class="confirm-title" id="confirm-title">Are you sure?</div>
    <p class="confirm-msg" id="confirm-msg">This action cannot be undone.</p>
    <div class="confirm-actions">
      <button class="btn btn-ghost" onclick="closeConfirm()">Cancel</button>
      <button class="btn btn-danger" id="confirm-ok">Delete</button>
    </div>
  </div>
</div>

<!-- ══ TOAST CONTAINER ══ -->
<div class="toast-wrap" id="toast-wrap"></div>

<!-- ══ SHARED JS ══ -->
<script src="/admin/js/shared.js"></script>
<script>
// PHP logout — destroy session server-side
function doLogout() {
  fetch('/admin/api/logout.php', { method: 'POST', credentials: 'same-origin' })
    .catch(() => {})
    .finally(() => { window.location.href = '/admin/login.php'; });
}
// Load badge counts from API
async function loadBadges() {
  try {
    const r = await vxApi('counts.php');
    if (!r) return;
    ['gallery','reviews','blog'].forEach(k => {
      const el = document.getElementById('badge-' + k);
      if (el && r[k] !== undefined) el.textContent = r[k];
    });
  } catch {}
}
loadBadges();
</script>
<?php if (isset($extraJs)): ?>
<script src="/admin/js/<?= htmlspecialchars($extraJs) ?>.js"></script>
<?php endif; ?>
</body>
</html>