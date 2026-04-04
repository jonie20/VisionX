<?php 
require_once __DIR__ . '/admin/api/db.php';

$faqs    = [];
$reviews = [];
$gallery = [];

try {
    $stmt = db()->query("
        SELECT question, answer 
        FROM faqs 
        WHERE active = 1 
        ORDER BY sort_order ASC, id DESC
        LIMIT 6
    ");
    $faqs = $stmt->fetchAll();
} catch (Exception $e) {
    $faqs = [];
}

try {
    $stmt1 = db()->query("
        SELECT author, rating, body, area 
        FROM reviews 
        WHERE status = 'approved'
        ORDER BY id DESC
        LIMIT 6
    ");
    $reviews = $stmt1->fetchAll();
} catch (Exception $e) {
    $reviews = [];
}

try {
    $stmt2 = db()->query("
        SELECT id, title, brand, area, appliance, img_path, img_alt, fault
        FROM gallery
        WHERE active = 1
          AND status = 'after'
        ORDER BY sort_order ASC, id DESC
        LIMIT 4
    ");
    $gallery = $stmt2->fetchAll();
} catch (Exception $e) {
    $gallery = [];
}

/* Map appliance enum → FontAwesome icon class + label */
$applianceIcon = [
    'fridge'          => ['icon' => 'fa-snowflake',      'label' => 'Fridge Repair'],
    'washing-machine' => ['icon' => 'fa-tshirt',         'label' => 'Washing Machine'],
    'microwave'       => ['icon' => 'fa-broadcast-tower','label' => 'Microwave'],
    'freezer'         => ['icon' => 'fa-thermometer-empty', 'label' => 'Freezer'],
];
 ?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Appliance Repair in Nairobi | VisionX Repairs – Same-Day, Affordable Service</title>
  <link rel="icon" type="image/x-icon" href="assets/images/web/p-logo-bg.png">
  <meta name="title" content="Appliance Repair in Nairobi | VisionX Repairs – Same-Day, Affordable Service">
  <meta name="description"
    content="Expert fridge, washing machine & microwave repair in Nairobi. Serving Westlands, Kilimani, Karen, Embakasi & more. Same-day service. All brands. 90-day warranty. Call +254 797 340 140.">
  <meta name="keywords"
    content="fridge repair Nairobi, refrigerator repair Nairobi, washing machine repair Nairobi, microwave repair Nairobi, appliance repair Nairobi, Samsung fridge repair Nairobi, LG fridge repair Nairobi, same day appliance repair Nairobi">
  <meta name="robots" content="index, follow">
  <meta name="geo.region" content="KE-110">
  <meta name="geo.placename" content="Nairobi">
  <link rel="canonical" href="https://www.visionxrepairs.co.ke/">
  <meta property="og:title" content="Appliance Repair in Nairobi | VisionX Repairs – Same-Day, Affordable Service">
  <meta property="og:description"
    content="Expert fridge, washing machine & microwave repair in Nairobi. Serving Westlands, Kilimani, Karen, Embakasi & more. Same-day service. All brands. 90-day warranty. Call +254 797 340 140.">
  <meta property="og:type" content="website">
  <meta property="og:url" content="https://www.visionxrepairs.co.ke/">
  <meta property="og:image" content="https://www.visionxrepairs.co.ke/assets/images/hero-technician.jpg">
  <meta property="og:locale" content="en_KE">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="Appliance Repair in Nairobi | VisionX Repairs – Same-Day, Affordable Service">
  <meta name="twitter:description"
    content="Expert fridge, washing machine & microwave repair in Nairobi. Serving Westlands, Kilimani, Karen, Embakasi & more. Same-day service. All brands. 90-day warranty. Call +254 797 340 140.">
  <script type="application/ld+json">{
  "@context":"https://schema.org",
  "@type":"LocalBusiness",
  "name":"VisionX Appliance Repairs Nairobi",
  "description":"Expert fridge, washing machine & microwave repair in Nairobi. Serving Westlands, Kilimani, Karen, Embakasi & more. Same-day service. All brands. 90-day warranty. Call +254 797 340 140.",
  "url":"https://www.visionxrepairs.co.ke/",
  "telephone":"+254797340140",
  "email":"info@visionxrepairs.co.ke",
  "address":{"@type":"PostalAddress","addressLocality":"Nairobi","addressCountry":"KE"},
  "geo":{"@type":"GeoCoordinates","latitude":-1.286389,"longitude":36.817223},
  "openingHours":"Mo-Su 07:00-20:00",
  "priceRange":"KSh 1,500 - KSh 15,000",
  "areaServed":["Westlands","Kilimani","Karen","Embakasi","Lavington","Parklands","Kasarani","Langata"],
  "aggregateRating":{"@type":"AggregateRating","ratingValue":"4.9","reviewCount":"89"},
  "image":[
    "https://www.visionxrepairs.co.ke/assets/images/repairs/fridge-repair-nairobi.jpg",
    "https://www.visionxrepairs.co.ke/assets/images/repairs/washing-machine-repair-nairobi.jpg",
    "https://www.visionxrepairs.co.ke/assets/images/repairs/microwave-repair-nairobi.jpg",
    "https://www.visionxrepairs.co.ke/assets/images/repairs/commercial-fridge-repair-nairobi.jpg"
  ]
}</script>
  <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css"
    integrity="sha384-AYmEC3Yw5cVb3ZcuHtOA93w35dYTsvhLPVnYs9eStHfGJvOvKxVfELGroGkvsg+p" crossorigin="anonymous" />
  <link rel="preconnect" href="https://fonts.googleapis.com">

  <link rel="stylesheet" href="assets/styles.css">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
    rel="stylesheet">
  <style>
    /* ── HERO IMAGE ── */
    .hero-img-wrap {
      position: relative;
      border-radius: 20px;
      overflow: visible;
      width: 100%;
      max-width: 560px;
    }
    .hero-img-wrap img {
      width: 100%;
      height: 480px;
      object-fit: cover;
      border-radius: 20px;
      display: block;
      box-shadow: 0 24px 64px rgba(0,0,0,.45);
    }
    /* Fallback when image is missing */
    .hero-img-wrap.hero-img-fallback {
      height: 480px;
      border-radius: 20px;
      background: linear-gradient(135deg, #1a1a2e 0%, #16213e 60%, #e85d04 100%);
      display: flex; align-items: center; justify-content: center;
      font-size: 80px;
    }
    .hero-img-wrap.hero-img-fallback::after {
      content: '🔧';
    }
    .hero-img-wrap.hero-img-fallback img { display: none; }

    /* Floating badge base */
    .hero-badge {
      position: absolute;
      background: #fff;
      border-radius: 12px;
      padding: 10px 14px;
      display: flex; align-items: center; gap: 10px;
      box-shadow: 0 8px 28px rgba(0,0,0,.22);
      white-space: nowrap;
      animation: badge-float 3s ease-in-out infinite;
    }
    .hero-badge strong { display:block; font-size:13px; font-weight:700; color:#0f0f0f; line-height:1.2; }
    .hero-badge span   { display:block; font-size:11px; color:#888; }
    .hb-icon { font-size: 22px; }

    /* Positions */
    .hero-badge--tl { top: 24px; left: -28px; animation-delay: 0s; }
    .hero-badge--br { bottom: 40px; right: -28px; animation-delay: 1.1s; }
    .hero-badge--rating {
      bottom: -18px; left: 50%; transform: translateX(-50%);
      flex-direction: column; align-items: center; gap: 2px;
      padding: 10px 20px;
      animation-delay: 0.6s;
    }
    .hb-stars { color: #f59e0b; font-size: 16px; letter-spacing: 2px; }
    .hb-rcount { font-size: 11px; color: #555; font-weight: 600; }

    @keyframes badge-float {
      0%, 100% { transform: translateY(0); }
      50%       { transform: translateY(-6px); }
    }
    .hero-badge--rating {
      animation-name: badge-float-centered;
    }
    @keyframes badge-float-centered {
      0%, 100% { transform: translateX(-50%) translateY(0); }
      50%       { transform: translateX(-50%) translateY(-6px); }
    }

    /* Responsive: hide side badges on small screens */
    @media (max-width: 768px) {
      .hero-badge--tl, .hero-badge--br { display: none; }
      .hero-badge--rating { bottom: 12px; }
      .hero-img-wrap img, .hero-img-wrap.hero-img-fallback { height: 300px; }
    }

    /* ── PROOF / IMAGE SECTION ── */
    .proof-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 20px;
      margin-top: 40px;
    }
    @media (max-width: 1024px) { .proof-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 600px)  { .proof-grid { grid-template-columns: 1fr; } }

    .proof-card {
      border-radius: var(--radius, 14px);
      overflow: hidden;
      background: #fff;
      box-shadow: 0 4px 24px rgba(0,0,0,.08);
      transition: transform .25s, box-shadow .25s;
    }
    .proof-card:hover { transform: translateY(-5px); box-shadow: 0 12px 40px rgba(0,0,0,.14); }

    .proof-img-wrap {
      position: relative;
      margin: 0;
      aspect-ratio: 4/3;
      overflow: hidden;
      background: #f5f5f5;
    }
    .proof-img-wrap img {
      width: 100%; height: 100%;
      object-fit: cover;
      display: block;
      transition: transform .4s ease;
    }
    .proof-card:hover .proof-img-wrap img { transform: scale(1.04); }

    /* Fallback placeholder when image is missing */
    .proof-img-wrap.img-fallback {
      display: flex; align-items: center; justify-content: center;
      background: linear-gradient(135deg, #fff3e6 0%, #ffe0c4 100%);
    }
    .proof-img-wrap.img-fallback::after {
      content: '🔧';
      font-size: 56px;
    }

    .proof-badge {
      position: absolute;
      bottom: 10px; left: 10px;
      background: var(--orange, #e85d04);
      color: #fff;
      font-size: 11px;
      font-weight: 700;
      padding: 5px 10px;
      border-radius: 20px;
      display: flex; align-items: center; gap: 5px;
      text-transform: uppercase;
      letter-spacing: .04em;
    }

    .proof-caption {
      padding: 14px 16px 16px;
      display: flex; flex-direction: column; gap: 4px;
    }
    .proof-caption strong {
      font-size: 14px;
      font-weight: 700;
      color: var(--dark, #0f0f0f);
    }
    .proof-caption span {
      font-size: 12px;
      color: var(--muted, #888);
    }
  </style>
</head>
<script
  type="application/ld+json">{"@context":"https://schema.org","@type":"FAQPage","mainEntity":[{"@type":"Question","name":"How much does fridge repair cost in Nairobi?","acceptedAnswer":{"@type":"Answer","text":"Fridge repair in Nairobi typically costs <strong>KSh 1,500–8,000</strong> depending on the fault. A gas refill costs KSh 3,500–6,500. A compressor replacement is KSh 6,000–12,000. We always provide a "}},{"@type":"Question","name":"Do you offer same-day appliance repair in Nairobi?","acceptedAnswer":{"@type":"Answer","text":"Yes! VisionX offers <strong>same-day appliance repair across Nairobi</strong>. Call before noon and we'll dispatch a technician to your home the same day. We cover Westlands, Kilimani, Karen, Embakasi"}},{"@type":"Question","name":"Which brands do you repair in Nairobi?","acceptedAnswer":{"@type":"Answer","text":"We repair <strong>all major appliance brands</strong> in Nairobi: Samsung, LG, Bosch, Whirlpool, Von Hotpoint, Ramtons, Hisense, Bruhm, Mika, GE, Electrolux, Panasonic, Beko, Siemens and more."}},{"@type":"Question","name":"Is there a warranty on your repairs?","acceptedAnswer":{"@type":"Answer","text":"Yes — every VisionX repair in Nairobi comes with a <strong>90-day warranty</strong> on parts and labour. If the same fault returns within 90 days, we fix it at no charge."}},{"@type":"Question","name":"Do you repair commercial fridges in Nairobi?","acceptedAnswer":{"@type":"Answer","text":"Yes — we repair commercial refrigerators, display coolers, and walk-in cold rooms for Nairobi restaurants, supermarkets, hospitals, and offices. <a href='service/commercial/index.html' style='color:va"}},{"@type":"Question","name":"How do I book a repair?","acceptedAnswer":{"@type":"Answer","text":"Simply <a href='tel:+254797340140' style='color:var(--orange)'>call +254797340140</a> or <a href='https://api.whatsapp.com/send?phone=254797340140&text=Hello%21%20I%20need%20appliance%20repair%20in%20"}}]}</script>
<script type="application/ld+json">{
  "@context":"https://schema.org","@type":"FAQPage",
  "mainEntity":[
    {"@type":"Question","name":"How much does fridge repair cost in Nairobi?","acceptedAnswer":{"@type":"Answer","text":"KSh 1,500–8,000 depending on fault. Gas refill KSh 3,500–6,500. Free diagnosis before quoting."}},
    {"@type":"Question","name":"Do you offer same-day appliance repair in Nairobi?","acceptedAnswer":{"@type":"Answer","text":"Yes — call before noon for same-day service across all Nairobi areas."}}
  ]
}</script>

<body>
  <div class="announce-bar">
    🔥 Same-Day Service Across Nairobi &nbsp;·&nbsp; &nbsp;·&nbsp;
    <a href="tel:+254797340140">Call Now: +254797340140</a>
  </div>
  <header class="site-header">
  <div class="header-inner">
    <a href="index.html" class="site-logo">
      <div class="logo-mark"><img src="assets/images/web/p-logo-bg.png" alt="VisionX Logo" width="auto"></div>
      <span class="logo-text">Vision<span>X</span> Repairs</span>
    </a>
    <nav class="main-nav">
      <a href="index.html">Home</a>
      <a href="index.html#services">Services</a>
      <a href="index.html#brands">Brands</a>
      <a href="index.html#areas">Areas</a>
      <a href="gallery/gallery.html">Gallery</a>
      <a href="index.html#faq">FAQ</a>
      <a href="index.html#reviews">Reviews</a>
      <a href="tel:+254797340140" class="nav-call-btn"><i class="fas fa-phone"></i> Call Now</a>
    </nav>
    <button class="hamburger" id="nav-open" aria-label="Open menu">
      <span></span><span></span><span></span>
    </button>
  </div>
</header>
<div class="nav-overlay" id="nav-overlay"></div>
<nav class="mobile-nav" id="mobile-nav">
  <div class="mobile-nav-head">
    <span>VisionX Repairs</span>
    <button class="close-btn" id="nav-close">&#x2715;</button>
  </div>
  <a href="index.html"> Home</a>
  <a href="index.html#services"> Services</a>
  <a href="index.html#brands"> Brands</a>
  <a href="index.html#areas"> Areas</a>
  <a href="index.html#faq"> FAQ</a>
  <a href="gallery/gallery.html"> Repair Gallery</a>
  <a href="index.html#reviews"> Reviews</a>
  <a href="service/emergency/index.html"> Emergency Repair</a>
  <a href="service/commercial/index.html"> Commercial Repair</a>
  <div class="mobile-nav-footer">
    <a href="tel:+254797340140" class="mnf-call"><i class="fas fa-phone"></i> Call +254797340140</a>
    <a href="https://api.whatsapp.com/send?phone=254797340140&text=Hello%21%20I%20need%20appliance%20repair%20in%20Nairobi.%20Please%20assist." target="_blank" class="mnf-wa"><i class="fab fa-whatsapp"></i> WhatsApp</a>
  </div>
</nav>
  <div class="trust-bar">
    <div class="trust-ticker">
      <div class="trust-ticker-item"><i class="fas fa-bolt"></i>Same-Day Service</div>
      <div class="trust-ticker-item"><i class="fas fa-shield-alt"></i>90-Day Warranty</div>
      <div class="trust-ticker-item"><i class="fas fa-clock"></i>24/7 Emergency</div>
      <div class="trust-ticker-item"><i class="fas fa-star"></i>4.9★ Rated</div>
      <div class="trust-ticker-item"><i class="fas fa-map-marker-alt"></i>All Nairobi Areas</div>
      <div class="trust-ticker-item"><i class="fas fa-tools"></i>All Major Brands</div>
      <div class="trust-ticker-item"><i class="fas fa-tags"></i>Affordable Pricing</div>
      <div class="trust-ticker-item"><i class="fas fa-bolt"></i>Same-Day Service</div>
      <div class="trust-ticker-item"><i class="fas fa-shield-alt"></i>90-Day Warranty</div>
      <div class="trust-ticker-item"><i class="fas fa-clock"></i>24/7 Emergency</div>
      <div class="trust-ticker-item"><i class="fas fa-star"></i>4.9★ Rated</div>
      <div class="trust-ticker-item"><i class="fas fa-map-marker-alt"></i>All Nairobi Areas</div>
      <div class="trust-ticker-item"><i class="fas fa-tools"></i>All Major Brands</div>
      <div class="trust-ticker-item"><i class="fas fa-tags"></i>Affordable Pricing</div>
    </div>
  </div>

  <!-- ── HOME HERO ── -->
  <section class="home-hero" aria-label="Appliance repair Nairobi">
    <div class="home-hero-grid"></div>
    <div class="home-hero-inner">
      <div>
        <span class="label-tag">Nairobi's Trusted Repair Experts</span>
        <h1>Fast <span class="span-orange">Appliance Repair</span> Across Nairobi</h1>
        <p>Expert fridge, washing machine &amp; microwave repair — Westlands, Kilimani, Karen, Embakasi &amp; all
          Nairobi areas. Same-day service. All major brands. Free diagnosis.</p>
        <div class="btn-group">
          <a href="tel:+254797340140" class="btn-primary"><i class="fas fa-phone"></i> Call Now</a>
          <a href="https://api.whatsapp.com/send?phone=254797340140&text=Hello%21%20I%20need%20appliance%20repair%20in%20Nairobi.%20Please%20assist."
            target="_blank" class="btn-wa"><i class="fab fa-whatsapp"></i> WhatsApp</a>
        </div>
        <div class="hero-stats">
          <div>
            <div class="hero-stat-num">500<span>+</span></div>
            <div class="hero-stat-label">Nairobi Repairs</div>
          </div>
          <div>
            <div class="hero-stat-num">4.9<span>★</span></div>
            <div class="hero-stat-label">Average Rating</div>
          </div>
          <div>
            <div class="hero-stat-num">90<span>d</span></div>
            <div class="hero-stat-label">Warranty</div>
          </div>
          <div>
            <div class="hero-stat-num">24<span>/7</span></div>
            <div class="hero-stat-label">Emergency</div>
          </div>
        </div>
      </div>
      <div class="home-hero-visual">
        <div class="hero-img-wrap">
          <img
            src="assets/images/hero-technician.jpg"
            alt="VisionX certified appliance repair technician fixing a fridge in a Nairobi home"
            width="600" height="520"
            fetchpriority="high"
            onerror="this.closest('.hero-img-wrap').classList.add('hero-img-fallback')"
          >
          <!-- Floating trust badges -->
          <div class="hero-badge hero-badge--tl">
            <span class="hb-icon">🛡️</span>
            <div><strong>90-Day Warranty</strong><span>Parts &amp; labour</span></div>
          </div>
          <div class="hero-badge hero-badge--br">
            <span class="hb-icon">⚡</span>
            <div><strong>Same-Day Service</strong><span>Call before noon</span></div>
          </div>
          <div class="hero-badge hero-badge--rating">
            <span class="hb-stars">★★★★★</span>
            <span class="hb-rcount">4.9 · 89 reviews</span>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ── SOCIAL PROOF IMAGE STRIP ── -->
  <section class="section section-proof" aria-label="VisionX appliance repair technicians at work in Nairobi">
    <div class="container">
      <div class="section-header center reveal">
        <span class="label-tag">Real Repairs. Real Nairobi Homes.</span>
        <h2>See Our <span class="span-orange">Work in Action</span></h2>
        <p>Every photo is a real repair — taken at a customer's home in Nairobi. No stock photos, just results.</p>
      </div>
      <div class="proof-grid">

        <?php if (!empty($gallery)): ?>
          <?php foreach ($gallery as $item): ?>
            <?php
              $appliance = $item['appliance'] ?? 'fridge';
              $meta      = $applianceIcon[$appliance] ?? ['icon' => 'fa-tools', 'label' => 'Repair'];
              $alt       = !empty($item['img_alt'])
                             ? htmlspecialchars($item['img_alt'])
                             : htmlspecialchars($item['title'] . ' – VisionX Repairs Nairobi');
              $caption   = trim($item['area'] ?? '');
              if (!empty($item['fault'])) {
                  $caption .= ($caption ? ' · ' : '') . htmlspecialchars($item['fault']);
              }
            ?>
            <div class="proof-card reveal">
              <figure class="proof-img-wrap">
                <img
                  src="<?php echo htmlspecialchars($item['img_path']); ?>"
                  alt="<?php echo $alt; ?>"
                  width="480" height="360"
                  loading="lazy"
                  onerror="this.closest('.proof-img-wrap').classList.add('img-fallback')"
                >
                <figcaption class="proof-badge">
                  <i class="fas <?php echo $meta['icon']; ?>"></i>
                  <?php echo $meta['label']; ?>
                </figcaption>
              </figure>
              <div class="proof-caption">
                <strong><?php echo htmlspecialchars($item['title']); ?></strong>
                <?php if ($caption): ?>
                  <span><?php echo $caption; ?></span>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>

        <?php else: ?>
          <p style="text-align:center;grid-column:1/-1;color:var(--muted);">Gallery images coming soon.</p>
        <?php endif; ?>

      </div>
      <p class="text-center mt-24" style="font-size:14px;color:var(--muted);">
        Want to see more? <a href="gallery/gallery.html" style="color:var(--orange);font-weight:700;">View the full repair gallery →</a>
      </p>
    </div>
  </section>

  <!-- ── SERVICES ── -->
  <section class="section" id="services" aria-label="Appliance repair services Nairobi">
    <div class="container">
      <div class="section-header center reveal">
        <span class="label-tag">What We Fix</span>
        <h2>Our <span class="span-orange">Repair Services</span> in Nairobi</h2>
        <p>Full appliance repair coverage across all Nairobi areas. Our certified technicians come to your door —
          same-day service available.</p>
      </div>
      <div class="services-grid">
        <div class="service-card reveal">
          <div class="sc-icon"><i class="fas fa-bolt"></i></div>
          <h3>Emergency Fridge Repair</h3>
          <p>A broken fridge is an emergency — every hour costs you food and money. VisionX Repairs operates a 24/7
            emergency fridge ...</p>
          <a href="services/em-fridge-repair.html">Learn more →</a>
        </div>
        <div class="service-card reveal">
          <div class="sc-icon"><i class="fas fa-store"></i></div>
          <h3>Commercial Fridge Repair</h3>
          <p>A faulty commercial fridge can mean thousands in lost stock and a failed health inspection. VisionX repairs
            commercial r...</p>
          <a href="services/com-fridge-repair.html">Learn more →</a>
        </div>
        <div class="service-card reveal">
          <div class="sc-icon"><i class="fas fa-snowflake"></i></div>
          <h3>Freezer Repair Nairobi</h3>
          <p>VisionX repairs all types of freezers in Nairobi — chest freezers, upright freezers, and commercial blast
            freezers — for...</p>
          <a href="services/freezer-repair.html">Learn more →</a>
        </div>
        <div class="service-card reveal">
          <div class="sc-icon"><i class="fas fa-tshirt"></i></div>
          <h3>Washing Machine Repair Nairobi</h3>
          <p>VisionX Repairs provides expert washing machine repair across all Nairobi areas. We fix front-loaders,
            top-loaders, and ...</p>
          <a href="services/washing-repair.html">Learn more →</a>
        </div>
        <div class="service-card reveal">
          <div class="sc-icon"><i class="fas fa-broadcast-tower"></i></div>
          <h3>Microwave Repair Nairobi</h3>
          <p>Microwave not heating? VisionX repairs all major microwave brands in Nairobi — LG, Samsung, Panasonic,
            Whirlpool and mor...</p>
          <a href="services/microwave-repair.html">Learn more →</a>
        </div>
      </div>
    </div>
  </section>

  <!-- ── BRANDS ── -->
  <section class="section bg-off" id="brands" aria-label="Appliance brands repaired in Nairobi">
    <div class="container">
      <div class="section-header center reveal">
        <span class="label-tag">All Major Brands</span>
        <h2>Brands We <span class="span-orange">Repair in Nairobi</span></h2>
        <p>Trained to repair every major appliance brand in Nairobi. Click a brand for dedicated repair information.</p>
      </div>
      <div class="brand-grid reveal"><a href="brands/samsung repair.html" class="brand-card"><span
            class="bc-emoji">🔵</span>Samsung</a><a href="brands/lg-repair.html" class="brand-card"><span
            class="bc-emoji">🔴</span>LG</a><a href="brands/bosch-repair.html" class="brand-card"><span
            class="bc-emoji">⚙️</span>Bosch</a><a href="brands/whirlpool-repair.html" class="brand-card"><span
            class="bc-emoji">🌀</span>Whirlpool</a><a href="brands/von-repair.html" class="brand-card"><span
            class="bc-emoji">🟡</span>Von Hotpoint</a><a href="brands/hisense-repair.html" class="brand-card"><span
            class="bc-emoji">🟢</span>Hisense</a><a href="brands/ramtons-repair.html" class="brand-card"><span
            class="bc-emoji">🟠</span>Ramtons</a></div>
    </div>
  </section>

  <!-- ── TRUST ── -->
  <section class="section" aria-label="Why choose VisionX appliance repair Nairobi">
    <div class="container">
      <div class="section-header center reveal">
        <span class="label-tag">Why Nairobi Chooses VisionX</span>
        <h2>Repair You Can <span class="span-orange">Trust</span></h2>
      </div>
      <div class="trust-grid">
        <div class="trust-card reveal">
          <div class="tc-icon"><i class="fas fa-bolt"></i></div>
          <h4>Same-Day Service</h4>
          <p>Call before noon for guaranteed same-day appliance repair anywhere in Nairobi.</p>
        </div>
        <div class="trust-card reveal">
          <div class="tc-icon"><i class="fas fa-shield-alt"></i></div>
          <h4>90-Day Warranty</h4>
          <p>All repairs backed by a 90-day parts and labour warranty for peace of mind.</p>
        </div>
        <div class="trust-card reveal">
          <div class="tc-icon"><i class="fas fa-tags"></i></div>
          <h4>Transparent Pricing</h4>
          <p>No hidden charges. You see the full quote before we start any repair.</p>
        </div>
        <div class="trust-card reveal">
          <div class="tc-icon"><i class="fas fa-clock"></i></div>
          <h4>24/7 Emergency</h4>
          <p>Fridge breakdown at midnight? Our emergency repair line is always open.</p>
        </div>
        <div class="trust-card reveal">
          <div class="tc-icon"><i class="fas fa-user-check"></i></div>
          <h4>Certified Technicians</h4>
          <p>Our Nairobi technicians are trained and certified on all major appliance brands.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- ── AREAS ── -->
  <section class="section bg-off" id="areas" aria-label="Appliance repair areas Nairobi">
    <div class="container">
      <div class="section-header center reveal">
        <span class="label-tag">Full Nairobi Coverage</span>
        <h2>We Repair Appliances <span class="span-orange">Across Nairobi</span></h2>
        <p>Click your area for dedicated local repair information, pricing, and same-day availability.</p>
      </div>
      <div class="area-grid"><a href="areas/westlands.html" class="area-card reveal" onclick="void(0)">
          <div class="ac-icon"><i class="fas fa-map-marker-alt"></i></div>
          <h4>Westlands</h4>
          <p>10–15 min · Same-day</p>
        </a><a href="areas/kilimani.html" class="area-card reveal" onclick="void(0)">
          <div class="ac-icon"><i class="fas fa-map-marker-alt"></i></div>
          <h4>Kilimani</h4>
          <p>15–20 min · Same-day</p>
        </a><a href="areas/karen.html" class="area-card reveal" onclick="void(0)">
          <div class="ac-icon"><i class="fas fa-map-marker-alt"></i></div>
          <h4>Karen</h4>
          <p>20–30 min · Same-day</p>
        </a><a href="areas/embakasi.html" class="area-card reveal" onclick="void(0)">
          <div class="ac-icon"><i class="fas fa-map-marker-alt"></i></div>
          <h4>Embakasi</h4>
          <p>20–30 min · Same-day</p>
        </a><a href="areas/lavington.html" class="area-card reveal" onclick="void(0)">
          <div class="ac-icon"><i class="fas fa-map-marker-alt"></i></div>
          <h4>Lavington</h4>
          <p>15–20 min · Same-day</p>
        </a><a href="areas/parklands.html" class="area-card reveal" onclick="void(0)">
          <div class="ac-icon"><i class="fas fa-map-marker-alt"></i></div>
          <h4>Parklands</h4>
          <p>10–15 min · Same-day</p>
        </a><a href="areas/kasarani.html" class="area-card reveal" onclick="void(0)">
          <div class="ac-icon"><i class="fas fa-map-marker-alt"></i></div>
          <h4>Kasarani</h4>
          <p>25–35 min · Same-day</p>
        </a><a href="areas/langata.html" class="area-card reveal" onclick="void(0)">
          <div class="ac-icon"><i class="fas fa-map-marker-alt"></i></div>
          <h4>Langata</h4>
          <p>20–30 min · Same-day</p>
        </a></div>
      <p class="text-center mt-24" style="font-size:14px; color:var(--muted);">Don't see your area? <a
          href="tel:+254797340140" style="color:var(--orange); font-weight:700;">Call us</a> — we very likely cover it!
      </p>
    </div>
  </section>

  <!-- ── COMPARE ── -->
  <section class="section bg-off" aria-label="Why choose VisionX over competitors Nairobi">
    <div class="container">
      <div class="section-header center reveal">
        <span class="label-tag">How We Compare</span>
        <h2>Why Nairobi Customers <span class="span-orange">Choose VisionX</span></h2>
      </div>
      <div class="reveal" style="overflow-x:auto;">
        <table class="compare-table" style="min-width:500px;">
          <thead>
            <tr>
              <th>Feature</th>
              <th>✅ VisionX Repairs</th>
              <th>Typical Nairobi Repairer</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>Free Diagnosis Before Charging</td>
              <td><span class="yes">✓</span></td>
              <td class="no">Sometimes</td>
            </tr>
            <tr>
              <td>90-Day Parts &amp; Labour Warranty</td>
              <td><span class="yes">✓</span></td>
              <td class="no">Rarely</td>
            </tr>
            <tr>
              <td>Transparent Pricing (No Hidden Fees)</td>
              <td><span class="yes">✓</span></td>
              <td class="no">Often hidden</td>
            </tr>
            <tr>
              <td>Same-Day Service Across Nairobi</td>
              <td><span class="yes">✓</span></td>
              <td class="no">1–3 days wait</td>
            </tr>
            <tr>
              <td>24/7 Emergency Line</td>
              <td><span class="yes">✓</span></td>
              <td class="no">Business hours only</td>
            </tr>
            <tr>
              <td>All Major Brands (14+)</td>
              <td><span class="yes">✓</span></td>
              <td class="no">Limited brands</td>
            </tr>
            <tr>
              <td>Commercial Fridge Repair</td>
              <td><span class="yes">✓</span></td>
              <td class="no">Rarely offered</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </section>

  <!-- ── FAQ ── -->
  <section class="section" id="faq" aria-label="Appliance repair FAQ Nairobi">
    <div class="container">
      <div class="section-header center reveal">
        <span class="label-tag">Got Questions?</span>
        <h2>Frequently Asked <span class="span-orange">Questions</span></h2>
        <p>Everything you need to know about appliance repair in Nairobi.</p>
      </div>
      

      <?php if (!empty($faqs)): ?>
          <?php foreach ($faqs as $faq): ?>
              <div class="faq-item reveal">
                  <div class="faq-question">
                      <span><?php echo htmlspecialchars($faq['question']); ?></span>
                      <span class="faq-icon">+</span>
                  </div>
                  <div class="faq-answer">
                      <?php echo nl2br(htmlspecialchars($faq['answer'])); ?>
                  </div>
              </div>
          <?php endforeach; ?>
      <?php else: ?>
          <p style="text-align:center;">No FAQs available at the moment.</p>
      <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- ── BLOG ── -->
  <section class="section bg-off" id="blog" aria-label="Appliance repair guides Nairobi">
    <div class="container">
      <div class="section-header center reveal">
        <span class="label-tag">Expert Advice</span>
        <h2>Appliance Repair <span class="span-orange">Guides for Nairobi</span></h2>
      </div>
      <div class="blog-grid">
        <div class="blog-card reveal">
          <div class="blog-thumb" style="background:#fff3e6;">🥶</div>
          <div class="blog-body">
            <div class="blog-tag">Fridge Repair Nairobi</div>
            <h3>Why Is My Fridge Not Cooling? (Nairobi 2025)</h3>
            <p>Common causes of fridge cooling failure in Nairobi — from dirty condenser coils to compressor faults —
              and when to call a technician.</p>
            <a href="tel:+254797340140" class="blog-link">Book a Repair →</a>
          </div>
        </div>
        <div class="blog-card reveal">
          <div class="blog-thumb" style="background:#e8f5e9;">💰</div>
          <div class="blog-body">
            <div class="blog-tag">Pricing Guide</div>
            <h3>Cost of Fridge Repair in Nairobi (2025 Prices)</h3>
            <p>A complete breakdown of fridge repair costs in Nairobi — gas refills, compressors, thermostats and more.
            </p>
            <a href="#pricing" class="blog-link">View Price Guide →</a>
          </div>
        </div>
        <div class="blog-card reveal">
          <div class="blog-thumb" style="background:#e3f2fd;">💧</div>
          <div class="blog-body">
            <div class="blog-tag">Troubleshooting</div>
            <h3>Fridge Leaking Water in Nairobi? Here's Why</h3>
            <p>Water pooling inside or under your fridge is usually a blocked defrost drain or worn door seal — here's
              how to tell and what to do.</p>
            <a href="https://api.whatsapp.com/send?phone=254797340140&text=My%20fridge%20is%20leaking%20water%20in%20Nairobi."
              target="_blank" class="blog-link">WhatsApp for Help →</a>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ── REVIEWS ── -->
  <section class="section" id="reviews" aria-label="VisionX customer reviews Nairobi">
    <div class="container">
      <div class="section-header center reveal">
        <span class="label-tag">What Nairobi Says</span>
        <h2>Customer <span class="span-orange">Reviews</span></h2>
      </div>
      <div class="reviews-grid">
        <?php if (!empty($reviews)): ?>
          <?php foreach ($reviews as $review): ?>

            <div class="review-card reveal">

              <!-- ⭐ Stars -->
              <div class="review-stars">
                <?php
                $stars = (int)$review['rating'];
                for ($i = 1; $i <= 5; $i++) {
                    echo $i <= $stars ? '★' : '☆';
                }
                ?>
              </div>

              <!-- 💬 Message -->
              <p class="review-text">
                "<?php echo htmlspecialchars($review['body']); ?>"
              </p>

              <!-- 👤 Author -->
              <div class="review-author">
                <?php echo htmlspecialchars($review['author']); ?>
              </div>

              <!-- 📍 Area -->
              <div class="review-area">
                📍 <?php echo htmlspecialchars($review['area']); ?>, Nairobi
              </div>

            </div>

          <?php endforeach; ?>
        <?php else: ?>
          <p style="text-align:center;">No reviews available yet.</p>
        <?php endif; ?>
      </div>
      <div class="text-center mt-32 reveal">
        <a href="https://g.page/r/YOUR_GOOGLE_PLACE_ID/review" target="_blank" class="btn-outline"><i
            class="fas fa-star"></i> Leave a Google Review</a>
      </div>
    </div>
  </section>

  <!-- ── GOOGLE BIZ CTA ── -->
  <section class="section-sm" style="background:var(--off-white);" aria-label="Find VisionX on Google Maps Nairobi">
    <div class="container">
      <div
        style="background:#fff;border-radius:var(--radius);padding:36px;display:flex;align-items:center;gap:28px;flex-wrap:wrap;box-shadow:var(--shadow);">
        <div style="font-size:52px;">📍</div>
        <div style="flex:1;min-width:200px;">
          <span class="label-tag">Google Business Profile</span>
          <h3 style="margin-top:8px;">Find &amp; Review Us on <span class="span-orange">Google Maps</span></h3>
          <p style="font-size:14px;margin:10px 0 20px;">Search "VisionX Repairs Nairobi" on Google Maps. Your review
            helps other Nairobi customers find trusted repair service — and helps us rank higher!</p>
          <div class="btn-group">
            <a href="https://g.page/r/YOUR_GOOGLE_PLACE_ID/review" target="_blank" class="btn-primary"
              style="font-size:14px;padding:11px 20px;"><i class="fas fa-star"></i> Leave a Review</a>
            <a href="https://maps.google.com/?q=VisionX+Repairs+Nairobi" target="_blank" class="btn-outline"
              style="font-size:14px;padding:11px 20px;"><i class="fas fa-map-marker-alt"></i> View on Maps</a>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ── CTA ── -->
  <section class="cta-section" aria-label="Book appliance repair Nairobi">
    <div class="container">
      <span class="label-tag" style="background:rgba(255,255,255,.15);color:#fff;">Book Today</span>
      <h2 style="color:#fff;margin-top:10px;">Need Appliance Repair in <span class="span-orange">Nairobi</span> Today?
      </h2>
      <p>Same-day service · Free diagnosis · 90-day warranty · All Nairobi areas</p>
      <div class="btn-group" style="justify-content:center;">
        <a href="tel:+254797340140" class="btn-white"><i class="fas fa-phone"></i> Call +254797340140</a>
        <a href="https://api.whatsapp.com/send?phone=254797340140&text=Hello%21%20I%20need%20appliance%20repair%20in%20Nairobi.%20Please%20assist."
          target="_blank" class="btn-wa"><i class="fab fa-whatsapp"></i> WhatsApp Now</a>
      </div>
    </div>
  </section>

  <footer class="site-footer">
    <div class="container">
      <div class="footer-grid">
        <div>
          <div class="footer-logo">
            <div class="logo-mark">V</div>
            <span class="logo-text text-white">Vision<span style="color:var(--orange)">X</span></span>
          </div>
          <p class="footer-about">Nairobi's trusted appliance repair specialists. Fast, reliable, and affordable fridge,
            washing machine &amp; microwave repair across all Nairobi areas.</p>
          <div class="footer-social">
            <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
            <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
            <a href="https://www.youtube.com/@VisionXRepairs" aria-label="YouTube" target="_blank"><i
                class="fab fa-youtube"></i></a>
            <a href="https://api.whatsapp.com/send?phone=254797340140&text=Hello%21%20I%20need%20appliance%20repair%20in%20Nairobi.%20Please%20assist."
              aria-label="WhatsApp" target="_blank"><i class="fab fa-whatsapp"></i></a>
          </div>
        </div>
        <div class="footer-col">
          <h5>Services</h5>
          <a href="index.html#services">Fridge Repair</a>
          <a href="index.html#services">Washing Machine</a>
          <a href="index.html#services">Microwave Repair</a>
          <a href="service/freezer/index.html">Freezer Repair</a>
          <a href="service/emergency/index.html">Emergency Repair</a>
          <a href="service/commercial/index.html">Commercial Repair</a>
        </div>
        <div class="footer-col">
          <h5>Nairobi Areas</h5>
          <a href="area/westlands/index.html">Westlands</a>
          <a href="area/kilimani/index.html">Kilimani</a>
          <a href="area/karen/index.html">Karen</a>
          <a href="area/embakasi/index.html">Embakasi</a>
          <a href="area/lavington/index.html">Lavington</a>
          <a href="area/parklands/index.html">Parklands</a>
          <a href="area/kasarani/index.html">Kasarani</a>
          <a href="area/langata/index.html">Langata</a>
        </div>
        <div class="footer-col">
          <h5>Contact Us</h5>
          <div class="footer-contact-item"><i class="fas fa-map-marker-alt"></i><span>Nairobi, Kenya</span></div>
          <div class="footer-contact-item"><i class="fas fa-phone"></i><a href="tel:+254797340140">+254797340140</a>
          </div>
          <div class="footer-contact-item"><i class="fas fa-envelope"></i><a
              href="mailto:info@visionxrepairs.co.ke">info@visionxrepairs.co.ke</a></div>
          <div class="footer-contact-item"><i class="fas fa-clock"></i><span>Mon–Sun: 7am–8pm<br>24/7 Emergency
              Line</span></div>
        </div>
      </div>
    </div>
    <div class="footer-bottom">
      <p>© 2025 VisionX Repairs – All Rights Reserved &nbsp;|&nbsp; Nairobi, Kenya &nbsp;|&nbsp;
        Designed by <a href="https://inoootechnologies.com/" target="_blank">Inooo Technologies</a></p>
    </div>
  </footer>
  <div class="fab-group">
    <a href="tel:+254797340140" class="fab-btn fab-call"><i class="fas fa-phone"></i><span>Call Now</span></a>
    <a href="https://api.whatsapp.com/send?phone=254797340140&text=Hello%21%20I%20need%20appliance%20repair%20in%20Nairobi."
      target="_blank" class="fab-btn fab-wa"><i class="fab fa-whatsapp"></i><span>WhatsApp</span></a>
  </div>
  <div class="bottom-bar">
    <a href="tel:+254797340140" class="bb-call"><i class="fas fa-phone"></i> Call Now</a>
    <a href="https://api.whatsapp.com/send?phone=254797340140&text=Hello%21%20I%20need%20appliance%20repair%20in%20Nairobi."
      target="_blank" class="bb-wa"><i class="fab fa-whatsapp"></i> WhatsApp</a>
  </div>

  <script>
    // ── Mobile Nav ──
    (function () {
      var openBtn = document.getElementById('nav-open');
      var closeBtn = document.getElementById('nav-close');
      var nav = document.getElementById('mobile-nav');
      var overlay = document.getElementById('nav-overlay');
      function open() { nav.classList.add('open'); overlay.classList.add('open'); document.body.style.overflow = 'hidden'; }
      function close() { nav.classList.remove('open'); overlay.classList.remove('open'); document.body.style.overflow = ''; }
      if (openBtn) openBtn.addEventListener('click', open);
      if (closeBtn) closeBtn.addEventListener('click', close);
      if (overlay) overlay.addEventListener('click', close);
      document.querySelectorAll('#mobile-nav a').forEach(function (a) { a.addEventListener('click', close); });
      // Sticky header shadow
      var hdr = document.querySelector('.site-header');
      window.addEventListener('scroll', function () {
        hdr && (hdr.style.boxShadow = window.scrollY > 10 ? '0 4px 32px rgba(0,0,0,.35)' : '');
      }, { passive: true });
    })();

    // ── FAQ ──
    document.querySelectorAll('.faq-question').forEach(function (q) {
      q.addEventListener('click', function () {
        var a = this.nextElementSibling;
        var open = a.classList.contains('open');
        document.querySelectorAll('.faq-answer').forEach(function (x) { x.classList.remove('open'); });
        document.querySelectorAll('.faq-question').forEach(function (x) { x.classList.remove('active'); });
        if (!open) { a.classList.add('open'); this.classList.add('active'); }
      });
    });

    // ── Scroll Reveal ──
    var obs = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) { if (e.isIntersecting) e.target.classList.add('visible'); });
    }, { threshold: .12 });
    document.querySelectorAll('.reveal').forEach(function (el) { obs.observe(el); });

    // ── Area WhatsApp shortcut ──
    function areaWA(area, brand) {
      var msg = brand
        ? 'Hello! I need ' + brand + ' appliance repair in ' + area + ', Nairobi.'
        : 'Hello! I need appliance repair in ' + area + ', Nairobi. Please assist.';
      window.open('https://api.whatsapp.com/send?phone=254797340140&text=' + encodeURIComponent(msg), '_blank');
    }
  </script>

</body>

</html>