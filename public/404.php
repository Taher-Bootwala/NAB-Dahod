<?php
require __DIR__ . '/../app/bootstrap.php';
http_response_code(404);
$page = ['title' => 'Page not found', 'nav' => ''];
require APP_DIR . '/layout/header.php';
?>
<section class="section">
  <div class="container center" style="max-width:620px">
    <div class="clay" style="padding:3rem 2rem">
      <div class="grad-text" style="font-size:5rem;font-weight:900;line-height:1">404</div>
      <h1 style="margin-top:.4rem">We couldn't find that page</h1>
      <p class="lead" style="margin-inline:auto">The link may be broken or the page may have moved. Let's get you back on track.</p>
      <div style="display:flex;gap:.8rem;justify-content:center;margin-top:1.6rem;flex-wrap:wrap">
        <a href="/" class="btn"><?= icon('arrow-right','w-5 h-5') ?> Back to home</a>
        <a href="/contact.php" class="btn btn-ghost">Contact us</a>
      </div>
    </div>
  </div>
</section>
<?php require APP_DIR . '/layout/footer.php'; ?>
