<?php
/**
 * footer.php — Shared Page Footer Partial
 * ==========================================
 * Include this at the BOTTOM of every page, just before </body>:
 *   require_once __DIR__ . '/includes/footer.php';  (from root pages)
 *   require_once __DIR__ . '/../includes/footer.php'; (from admin/ pages)
 *
 * It outputs:
 *   - The site footer section
 *   - Shared JavaScript includes
 *   - Page-specific JS slot
 *   - </body> and </html> closing tags
 */

// Load config if not already loaded
if (!defined('APP_NAME')) {
    require_once __DIR__ . '/config.php';
}

$current_year = date('Y');
?>

<!-- ─── Site Footer ────────────────────────────────────────────────────── -->
<footer class="footer" role="contentinfo">
    <div class="container">
        <p>
            &copy; <?= $current_year ?> <strong><?= APP_NAME ?></strong> — <?= APP_TAGLINE ?>
        </p>
        <p style="margin-top: 0.5rem; font-size: 0.75rem; color: var(--clr-text-faint);">
            Built with ❤️ for food safety and customer care.
        </p>
    </div>
</footer>
<!-- ─── End Footer ────────────────────────────────────────────────────── -->

<!-- ─── Shared JavaScript ─────────────────────────────────────────────── -->
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
