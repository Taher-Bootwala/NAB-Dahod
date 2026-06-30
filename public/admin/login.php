<?php
require __DIR__ . '/../../app/bootstrap.php';
admin_logout();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf'] ?? null)) {
        $error = 'Invalid request. Please try again.';
    } else {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        [$ok, $msg] = admin_login($supabase, $username, $password);
        if ($ok) redirect('/admin/');
        $error = $msg;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin Login · <?= e(SITE_NAME) ?></title>
<link rel="icon" href="/assets/img/favicon.svg" type="image/svg+xml">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/global.css">
<style>
body {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1.5rem;
}
body::before {
  content: "";
  position: fixed; inset: 0; z-index: -1;
  background:
    radial-gradient(70vw 60vh at 10% -10%, var(--brand-100), transparent 60%),
    radial-gradient(50vw 50vh at 100% 10%, #d1fae5, transparent 55%),
    radial-gradient(40vw 50vh at 60% 110%, var(--brand-50), transparent 60%);
}
.login-card {
  width: 100%;
  max-width: 420px;
  animation: fadeUp .5s cubic-bezier(.2,.8,.2,1) both;
}
.logo-wrap {
  width: 72px; height: 72px;
  border-radius: 22px;
  background: linear-gradient(135deg, var(--brand-500), #10b981);
  display: grid; place-items: center;
  margin: 0 auto 1.4rem;
  box-shadow: 0 12px 32px rgba(47,131,247,.35);
}
.show-pwd {
  position: absolute; right: .9rem; top: 50%; transform: translateY(-50%);
  background: none; border: none; cursor: pointer; color: var(--ink-mute);
  padding: .25rem; line-height: 0;
}
.show-pwd:hover { color: var(--brand-600); }
</style>
</head>
<body>

<div class="login-card">
  <div class="clay" style="padding:2.4rem 2rem">

    <!-- Logo -->
    <div class="logo-wrap">
      <img src="/assets/img/logo.png" alt="Logo"
        style="width:46px;height:46px;object-fit:contain"
        onerror="this.replaceWith(Object.assign(document.createElement('span'),{textContent:'A',style:'color:#fff;font-size:1.6rem;font-weight:900'}))">
    </div>

    <div class="center" style="margin-bottom:1.8rem">
      <h1 style="font-size:1.6rem;margin-bottom:.3rem">Admin Portal</h1>
      <p class="muted" style="font-size:.9rem;margin:0"><?= e(SITE_NAME) ?></p>
    </div>

    <?php if ($error): ?>
    <div style="background:#fff1f2;border:1px solid #fecdd3;color:#be123c;padding:.8rem 1rem;border-radius:14px;margin-bottom:1.2rem;font-weight:600;font-size:.9rem;display:flex;gap:.5rem;align-items:center">
      <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01" stroke-linecap="round"/></svg>
      <?= e($error) ?>
    </div>
    <?php endif ?>

    <form method="POST" autocomplete="off">
      <?= csrf_field() ?>
      <div style="margin-bottom:1rem">
        <label class="label" for="username">Username</label>
        <input id="username" class="field" type="text" name="username"
          value="<?= e($_POST['username'] ?? '') ?>"
          placeholder="Enter username" required autofocus>
      </div>
      <div style="margin-bottom:1.5rem;position:relative">
        <label class="label" for="password">Password</label>
        <div style="position:relative">
          <input id="password" class="field" type="password" name="password"
            placeholder="Enter password" required style="padding-right:3rem">
          <button type="button" class="show-pwd" onclick="togglePwd()" aria-label="Show password">
            <svg id="eye" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
            </svg>
          </button>
        </div>
      </div>

      <button type="submit" class="btn btn-lg btn-morph" style="width:100%;
        background:linear-gradient(135deg,var(--brand-500),#10b981);
        box-shadow:0 12px 28px -8px rgba(47,131,247,.5)">
        Sign In →
      </button>
    </form>

  </div>

  <div class="center" style="margin-top:1.2rem">
    <a href="/" style="color:var(--ink-mute);font-size:.85rem">← Back to website</a>
  </div>
</div>

<script>
function togglePwd(){
  const f=document.getElementById('password');
  const show=f.type==='password';
  f.type=show?'text':'password';
  document.getElementById('eye').innerHTML=show
    ?'<path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23" stroke-linecap="round"/>'
    :'<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
}
</script>
</body>
</html>
