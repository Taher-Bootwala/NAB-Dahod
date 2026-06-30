<?php
/** admin_header.php — claymorphism light theme matching the public site */
require_once APP_DIR . '/icons.php';
require_admin();
$u = admin_user();
$adminPage = $adminPage ?? '';
$nav = [
    ['index.php',       'Dashboard',    'dashboard'],
    ['activities.php',  'Activities',   'sparkle'],
    ['gallery.php',     'Gallery',      'image'],
    ['home_images.php', 'Display Images', 'sparkle'],
    ['progress.php',    'Progress',     'document'],
    ['trustees.php',    'Trustees',     'users'],
    ['donations.php',   'Donations',    'heart'],
    ['messages.php',    'Messages',     'inbox'],
    ['content.php',     'Site Content', 'settings'],
    ['storage.php',     'Storage',      'database'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title><?= e($adminPage ? ucfirst($adminPage) : 'Dashboard') ?> · Admin · <?= e(SITE_NAME) ?></title>
<link rel="icon" href="/assets/img/logo.png" type="image/svg+xml">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="<?= asset('assets/css/global.css') ?>">
<style>
/* Admin shell — inherits public-site design tokens */
body { background: var(--bg); }

.admin-shell {  
  display: grid;
  grid-template-columns: 260px 1fr;
  min-height: 100vh;
}

/* Sidebar */
.admin-side {
  background: var(--surface);
  border-right: 1px solid var(--line);
  box-shadow: 4px 0 24px rgba(26,60,116,.06);
  padding: 1.4rem 1rem;
  position: sticky;
  top: 0;
  height: 100vh;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: .2rem;
}

.admin-logo {
  display: flex;
  align-items: center;
  gap: .7rem;
  padding: .6rem .8rem 1.2rem;
  text-decoration: none;
  color: var(--ink);
  font-weight: 800;
}
.admin-logo span.sub { font-size: .75rem; font-weight: 500; color: var(--ink-mute); display: block; }

.admin-link {
  display: flex;
  align-items: center;
  gap: .75rem;
  padding: .75rem 1rem;
  border-radius: 14px;
  color: var(--ink-soft);
  font-weight: 600;
  font-size: .93rem;
  text-decoration: none;
  transition: background .18s, color .18s, transform .18s;
}
.admin-link:hover {
  background: var(--brand-50);
  color: var(--brand-700);
  transform: translateX(3px);
}
.admin-link.active {
  background: linear-gradient(135deg, var(--brand-500), var(--brand-700));
  color: #fff;
  box-shadow: var(--shadow-card);
}
.admin-link.active svg { opacity: 1; }

.side-section {
  font-size: .72rem;
  font-weight: 700;
  letter-spacing: .1em;
  text-transform: uppercase;
  color: var(--ink-mute);
  padding: .9rem 1rem .3rem;
}

/* Topbar */
.admin-topbar {
  position: sticky;
  top: 0;
  z-index: 20;
  background: var(--glass-bg);
  -webkit-backdrop-filter: blur(14px) saturate(160%);
  backdrop-filter: blur(14px) saturate(160%);
  border-bottom: 1px solid var(--line);
  padding: .9rem clamp(1.2rem, 3vw, 2rem);
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
}

.admin-main { padding: clamp(1.4rem, 3vw, 2.2rem); }

/* Stat cards */
.stat-card {
  background: var(--surface);
  border-radius: var(--radius);
  box-shadow: var(--shadow-clay);
  border: 1px solid rgba(255,255,255,.7);
  padding: 1.4rem;
  transition: transform .2s cubic-bezier(.2,.8,.2,1), box-shadow .2s;
}
.stat-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-soft); }
.stat-icon {
  width: 44px; height: 44px;
  border-radius: 12px;
  display: grid;
  place-items: center;
  margin-bottom: .9rem;
}

/* Tables */
.a-table { width: 100%; border-collapse: collapse; }
.a-table th {
  text-align: left;
  padding: .8rem 1rem;
  color: var(--ink-mute);
  font-size: .78rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .06em;
  border-bottom: 2px solid var(--line);
}
.a-table td {
  padding: .85rem 1rem;
  border-bottom: 1px solid var(--line);
  vertical-align: middle;
  color: var(--ink);
  font-size: .93rem;
}
.a-table tr:last-child td { border-bottom: none; }
.a-table tr:hover td { background: var(--brand-50); }

/* Avatar */
.avatar {
  width: 36px; height: 36px;
  border-radius: 50%;
  background: linear-gradient(150deg, var(--brand-400), var(--brand-700));
  display: grid; place-items: center;
  color: #fff; font-weight: 800; font-size: .85rem;
  flex: none;
}

/* Mobile */
@media (max-width: 880px) {
  .admin-shell { grid-template-columns: 1fr; }
  .admin-side { position: static; height: auto; flex-direction: row; flex-wrap: wrap; padding: .8rem; }
  .admin-logo { padding-bottom: 0; }
  .side-section { display: none; }
  .admin-link { padding: .6rem .8rem; font-size: .85rem; }
}
</style>
</head>
<body>
<!-- Ambient bg matching public site -->
<div aria-hidden="true" style="position:fixed;inset:0;z-index:-1;
  background: radial-gradient(60vw 50vh at 5% 0%, var(--brand-100), transparent 60%),
              radial-gradient(50vw 40vh at 100% 0%, #e6fbf7, transparent 55%),
              radial-gradient(40vw 40vh at 80% 100%, var(--brand-50), transparent 60%);
  opacity:.7"></div>

<div class="admin-shell">

  <!-- Sidebar -->
  <aside class="admin-side">
    <a href="/admin/" class="admin-logo">
      <img src="/assets/img/logo.png" alt="Logo"
        style="width:40px;height:40px;object-fit:contain;border-radius:10px;box-shadow:var(--shadow-card)"
        onerror="this.style.display='none'">
      <div>
        <span style="display:block;line-height:1.1">Admin Panel</span>
        <span class="sub"><?= e(SITE_NAME) ?></span>
      </div>
    </a>

    <span class="side-section">Menu</span>
    <nav aria-label="Admin navigation">
      <?php foreach ($nav as [$href, $label, $ic]):
        $active = $adminPage === pathinfo($href, PATHINFO_FILENAME); ?>
        <a class="admin-link<?= $active ? ' active' : '' ?>" href="/admin/<?= e($href) ?>">
          <?= icon($ic, 'w-5 h-5') ?> <?= e($label) ?>
        </a>
      <?php endforeach; ?>
    </nav>

    <div style="flex:1"></div>
    <div style="border-top:1px solid var(--line);padding-top:.8rem;margin-top:.8rem">
      <a class="admin-link" href="/" target="_blank"><?= icon('globe','w-5 h-5') ?> View Site</a>
      <a class="admin-link" href="/admin/logout.php" style="color:var(--ink-mute)"><?= icon('logout','w-5 h-5') ?> Sign Out</a>
    </div>
  </aside>

  <!-- Content area -->
  <div style="min-width:0;display:flex;flex-direction:column">

    <!-- Topbar -->
    <header class="admin-topbar">
      <h1 style="font-size:1.2rem;margin:0;font-weight:800;color:var(--ink)">
        <?= e($pageHeading ?? ucfirst(str_replace('-', ' ', $adminPage))) ?>
      </h1>
      <div style="display:flex;align-items:center;gap:.8rem">
        <a href="/donate.php" class="btn" style="padding:.55rem 1.1rem;font-size:.85rem">
          <?= icon('heart','w-4 h-4') ?> Donate
        </a>
        <div style="display:flex;align-items:center;gap:.6rem">
          <div class="avatar"><?= e(mb_strtoupper(mb_substr($u['name'] ?? 'A', 0, 1))) ?></div>
          <div style="line-height:1.2">
            <div style="font-weight:700;font-size:.88rem;color:var(--ink)"><?= e($u['name'] ?? 'Admin') ?></div>
            <div style="font-size:.75rem;color:var(--ink-mute)"><?= e(ucfirst($u['role'] ?? '')) ?></div>
          </div>
        </div>
      </div>
    </header>

    <main class="admin-main" role="main">
