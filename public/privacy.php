<?php
require __DIR__ . '/../app/bootstrap.php';
$page = ['title' => 'Privacy Policy', 'nav' => '', 'desc' => 'Privacy policy for National Association for the Blind Dahod.'];
require APP_DIR . '/layout/header.php';
?>
<section class="section">
  <div class="container" style="max-width:760px">
    <div class="reveal">
      <span class="eyebrow"><?= icon('lock','w-4 h-4') ?> Your privacy matters</span>
      <h1 style="margin-top:1rem">Privacy Policy</h1>
      <p class="muted">Last updated <?= date('F Y') ?></p>
    </div>
    <div class="clay reveal" style="padding:2rem;margin-top:1.5rem;line-height:1.8">
      <h3>What we collect</h3>
      <p>When you contact us or donate, we collect only the information you choose to provide — your name, email, phone and message — so we can respond and issue receipts.</p>
      <h3>Cookies</h3>
      <p>We use minimal local storage to remember your accessibility preferences (text size, contrast, motion). We do not use advertising or tracking cookies.</p>
      <h3>How we use your data</h3>
      <p>Your information is used solely to communicate with you, process donations and send receipts. We never sell or share your personal data with third parties.</p>
      <h3>Payments</h3>
      <p>Donations are made directly through UPI. We do not store your bank or card details.</p>
      <h3>Your rights</h3>
      <p>You may request access to, correction of, or deletion of your personal data at any time by <a href="/contact.php">contacting us</a>.</p>
    </div>
  </div>
</section>
<?php require APP_DIR . '/layout/footer.php'; ?>
