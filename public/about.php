<?php
require __DIR__ . '/../app/bootstrap.php';
$s = $repo->settings();
$timeline = $repo->timeline();
$page = ['title' => 'About', 'nav' => 'about', 'desc' => 'The story, mission and vision of National Association for the Blind Dahod — a free residential school for visually impaired children in Gujarat since 1998.'];
require APP_DIR . '/layout/header.php';

$objectives = [
    ['braille', 'Braille & literacy', 'Give every child the power to read and write independently.'],
    ['rocket', 'Self-reliance', 'Teach orientation, mobility and daily-living skills for an independent life.'],
    ['grad', 'Academic excellence', 'Deliver a full, accredited curriculum with assistive technology.'],
    ['users', 'Inclusion', 'Prepare students for higher education, employment and community life.'],
    ['palette', 'Talent & arts', 'Nurture music, sport and creativity where our students truly shine.'],
    ['heart', 'Care & dignity', 'Provide free residential care in a safe, loving home.'],
];
?>

<!-- Hero -->
<section class="section" style="padding-bottom:1rem">
  <div class="container">
    <div class="center reveal" style="max-width:760px;margin-inline:auto">
      <span class="eyebrow"><?= icon('book','w-4 h-4') ?> Our Story</span>
      <h1 style="margin-top:1rem">A home where blind children learn to <span class="grad-text">see the world differently</span></h1>
      <p class="lead" style="margin-inline:auto"><?= e($s['about_history'] ?? '') ?></p>
      <button data-speak class="btn btn-ghost" type="button" style="margin-top:1.2rem"><?= icon('volume','w-5 h-5') ?> <span class="speak-label">Listen</span> to this page</button>
    </div>
  </div>
</section>

<!-- Mission & Vision -->
<section class="section" style="padding-top:1rem">
  <div class="container" style="display:grid;gap:1.5rem;grid-template-columns:1fr 1fr">
    <article class="clay reveal" data-reveal="left" style="padding:2rem">
      <div style="display:inline-grid;place-items:center;width:58px;height:58px;border-radius:16px;background:var(--brand-50);margin-bottom:1rem"><?= icon('eye','w-7 h-7',['style'=>'color:var(--brand-600)']) ?></div>
      <h2 style="font-size:1.7rem">Our Mission</h2>
      <p><?= e($s['mission_statement'] ?? '') ?></p>
    </article>
    <article class="clay reveal" data-reveal="right" style="padding:2rem">
      <div style="display:inline-grid;place-items:center;width:58px;height:58px;border-radius:16px;background:#e6fbf7;margin-bottom:1rem"><?= icon('rocket','w-7 h-7',['style'=>'color:#2a9d8f']) ?></div>
      <h2 style="font-size:1.7rem">Our Vision</h2>
      <p>A society where no visually impaired child is left behind — where blindness is met not with pity, but with opportunity, world-class education and the unshakeable belief that every child can thrive.</p>
    </article>
  </div>
</section>

<!-- Timeline -->
<section class="section">
  <div class="container">
    <div class="center reveal" style="max-width:620px;margin-inline:auto">
      <span class="eyebrow"><?= icon('tree','w-4 h-4') ?> Our Journey</span>
      <h2 style="margin-top:1rem">From one room to a movement</h2>
    </div>

    <ol class="timeline" style="list-style:none;padding:0;margin:2.5rem auto 0;max-width:780px;position:relative">
      <div aria-hidden="true" style="position:absolute;left:50%;top:0;bottom:0;width:3px;background:linear-gradient(var(--brand-200),var(--accent-mint));transform:translateX(-50%);border-radius:2px"></div>
      <?php foreach ($timeline as $i => $t): $left = $i % 2 === 0; ?>
        <li class="reveal" data-reveal="<?= $left ? 'left' : 'right' ?>" style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-bottom:1.6rem;position:relative">
          <div style="grid-column:<?= $left ? '1' : '2' ?>">
            <div class="clay card-hover" style="padding:1.4rem">
              <span class="badge"><?= e($t['year']) ?></span>
              <h3 style="margin:.5rem 0 .3rem;font-size:1.2rem"><?= e($t['title']) ?></h3>
              <p style="font-size:.95rem"><?= e($t['text']) ?></p>
            </div>
          </div>
          <span aria-hidden="true" style="position:absolute;left:50%;top:1.4rem;transform:translate(-50%,0);width:18px;height:18px;border-radius:50%;background:var(--brand-600);box-shadow:0 0 0 5px var(--brand-100)"></span>
        </li>
      <?php endforeach; ?>
    </ol>
  </div>
</section>

<!-- Principal message -->
<section class="section" style="padding-top:0">
  <div class="container">
    <div class="glass reveal" style="padding:clamp(2rem,4vw,3rem);border-radius:var(--radius-lg);display:grid;gap:2rem;grid-template-columns:auto 1fr;align-items:center">
      <div style="width:120px;height:120px;border-radius:30px;background:linear-gradient(150deg,var(--brand-500),var(--brand-700));display:grid;place-items:center;flex:none">
        <?= icon('quote','w-12 h-12',['style'=>'color:#fff']) ?>
      </div>
      <div>
        <span class="eyebrow"><?= icon('badge','w-4 h-4') ?> Principal's Message</span>
        <blockquote style="font-size:clamp(1.15rem,2vw,1.5rem);font-weight:600;line-height:1.5;margin:1rem 0;color:var(--ink)">“<?= e($s['principal_message'] ?? '') ?>”</blockquote>
        <p style="font-weight:700;color:var(--brand-700);margin:0">— Principal, <?= e(SITE_NAME) ?></p>
      </div>
    </div>
  </div>
</section>

<!-- Objectives -->
<section class="section" style="padding-top:0">
  <div class="container">
    <div class="center reveal" style="max-width:600px;margin-inline:auto">
      <span class="eyebrow"><?= icon('check-circle','w-4 h-4') ?> What we set out to do</span>
      <h2 style="margin-top:1rem">Our objectives</h2>
    </div>
    <div data-stagger class="auto-grid" style="margin-top:2rem">
      <?php foreach ($objectives as [$ic,$t,$d]): ?>
        <article class="clay card-hover reveal" style="padding:1.6rem;display:flex;gap:1rem;align-items:flex-start">
          <div style="display:inline-grid;place-items:center;width:48px;height:48px;border-radius:14px;background:var(--brand-50);flex:none"><?= icon($ic,'w-6 h-6',['style'=>'color:var(--brand-600)']) ?></div>
          <div><h3 style="font-size:1.1rem;margin-bottom:.3rem"><?= e($t) ?></h3><p style="font-size:.95rem;margin:0"><?= e($d) ?></p></div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="section" style="padding-top:0">
  <div class="container center reveal">
    <div class="clay" style="padding:2.5rem">
      <h2>Be part of the next chapter</h2>
      <p class="lead" style="margin-inline:auto">Your support keeps this school free for every child who needs it.</p>
      <div style="display:flex;gap:.8rem;justify-content:center;margin-top:1.4rem;flex-wrap:wrap">
        <a href="/donate.php" class="btn btn-lg"><?= icon('heart','w-5 h-5') ?> Donate Now</a>
        <a href="/contact.php" class="btn btn-lg btn-ghost"><?= icon('hand','w-5 h-5') ?> Volunteer</a>
      </div>
    </div>
  </div>
</section>

<?php require APP_DIR . '/layout/footer.php'; ?>
