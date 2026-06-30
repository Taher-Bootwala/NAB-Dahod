<?php /** admin_footer.php */ ?>
    </main>
  </div>
</div>
<script src="<?= asset('assets/js/app.js') ?>" defer></script>
<script src="<?= asset('assets/js/image-compress.js') ?>" defer></script>
<script>
  document.querySelectorAll('form[data-confirm]').forEach(f =>
    f.addEventListener('submit', e => { if (!confirm(f.dataset.confirm)) e.preventDefault(); }));
</script>
</body>
</html>
