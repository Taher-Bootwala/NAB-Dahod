<?php
require __DIR__ . '/../app/bootstrap.php';

$s = $repo->settings();
$stats = $repo->donationStats();
$testimonials = $repo->testimonials();
$missionCards = [
    ['braille', 'Braille Literacy', 'Every child learns to read and write — from their first dots to full fluency in Gujarati, Hindi and English.'],
    ['grad', 'Quality Education', 'A complete, accredited curriculum taught with tactile materials, audio books and infinite patience.'],
    ['rocket', 'Skill & Independence', 'Orientation, mobility, cooking and money skills that turn dependence into a confident, independent life.'],
    ['globe', 'Equal Opportunity', 'Free residential education for every visually impaired child — no family is ever turned away for money.'],
];
$featured = $repo->activities(['limit' => 3]);

$page = ['title' => false, 'nav' => 'home', 'desc' => $s['hero_subtitle'] ?? null];
require APP_DIR . '/layout/header.php';
?>

<!-- ============== ORG BRAND (below navbar) ============== -->
<section class="section" style="padding-top:2.2rem;padding-bottom:0">
  <div class="container center reveal">
    <img src="/assets/img/logo.png" alt="<?= attr(SITE_NAME) ?> logo"
      style="width:clamp(140px,20vw,240px);height:auto;object-fit:contain;display:block;margin:0 auto 1rem"
      onerror="this.style.display='none'">
    <h2 style="margin:0;font-size:clamp(1.1rem,3.2vw,1.8rem);letter-spacing:.05em;text-transform:uppercase;color:var(--brand-700)">
      National Association for the Blind, Dahod
    </h2>
  </div>
</section>

<!-- ============== HERO ============== -->
<section class="section" style="padding-top:1.6rem;overflow:hidden">
  <div class="container center" style="display:flex;flex-direction:column;align-items:center;gap:2.5rem;text-align:center">
    <div class="reveal" style="max-width:760px">
      <span class="eyebrow"><?= icon('sparkle','w-4 h-4') ?> Serving Dahod since 1998</span>
      <h1 style="margin-top:1rem">
        Empowering <span class="grad-text">Visually Impaired</span> Students to Build Their Future
      </h1>
      <p class="lead" style="margin-top:1.2rem;margin-inline:auto"><?= e($s['hero_subtitle'] ?? '') ?></p>

      <div style="display:flex;gap:.9rem;flex-wrap:wrap;margin-top:1.8rem;justify-content:center">
        <a href="/donate.php" class="btn btn-lg btn-morph"><?= icon('heart','w-5 h-5') ?> Donate Now</a>
        <a href="/about.php" class="btn btn-lg btn-ghost"><?= icon('arrow-right','w-5 h-5') ?> Learn More</a>
      </div>

      <div style="display:flex;gap:1.5rem;flex-wrap:wrap;margin-top:2rem;align-items:center;justify-content:center">
        <div style="display:flex;align-items:center;gap:.5rem;color:var(--ink-soft);font-weight:600"><?= icon('shield','w-5 h-5',['style'=>'color:var(--brand-600)']) ?> 80G Tax Benefit</div>
      </div>
    </div>

    <!-- Donation QR (same QR as the Donate page) -->
    <div class="reveal" data-reveal="zoom" style="position:relative">
      <div class="clay center" style="padding:1.8rem;border-radius:var(--radius-lg);max-width:380px;margin-inline:auto">
        <span class="eyebrow" style="margin-bottom:1rem"><?= icon('gift','w-4 h-4') ?> Scan &amp; Donate</span>
        <div style="background:#fff;padding:14px;border-radius:20px;display:inline-block;box-shadow:var(--shadow-clay)">
          <img src="<?= attr(upi_qr_url()) ?>" alt="UPI QR code to donate to <?= attr(SITE_NAME) ?>" width="280" height="280" loading="lazy" style="display:block;border-radius:8px;width:100%;max-width:280px;height:auto">
        </div>
        <p style="margin:1rem 0 .2rem;font-weight:700"><?= e(UPI_ID) ?></p>
        <p class="muted" style="margin:0;font-size:.85rem">Scan with any UPI app — GPay, PhonePe, Paytm.</p>
      </div>
    </div>
  </div>
</section>

<!-- ============== DISPLAY IMAGES (auto slideshow) ============== -->
<?php $homeImages = $repo->homeImages(); ?>
<section class="section" style="padding-top:1rem;padding-bottom:1rem">
  <div class="container">
    <div class="clay display-images" style="padding:clamp(1.1rem,3vw,1.8rem);border-radius:var(--radius-lg);overflow:hidden">
      <?php if ($homeImages): ?>
        <div data-slider data-interval="5000" class="home-slider" style="position:relative;overflow:hidden;border-radius:var(--radius)">
          <div class="slider-track" style="display:flex;transition:transform .7s cubic-bezier(.2,.8,.2,1)">
            <?php foreach ($homeImages as $img): ?>
              <figure class="slide" style="position:relative;flex:0 0 100%;margin:0;background:var(--bg-soft);border-radius:var(--radius);overflow:hidden">
                <!-- blurred backdrop: same image, scaled & blurred to fill the letterbox -->
                <img src="<?= attr($img['image_url'] ?? '') ?>" alt="" aria-hidden="true" loading="lazy" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;filter:blur(26px) saturate(1.25) brightness(.92);transform:scale(1.18);z-index:0">
                <!-- sharp, full image (no crop) -->
                <img src="<?= attr($img['image_url'] ?? '') ?>" alt="<?= attr($img['title'] ?? 'School moment') ?>" loading="lazy" style="position:relative;z-index:1;width:100%;height:clamp(240px,46vw,460px);object-fit:contain;display:block">
                <?php if (!empty($img['title'])): ?>
                  <figcaption style="position:absolute;z-index:2;left:0;right:0;bottom:0;padding:1.1rem 1.3rem;color:#fff;font-weight:600;background:linear-gradient(to top,rgba(8,14,28,.72),transparent)"><?= e($img['title']) ?></figcaption>
                <?php endif; ?>
              </figure>
            <?php endforeach; ?>
          </div>

          <?php if (count($homeImages) > 1): ?>
            <div class="slider-dots" style="position:absolute;left:0;right:0;bottom:.9rem;display:flex;justify-content:center;gap:.5rem;z-index:2">
              <?php foreach ($homeImages as $k => $img): ?>
                <button class="slider-dot" type="button" aria-label="Go to image <?= $k+1 ?>"></button>
              <?php endforeach; ?>
            </div>
            <button data-slider-prev class="a11y-btn" type="button" aria-label="Previous image" style="position:absolute;top:50%;transform:translateY(-50%);left:10px;z-index:2"><?= icon('chevron-right','w-5 h-5',['style'=>'transform:rotate(180deg)']) ?></button>
            <button data-slider-next class="a11y-btn" type="button" aria-label="Next image" style="position:absolute;top:50%;transform:translateY(-50%);right:10px;z-index:2"><?= icon('chevron-right','w-5 h-5') ?></button>
          <?php endif; ?>
        </div>
      <?php else: ?>
        <div class="center" style="padding:clamp(2rem,5vw,3rem) 1rem">
          <div style="display:inline-grid;place-items:center;width:64px;height:64px;border-radius:20px;background:var(--brand-50);margin-bottom:1rem"><?= icon('image','w-8 h-8',['style'=>'color:var(--brand-400)']) ?></div>
          <h3 style="margin-bottom:.3rem">Moments coming soon</h3>
          <p class="muted" style="margin:0">Photos from our school will appear here.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- ============== MISSION ============== -->
<section class="section" style="padding-top:1rem">
  <div class="container">
    <div class="center reveal" style="max-width:680px;margin-inline:auto">
      <span class="eyebrow"><?= icon('eye','w-4 h-4') ?> Our Mission</span>
      <h2 style="margin-top:1rem">Loss of sight is never a loss of vision</h2>
      <p class="lead" style="margin-inline:auto"><?= e($s['mission_statement'] ?? '') ?></p>
    </div>

    <div data-stagger class="auto-grid" style="margin-top:2.5rem">
      <?php foreach ($missionCards as [$ic, $title, $body]): ?>
        <article class="clay card-hover reveal" data-reveal="up" style="padding:1.8rem">
          <div style="display:inline-grid;place-items:center;width:60px;height:60px;border-radius:18px;background:linear-gradient(150deg,var(--brand-500),var(--brand-700));box-shadow:var(--shadow-card);margin-bottom:1.1rem"><?= icon($ic,'w-7 h-7',['style'=>'color:#fff']) ?></div>
          <h3><?= e($title) ?></h3>
          <p><?= e($body) ?></p>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ============== IMPACT ============== -->
<section class="section">
  <div class="container">
    <div class="glass reveal" style="padding:clamp(2rem,4vw,3.5rem);border-radius:var(--radius-lg);position:relative;overflow:hidden">
      <div aria-hidden="true" class="blob float" style="position:absolute;top:-30%;right:-10%;width:30vw;height:30vw;background:linear-gradient(135deg,#bcdcff,#5fd0c5);opacity:.3"></div>
      <div style="display:grid;gap:2.5rem;grid-template-columns:1fr 1fr;align-items:center;position:relative">
        <div>
          <span class="eyebrow"><?= icon('chart','w-4 h-4') ?> Our Impact</span>
          <h2 style="margin-top:1rem">Real change, measured in lives</h2>
          <p class="lead">Behind every number is a child who can now read, a parent who can now hope, and a future that is no longer limited by sight.</p>
          <a href="/transparency.php" class="btn btn-ghost" style="margin-top:1rem"><?= icon('receipt','w-5 h-5') ?> See full transparency</a>
        </div>
        <div data-stagger style="display:grid;gap:1.2rem;grid-template-columns:1fr 1fr">
          <?php
          $impact = [
              [(int)$s['stat_volunteers'], '+', 'Active volunteers'],
              [96, '%', 'Literacy success rate'],
          ];
          foreach ($impact as $im):
              $isMoney = $im[3] ?? false; ?>
            <div class="clay reveal" style="padding:1.4rem;text-align:center">
              <?php if ($isMoney): ?>
                <div class="counter" style="font-size:clamp(1.6rem,3vw,2.2rem);font-weight:900;color:var(--brand-700)" data-counter="<?= $im[0] ?>" data-prefix="₹">0</div>
              <?php else: ?>
                <div class="counter" style="font-size:clamp(1.6rem,3vw,2.2rem);font-weight:900;color:var(--brand-700)" data-counter="<?= $im[0] ?>" data-suffix="<?= e($im[1]) ?>">0</div>
              <?php endif; ?>
              <div class="muted" style="font-weight:600;font-size:.9rem"><?= e($im[2]) ?></div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ============== FEATURED ACTIVITIES ============== -->
<section class="section" style="padding-top:0">
  <div class="container">
    <div style="display:flex;justify-content:space-between;align-items:end;gap:1rem;flex-wrap:wrap" class="reveal">
      <div>
        <span class="eyebrow"><?= icon('sparkle','w-4 h-4') ?> Life at school</span>
        <h2 style="margin-top:1rem">Recent activities</h2>
      </div>
      <a href="/activities.php" class="btn btn-ghost"><?= icon('arrow-right','w-5 h-5') ?> View all</a>
    </div>
    <div data-stagger class="auto-grid" style="margin-top:2rem">
      <?php foreach ($featured as $a): ?>
        <a href="/activity/<?= e($a['slug'] ?? $a['id']) ?>" class="clay card-hover reveal" style="overflow:hidden;text-decoration:none;color:inherit">
          <div style="aspect-ratio:16/10;overflow:hidden;background:var(--bg-soft)">
            <img src="<?= attr($a['images'][0] ?? '') ?>" alt="<?= attr($a['title']) ?>" loading="lazy" style="width:100%;height:100%;object-fit:cover">
          </div>
          <div style="padding:1.3rem">
            <span class="badge"><?= e($a['category']) ?></span>
            <h3 style="margin:.6rem 0 .4rem;font-size:1.2rem"><?= e($a['title']) ?></h3>
            <p style="font-size:.95rem"><?= e(excerpt($a['description'], 90)) ?></p>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ============== TESTIMONIALS ============== -->
<section class="section" style="padding-top:0">
  <div class="container">
    <div data-slider class="reveal" style="position:relative;max-width:820px;margin:0 auto;overflow:hidden;border-radius:var(--radius-lg)">
      <div class="slider-track" style="display:flex;transition:transform .6s cubic-bezier(.2,.8,.2,1)">
        <?php foreach ($testimonials as $t): ?>
          <figure class="slide" style="flex:0 0 100%;margin:0;padding:.4rem">
            <div class="clay" style="padding:2.2rem;text-align:center">
              <?= icon('quote','w-9 h-9',['style'=>'color:var(--brand-300);display:inline-block']) ?>
              <blockquote style="font-size:clamp(1.1rem,2vw,1.4rem);font-weight:600;color:var(--ink);margin:1rem 0;line-height:1.5">“<?= e($t['quote']) ?>”</blockquote>
              <figcaption style="font-weight:700;color:var(--brand-700)"><?= e($t['name']) ?> <span class="muted" style="font-weight:500">· <?= e($t['role']) ?></span></figcaption>
            </div>
          </figure>
        <?php endforeach; ?>
      </div>
      <div style="display:flex;justify-content:center;gap:.5rem;margin-top:1rem" aria-hidden="true">
        <?php foreach ($testimonials as $k => $t): ?>
          <button class="slider-dot" type="button" aria-label="Go to testimonial <?= $k+1 ?>" style="width:10px;height:10px;border-radius:50%;border:none;background:var(--brand-200);cursor:pointer"></button>
        <?php endforeach; ?>
      </div>
      <button data-slider-prev class="a11y-btn" type="button" aria-label="Previous testimonial" style="position:absolute;top:42%;left:6px"><?= icon('chevron-right','w-5 h-5',['style'=>'transform:rotate(180deg)']) ?></button>
      <button data-slider-next class="a11y-btn" type="button" aria-label="Next testimonial" style="position:absolute;top:42%;right:6px"><?= icon('chevron-right','w-5 h-5') ?></button>
    </div>
  </div>
</section>

<!-- ============== DONATION CTA ============== -->
<section class="section">
  <div class="container">
    <div class="reveal" style="position:relative;border-radius:var(--radius-lg);overflow:hidden;background:linear-gradient(135deg,var(--brand-700),var(--brand-900));color:#fff;padding:clamp(2.2rem,5vw,4rem)">
      <div aria-hidden="true" class="blob float" style="position:absolute;top:-20%;left:-8%;width:26vw;height:26vw;background:rgba(95,208,197,.4)"></div>
      <div aria-hidden="true" class="blob float-slow" style="position:absolute;bottom:-30%;right:-6%;width:30vw;height:30vw;background:rgba(89,164,255,.35)"></div>
      <div style="display:grid;gap:2.5rem;grid-template-columns:1.1fr .9fr;align-items:center;position:relative">
        <div>
          <span class="eyebrow" style="background:rgba(255,255,255,.15);color:#fff;border-color:rgba(255,255,255,.2)"><?= icon('gift','w-4 h-4') ?> Make a difference today</span>
          <h2 style="color:#fff;margin-top:1rem">Your gift opens a child's world</h2>
          <p style="color:#dbe7ff;font-size:1.15rem;max-width:46ch">A small, one-time gift buys Braille paper, audio books and a future. 100% transparent — every rupee is published.</p>
          <div style="display:flex;gap:1rem;margin-top:1.4rem;flex-wrap:wrap">
            <div style="display:flex;align-items:center;gap:.5rem"><?= icon('check-circle','w-5 h-5',['style'=>'color:#5fd0c5']) ?> 80G tax benefit</div>
            <div style="display:flex;align-items:center;gap:.5rem"><?= icon('lock','w-5 h-5',['style'=>'color:#5fd0c5']) ?> Secure UPI</div>
          </div>
        </div>

        <!-- floating donation card -->
        <div class="clay float-slow" style="padding:1.8rem;color:var(--ink);text-align:center">
          <h3 style="margin-bottom:.5rem">Support our students</h3>
          <p class="muted" style="font-size:.9rem;margin-bottom:1.4rem">Every rupee goes directly to a child's education.</p>
          <a href="/donate.php" class="btn btn-lg pulse-ring" style="width:100%"><?= icon('heart','w-5 h-5') ?> Donate Now</a>
          <button type="button" class="btn btn-ghost" data-modal-open="donateQR" style="width:100%;margin-top:.6rem"><?= icon('gift','w-5 h-5') ?> Scan QR to give</button>
        </div>
      </div>
    </div>
  </div>
</section>



<?php require APP_DIR . '/layout/footer.php'; ?>
