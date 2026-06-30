<?php
require __DIR__ . '/../app/bootstrap.php';
$stats = $repo->donationStats();
$recent = $repo->recentDonations(12);
$page = ['title' => 'Transparency', 'nav' => '', 'desc' => 'Donation transparency — how National Association for the Blind Dahod uses every rupee you give.'];
require APP_DIR . '/layout/header.php';

// Allocation breakdown (illustrative; editable via CMS in a full deployment)
$alloc = [
    ['Education & Braille materials', 38, 'var(--brand-600)'],
    ['Residential care (food, stay)', 30, '#5fd0c5'],
    ['Assistive technology', 14, '#59a4ff'],
    ['Teacher salaries & training', 12, '#ffb35c'],
    ['Administration', 6, '#b0bcd4'],
];
$gradStops = [];
$acc = 0;
foreach ($alloc as [$lbl, $pct, $col]) {
    $gradStops[] = "$col {$acc}% " . ($acc + $pct) . '%';
    $acc += $pct;
}
$donut = 'conic-gradient(' . implode(',', $gradStops) . ')';
?>
<section class="section" style="padding-bottom:1rem">
  <div class="container">
    <div class="center reveal" style="max-width:700px;margin-inline:auto">
      <span class="eyebrow"><?= icon('shield','w-4 h-4') ?> 100% Accountable</span>
      <h1 style="margin-top:1rem">Donation <span class="grad-text">Transparency</span></h1>
      <p class="lead" style="margin-inline:auto">We believe trust is earned. Here is exactly how your generosity is put to work for our students.</p>
    </div>
  </div>
</section>

<section class="section" style="padding-top:0">
  <div class="container">
    <div data-stagger style="display:grid;gap:1.2rem;grid-template-columns:repeat(auto-fit,minmax(200px,1fr))">
      <?php
      $cards = [
          ['heart', inr($stats['total']), 'Total raised'],
          ['users', number_format($stats['count']) . '+', 'Generous donors'],
          ['grad', '100%', 'Goes to the cause'],
          ['receipt', '80G', 'Tax certified'],
      ];
      foreach ($cards as [$ic, $big, $lbl]): ?>
        <div class="clay reveal center" style="padding:1.6rem">
          <div style="display:inline-grid;place-items:center;width:52px;height:52px;border-radius:14px;background:var(--brand-50);margin-bottom:.6rem"><?= icon($ic,'w-6 h-6',['style'=>'color:var(--brand-600)']) ?></div>
          <div style="font-size:clamp(1.4rem,3vw,2rem);font-weight:900;color:var(--brand-700)"><?= e($big) ?></div>
          <div class="muted" style="font-weight:600;font-size:.9rem"><?= e($lbl) ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section" style="padding-top:0">
  <div class="container" style="display:grid;gap:2rem;grid-template-columns:1fr 1fr;align-items:center">
    <div class="glass reveal" data-reveal="left" style="padding:2rem;text-align:center">
      <h2 style="font-size:1.6rem">Where your money goes</h2>
      <div style="display:grid;place-items:center;margin:1.5rem 0">
        <div style="width:220px;height:220px;border-radius:50%;background:<?= $donut ?>;display:grid;place-items:center;box-shadow:var(--shadow-card)">
          <div style="width:120px;height:120px;border-radius:50%;background:var(--surface);display:grid;place-items:center">
            <div><div style="font-weight:900;font-size:1.3rem;color:var(--brand-700)">100%</div><div class="muted" style="font-size:.75rem">to students</div></div>
          </div>
        </div>
      </div>
    </div>
    <div class="reveal" data-reveal="right">
      <ul style="list-style:none;padding:0;margin:0;display:grid;gap:.8rem">
        <?php foreach ($alloc as [$lbl, $pct, $col]): ?>
          <li class="clay" style="padding:1rem 1.2rem;display:flex;align-items:center;gap:1rem">
            <span style="width:16px;height:16px;border-radius:5px;background:<?= $col ?>;flex:none"></span>
            <span style="flex:1;font-weight:600"><?= e($lbl) ?></span>
            <span style="font-weight:800;color:var(--brand-700)"><?= $pct ?>%</span>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
</section>

<section class="section" style="padding-top:0">
  <div class="container">
    <h2 style="text-align:center">Recent contributions</h2>
    <div class="clay reveal" style="padding:1.2rem;margin-top:1.5rem;overflow-x:auto">
      <table style="width:100%;border-collapse:collapse;min-width:420px">
        <thead><tr style="text-align:left;color:var(--ink-mute);font-size:.85rem">
          <th style="padding:.8rem 1rem">Donor</th><th style="padding:.8rem 1rem">Date</th><th style="padding:.8rem 1rem;text-align:right">Amount</th>
        </tr></thead>
        <tbody>
          <?php foreach ($recent as $d): ?>
            <tr style="border-top:1px solid var(--line)">
              <td style="padding:.8rem 1rem;font-weight:600"><?= e($d['donor_name'] ?: 'Anonymous') ?></td>
              <td style="padding:.8rem 1rem;color:var(--ink-mute)"><?= e(fmt_date($d['created_at'] ?? '', 'j M Y')) ?></td>
              <td style="padding:.8rem 1rem;text-align:right;font-weight:800;color:var(--brand-700)"><?= e(inr((float)$d['amount'])) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <p class="center muted" style="margin-top:1rem;font-size:.9rem">Audited annual financial statements are available on request — <a href="/contact.php">contact us</a>.</p>
  </div>
</section>

<section class="section" style="padding-top:0">
  <div class="container center reveal">
    <div class="clay" style="padding:2.4rem">
      <h2>Every rupee, accounted for</h2>
      <p class="lead" style="margin-inline:auto">Join hundreds of donors who trust us with their generosity.</p>
      <a href="/donate.php" class="btn btn-lg" style="margin-top:1.2rem"><?= icon('heart','w-5 h-5') ?> Donate Now</a>
    </div>
  </div>
</section>

<?php require APP_DIR . '/layout/footer.php'; ?>
