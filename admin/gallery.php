<?php
/**
 * VISIONX ADMIN — GALLERY MANAGER
 * File: admin/gallery.php
 *
 * PHP Integration:
 *   GET  /admin/api/gallery/list.php     → [ {id, title, brand, area, type, status, img_path, desc, fault, created_at}, ... ]
 *   POST /admin/api/gallery/save.php     → multipart/form-data with image + metadata
 *   POST /admin/api/gallery/delete.php   → { id }
 *   POST /admin/api/gallery/status.php   → { id, status }
 *
 * DB Table: gallery
 *   id, title, brand, area, appliance_type, status(before|after|fixed),
 *   img_path, img_alt, description, fault, sort_order, is_active, created_at
 */
$pageTitle  = 'Gallery Manager';
$activePage = 'gallery';
$extraJs    = 'gallery';
require_once __DIR__ . '/includes/header.php';

$brands = ['Samsung','LG','Bosch','Whirlpool','Von Hotpoint','Hisense','Ramtons','Mika','Bruhm'];
$areas  = ['Westlands','Kilimani','Karen','Embakasi','Lavington','Parklands','Kasarani','Langata','Nairobi CBD','Thika Road'];
$types  = ['fridge'=>'Fridge','washing-machine'=>'Washing Machine','microwave'=>'Microwave','freezer'=>'Freezer'];
?>

<!-- ══ TOOLBAR ══ -->
<div class="toolbar">
  <div class="toolbar-left">
    <div class="search-wrap">
      <i class="fas fa-search"></i>
      <input class="search-input" id="gallery-search" placeholder="Search photos…">
    </div>
    <select class="form-control" id="filter-type" style="width:auto;padding:9px 14px;">
      <option value="">All Types</option>
      <?php foreach ($types as $k => $v): ?>
      <option value="<?= $k ?>"><?= $v ?></option>
      <?php endforeach; ?>
    </select>
    <select class="form-control" id="filter-status" style="width:auto;padding:9px 14px;">
      <option value="">All Statuses</option>
      <option value="after">After (Fixed)</option>
      <option value="before">Before</option>
    </select>
  </div>
  <div class="toolbar-right">
    <button class="btn btn-ghost btn-sm" id="view-grid-btn" title="Grid view">⊞</button>
    <button class="btn btn-ghost btn-sm" id="view-list-btn" title="List view">☰</button>
    <button class="btn btn-primary" id="add-photo-btn">
      <i class="fas fa-plus"></i> Add Photo
    </button>
  </div>
</div>

<!-- Stats strip -->
<div class="quick-stats" id="gallery-stats">
  <div class="qs-item"><div class="qs-num" id="qs-total">–</div><div class="qs-label">Total Photos</div></div>
  <div class="qs-item"><div class="qs-num" id="qs-after">–</div><div class="qs-label">After (Fixed)</div></div>
  <div class="qs-item"><div class="qs-num" id="qs-before">–</div><div class="qs-label">Before</div></div>
  <div class="qs-item"><div class="qs-num" id="qs-brands">–</div><div class="qs-label">Brands</div></div>
</div>

<!-- ══ GALLERY GRID VIEW ══ -->
<div id="view-grid-wrap">
  <div class="gallery-manager-grid" id="gallery-mgr-grid">
    <!-- JS rendered from API -->
  </div>
  <div class="empty-state" id="gallery-empty" style="display:none;">
    <div class="es-icon">🖼️</div>
    <h3>No photos yet</h3>
    <p>Add your first before &amp; after repair photo to build trust and improve SEO.</p>
    <button class="btn btn-primary" id="add-photo-empty-btn"><i class="fas fa-plus"></i> Add First Photo</button>
  </div>
</div>

<!-- ══ TABLE VIEW ══ -->
<div id="view-list-wrap" style="display:none;">
  <div class="panel">
    <div class="data-table-wrap">
      <table class="data-table" id="gallery-table">
        <thead>
          <tr>
            <th style="width:60px;">Photo</th>
            <th>Title</th>
            <th>Brand</th>
            <th>Area</th>
            <th>Type</th>
            <th>Status</th>
            <th>Date</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="gallery-tbody">
          <!-- JS rendered -->
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- ══ ADD / EDIT MODAL ══ -->
<div class="modal-overlay" id="add-modal">
  <div class="modal-box modal-lg">
    <div class="modal-head">
      <div class="modal-title" id="modal-title">Add Gallery Photo</div>
      <button class="btn btn-ghost modal-close" id="modal-close-btn">✕</button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="edit-id" value="">

      <!-- Upload zone -->
      <div class="form-group">
        <label class="form-label">Photo <span class="req">*</span>
          <span style="font-weight:400;color:var(--muted);text-transform:none;">
            — 800×600px recommended, JPG/PNG, max 2MB
          </span>
        </label>
        <div class="upload-zone" id="photo-upload-zone">
          <input type="file" id="photo-file" accept="image/jpeg,image/png,image/webp">
          <div class="upload-icon">📷</div>
          <h4>Drop photo here or click to browse</h4>
          <p>Supported: JPG, PNG, WebP · Max 2MB</p>
        </div>
        <div class="upload-preview" id="photo-preview"></div>
        <!-- PHP: on save, file goes to /assets/images/gallery/[generated-slug].jpg -->
        <p class="form-hint">
          File will be saved as: <code id="filename-preview">brand-type-area-01.jpg</code>
          — optimised filename for SEO
        </p>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Appliance Brand <span class="req">*</span></label>
          <select class="form-control" id="f-brand">
            <option value="">Select brand…</option>
            <?php foreach ($brands as $b): ?>
            <option value="<?= htmlspecialchars($b) ?>"><?= htmlspecialchars($b) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Appliance Type <span class="req">*</span></label>
          <select class="form-control" id="f-type">
            <option value="">Select type…</option>
            <?php foreach ($types as $k => $v): ?>
            <option value="<?= $k ?>"><?= $v ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Nairobi Area <span class="req">*</span></label>
          <select class="form-control" id="f-area">
            <option value="">Select area…</option>
            <?php foreach ($areas as $a): ?>
            <option value="<?= htmlspecialchars($a) ?>"><?= htmlspecialchars($a) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Photo Status <span class="req">*</span></label>
          <select class="form-control" id="f-status">
            <option value="after">✓ After (Fixed)</option>
            <option value="before">Before Repair</option>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Title <span class="req">*</span>
          <span style="font-weight:400;color:var(--muted);text-transform:none;">— shown in lightbox and SEO</span>
        </label>
        <input class="form-control" id="f-title" type="text"
               placeholder="e.g. Samsung Fridge Gas Refill – Westlands">
        <p class="form-hint">Include brand, fault, and area for best SEO. Max 80 chars.</p>
      </div>

      <div class="form-group">
        <label class="form-label">Fault / Repair Done <span class="req">*</span></label>
        <input class="form-control" id="f-fault" type="text"
               placeholder="e.g. Not Cooling – Gas Refill (R600a)">
        <p class="form-hint">Short fault label shown on the card. Max 60 chars.</p>
      </div>

      <div class="form-group">
        <label class="form-label">Description
          <span style="font-weight:400;color:var(--muted);text-transform:none;">— shown in lightbox popup</span>
        </label>
        <textarea class="form-control" id="f-desc" rows="3"
          placeholder="Describe what was wrong and what was done. Include brand, area, and outcome. 1–3 sentences is ideal."></textarea>
        <p class="form-hint">2–3 sentences with keywords: brand + fault + area + Nairobi.</p>
      </div>

      <div class="form-group">
        <label class="form-label">Image Alt Text
          <span style="font-weight:400;color:var(--muted);text-transform:none;">— auto-generated but you can override</span>
        </label>
        <input class="form-control" id="f-alt" type="text"
               placeholder="e.g. Samsung fridge gas refill repair Westlands Nairobi VisionX Repairs">
        <p class="form-hint">Auto-filled from Brand + Type + Area. Critical for Google Image SEO.</p>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Sort Order</label>
          <input class="form-control" id="f-sort" type="number" value="0" min="0">
          <p class="form-hint">Lower number = shown first (0 = auto).</p>
        </div>
        <div class="form-group">
          <label class="form-label">Visibility</label>
          <div class="toggle-wrap" style="margin-top:10px;">
            <div class="toggle on" id="toggle-active"></div>
            <span>Published (visible on website)</span>
            <input type="hidden" id="f-active" value="1">
          </div>
        </div>
      </div>

    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" id="modal-cancel-btn">Cancel</button>
      <button class="btn btn-primary" id="save-btn">
        <i class="fas fa-save"></i> Save Photo
      </button>
    </div>
  </div>
</div>

<script>
// ══════════════════════════════════════════════
//  GALLERY PAGE JS  —  All event listeners use
//  addEventListener only. No inline onclick attrs
//  that conflict with JS listeners.
// ══════════════════════════════════════════════

let galleryData  = [];
let currentView  = 'grid';
let altManualEdit = false;  // tracks whether user has manually edited alt text

// ── Helpers ─────────────────────────────────────────────────────────────────

function esc(str) {
  if (str == null) return '';
  return String(str)
    .replace(/&/g,'&amp;')
    .replace(/</g,'&lt;')
    .replace(/>/g,'&gt;')
    .replace(/"/g,'&quot;')
    .replace(/'/g,'&#39;');
}

function uid() {
  return 'g' + Math.random().toString(36).slice(2, 9);
}

function typeEmoji(t) {
  return { fridge:'🧊', 'washing-machine':'🫧', microwave:'📡', freezer:'❄️' }[t] || '🔧';
}

function typeLabel(t) {
  return { fridge:'Fridge', 'washing-machine':'Washer', microwave:'Microwave', freezer:'Freezer' }[t] || t;
}

// ── Modal helpers ────────────────────────────────────────────────────────────

function openModal(id) {
  const el = document.getElementById(id);
  if (el) el.classList.add('open');   // assumes .modal-overlay.open { display:flex }
  // Fallback: force display in case CSS class isn't set up
  if (el && getComputedStyle(el).display === 'none') {
    el.style.display = 'flex';
  }
}

function closeModal(id) {
  const el = document.getElementById(id);
  if (!el) return;
  el.classList.remove('open');
  el.style.display = '';
}

// ── Load gallery from PHP API ────────────────────────────────────────────────

async function loadGallery() {
  try {
    /**
     * PHP: GET /admin/api/gallery/list.php
     * Returns: { success: true, data: [{id, title, brand, area, type, status,
     *             img_path, img_alt, description, fault, sort_order, is_active, created_at},...] }
     */
    const r = await vxApi('gallery/list.php');
    galleryData = (r && r.success) ? r.data : getDemoGallery();
  } catch {
    galleryData = getDemoGallery();
  }
  renderGallery();
  updateStats();
}

function getDemoGallery() {
  return [
    { id:'g1', title:'Samsung Fridge Gas Refill – Westlands',   brand:'Samsung',     area:'Westlands', type:'fridge',           status:'after',  img_path:'', img_alt:'', fault:'Not Cooling – Gas Refill',   description:'Fridge repaired same-day.',   is_active:1, sort_order:0, created_at:'2025-01-15' },
    { id:'g2', title:'LG Washing Machine UE Error – Kilimani',  brand:'LG',          area:'Kilimani',  type:'washing-machine',  status:'after',  img_path:'', img_alt:'', fault:'UE Error – Drum Bearing',     description:'Drum bearing replaced.',      is_active:1, sort_order:0, created_at:'2025-01-12' },
    { id:'g3', title:'Bosch Fridge Thermostat – Karen',         brand:'Bosch',       area:'Karen',     type:'fridge',           status:'after',  img_path:'', img_alt:'', fault:'Not Cooling – Thermostat',    description:'Thermostat replaced.',        is_active:1, sort_order:0, created_at:'2025-01-08' },
    { id:'g4', title:'Von Hotpoint Chest Freezer – Embakasi',   brand:'Von Hotpoint',area:'Embakasi',  type:'freezer',          status:'after',  img_path:'', img_alt:'', fault:'Not Freezing – Relay',        description:'Relay replaced.',             is_active:0, sort_order:0, created_at:'2025-01-05' },
  ];
}

// ── Render ───────────────────────────────────────────────────────────────────

function renderGallery() {
  const grid  = document.getElementById('gallery-mgr-grid');
  const tbody = document.getElementById('gallery-tbody');
  const empty = document.getElementById('gallery-empty');

  if (!galleryData.length) {
    grid.innerHTML = '';
    empty.style.display = 'block';
    tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:40px;color:var(--muted);">No gallery photos found.</td></tr>';
    return;
  }

  empty.style.display = 'none';

  // ── Grid cards
  grid.innerHTML = galleryData.map(item => `
    <div class="gm-card" data-id="${esc(item.id)}" data-type="${esc(item.type)}" data-status="${esc(item.status)}">
      <div class="gm-img">
        ${item.img_path
          ? `<img src="/${esc(item.img_path)}" alt="${esc(item.img_alt || item.title)}">`
          : `<span style="font-size:34px;">${typeEmoji(item.type)}</span>`}
        <span class="gm-status ${esc(item.status)}">${item.status === 'before' ? 'Before' : '✓ Fixed'}</span>
        ${!item.is_active
          ? '<span style="position:absolute;top:6px;right:6px;background:rgba(0,0,0,.6);color:#fff;font-size:10px;font-weight:700;padding:2px 7px;border-radius:20px;">Hidden</span>'
          : ''}
      </div>
      <div class="gm-body">
        <div class="gm-name" title="${esc(item.title)}">${esc(item.title)}</div>
        <div class="gm-meta">${esc(item.brand)} · ${esc(item.area)}</div>
      </div>
      <div class="gm-actions">
        <button class="btn btn-ghost btn-sm" data-action="edit" data-id="${esc(item.id)}" title="Edit">✏️</button>
        <button class="btn btn-ghost btn-sm" data-action="toggle" data-id="${esc(item.id)}" data-active="${item.is_active ? 0 : 1}"
                title="${item.is_active ? 'Hide' : 'Show'}">
          ${item.is_active ? '👁' : '🙈'}
        </button>
        <button class="btn btn-danger btn-sm" data-action="delete" data-id="${esc(item.id)}" data-title="${esc(item.title)}" title="Delete">🗑</button>
      </div>
    </div>`).join('');

  // ── Table rows
  tbody.innerHTML = galleryData.map(item => `
    <tr data-type="${esc(item.type)}" data-status="${esc(item.status)}">
      <td>
        <div style="width:48px;height:36px;border-radius:6px;overflow:hidden;background:var(--navy-mid);display:flex;align-items:center;justify-content:center;">
          ${item.img_path
            ? `<img src="/${esc(item.img_path)}" style="width:100%;height:100%;object-fit:cover;">`
            : `<span>${typeEmoji(item.type)}</span>`}
        </div>
      </td>
      <td style="max-width:240px;">
        <div style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="${esc(item.title)}">${esc(item.title)}</div>
        <div style="font-size:11px;color:var(--muted);">${esc(item.fault)}</div>
      </td>
      <td>${esc(item.brand)}</td>
      <td>${esc(item.area)}</td>
      <td><span class="badge badge-blue">${typeLabel(item.type)}</span></td>
      <td>
        <span class="badge ${item.status === 'after' ? 'badge-green' : 'badge-red'}">${item.status === 'after' ? '✓ Fixed' : 'Before'}</span>
        ${!item.is_active ? '<span class="badge badge-gray" style="margin-left:4px;">Hidden</span>' : ''}
      </td>
      <td style="font-size:12px;color:var(--muted);">${item.created_at}</td>
      <td>
        <div class="td-actions">
          <button class="btn btn-ghost btn-sm btn-icon" data-action="edit" data-id="${esc(item.id)}" title="Edit">✏️</button>
          <button class="btn btn-danger btn-sm btn-icon" data-action="delete" data-id="${esc(item.id)}" data-title="${esc(item.title)}" title="Delete">🗑</button>
        </div>
      </td>
    </tr>`).join('');
}

function updateStats() {
  document.getElementById('qs-total').textContent  = galleryData.length;
  document.getElementById('qs-after').textContent  = galleryData.filter(i => i.status === 'after').length;
  document.getElementById('qs-before').textContent = galleryData.filter(i => i.status === 'before').length;
  document.getElementById('qs-brands').textContent = new Set(galleryData.map(i => i.brand)).size;
}

// ── Form helpers ─────────────────────────────────────────────────────────────

function updateFilename() {
  const brand = document.getElementById('f-brand').value;
  const type  = document.getElementById('f-type').value;
  const area  = document.getElementById('f-area').value;
  if (brand && type && area) {
    const slug = `${brand.toLowerCase()}-${type}-${area.toLowerCase().replace(/\s+/g, '-')}`;
    document.getElementById('filename-preview').textContent = slug + '-01.jpg';
    if (!altManualEdit) {
      document.getElementById('f-alt').value =
        `${brand} ${typeLabel(type).toLowerCase()} repair ${area} Nairobi VisionX Repairs`;
    }
  }
}

function resetForm() {
  document.getElementById('modal-title').textContent = 'Add Gallery Photo';
  document.getElementById('edit-id').value           = '';
  altManualEdit = false;

  ['f-brand','f-type','f-area','f-title','f-fault','f-desc','f-alt'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.value = '';
  });

  document.getElementById('f-status').value = 'after';
  document.getElementById('f-sort').value   = '0';
  document.getElementById('f-active').value = '1';
  document.getElementById('toggle-active').classList.add('on');
  document.getElementById('photo-preview').innerHTML         = '';
  document.getElementById('filename-preview').textContent    = 'brand-type-area-01.jpg';
  document.getElementById('photo-file').value                = '';
}

function openAddModal() {
  resetForm();
  openModal('add-modal');
}

function editItem(item) {
  resetForm();   // clear everything first

  document.getElementById('modal-title').textContent = 'Edit Gallery Photo';
  document.getElementById('edit-id').value   = item.id;
  document.getElementById('f-brand').value   = item.brand;
  document.getElementById('f-type').value    = item.type;
  document.getElementById('f-area').value    = item.area;
  document.getElementById('f-status').value  = item.status;
  document.getElementById('f-title').value   = item.title;
  document.getElementById('f-fault').value   = item.fault;
  document.getElementById('f-desc').value    = item.description || '';
  document.getElementById('f-alt').value     = item.img_alt || '';
  document.getElementById('f-sort').value    = item.sort_order || 0;
  document.getElementById('f-active').value  = item.is_active;

  if (item.img_alt) altManualEdit = true;   // treat existing alt as manual

  const tog = document.getElementById('toggle-active');
  item.is_active ? tog.classList.add('on') : tog.classList.remove('on');

  document.getElementById('photo-preview').innerHTML = item.img_path
    ? `<div class="upload-thumb"><img src="/${esc(item.img_path)}" alt="current photo"></div>`
    : '';

  updateFilename();
  openModal('add-modal');
}

// ── Save ─────────────────────────────────────────────────────────────────────

async function saveGalleryItem() {
  const id    = document.getElementById('edit-id').value;
  const brand = document.getElementById('f-brand').value;
  const type  = document.getElementById('f-type').value;
  const area  = document.getElementById('f-area').value;
  const title = document.getElementById('f-title').value.trim();
  const fault = document.getElementById('f-fault').value.trim();

  if (!brand || !type || !area || !title || !fault) {
    toast('Please fill in all required fields.', 'error');
    return;
  }

  const fd = new FormData();
  fd.append('id',          id);
  fd.append('brand',       brand);
  fd.append('type',        type);
  fd.append('area',        area);
  fd.append('status',      document.getElementById('f-status').value);
  fd.append('title',       title);
  fd.append('fault',       fault);
  fd.append('description', document.getElementById('f-desc').value.trim());
  fd.append('alt',         document.getElementById('f-alt').value.trim());
  fd.append('sort_order',  document.getElementById('f-sort').value);
  fd.append('is_active',   document.getElementById('f-active').value);

  const file = document.getElementById('photo-file').files[0];
  if (file) fd.append('photo', file);

  /**
   * PHP: POST /admin/api/gallery/save.php
   * Handles: multipart form with optional 'photo' file
   * Returns: { success: true, id: "...", img_path: "assets/images/gallery/..." }
   */
  try {
    const r = await vxApi('gallery/save.php', { method: 'POST', body: fd });
    if (r && r.success) {
      toast(id ? 'Photo updated!' : 'Photo added!', 'success');
      closeModal('add-modal');
      loadGallery();
    } else {
      toast((r && r.error) || 'Save failed', 'error');
    }
  } catch {
    // Demo fallback when PHP not connected
    toast(id ? 'Photo updated (demo)!' : 'Photo added (demo)!', 'success');
    closeModal('add-modal');
    if (!id) {
      galleryData.unshift({
        id: uid(), title, brand, area, type,
        status: document.getElementById('f-status').value,
        img_path: '', img_alt: document.getElementById('f-alt').value,
        fault, description: document.getElementById('f-desc').value,
        sort_order: parseInt(document.getElementById('f-sort').value) || 0,
        is_active: parseInt(document.getElementById('f-active').value),
        created_at: new Date().toISOString().split('T')[0],
      });
    } else {
      const idx = galleryData.findIndex(i => i.id === id);
      if (idx > -1) {
        galleryData[idx] = { ...galleryData[idx], title, brand, area, type,
          status: document.getElementById('f-status').value, fault,
          description: document.getElementById('f-desc').value,
          img_alt: document.getElementById('f-alt').value,
          sort_order: parseInt(document.getElementById('f-sort').value) || 0,
          is_active: parseInt(document.getElementById('f-active').value),
        };
      }
    }
    renderGallery();
    updateStats();
  }
}

// ── Delete ───────────────────────────────────────────────────────────────────

function deleteItem(id, title) {
  confirmDialog('Delete Photo', `Delete "${title}"? This cannot be undone.`, async () => {
    /**
     * PHP: POST /admin/api/gallery/delete.php
     * Body: { id }
     */
    try {
      const r = await vxApi('gallery/delete.php', { method: 'POST', body: { id } });
      if (r && r.success) {
        toast('Photo deleted.', 'success');
      } else {
        toast((r && r.error) || 'Delete failed', 'error');
      }
    } catch {}
    galleryData = galleryData.filter(i => i.id !== id);
    renderGallery();
    updateStats();
  });
}

// ── Toggle visibility ────────────────────────────────────────────────────────

async function toggleVisibility(id, active) {
  /**
   * PHP: POST /admin/api/gallery/status.php
   * Body: { id, is_active }
   */
  try {
    await vxApi('gallery/status.php', { method: 'POST', body: { id, is_active: active } });
  } catch {}
  const item = galleryData.find(i => i.id === id);
  if (item) {
    item.is_active = active;
    renderGallery();
  }
  toast(active ? 'Photo published.' : 'Photo hidden.', 'success');
}

// ── View switch ──────────────────────────────────────────────────────────────

function switchView(v) {
  currentView = v;
  document.getElementById('view-grid-wrap').style.display = v === 'grid' ? '' : 'none';
  document.getElementById('view-list-wrap').style.display = v === 'list' ? '' : 'none';
  document.getElementById('view-grid-btn').classList.toggle('active', v === 'grid');
  document.getElementById('view-list-btn').classList.toggle('active', v === 'list');
}

// ── Filters ──────────────────────────────────────────────────────────────────

function applyFilters() {
  const type   = document.getElementById('filter-type').value;
  const status = document.getElementById('filter-status').value;

  document.querySelectorAll('#gallery-mgr-grid .gm-card').forEach(el => {
    const show = (!type || el.dataset.type === type) && (!status || el.dataset.status === status);
    el.style.display = show ? '' : 'none';
  });

  document.querySelectorAll('#gallery-tbody tr').forEach(el => {
    const show = (!type || el.dataset.type === type) && (!status || el.dataset.status === status);
    el.style.display = show ? '' : 'none';
  });
}

function applySearch(q) {
  q = q.toLowerCase();
  document.querySelectorAll('#gallery-mgr-grid .gm-card').forEach(el => {
    el.style.display = el.textContent.toLowerCase().includes(q) ? '' : 'none';
  });
  document.querySelectorAll('#gallery-tbody tr').forEach(el => {
    el.style.display = el.textContent.toLowerCase().includes(q) ? '' : 'none';
  });
}

// ══════════════════════════════════════════════
//  ALL EVENT LISTENERS — single, centralised
//  section. No inline onclick attributes used.
// ══════════════════════════════════════════════

// Toolbar: Add Photo button
document.getElementById('add-photo-btn').addEventListener('click', openAddModal);

// Empty state: Add First Photo button
document.getElementById('add-photo-empty-btn').addEventListener('click', openAddModal);

// Modal: close via ✕ button
document.getElementById('modal-close-btn').addEventListener('click', () => closeModal('add-modal'));

// Modal: close via Cancel button
document.getElementById('modal-cancel-btn').addEventListener('click', () => closeModal('add-modal'));

// Modal: close by clicking the backdrop (overlay itself, not the inner box)
document.getElementById('add-modal').addEventListener('click', function(e) {
  if (e.target === this) closeModal('add-modal');
});

// Save button
document.getElementById('save-btn').addEventListener('click', saveGalleryItem);

// View toggle buttons
document.getElementById('view-grid-btn').addEventListener('click', () => switchView('grid'));
document.getElementById('view-list-btn').addEventListener('click', () => switchView('list'));

// Filters
document.getElementById('filter-type').addEventListener('change', applyFilters);
document.getElementById('filter-status').addEventListener('change', applyFilters);

// Search
document.getElementById('gallery-search').addEventListener('input', function() {
  applySearch(this.value);
});

// Auto-fill filename + alt text when brand/type/area change
['f-brand', 'f-type', 'f-area'].forEach(id => {
  document.getElementById(id).addEventListener('change', updateFilename);
});

// Mark alt text as manually edited so auto-fill stops overwriting it
document.getElementById('f-alt').addEventListener('input', () => { altManualEdit = true; });

// Toggle active visibility toggle widget
document.getElementById('toggle-active').addEventListener('click', function() {
  const isOn = this.classList.toggle('on');
  document.getElementById('f-active').value = isOn ? '1' : '0';
});

// Delegated click handler for dynamically rendered grid cards and table rows
//   Handles: edit, delete, toggle actions set via data-action attributes
document.getElementById('gallery-mgr-grid').addEventListener('click', function(e) {
  const btn = e.target.closest('[data-action]');
  if (!btn) return;
  const action = btn.dataset.action;
  const id     = btn.dataset.id;

  if (action === 'edit') {
    const item = galleryData.find(i => String(i.id) === String(id));
    if (item) editItem(item);
  } else if (action === 'delete') {
    deleteItem(id, btn.dataset.title);
  } else if (action === 'toggle') {
    toggleVisibility(id, parseInt(btn.dataset.active));
  }
});

document.getElementById('gallery-tbody').addEventListener('click', function(e) {
  const btn = e.target.closest('[data-action]');
  if (!btn) return;
  const action = btn.dataset.action;
  const id     = btn.dataset.id;

  if (action === 'edit') {
    const item = galleryData.find(i => String(i.id) === String(id));
    if (item) editItem(item);
  } else if (action === 'delete') {
    deleteItem(id, btn.dataset.title);
  }
});

// ── Boot ─────────────────────────────────────────────────────────────────────
loadGallery();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>