/**
 * main.js — FoodCare Shared JavaScript
 * =======================================
 * This file contains shared JS utilities used across all pages.
 * It is included via <script src="/js/main.js"></script> in footer.php.
 *
 * Features:
 *   1. Auto-dismiss flash/alert messages
 *   2. Mobile nav hamburger toggle
 *   3. Active nav link highlighting
 *   4. File upload preview
 */

// ── Run after the DOM is ready ───────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    initNavbar();
    initAlerts();
    initFileUploads();
    initProductCards();
});

// ── 1. NAVBAR ─────────────────────────────────────────────────────────────────

function initNavbar() {
    const toggle = document.getElementById('nav-toggle');
    const links  = document.getElementById('nav-links');

    // Mobile hamburger toggle
    if (toggle && links) {
        toggle.addEventListener('click', () => {
            links.classList.toggle('open');
            toggle.setAttribute('aria-expanded', links.classList.contains('open'));
        });

        // Close mobile menu when a link is clicked
        links.querySelectorAll('.navbar__link').forEach(link => {
            link.addEventListener('click', () => links.classList.remove('open'));
        });
    }

    // Highlight the active nav link based on current page URL
    const currentPath = window.location.pathname;
    document.querySelectorAll('.navbar__link').forEach(link => {
        const href = link.getAttribute('href');
        if (href && currentPath.endsWith(href)) {
            link.classList.add('active');
        }
    });
}

// ── 2. ALERTS / FLASH MESSAGES ───────────────────────────────────────────────

function initAlerts() {
    // Close button on alerts
    document.querySelectorAll('.alert__close').forEach(btn => {
        btn.addEventListener('click', () => {
            const alert = btn.closest('.alert');
            if (alert) {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-5px)';
                alert.style.transition = 'all 0.3s ease';
                setTimeout(() => alert.remove(), 300);
            }
        });
    });

    // Auto-dismiss success alerts after 5 seconds
    document.querySelectorAll('.alert--success').forEach(alert => {
        setTimeout(() => {
            if (document.contains(alert)) {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-5px)';
                alert.style.transition = 'all 0.5s ease';
                setTimeout(() => alert.remove(), 500);
            }
        }, 5000);
    });
}

// ── 3. FILE UPLOAD PREVIEW ────────────────────────────────────────────────────

function initFileUploads() {
    document.querySelectorAll('.upload-zone').forEach(zone => {
        const input   = zone.querySelector('input[type="file"]');
        const preview = zone.querySelector('.upload-zone__preview');
        const textEl  = zone.querySelector('.upload-zone__text');

        if (!input) return;

        // Click on the zone triggers the file input
        zone.addEventListener('click', (e) => {
            // If the user clicked the actual input, let it happen.
            // Otherwise, trigger the click event on the input.
            if (e.target !== input) {
                e.preventDefault();
                input.click();
            }
        });

        // Drag-and-drop styling
        zone.addEventListener('dragover', (e) => {
            e.preventDefault();
            zone.classList.add('dragover');
        });
        zone.addEventListener('dragleave', () => zone.classList.remove('dragover'));
        zone.addEventListener('drop', (e) => {
            e.preventDefault();
            zone.classList.remove('dragover');
            if (e.dataTransfer.files.length) {
                input.files = e.dataTransfer.files;
                handleFileChange(input, zone, textEl);
            }
        });

        // When a file is selected via the input
        input.addEventListener('change', () => handleFileChange(input, zone, textEl));
    });
}

function handleFileChange(input, zone, textEl) {
    const file = input.files[0];
    if (!file) return;

    // Show image preview if it's an image
    if (file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = (e) => {
            // Remove old preview or buttons if any exist
            const oldImg = zone.querySelector('.upload-preview-img');
            if (oldImg) oldImg.remove();
            
            const oldBtn = zone.querySelector('.upload-remove-btn');
            if (oldBtn) oldBtn.remove();

            // Create image preview element
            const img = document.createElement('img');
            img.src = e.target.result;
            img.className = 'upload-preview-img';
            img.style.cssText = 'max-height:140px;border-radius:8px;margin:0.5rem auto 0;display:block;object-fit:cover;';
            zone.appendChild(img);

            // Create the "Remove Photo" button
            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'btn btn--secondary btn--sm upload-remove-btn';
            removeBtn.style.cssText = 'margin:0.75rem auto 0; display:block; z-index: 10; position:relative;';
            removeBtn.textContent = '❌ Remove Photo';

            // Click event for the remove button
            removeBtn.addEventListener('click', (event) => {
                event.stopPropagation(); // 🛡️ Stop click event from bubbling up to parent box!
                input.value = '';        // Clear the file from input
                img.remove();            // Remove preview image
                removeBtn.remove();      // Remove this button
                
                // Restore default text instructions
                if (textEl) {
                    textEl.innerHTML = 'Click to upload or drag & drop a photo<br><small style="color:var(--clr-text-faint);">JPG, PNG, WEBP · Max 5MB</small>';
                }
            });
            zone.appendChild(removeBtn);

            if (textEl) textEl.textContent = `📎 ${file.name} (${formatFileSize(file.size)})`;
        };
        reader.readAsDataURL(file);
    } else {
        if (textEl) textEl.textContent = `📎 ${file.name} (${formatFileSize(file.size)})`;
    }
}

function formatFileSize(bytes) {
    if (bytes < 1024)       return bytes + ' B';
    if (bytes < 1048576)    return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / 1048576).toFixed(1) + ' MB';
}

// ── 4. PRODUCT CARD SELECTION ─────────────────────────────────────────────────

function initProductCards() {
    const cards = document.querySelectorAll('.product-card[data-product-id]');
    if (!cards.length) return;

    // Hidden input that stores the selected product ID for form submission
    const hiddenInput = document.getElementById('selected_product_id');

    cards.forEach(card => {
        card.addEventListener('click', () => {
            // Deselect all
            cards.forEach(c => c.classList.remove('selected'));
            // Select clicked card
            card.classList.add('selected');
            // Update hidden input
            if (hiddenInput) {
                hiddenInput.value = card.dataset.productId;
            }
        });
    });
}
