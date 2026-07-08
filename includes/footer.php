<?php
/**
 * footer.php — Shared Page Footer Partial
 * ==========================================
 * Include at the BOTTOM of every page, just before </body>:
 *   require_once __DIR__ . '/includes/footer.php';  (from root pages)
 *   require_once __DIR__ . '/../includes/footer.php'; (from admin/ pages)
 */

if (!defined('APP_NAME')) {
    require_once __DIR__ . '/config.php';
}

$current_year = date('Y');
?>

<!-- ─── Site Footer ───────────────────────────────────────────────────── -->
<footer class="site-footer" role="contentinfo">
    <div class="container site-footer__inner">
        <span class="site-footer__copy">© <?= $current_year ?> FoodCare. All rights reserved.</span>
        <div class="site-footer__links">
            <a href="#" id="footer-privacy">Privacy Policy</a>
            <a href="#" id="footer-terms">Terms of Service</a>
        </div>
    </div>
</footer>
<!-- ─── End Footer ───────────────────────────────────────────────────── -->

<!-- ─── Shared JavaScript ────────────────────────────────────────────── -->
<script src="<?= BASE_PATH ?>/js/main.js"></script>

<!-- Page-specific JS slot (define $extra_js before including footer.php) -->
<?php if (!empty($extra_js)): ?>
    <script src="<?= htmlspecialchars($extra_js) ?>"></script>
<?php endif; ?>

<!-- Inline JS slot for small page-specific scripts -->
<?php if (!empty($inline_js)): ?>
    <script><?= $inline_js ?></script>
<?php endif; ?>

</body>
</html>
