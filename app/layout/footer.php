<?php
/** footer.php — closes main, renders footer + global widgets + scripts. */
$s = $repo->settings();
?>
</main>

<footer class="site-footer" role="contentinfo">
  <div class="container" style="padding:3.5rem 0 1.5rem">
    <div style="display:grid;gap:2.5rem;grid-template-columns:repeat(auto-fit,minmax(220px,1fr))">
      <div>
        <div class="brand-logo" style="color:#fff;margin-bottom:1rem">
          <span class="brand-mark"><img src="/assets/img/logo.png" alt="Logo" style="width:40px;height:40px;object-fit:contain"></span>
          <span><?= e(SITE_NAME) ?></span>
        </div>
        <p style="color:#aebcd6;max-width:34ch"><?= e($s['footer_about'] ?? '') ?></p>
        <div style="display:flex;gap:.6rem;margin-top:1.1rem">
          <span class="badge" style="background:rgba(255,255,255,.1);color:#cfe0ff">80G Certified</span>
          <span class="badge" style="background:rgba(255,255,255,.1);color:#cfe0ff">12A Registered</span>
        </div>
      </div>

      <div>
        <h4>Explore</h4>
        <ul style="list-style:none;padding:0;margin:.8rem 0 0;display:grid;gap:.55rem">
          <li><a href="/about.php">About the school</a></li>
          <li><a href="/activities.php">Activities</a></li>
          <li><a href="/gallery.php">Photo gallery</a></li>
          <li><a href="/trustees.php">Our trustees</a></li>
          <li><a href="/transparency.php">Donation transparency</a></li>
        </ul>
      </div>

      <div>
        <h4>Get involved</h4>
        <ul style="list-style:none;padding:0;margin:.8rem 0 0;display:grid;gap:.55rem">
          <li><a href="/donate.php">Donate now</a></li>
          <li><a href="/contact.php">Volunteer</a></li>
          <li><a href="/contact.php">Contact us</a></li>
        </ul>
      </div>

      <div>
        <h4>Reach us</h4>
        <ul style="list-style:none;padding:0;margin:.8rem 0 0;display:grid;gap:.7rem;color:#aebcd6">
          <li style="display:flex;gap:.6rem"><?= icon('map-pin','w-5 h-5',['style'=>'flex:none;color:#9fc3ff']) ?> <span><?= e(ORG_ADDRESS) ?></span></li>
          <li style="display:flex;gap:.6rem"><?= icon('phone','w-5 h-5',['style'=>'flex:none;color:#9fc3ff']) ?> <a href="tel:<?= e(preg_replace('/\s+/', '', ORG_PHONE)) ?>"><?= e(ORG_PHONE) ?></a></li>
          <li style="display:flex;gap:.6rem"><?= icon('mail','w-5 h-5',['style'=>'flex:none;color:#9fc3ff']) ?> <a href="mailto:<?= e(ORG_EMAIL) ?>"><?= e(ORG_EMAIL) ?></a></li>
        </ul>
      </div>
    </div>

    <hr style="border:0;border-top:1px solid rgba(255,255,255,.1);margin:2.2rem 0 1.2rem">
    <div style="display:flex;flex-wrap:wrap;gap:1rem;justify-content:space-between;color:#8298bd;font-size:.9rem">
      <span>© <?= date('Y') ?> <?= e(SITE_NAME) ?>. All rights reserved.</span>
      <span>Made with care for our students · <a href="/transparency.php">Every rupee accounted for</a></span>
      <a href="/admin/login.php" style="color:#8298bd;opacity:.5;font-size:.8rem">Admin</a>
    </div>
  </div>
</footer>

<!-- Floating widgets -->
<a class="fab fab-whatsapp" href="https://wa.me/<?= e(ORG_WHATSAPP) ?>?text=<?= rawurlencode('Hello! I would like to know more about National Association for the Blind Dahod.') ?>" target="_blank" rel="noopener" aria-label="Chat on WhatsApp">
  <?= icon('whatsapp','w-7 h-7') ?>
</a>
<button class="fab fab-top" type="button" aria-label="Back to top"><?= icon('arrow-up','w-6 h-6') ?></button>

<!-- Cookie consent -->
<div id="cookieBanner" class="cookie glass" role="dialog" aria-label="Cookie notice">
  <p style="margin:0 0 .8rem;font-size:.92rem;color:var(--ink)">We use minimal cookies to remember your accessibility preferences and improve your experience.</p>
  <div style="display:flex;gap:.5rem">
    <button id="cookieAccept" class="btn" style="padding:.6rem 1.2rem;font-size:.9rem">Got it</button>
    <a href="/privacy.php" class="btn btn-ghost" style="padding:.6rem 1.2rem;font-size:.9rem">Learn more</a>
  </div>
</div>

<!-- Global UPI donate QR modal -->
<div id="donateQR" class="modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="qrTitle" aria-hidden="true">
  <div class="modal clay center">
    <button data-modal-close class="a11y-btn" type="button" aria-label="Close" style="position:absolute;top:14px;right:14px"><?= icon('close','w-5 h-5') ?></button>
    <span class="eyebrow" style="margin-bottom:1rem"><?= icon('gift','w-4 h-4') ?> Scan & Donate</span>
    <h3 id="qrTitle" style="margin-bottom:.3rem">Donate via UPI</h3>
    <p class="muted" style="margin-top:0">Scan with any UPI app — GPay, PhonePe, Paytm.</p>
    <div style="background:#fff;padding:14px;border-radius:20px;display:inline-block;box-shadow:var(--shadow-clay);margin:.4rem 0 1rem">
      <img id="qrImage" src="<?= attr(upi_qr_url()) ?>" alt="UPI QR code to donate to <?= attr(SITE_NAME) ?>" width="280" height="280" loading="lazy" style="display:block;border-radius:8px">
    </div>
    <p style="margin:.2rem 0;font-weight:700"><?= e(UPI_ID) ?></p>
    <p class="muted" style="font-size:.85rem">After paying, please keep your reference number for the receipt.</p>
  </div>
</div>

<!-- Lightbox -->
<div id="lightbox" class="lightbox" role="dialog" aria-modal="true" aria-label="Image preview" aria-hidden="true">
  <button data-lb-close class="a11y-btn" type="button" aria-label="Close preview" style="position:absolute;top:20px;right:20px;z-index:2"><?= icon('close','w-6 h-6') ?></button>
  <figure style="margin:0;text-align:center">
    <img id="lightboxImg" src="" alt="">
    <figcaption id="lightboxCap" style="color:#dce6f7;margin-top:1rem;font-weight:600"></figcaption>
  </figure>
</div>

<script src="<?= asset('assets/js/app.js') ?>" defer></script>
</body>
</html>
