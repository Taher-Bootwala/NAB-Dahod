<?php
require __DIR__ . '/../app/bootstrap.php';
$page = ['title' => 'Donate', 'nav' => '', 'desc' => 'Donate to National Association for the Blind Dahod. 100% transparent, 80G tax benefit. Your gift gives a blind child education and dignity.'];
require APP_DIR . '/layout/header.php';

$upiId   = $repo->setting('upi_id', UPI_ID);
$payee   = rawurlencode($repo->setting('upi_payee_name', UPI_PAYEE_NAME));
$upiLink = "upi://pay?pa={$upiId}&pn={$payee}&cu=INR";
?>

<section class="section" style="padding-bottom:1rem">
  <div class="container">
    <div class="center reveal" style="max-width:680px;margin-inline:auto">
      <span class="eyebrow"><?= icon('heart','w-4 h-4') ?> Give the gift of education</span>
      <h1 style="margin-top:1rem">Your donation <span class="grad-text">changes a life</span></h1>
      <p class="lead" style="margin-inline:auto">Scan the QR code or tap a payment app below. Every rupee goes directly to educating visually impaired children.</p>
    </div>
  </div>
</section>

<section class="section" style="padding-top:0">
  <div class="container">
    <div class="donate-layout">

      <!-- QR + UPI card -->
      <div class="reveal" data-reveal="left">
        <div class="clay donate-card center">
          <span class="eyebrow" style="margin-bottom:1rem"><?= icon('gift','w-4 h-4') ?> Scan &amp; Pay</span>
          <h2 style="font-size:1.5rem;margin-bottom:.3rem">Pay via UPI</h2>
          <p class="muted" style="font-size:.9rem;margin-bottom:1.2rem">Open any UPI app, scan this code, enter amount &amp; pay.</p>

          <!-- QR Code -->
          <div class="qr-wrap float">
            <img
              src="/api/qr.php"
              alt="UPI QR code — scan to donate"
              width="240" height="240"
              loading="lazy"
              style="display:block;border-radius:12px">
          </div>

          <p style="margin:.8rem 0 .2rem;font-weight:800;font-size:1.05rem;color:var(--ink)"><?= e($upiId) ?></p>
          <p class="muted" style="font-size:.84rem;margin-bottom:1.4rem"><?= e(UPI_PAYEE_NAME) ?></p>

          <!-- UPI deep link button -->
          <a href="<?= attr($upiLink) ?>" class="btn btn-lg btn-morph" style="width:100%" id="upiPayBtn">
            <?= icon('heart','w-5 h-5') ?> Pay via UPI App
          </a>
          <p class="muted" style="font-size:.78rem;margin-top:.6rem">Opens payment app selector on mobile.</p>

          <!-- App picker (shown on desktop or as fallback) -->
          <div class="app-picker" id="appPicker" aria-label="Choose a payment app">
            <p style="font-weight:700;font-size:.9rem;color:var(--ink);margin-bottom:.8rem">Choose your payment app</p>
            <div class="app-grid">
              <?php
              $apps = [
                ['GPay',     "https://gpay.app.goo.gl/upi?pa={$upiId}&pn={$payee}&cu=INR",     '#fff', 'data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48"><text y="36" font-size="36">G</text></svg>'],
                ['PhonePe',  "phonepe://pay?pa={$upiId}&pn={$payee}&cu=INR",                    '#5f259f', ''],
                ['Paytm',    "paytmmp://pay?pa={$upiId}&pn={$payee}&cu=INR",                    '#00b9f1', ''],
                ['BHIM',     "upi://pay?pa={$upiId}&pn={$payee}&cu=INR",                        '#f58020', ''],
                ['Amazon Pay',"amazonpay://pay?pa={$upiId}&pn={$payee}&cu=INR",                 '#ff9900', ''],
                ['Other UPI',$upiLink,                                                           'var(--brand-600)', ''],
              ];
              foreach ($apps as [$name, $link, $color, $img]): ?>
              <a href="<?= attr($link) ?>" class="app-btn clay" style="--app-color:<?= $color ?>">
                <span class="app-icon" style="background:<?= $color ?>">
                  <?php if ($name === 'GPay'): ?>
                    <svg viewBox="0 0 24 24" fill="none" width="22" height="22" xmlns="http://www.w3.org/2000/svg"><text y="18" font-size="17" fill="#4285F4" font-family="Arial" font-weight="bold">G</text></svg>
                  <?php elseif ($name === 'PhonePe'): ?>
                    <svg viewBox="0 0 24 24" width="22" height="22" fill="#fff" xmlns="http://www.w3.org/2000/svg"><text y="18" font-size="14" font-family="Arial" font-weight="bold">Pe</text></svg>
                  <?php elseif ($name === 'Paytm'): ?>
                    <svg viewBox="0 0 24 24" width="22" height="22" fill="#fff" xmlns="http://www.w3.org/2000/svg"><text y="18" font-size="11" font-family="Arial" font-weight="bold">Paytm</text></svg>
                  <?php elseif ($name === 'BHIM'): ?>
                    <svg viewBox="0 0 24 24" width="22" height="22" fill="#fff" xmlns="http://www.w3.org/2000/svg"><text y="18" font-size="11" font-family="Arial" font-weight="bold">BHIM</text></svg>
                  <?php elseif ($name === 'Amazon Pay'): ?>
                    <svg viewBox="0 0 24 24" width="22" height="22" fill="#111" xmlns="http://www.w3.org/2000/svg"><text y="18" font-size="9" font-family="Arial" font-weight="bold">amazon</text></svg>
                  <?php else: ?>
                    <svg viewBox="0 0 24 24" width="22" height="22" fill="#fff" xmlns="http://www.w3.org/2000/svg"><text y="18" font-size="10" font-family="Arial" font-weight="bold">UPI</text></svg>
                  <?php endif; ?>
                </span>
                <span class="app-name"><?= e($name) ?></span>
              </a>
              <?php endforeach; ?>
            </div>
          </div>

          <div style="display:flex;gap:1rem;justify-content:center;margin-top:1.4rem;flex-wrap:wrap">
            <span class="muted" style="display:flex;align-items:center;gap:.4rem;font-size:.82rem"><?= icon('lock','w-4 h-4') ?> Secure UPI</span>
            <span class="muted" style="display:flex;align-items:center;gap:.4rem;font-size:.82rem"><?= icon('shield','w-4 h-4') ?> 80G tax benefit</span>
          </div>
        </div>
      </div>

      <!-- Right: impact info -->
      <div class="reveal" data-reveal="right" style="display:flex;flex-direction:column;gap:1.2rem">

        <div class="clay" style="padding:1.4rem;display:flex;gap:.8rem;align-items:flex-start">
          <?= icon('shield','w-8 h-8',['style'=>'color:var(--brand-600);flex:none;margin-top:.1rem']) ?>
          <p style="margin:0;font-size:.93rem">Donations to <?= e(SITE_NAME) ?> qualify for <strong>80G tax deduction</strong>. Your contribution is 100% transparent.</p>
        </div>

        <div class="clay" style="padding:1.4rem">
          <h3 style="font-size:1.1rem;margin-bottom:1rem">Your gift's impact</h3>
          <ul style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:.7rem">
            <?php foreach ([
              ['₹100',  'A pack of Braille writing paper'],
              ['₹500',  'Educational materials for a month'],
              ['₹1000', 'White cane + mobility training'],
              ['₹5000', 'One month of residential care'],
            ] as [$amt, $desc]): ?>
            <li style="display:flex;gap:.8rem;align-items:center">
              <span style="flex:none;font-weight:800;color:var(--brand-700);min-width:3.5rem"><?= $amt ?></span>
              <span style="font-size:.9rem;color:var(--ink-soft)"><?= $desc ?></span>
            </li>
            <?php endforeach; ?>
          </ul>
        </div>

        <div class="glass" style="padding:1.2rem 1.4rem;display:flex;gap:.7rem;align-items:center">
          <?= icon('sparkle','w-6 h-6',['style'=>'color:var(--brand-600);flex:none']) ?>
          <p style="margin:0;font-size:.9rem;font-weight:600">Enter any amount in the payment app — no minimum donation required.</p>
        </div>

      </div>
    </div>
  </div>
</section>

<style>
.donate-layout {
  display: grid;
  gap: 2rem;
  grid-template-columns: 1fr 1fr;
  align-items: start;
}
.donate-card { padding: 2rem; }

/* QR wrapper */
.qr-wrap {
  background: #fff;
  padding: 14px;
  border-radius: 20px;
  display: inline-block;
  box-shadow: var(--shadow-clay);
  margin-bottom: .6rem;
}

/* App picker — always visible */
.app-picker {
  margin-top: 1.4rem;
  padding-top: 1.2rem;
  border-top: 1px solid var(--line);
  text-align: left;
}
.app-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: .7rem;
}
.app-btn {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: .45rem;
  padding: .85rem .5rem;
  border-radius: 16px;
  text-decoration: none;
  transition: transform .2s cubic-bezier(.2,.8,.2,1), box-shadow .2s;
  border: 1.5px solid var(--line);
}
.app-btn:hover {
  transform: translateY(-4px);
  box-shadow: var(--shadow-soft);
  border-color: var(--app-color, var(--brand-300));
}
.app-icon {
  width: 44px; height: 44px;
  border-radius: 12px;
  display: grid;
  place-items: center;
  flex: none;
  overflow: hidden;
}
.app-name {
  font-size: .76rem;
  font-weight: 700;
  color: var(--ink);
  text-align: center;
  line-height: 1.2;
}

/* Responsive */
@media (max-width: 768px) {
  .donate-layout { grid-template-columns: 1fr; }
  .donate-card { padding: 1.4rem; }
  .app-grid { grid-template-columns: repeat(3, 1fr); gap: .5rem; }
}
@media (max-width: 400px) {
  .app-grid { grid-template-columns: repeat(2, 1fr); }
}
</style>

<?php require APP_DIR . '/layout/footer.php'; ?>
