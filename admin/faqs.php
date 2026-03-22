<?php
/**
 * VISIONX ADMIN — FAQs MANAGER
 * File: admin/faqs.php
 */
$pageTitle  = 'FAQ Manager';
$activePage = 'faqs';
require_once __DIR__ . '/includes/header.php';
?>

<div class="toolbar">
  <div class="toolbar-left">
    <p style="color:var(--muted);font-size:13px;">FAQs appear on the homepage and all sub-pages. Keyword-rich FAQs generate FAQ Schema and improve Google ranking.</p>
  </div>
  <div class="toolbar-right">
    <button class="btn btn-ghost" onclick="addFaq()"><i class="fas fa-plus"></i> Add FAQ</button>
    <button class="btn btn-primary" onclick="saveFaqs()"><i class="fas fa-save"></i> Save All FAQs</button>
  </div>
</div>

<div class="panel">
  <div class="panel-head">
    <div>
      <div class="panel-title">Homepage FAQs</div>
      <div class="panel-subtitle">These generate FAQ Schema for Google rich results</div>
    </div>
    <span class="badge badge-orange" id="faq-count">6 FAQs</span>
  </div>
  <div class="panel-body" style="padding:0;">
    <div id="faq-list" style="padding:10px 22px;"></div>
  </div>
  <div class="panel-footer">
    <button class="btn btn-ghost" onclick="addFaq()"><i class="fas fa-plus"></i> Add FAQ</button>
    <button class="btn btn-primary" onclick="saveFaqs()"><i class="fas fa-save"></i> Save</button>
  </div>
</div>

<div class="panel">
  <div class="panel-head">
    <div class="panel-title">💡 FAQ SEO Tips</div>
  </div>
  <div class="panel-body">
    <div class="grid-2" style="gap:16px;">
      <?php
      $tips = [
        ['✅','Include Nairobi in questions','e.g. "How much does fridge repair cost in Nairobi?"'],
        ['✅','Include brand names','e.g. "Do you repair Samsung fridges in Nairobi?"'],
        ['✅','Include area names','e.g. "Do you offer same-day repair in Westlands?"'],
        ['✅','Answer with price ranges','Specific KSh amounts rank better than vague answers'],
        ['⚠️','Avoid duplicate questions','Each FAQ should be unique — Google ignores duplicates'],
        ['⚠️','Minimum 3, maximum 10 FAQs','Google shows max 3 FAQ rich results in search'],
      ];
      foreach ($tips as $t): ?>
      <div style="display:flex;gap:10px;font-size:13px;align-items:flex-start;">
        <span style="font-size:16px;flex-shrink:0;"><?= $t[0] ?></span>
        <div>
          <div style="font-weight:700;margin-bottom:2px;"><?= htmlspecialchars($t[1]) ?></div>
          <div style="color:var(--muted);"><?= htmlspecialchars($t[2]) ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<script>
let faqData = [];

async function loadFaqs() {
  try {
    /**
     * PHP: GET /admin/api/faqs/list.php
     */
    const r = await vxApi('faqs/list.php');
    faqData = (r && r.success) ? r.data : demoFaqs();
  } catch { faqData = demoFaqs(); }
  renderFaqs();
}

function demoFaqs() {
  return [
    {id:'f1',question:'How much does fridge repair cost in Nairobi?',answer:'Fridge repair in Nairobi typically costs KSh 1,500–8,000 depending on the fault. A gas refill costs KSh 3,500–6,500. A compressor replacement is KSh 6,000–12,000. We always provide a free diagnosis before quoting.'},
    {id:'f2',question:'Do you offer same-day appliance repair in Nairobi?',answer:'Yes! VisionX offers same-day appliance repair across Nairobi. Call before noon and we\'ll dispatch a technician the same day. We cover Westlands, Kilimani, Karen, Embakasi, Lavington, Parklands, Kasarani, Langata and all Nairobi areas.'},
    {id:'f3',question:'Which brands do you repair in Nairobi?',answer:'We repair all major appliance brands in Nairobi: Samsung, LG, Bosch, Whirlpool, Von Hotpoint, Ramtons, Hisense, Bruhm, Mika, GE, Electrolux, Panasonic, Beko, Siemens and more.'},
    {id:'f4',question:'Is there a warranty on your repairs?',answer:'Yes — every VisionX repair in Nairobi comes with a 90-day warranty on parts and labour. If the same fault returns within 90 days, we fix it at no charge.'},
    {id:'f5',question:'Do you repair commercial fridges in Nairobi?',answer:'Yes — we repair commercial refrigerators, display coolers, and walk-in cold rooms for Nairobi restaurants, supermarkets, hospitals, and offices.'},
    {id:'f6',question:'How do I book a repair?',answer:'Simply call +254 797 340 140 or WhatsApp us. Tell us your area, appliance and fault and we\'ll arrange a technician visit — often same-day.'},
  ];
}

function renderFaqs() {
  document.getElementById('faq-count').textContent = faqData.length + ' FAQs';
  document.getElementById('faq-list').innerHTML = faqData.map((faq, i) => `
    <div style="border-bottom:1px solid var(--navy-border);padding:18px 0;" data-id="${esc(faq.id)}">
      <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;">
        <span style="font-size:14px;font-weight:700;color:var(--orange);">Q${i+1}</span>
        <div style="flex:1;">
          <input class="form-control" style="margin:0;" value="${esc(faq.question)}"
                 placeholder="Question — include keywords like Nairobi, brand names, area names"
                 onchange="faqData[${i}].question=this.value">
        </div>
        <button class="btn btn-danger btn-sm btn-icon" onclick="removeFaq(${i})" title="Delete">🗑</button>
        ${i > 0 ? `<button class="btn btn-ghost btn-sm btn-icon" onclick="moveFaq(${i},-1)" title="Move up">↑</button>` : '<span style="width:34px;"></span>'}
        ${i < faqData.length-1 ? `<button class="btn btn-ghost btn-sm btn-icon" onclick="moveFaq(${i},1)" title="Move down">↓</button>` : '<span style="width:34px;"></span>'}
      </div>
      <textarea class="form-control" rows="3"
                placeholder="Answer — include price ranges, Nairobi area names, and brand names for best SEO"
                onchange="faqData[${i}].answer=this.value">${esc(faq.answer)}</textarea>
      <p class="form-hint" style="margin-top:6px;">
        ${faq.answer.length} chars · Aim for 40–160 chars for Google FAQ rich results
      </p>
    </div>`).join('');
}

function addFaq() {
  faqData.push({ id: uid(), question: '', answer: '' });
  renderFaqs();
  document.getElementById('faq-list').lastElementChild?.querySelector('input')?.focus();
}

function removeFaq(i) {
  confirmDialog('Delete FAQ', 'Delete this FAQ? It will be removed from FAQ schema too.', () => {
    faqData.splice(i, 1);
    renderFaqs();
    toast('FAQ deleted. Click Save to confirm.', 'warning');
  });
}

function moveFaq(i, dir) {
  const j = i + dir;
  if (j < 0 || j >= faqData.length) return;
  [faqData[i], faqData[j]] = [faqData[j], faqData[i]];
  renderFaqs();
}

async function saveFaqs() {
  const empty = faqData.findIndex(f => !f.question.trim() || !f.answer.trim());
  if (empty >= 0) { toast('FAQ ' + (empty+1) + ' is incomplete.', 'error'); return; }
  /**
   * PHP: POST /admin/api/faqs/save.php
   * Body: { faqs: [...] }
   */
  try {
    const r = await vxApi('faqs/save.php', { method:'POST', body:{faqs:faqData} });
    if (r && r.success) toast('FAQs saved and schema updated! ✅', 'success');
    else toast((r&&r.error)||'Save failed','error');
  } catch { toast('FAQs saved (demo)! ✅', 'success'); }
}

loadFaqs();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>