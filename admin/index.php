<?php
/**
 * VisionX Admin — Main Dashboard
 * File: admin/index.php
 * Manages: Gallery · Reviews · FAQs
 */
session_start();
if (empty($_SESSION['admin'])) { header('Location: login.php'); exit; }
$admin = $_SESSION['admin'];

// Quick counts from DB for the stat cards
$counts = ['gallery' => 0, 'reviews' => 0, 'faqs' => 0, 'pending' => 0];
try {
    require_once __DIR__ . '/api/db.php';
    $pdo = db();
    $counts['gallery'] = $pdo->query('SELECT COUNT(*) FROM gallery WHERE active=1')->fetchColumn();
    $counts['reviews'] = $pdo->query('SELECT COUNT(*) FROM reviews')->fetchColumn();
    $counts['faqs']    = $pdo->query('SELECT COUNT(*) FROM faqs WHERE active=1')->fetchColumn();
    $counts['pending'] = $pdo->query("SELECT COUNT(*) FROM reviews WHERE status='pending'")->fetchColumn();
} catch (Exception $e) {
    // DB not connected yet — show zeros, page still works
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard — VisionX Repairs</title>
  <meta name="robots" content="noindex,nofollow">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="layout">

<!-- ══ SIDEBAR ══ -->
<aside class="sidebar" id="sidebar">
  <div class="sidebar-logo">
    <div class="logo-icon">V</div>
    <span class="logo-text">Vision<span>X</span> <small style="font-size:10px;font-weight:600;color:rgba(255,255,255,.35);display:block;margin-top:-2px;">Admin Panel</small></span>
  </div>

  <nav class="sidebar-nav">
    <div class="nav-label">Manage</div>
    <button class="nav-link active" data-tab="gallery" onclick="switchTab('gallery',this)">
      <span class="nav-icon">🖼️</span> Gallery
    </button>
    <button class="nav-link" data-tab="reviews" onclick="switchTab('reviews',this)">
      <span class="nav-icon">⭐</span> Reviews
      <?php if($counts['pending']>0): ?>
      <span class="badge badge-yellow" style="margin-left:auto;"><?= $counts['pending'] ?></span>
      <?php endif; ?>
    </button>
    <button class="nav-link" data-tab="faqs" onclick="switchTab('faqs',this)">
      <span class="nav-icon">❓</span> FAQs
    </button>

    <div class="nav-label" style="margin-top:10px;">Site</div>
    <a class="nav-link" href="/" target="_blank">
      <span class="nav-icon">🌐</span> View Website
    </a>
  </nav>

  <div class="sidebar-bottom">
    <div class="user-row">
      <div class="user-avatar"><?= strtoupper(substr($admin['name'],0,1)) ?></div>
      <div>
        <div class="user-name"><?= htmlspecialchars($admin['name']) ?></div>
        <div class="user-role">Administrator</div>
      </div>
      <button class="btn-logout" onclick="doLogout()" title="Logout">⏻</button>
    </div>
  </div>
</aside>

<!-- ══ MAIN ══ -->
<div class="main">

  <!-- Top bar -->
  <div class="topbar">
    <button class="ham" id="ham" onclick="toggleSidebar()">☰</button>
    <div class="topbar-title" id="page-title">Gallery</div>
    <div class="topbar-right">
      <button class="btn btn-primary" id="add-btn" onclick="openAdd()">+ Add New</button>
    </div>
  </div>

  <div class="content">

    <!-- Stat cards -->
    <div class="stats">
      <div class="stat" onclick="switchTab('gallery',document.querySelector('[data-tab=gallery]'))" style="cursor:pointer;">
        <div class="stat-num"><?= $counts['gallery'] ?></div>
        <div class="stat-lbl">Gallery Photos</div>
      </div>
      <div class="stat" onclick="switchTab('reviews',document.querySelector('[data-tab=reviews]'))" style="cursor:pointer;">
        <div class="stat-num"><?= $counts['reviews'] ?></div>
        <div class="stat-lbl">Reviews <?php if($counts['pending']>0): ?><span class="badge badge-yellow"><?= $counts['pending'] ?> pending</span><?php endif; ?></div>
      </div>
      <div class="stat" onclick="switchTab('faqs',document.querySelector('[data-tab=faqs]'))" style="cursor:pointer;">
        <div class="stat-num"><?= $counts['faqs'] ?></div>
        <div class="stat-lbl">FAQs</div>
      </div>
    </div>

    <!-- ════════════════ GALLERY TAB ════════════════ -->
    <div id="tab-gallery">
      <div class="card">
        <div class="card-head">
          <span class="card-title">🖼️ Gallery — Before &amp; After Repairs</span>
          <input class="input" id="gallery-search" placeholder="Search…" oninput="searchTable('gallery-tbody',this.value)"
                 style="width:200px;padding:6px 11px;font-size:13px;">
        </div>
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>Photo</th>
                <th>Title</th>
                <th>Brand</th>
                <th>Area</th>
                <th>Type</th>
                <th>Status</th>
                <th>Visible</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="gallery-tbody">
              <tr><td colspan="8" style="text-align:center;padding:32px;color:rgba(255,255,255,.3);">Loading…</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ════════════════ REVIEWS TAB ════════════════ -->
    <div id="tab-reviews" style="display:none;">
      <div id="pending-alert" class="alert alert-warn" style="display:none;">
        ⚠️ You have pending reviews waiting for approval — see the table below.
      </div>
      <div class="card">
        <div class="card-head">
          <span class="card-title">⭐ Customer Reviews</span>
          <select class="input" id="review-filter" onchange="filterReviews()" style="width:140px;padding:6px 11px;font-size:13px;">
            <option value="">All statuses</option>
            <option value="approved">Approved</option>
            <option value="pending">Pending</option>
            <option value="hidden">Hidden</option>
          </select>
        </div>
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>Reviewer</th>
                <th>Area</th>
                <th>Rating</th>
                <th>Review</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="reviews-tbody">
              <tr><td colspan="6" style="text-align:center;padding:32px;color:rgba(255,255,255,.3);">Loading…</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ════════════════ FAQS TAB ════════════════ -->
    <div id="tab-faqs" style="display:none;">
      <div class="alert alert-ok">
        💡 FAQs generate <strong>FAQ Schema</strong> that helps VisionX appear in Google rich results. Keep them keyword-rich.
      </div>
      <div class="card">
        <div class="card-head">
          <span class="card-title">❓ FAQs — Drag to reorder</span>
          <button class="btn btn-primary" onclick="saveFaqs()">💾 Save All FAQs</button>
        </div>
        <div class="card-body" style="padding:10px 20px;" id="faq-list">
          <div style="text-align:center;padding:32px;color:rgba(255,255,255,.3);">Loading…</div>
        </div>
      </div>
    </div>

  </div><!-- /content -->
</div><!-- /main -->
</div><!-- /layout -->

<!-- ══ GALLERY MODAL ══ -->
<div class="overlay" id="gallery-modal">
  <div class="modal">
    <div class="modal-head">
      <div class="modal-title" id="gallery-modal-title">Add Gallery Photo</div>
      <button class="modal-close" onclick="closeModal('gallery-modal')">✕</button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="g-id">

      <!-- Image upload -->
      <div class="form-group">
        <div class="label">Photo <span class="req">*</span>
          <span style="text-transform:none;font-weight:400;color:rgba(255,255,255,.3);"> — JPG/PNG/WebP, max 3MB</span>
        </div>
        <div class="upload-zone" id="g-upload">
          <input type="file" id="g-photo" accept="image/jpeg,image/png,image/webp" onchange="previewImg(this)">
          <div class="up-icon">📷</div>
          <p>Drop photo here or click to browse</p>
        </div>
        <div class="img-preview" id="g-preview"></div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label class="label">Brand <span class="req">*</span></label>
          <select class="input" id="g-brand">
            <option value="">Select brand…</option>
            <?php foreach(['Samsung','LG','Bosch','Whirlpool','Von Hotpoint','Hisense','Ramtons','Mika','Bruhm'] as $b): ?>
            <option><?= $b ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="label">Appliance <span class="req">*</span></label>
          <select class="input" id="g-appliance">
            <option value="fridge">Fridge</option>
            <option value="washing-machine">Washing Machine</option>
            <option value="microwave">Microwave</option>
            <option value="freezer">Freezer</option>
          </select>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label class="label">Nairobi Area <span class="req">*</span></label>
          <select class="input" id="g-area">
            <option value="">Select area…</option>
            <?php foreach(['Westlands','Kilimani','Karen','Embakasi','Lavington','Parklands','Kasarani','Langata','Nairobi CBD','Thika Road'] as $a): ?>
            <option><?= $a ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="label">Photo Status</label>
          <select class="input" id="g-status">
            <option value="after">✓ After (Fixed)</option>
            <option value="before">Before Repair</option>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label class="label">Title <span class="req">*</span></label>
        <input class="input" id="g-title" type="text" placeholder="e.g. Samsung Fridge Gas Refill – Westlands">
        <p class="hint">Include brand, fault and area for best Google image SEO.</p>
      </div>

      <div class="form-group">
        <label class="label">Fault / What Was Done <span class="req">*</span></label>
        <input class="input" id="g-fault" type="text" placeholder="e.g. Not Cooling – Gas Refill (R600a)">
      </div>

      <div class="form-group">
        <label class="label">Description <span style="font-weight:400;text-transform:none;color:rgba(255,255,255,.35);">— shown in lightbox popup</span></label>
        <textarea class="input" id="g-desc" rows="3" placeholder="2–3 sentences: what was wrong, what was done, where. Include brand + area + Nairobi for SEO."></textarea>
      </div>

      <div class="form-group">
        <label class="label">Visible on website</label>
        <select class="input" id="g-active" style="width:auto;">
          <option value="1">Yes — Published</option>
          <option value="0">No — Hidden</option>
        </select>
      </div>
    </div>
    <div class="modal-foot">
      <button class="btn btn-ghost" onclick="closeModal('gallery-modal')">Cancel</button>
      <button class="btn btn-primary" onclick="saveGallery()">💾 Save Photo</button>
    </div>
  </div>
</div>

<!-- ══ REVIEW MODAL ══ -->
<div class="overlay" id="review-modal">
  <div class="modal">
    <div class="modal-head">
      <div class="modal-title" id="review-modal-title">Add Review</div>
      <button class="modal-close" onclick="closeModal('review-modal')">✕</button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="r-id">
      <div class="form-row">
        <div class="form-group">
          <label class="label">Customer Name <span class="req">*</span></label>
          <input class="input" id="r-author" placeholder="e.g. James M.">
          <p class="hint">First name + last initial for privacy.</p>
        </div>
        <div class="form-group">
          <label class="label">Nairobi Area <span class="req">*</span></label>
          <select class="input" id="r-area">
            <option value="">Select area…</option>
            <?php foreach(['Westlands','Kilimani','Karen','Embakasi','Lavington','Parklands','Kasarani','Langata','Nairobi CBD','South B','South C'] as $a): ?>
            <option><?= $a ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label class="label">Star Rating <span class="req">*</span></label>
        <div class="stars" id="star-picker">
          <button class="star on" data-n="1" onclick="setStar(1)">★</button>
          <button class="star on" data-n="2" onclick="setStar(2)">★</button>
          <button class="star on" data-n="3" onclick="setStar(3)">★</button>
          <button class="star on" data-n="4" onclick="setStar(4)">★</button>
          <button class="star on" data-n="5" onclick="setStar(5)">★</button>
        </div>
        <input type="hidden" id="r-rating" value="5">
      </div>

      <div class="form-group">
        <label class="label">Review Text <span class="req">*</span></label>
        <textarea class="input" id="r-body" rows="4" placeholder="Customer review text. Include appliance type, brand, and area naturally for SEO."></textarea>
      </div>

      <div class="form-group">
        <label class="label">Status</label>
        <select class="input" id="r-status" style="width:auto;">
          <option value="approved">✅ Approved</option>
          <option value="pending">⏳ Pending</option>
          <option value="hidden">🙈 Hidden</option>
        </select>
      </div>
    </div>
    <div class="modal-foot">
      <button class="btn btn-ghost" onclick="closeModal('review-modal')">Cancel</button>
      <button class="btn btn-primary" onclick="saveReview()">💾 Save Review</button>
    </div>
  </div>
</div>

<!-- ══ CONFIRM ══ -->
<div class="confirm-overlay" id="confirm">
  <div class="confirm-box">
    <div class="confirm-icon">⚠️</div>
    <div class="confirm-title" id="confirm-title">Are you sure?</div>
    <p class="confirm-msg" id="confirm-msg">This cannot be undone.</p>
    <div class="confirm-btns">
      <button class="btn btn-ghost" onclick="closeConfirm()">Cancel</button>
      <button class="btn btn-danger" id="confirm-ok">Delete</button>
    </div>
  </div>
</div>

<!-- ══ TOASTS ══ -->
<div class="toasts" id="toasts"></div>

<script>
'use strict';
// ─── UTILS ───────────────────────────────────────────
const $ = id => document.getElementById(id);
function esc(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function toast(msg, type='ok'){
  const el = document.createElement('div');
  el.className = `toast ${type}`;
  el.innerHTML = (type==='ok'?'✅':type==='err'?'❌':'⚠️') + ' ' + msg;
  $('toasts').appendChild(el);
  setTimeout(()=>{ el.style.opacity='0'; el.style.transition='opacity .3s'; setTimeout(()=>el.remove(),300); }, 3000);
}
function openModal(id){ $(id).classList.add('open'); document.body.style.overflow='hidden'; }
function closeModal(id){ $(id).classList.remove('open'); document.body.style.overflow=''; }
function closeConfirm(){ $('confirm').classList.remove('open'); }
function confirm2(title, msg, cb){
  $('confirm-title').textContent = title;
  $('confirm-msg').textContent   = msg;
  $('confirm').classList.add('open');
  $('confirm-ok').onclick = ()=>{ closeConfirm(); cb(); };
}
// Close modal/confirm on overlay click
document.querySelectorAll('.overlay,.confirm-overlay').forEach(el =>
  el.addEventListener('click', e => { if(e.target===el){ el.classList.remove('open'); document.body.style.overflow=''; } })
);
document.addEventListener('keydown', e => {
  if(e.key==='Escape') document.querySelectorAll('.overlay.open,.confirm-overlay.open').forEach(el=>{ el.classList.remove('open'); document.body.style.overflow=''; });
});

async function api(url, opts={}){
  const r = await fetch(url, { credentials:'same-origin', ...opts });
  if(r.status===401){ window.location.href='login.php'; return null; }
  return r.json();
}

function searchTable(tbodyId, q){
  document.querySelectorAll(`#${tbodyId} tr`).forEach(tr =>
    tr.style.display = tr.textContent.toLowerCase().includes(q.toLowerCase()) ? '' : 'none'
  );
}

// ─── SIDEBAR + TABS ───────────────────────────────────
function toggleSidebar(){
  document.querySelector('.sidebar').classList.toggle('open');
}
let currentTab = 'gallery';
function switchTab(tab, btn){
  ['gallery','reviews','faqs'].forEach(t => {
    $(`tab-${t}`).style.display = t===tab ? '' : 'none';
  });
  document.querySelectorAll('.nav-link').forEach(b => b.classList.remove('active'));
  if(btn) btn.classList.add('active');

  // Update topbar
  const titles = {gallery:'Gallery',reviews:'Reviews',faqs:'FAQs'};
  $('page-title').textContent = titles[tab];
  $('add-btn').style.display = tab==='faqs' ? 'none' : '';

  currentTab = tab;
  if(tab==='gallery') loadGallery();
  else if(tab==='reviews') loadReviews();
  else loadFaqs();
}

// ─── LOGOUT ──────────────────────────────────────────
async function doLogout(){
  await fetch('api/logout.php',{method:'POST',credentials:'same-origin'}).catch(()=>{});
  window.location.href='login.php';
}
function openAdd(){
  if(currentTab==='gallery') openAddGallery();
  else if(currentTab==='reviews') openAddReview();
}

// ══════════════════════════════════════════════════════
//   GALLERY
// ══════════════════════════════════════════════════════
let galleryData = [];

async function loadGallery(){
  const r = await api('api/gallery.php?action=list');
  galleryData = (r&&r.ok) ? r.data : [];
  renderGallery();
}

function renderGallery(){
  const tbody = $('gallery-tbody');
  if(!galleryData.length){
    tbody.innerHTML = '<tr><td colspan="8"><div class="empty"><div class="empty-icon">🖼️</div><h4>No photos yet</h4><p>Add your first before &amp; after repair photo.</p></div></td></tr>';
    return;
  }
  tbody.innerHTML = galleryData.map(item => `
    <tr>
      <td>
        <div style="width:52px;height:40px;border-radius:6px;overflow:hidden;background:rgba(255,255,255,.05);display:flex;align-items:center;justify-content:center;font-size:20px;">
          ${item.img_path ? `<img src="/${esc(item.img_path)}" style="width:100%;height:100%;object-fit:cover;" alt="">` : applianceEmoji(item.appliance)}
        </div>
      </td>
      <td>
        <div style="font-weight:700;max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${esc(item.title)}</div>
        <div style="font-size:11px;color:rgba(255,255,255,.35);">${esc(item.fault)}</div>
      </td>
      <td>${esc(item.brand)}</td>
      <td>${esc(item.area)}</td>
      <td><span class="badge badge-blue">${applianceLabel(item.appliance)}</span></td>
      <td><span class="badge ${item.status==='after'?'badge-green':'badge-yellow'}">${item.status==='after'?'✓ Fixed':'Before'}</span></td>
      <td>
        <button class="btn btn-sm ${item.active?'btn-success':'btn-ghost'}"
                onclick="toggleActive(${item.id},${item.active?0:1})"
                title="${item.active?'Published — click to hide':'Hidden — click to publish'}">
          ${item.active?'✅':'🙈'}
        </button>
      </td>
      <td>
        <div class="td-actions">
          <button class="btn btn-sm btn-ghost btn-icon" onclick='editGallery(${JSON.stringify(item)})' title="Edit">✏️</button>
          <button class="btn btn-sm btn-danger btn-icon" onclick="delGallery(${item.id},'${esc(item.title)}')" title="Delete">🗑</button>
        </div>
      </td>
    </tr>`).join('');
}

function applianceEmoji(t){ return {fridge:'🧊','washing-machine':'🫧',microwave:'📡',freezer:'❄️'}[t]||'🔧'; }
function applianceLabel(t){ return {fridge:'Fridge','washing-machine':'Washer',microwave:'Microwave',freezer:'Freezer'}[t]||t; }

function openAddGallery(){
  $('gallery-modal-title').textContent = 'Add Gallery Photo';
  $('g-id').value=''; $('g-title').value=''; $('g-fault').value=''; $('g-desc').value='';
  $('g-brand').value=''; $('g-area').value=''; $('g-appliance').value='fridge';
  $('g-status').value='after'; $('g-active').value='1';
  $('g-photo').value=''; $('g-preview').innerHTML='';
  openModal('gallery-modal');
}
function editGallery(item){
  $('gallery-modal-title').textContent = 'Edit Gallery Photo';
  $('g-id').value        = item.id;
  $('g-title').value     = item.title;
  $('g-fault').value     = item.fault;
  $('g-desc').value      = item.description||'';
  $('g-brand').value     = item.brand;
  $('g-area').value      = item.area;
  $('g-appliance').value = item.appliance;
  $('g-status').value    = item.status;
  $('g-active').value    = item.active;
  $('g-photo').value     = '';
  $('g-preview').innerHTML = item.img_path
    ? `<div class="img-thumb"><img src="/${esc(item.img_path)}" alt="current photo"></div>` : '';
  openModal('gallery-modal');
}
function previewImg(input){
  const f = input.files[0]; if(!f) return;
  const reader = new FileReader();
  reader.onload = e => {
    $('g-preview').innerHTML = `<div class="img-thumb"><img src="${e.target.result}" alt="preview"></div>`;
  };
  reader.readAsDataURL(f);
}
async function saveGallery(){
  const title = $('g-title').value.trim();
  const brand = $('g-brand').value;
  const area  = $('g-area').value;
  const fault = $('g-fault').value.trim();
  if(!title||!brand||!area||!fault){ toast('Fill in Brand, Area, Title and Fault.','err'); return; }

  const fd = new FormData();
  fd.append('action',    $('g-id').value ? 'save' : 'save');
  fd.append('id',        $('g-id').value);
  fd.append('title',     title);
  fd.append('brand',     brand);
  fd.append('area',      area);
  fd.append('appliance', $('g-appliance').value);
  fd.append('status',    $('g-status').value);
  fd.append('fault',     fault);
  fd.append('desc',      $('g-desc').value.trim());
  fd.append('active',    $('g-active').value);
  const photo = $('g-photo').files[0];
  if(photo) fd.append('photo', photo);

  const r = await api('api/gallery.php?action=save', { method:'POST', body:fd });
  if(r&&r.ok){ toast('Photo saved! ✅'); closeModal('gallery-modal'); loadGallery(); }
  else toast((r&&r.error)||'Save failed.','err');
}
async function delGallery(id, title){
  confirm2('Delete Photo', `Delete "${title}"? This cannot be undone.`, async ()=>{
    const r = await api('api/gallery.php?action=delete',{
      method:'POST', headers:{'Content-Type':'application/json'},
      body:JSON.stringify({id})
    });
    if(r&&r.ok){ toast('Photo deleted.','ok'); loadGallery(); }
    else toast('Delete failed.','err');
  });
}
async function toggleActive(id, active){
  const r = await api('api/gallery.php?action=toggle',{
    method:'POST', headers:{'Content-Type':'application/json'},
    body:JSON.stringify({id, active})
  });
  if(r&&r.ok){ toast(active?'Photo published.':'Photo hidden.'); loadGallery(); }
  else toast('Update failed.','err');
}

// ══════════════════════════════════════════════════════
//   REVIEWS
// ══════════════════════════════════════════════════════
let reviewsData = [];

async function loadReviews(){
  const r = await api('api/reviews.php?action=list');
  reviewsData = (r&&r.ok) ? r.data : [];
  renderReviews();
}
function renderReviews(filter=''){
  const tbody = $('reviews-tbody');
  let data = filter ? reviewsData.filter(r=>r.status===filter) : reviewsData;
  const pending = reviewsData.filter(r=>r.status==='pending').length;
  $('pending-alert').style.display = pending>0 ? '' : 'none';

  if(!data.length){
    tbody.innerHTML = '<tr><td colspan="6"><div class="empty"><div class="empty-icon">⭐</div><h4>No reviews</h4><p>Add customer reviews to build trust and improve SEO.</p></div></td></tr>';
    return;
  }
  tbody.innerHTML = data.map(rv => `
    <tr data-status="${esc(rv.status)}">
      <td><strong>${esc(rv.author)}</strong></td>
      <td><span style="font-size:12px;">📍 ${esc(rv.area)}</span></td>
      <td><span style="color:#f59e0b;font-size:15px;">${'★'.repeat(rv.rating)}${'☆'.repeat(5-rv.rating)}</span></td>
      <td style="max-width:240px;">
        <div style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-size:13px;color:rgba(255,255,255,.55);">
          "${esc(rv.body)}"
        </div>
      </td>
      <td>
        <span class="badge ${rv.status==='approved'?'badge-green':rv.status==='pending'?'badge-yellow':'badge-gray'}">
          ${rv.status==='approved'?'✅ Approved':rv.status==='pending'?'⏳ Pending':'🙈 Hidden'}
        </span>
      </td>
      <td>
        <div class="td-actions">
          ${rv.status==='pending'?`
            <button class="btn btn-sm btn-success" onclick="approveReview(${rv.id},'approved')" title="Approve">✅</button>
            <button class="btn btn-sm btn-danger"  onclick="approveReview(${rv.id},'hidden')"   title="Reject">❌</button>
          `:''}
          <button class="btn btn-sm btn-ghost btn-icon" onclick='editReview(${JSON.stringify(rv)})' title="Edit">✏️</button>
          <button class="btn btn-sm btn-danger btn-icon" onclick="delReview(${rv.id},'${esc(rv.author)}')" title="Delete">🗑</button>
        </div>
      </td>
    </tr>`).join('');
}
function filterReviews(){ renderReviews($('review-filter').value); }

function setStar(n){
  $('r-rating').value = n;
  document.querySelectorAll('#star-picker .star').forEach((s,i) => s.classList.toggle('on',i<n));
}
function openAddReview(){
  $('review-modal-title').textContent='Add Review';
  $('r-id').value=''; $('r-author').value=''; $('r-body').value='';
  $('r-area').value=''; $('r-status').value='approved'; setStar(5);
  openModal('review-modal');
}
function editReview(rv){
  $('review-modal-title').textContent='Edit Review';
  $('r-id').value     = rv.id;
  $('r-author').value = rv.author;
  $('r-area').value   = rv.area;
  $('r-body').value   = rv.body;
  $('r-status').value = rv.status;
  setStar(parseInt(rv.rating)||5);
  openModal('review-modal');
}
async function saveReview(){
  const author = $('r-author').value.trim();
  const body   = $('r-body').value.trim();
  const area   = $('r-area').value;
  if(!author||!body){ toast('Author and review text are required.','err'); return; }

  const payload = {
    action:'save', id:$('r-id').value,
    author, area, rating:parseInt($('r-rating').value),
    body, status:$('r-status').value
  };
  const r = await api('api/reviews.php?action=save',{
    method:'POST', headers:{'Content-Type':'application/json'},
    body: JSON.stringify(payload)
  });
  if(r&&r.ok){ toast('Review saved! ✅'); closeModal('review-modal'); loadReviews(); }
  else toast((r&&r.error)||'Save failed.','err');
}
async function approveReview(id, status){
  const r = await api('api/reviews.php?action=approve',{
    method:'POST', headers:{'Content-Type':'application/json'},
    body:JSON.stringify({id, status})
  });
  if(r&&r.ok){ toast(status==='approved'?'Review approved ✅':'Review rejected'); loadReviews(); }
  else toast('Update failed.','err');
}
async function delReview(id, author){
  confirm2('Delete Review',`Delete ${author}'s review?`, async ()=>{
    const r = await api('api/reviews.php?action=delete',{
      method:'POST', headers:{'Content-Type':'application/json'},
      body:JSON.stringify({id})
    });
    if(r&&r.ok){ toast('Review deleted.'); loadReviews(); }
    else toast('Delete failed.','err');
  });
}

// ══════════════════════════════════════════════════════
//   FAQs
// ══════════════════════════════════════════════════════
let faqsData = [];

async function loadFaqs(){
  const r = await api('api/faqs.php?action=list');
  faqsData = (r&&r.ok) ? r.data : [];
  renderFaqs();
}
function renderFaqs(){
  const list = $('faq-list');
  if(!faqsData.length){
    list.innerHTML='<div class="empty"><div class="empty-icon">❓</div><h4>No FAQs yet</h4><p>Add FAQs to generate FAQ Schema for Google rich results.</p><button class="btn btn-primary" onclick="addFaqRow()">+ Add First FAQ</button></div>';
    return;
  }
  list.innerHTML = `
    <div style="margin-bottom:14px;display:flex;gap:10px;">
      <button class="btn btn-ghost" onclick="addFaqRow()">+ Add FAQ</button>
      <button class="btn btn-primary" onclick="saveFaqs()">💾 Save All FAQs</button>
    </div>
    ${faqsData.map((faq,i) => `
      <div class="faq-row" id="frow-${i}" style="border:1px solid rgba(255,255,255,.07);border-radius:8px;padding:14px;margin-bottom:10px;background:rgba(255,255,255,.02);">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px;">
          <span style="font-size:13px;font-weight:700;color:var(--orange);">Q${i+1}</span>
          <div style="flex:1;">
            <input class="input" id="fq-${i}" value="${esc(faq.question)}"
                   placeholder="Question — include Nairobi, brand names, area names for SEO"
                   style="font-weight:700;">
          </div>
          <button class="btn btn-sm btn-danger btn-icon" onclick="removeFaqRow(${i})" title="Remove">🗑</button>
          ${i>0?`<button class="btn btn-sm btn-ghost btn-icon" onclick="moveFaq(${i},-1)" title="Move up">↑</button>`:''}
          ${i<faqsData.length-1?`<button class="btn btn-sm btn-ghost btn-icon" onclick="moveFaq(${i},1)" title="Move down">↓</button>`:''}
        </div>
        <textarea class="input" id="fa-${i}" rows="2"
                  placeholder="Answer — mention KSh prices, Nairobi, brands, and areas"
                  style="resize:vertical;">${esc(faq.answer)}</textarea>
      </div>`).join('')}
    <div style="display:flex;gap:10px;margin-top:10px;">
      <button class="btn btn-ghost" onclick="addFaqRow()">+ Add FAQ</button>
      <button class="btn btn-primary" onclick="saveFaqs()">💾 Save All FAQs</button>
    </div>`;
}

function addFaqRow(){
  faqsData.push({id:0, question:'', answer:''});
  renderFaqs();
  // focus last question input
  const last = $(`fq-${faqsData.length-1}`);
  if(last){ last.focus(); last.scrollIntoView({behavior:'smooth',block:'center'}); }
}
function removeFaqRow(i){
  if(faqsData.length===1){ toast('At least one FAQ is required.','wrn'); return; }
  confirm2('Remove FAQ', 'Remove this question? Save All to confirm deletion.', ()=>{
    faqsData.splice(i,1); renderFaqs();
    toast('Row removed — click Save All to confirm.','wrn');
  });
}
function moveFaq(i,dir){
  const j = i+dir;
  if(j<0||j>=faqsData.length) return;
  // Read current values before moving
  faqsData[i].question = $(`fq-${i}`)?.value||faqsData[i].question;
  faqsData[i].answer   = $(`fa-${i}`)?.value||faqsData[i].answer;
  faqsData[j].question = $(`fq-${j}`)?.value||faqsData[j].question;
  faqsData[j].answer   = $(`fa-${j}`)?.value||faqsData[j].answer;
  [faqsData[i],faqsData[j]] = [faqsData[j],faqsData[i]];
  renderFaqs();
}
async function saveFaqs(){
  // Collect current values from inputs
  const updated = faqsData.map((f,i) => ({
    question: ($(`fq-${i}`)?.value||'').trim(),
    answer:   ($(`fa-${i}`)?.value||'').trim(),
  })).filter(f => f.question && f.answer);

  if(!updated.length){ toast('Add at least one FAQ with question and answer.','err'); return; }

  const incomplete = updated.findIndex(f=>!f.question||!f.answer);
  if(incomplete>=0){ toast(`FAQ ${incomplete+1} is incomplete.`,'err'); return; }

  const r = await api('api/faqs.php?action=saveall',{
    method:'POST', headers:{'Content-Type':'application/json'},
    body:JSON.stringify({faqs:updated})
  });
  if(r&&r.ok){ toast(`${r.saved} FAQs saved and schema updated! ✅`); loadFaqs(); }
  else toast((r&&r.error)||'Save failed.','err');
}

// ─── INIT ─────────────────────────────────────────────
loadGallery();
</script>
</body>
</html>