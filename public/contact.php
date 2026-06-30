<?php
require __DIR__ . '/../app/bootstrap.php';
$page = ['title' => 'Contact', 'nav' => 'contact', 'desc' => 'Get in touch with National Association for the Blind Dahod — address, phone, email and enquiry form.'];
require APP_DIR . '/layout/header.php';
$mapsQuery = rawurlencode(ORG_ADDRESS);
?>
<section class="section" style="padding-bottom:1rem">
  <div class="container">
    <div class="center reveal" style="max-width:680px;margin-inline:auto">
      <span class="eyebrow"><?= icon('mail','w-4 h-4') ?> We'd love to hear from you</span>
      <h1 style="margin-top:1rem">Get in <span class="grad-text">touch</span></h1>
      <p class="lead" style="margin-inline:auto">Questions, visits, volunteering or partnerships — reach out and we'll respond personally.</p>
    </div>
  </div>
</section>

<section class="section" style="padding-top:0">
  <div class="container" style="display:grid;gap:2rem;grid-template-columns:1fr 1.1fr;align-items:start">
    <!-- Info + map -->
    <div class="reveal" data-reveal="left">
      <div class="stack">
        <a href="tel:<?= e(preg_replace('/\s+/','',ORG_PHONE)) ?>" class="clay card-hover" style="display:flex;gap:1rem;align-items:center;padding:1.3rem;text-decoration:none;color:inherit">
          <div style="display:grid;place-items:center;width:50px;height:50px;border-radius:14px;background:var(--brand-50);flex:none"><?= icon('phone','w-6 h-6',['style'=>'color:var(--brand-600)']) ?></div>
          <div><div class="muted" style="font-size:.82rem">Call us</div><strong><?= e(ORG_PHONE) ?></strong></div>
        </a>
        <a href="mailto:<?= e(ORG_EMAIL) ?>" class="clay card-hover" style="display:flex;gap:1rem;align-items:center;padding:1.3rem;text-decoration:none;color:inherit">
          <div style="display:grid;place-items:center;width:50px;height:50px;border-radius:14px;background:#e6fbf7;flex:none"><?= icon('mail','w-6 h-6',['style'=>'color:#2a9d8f']) ?></div>
          <div><div class="muted" style="font-size:.82rem">Email us</div><strong><?= e(ORG_EMAIL) ?></strong></div>
        </a>
        <div class="clay" style="display:flex;gap:1rem;align-items:flex-start;padding:1.3rem">
          <div style="display:grid;place-items:center;width:50px;height:50px;border-radius:14px;background:#fff3e6;flex:none"><?= icon('map-pin','w-6 h-6',['style'=>'color:#f59324']) ?></div>
          <div><div class="muted" style="font-size:.82rem">Visit us</div><strong><?= e(ORG_ADDRESS) ?></strong></div>
        </div>
        <a href="https://wa.me/<?= e(ORG_WHATSAPP) ?>" target="_blank" rel="noopener" class="clay card-hover" style="display:flex;gap:1rem;align-items:center;padding:1.3rem;text-decoration:none;color:inherit">
          <div style="display:grid;place-items:center;width:50px;height:50px;border-radius:14px;background:#e7faef;flex:none"><?= icon('whatsapp','w-6 h-6',['style'=>'color:#25d366']) ?></div>
          <div><div class="muted" style="font-size:.82rem">Chat instantly</div><strong>WhatsApp us</strong></div>
        </a>
      </div>

      <div class="clay" style="margin-top:1.2rem;padding:.5rem;border-radius:var(--radius);overflow:hidden">
        <iframe title="Map to <?= e(SITE_NAME) ?>" width="100%" height="260" style="border:0;border-radius:18px;display:block" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
          src="https://www.google.com/maps?q=<?= $mapsQuery ?>&output=embed"></iframe>
      </div>
    </div>

    <!-- Form -->
    <div class="reveal" data-reveal="right">
      <form class="clay" style="padding:2rem" action="/api/contact.php" method="post" data-ajax>
        <?= csrf_field() ?>
        <h2 style="font-size:1.6rem">Send us a message</h2>
        <p class="muted" style="margin-top:0">We usually reply within 1–2 working days.</p>
        <!-- honeypot -->
        <input type="text" name="website" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px" aria-hidden="true">
        <div class="stack" style="margin-top:1.2rem">
          <div><label class="label" for="cName">Your name</label><input id="cName" class="field" name="name" required autocomplete="name"></div>
          <div style="display:grid;gap:1rem;grid-template-columns:1fr 1fr">
            <div><label class="label" for="cEmail">Email</label><input id="cEmail" class="field" type="email" name="email" required autocomplete="email"></div>
            <div><label class="label" for="cPhone">Phone <span class="muted">(optional)</span></label><input id="cPhone" class="field" type="tel" name="phone" autocomplete="tel"></div>
          </div>
          <div><label class="label" for="cMsg">Message</label><textarea id="cMsg" class="field" name="message" rows="5" required></textarea></div>
          <button type="submit" class="btn btn-lg" style="width:100%"><?= icon('mail','w-5 h-5') ?> Send message</button>
        </div>
      </form>
    </div>
  </div>
</section>

<?php require APP_DIR . '/layout/footer.php'; ?>
