<?php
/** header.php — opens the document, SEO, nav, a11y toolbar. Expects $page. */
require_once APP_DIR . '/icons.php';

$page = $page ?? [];
$pTitle = $page['title'] ?? SITE_NAME;
$fullTitle = ($page['title'] ?? false) ? $pTitle . ' · ' . SITE_NAME : SITE_NAME . ' — ' . SITE_TAGLINE;
$pDesc = $page['desc'] ?? 'A free residential school for visually impaired students in Dahod, Gujarat. Braille literacy, assistive technology, life skills and dignity for blind children since 1998.';
$nav = $page['nav'] ?? '';
$ogImage = $page['og'] ?? url('assets/img/og-cover.svg');
$canonical = url(ltrim($_SERVER['REQUEST_URI'] ?? '/', '/'));

$navItems = [
    'home'       => ['/', 'Home'],
    'about'      => ['/about.php', 'About'],
    'activities' => ['/activities.php', 'Activities'],
    'gallery'    => ['/gallery.php', 'Gallery'],
    'progress'   => ['/progress.php', 'Download'],
    'trustees'   => ['/trustees.php', 'Trustees'],
    'contact'    => ['/contact.php', 'Contact'],
];
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= e($fullTitle) ?></title>
<meta name="description" content="<?= attr($pDesc) ?>">
<link rel="canonical" href="<?= attr($canonical) ?>">
<meta name="theme-color" content="#1b66e0">

<!-- Open Graph / Twitter -->
<meta property="og:type" content="website">
<meta property="og:site_name" content="<?= attr(SITE_NAME) ?>">
<meta property="og:title" content="<?= attr($fullTitle) ?>">
<meta property="og:description" content="<?= attr($pDesc) ?>">
<meta property="og:image" content="<?= attr($ogImage) ?>">
<meta property="og:url" content="<?= attr($canonical) ?>">
<meta name="twitter:card" content="summary_large_image">

<!-- PWA -->
<link rel="manifest" href="/manifest.webmanifest">
<link rel="icon" href="/assets/img/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/assets/img/favicon.svg">

<!-- Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

<!-- Tailwind (utility layer) -->
<script src="https://cdn.tailwindcss.com"></script>
<script>
  tailwind.config = {
    theme: { extend: {
      fontFamily: { sans: ['Inter','Geist','system-ui','sans-serif'] },
      colors: {
        brand: { 50:'#eef6ff',100:'#d9ecff',200:'#bcdcff',300:'#8ec5ff',400:'#59a4ff',500:'#2f83f7',600:'#1b66e0',700:'#1850b4',800:'#19448e',900:'#1a3c74' },
        ink: '#16233b', mint: '#5fd0c5',
      },
      borderRadius: { '2xl':'22px','3xl':'32px' },
    } }
  };
</script>

<link rel="stylesheet" href="<?= asset('assets/css/global.css') ?>">

<!-- Apply saved accessibility prefs before paint (no flash) -->
<script>
(function(){try{var s=JSON.parse(localStorage.getItem('bsd_a11y')||'{}');var r=document.documentElement;
if(s.fontScale)r.style.setProperty('--fs',s.fontScale);
if(s.contrast)r.setAttribute('data-contrast','high');
if(s.motionOff)r.setAttribute('data-motion','off');}catch(e){}})();
</script>

<!-- Structured data -->
<script type="application/ld+json">
{"@context":"https://schema.org","@type":"EducationalOrganization","name":"<?= e(SITE_NAME) ?>",
"description":"<?= e($pDesc) ?>","url":"<?= e(SITE_URL) ?>","email":"<?= e(ORG_EMAIL) ?>","telephone":"<?= e(ORG_PHONE) ?>",
"address":{"@type":"PostalAddress","streetAddress":"<?= e(ORG_ADDRESS) ?>","addressRegion":"Gujarat","addressCountry":"IN"}}
</script>
</head>
<body>
<div class="progress-bar" aria-hidden="true"></div>
<a href="#main" class="skip-link">Skip to main content</a>

<header class="site-header" role="banner">
  <div class="container">
    <div class="nav-bar">
      <a href="/" id="brandLogo" class="brand-logo" aria-label="<?= e(SITE_NAME) ?> — home">
        <span class="brand-mark" aria-hidden="true" style="background:none">
          <img src="/assets/img/logo.png" alt="Logo" style="width:60px;height:60px;object-fit:contain">
        </span>
        <span>
          <span style="display:block;line-height:1.05">National Association<br>for the Blind</span>
          <span style="display:block;font-size:.78rem;font-weight:600;color:var(--ink-mute)">Dahod</span>
        </span>
      </a>

      <!-- Desktop nav -->
      <nav class="nav-links desktop" aria-label="Primary">
        <?php foreach ($navItems as $key => [$href, $label]): ?>
          <a href="<?= e($href) ?>"<?= $nav === $key ? ' aria-current="page"' : '' ?>><?= e($label) ?></a>
        <?php endforeach; ?>
      </nav>

      <div style="display:flex;align-items:center;gap:.6rem">
        <!-- Accessibility toolbar -->
        <div class="a11y-bar" role="group" aria-label="Accessibility tools">
          <button id="a11yDec" class="a11y-btn" type="button" aria-label="Decrease text size" title="Smaller text"><?= icon('minus','w-5 h-5') ?></button>
          <button id="a11yInc" class="a11y-btn" type="button" aria-label="Increase text size" title="Larger text"><?= icon('plus','w-5 h-5') ?></button>
          <button id="a11yContrast" class="a11y-btn" type="button" aria-pressed="false" aria-label="Toggle high contrast" title="High contrast"><?= icon('contrast','w-5 h-5') ?></button>
          <button data-speak class="a11y-btn" type="button" aria-pressed="false" aria-label="Listen to this page" title="Listen to this page"><?= icon('volume','w-5 h-5') ?></button>
        </div>

        <a href="/donate.php" class="btn btn-morph" style="padding:.7rem 1.3rem">
          <?= icon('heart','w-5 h-5') ?> Donate
        </a>

        <button class="a11y-btn menu-toggle" type="button" aria-label="Open menu" aria-expanded="false" aria-controls="mobileMenu"><?= icon('menu','w-6 h-6') ?></button>
      </div>
    </div>

    <!-- Mobile menu -->
    <nav id="mobileMenu" class="mobile-menu clay" hidden aria-label="Mobile" style="margin-top:.6rem;padding:1rem;border-radius:20px">
      <?php foreach ($navItems as $key => [$href, $label]): ?>
        <a href="<?= e($href) ?>" style="display:block;padding:.8rem 1rem;border-radius:12px;font-weight:600;color:var(--ink-soft)"<?= $nav === $key ? ' aria-current="page"' : '' ?>><?= e($label) ?></a>
      <?php endforeach; ?>
      <a href="/donate.php" class="btn" style="width:100%;margin-top:.6rem"><?= icon('heart','w-5 h-5') ?> Donate Now</a>
    </nav>
  </div>
</header>

<main id="main" role="main">
