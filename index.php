<?php
/**
 * index.php — Public Landing Page
 * FoodCare — Food Quality Complaint Management System.
 */
require_once __DIR__ . '/includes/auth.php';

if (is_logged_in()) {
    redirect(is_admin() ? '/admin/dashboard.php' : '/customer_dashboard.php');
}

$page_title = 'FoodCare — Professional Complaint Management';
require_once __DIR__ . '/includes/header.php';
?>

<style>
/* ══════════════════════════════════════════════
   LANDING PAGE — Exact Screenshot Layout
══════════════════════════════════════════════ */

/* ── HERO ── */
.lp-hero {
    padding: calc(var(--navbar-h) + 4rem) 0 0;
    background: var(--fc-white);
}
.lp-hero__inner {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 3rem;
    align-items: center;
    padding-bottom: 0;
}
.lp-hero__badge {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--fc-primary);
    text-transform: uppercase;
    letter-spacing: 0.06em;
    margin-bottom: 1.25rem;
}
.lp-hero__badge::before {
    content: '';
    display: inline-block;
    width: 6px; height: 6px;
    border-radius: 50%;
    background: var(--fc-primary);
}
.lp-hero__title {
    font-size: clamp(2rem, 4vw, 3rem);
    font-weight: 800;
    color: var(--fc-black);
    line-height: 1.15;
    margin-bottom: 1.25rem;
    letter-spacing: -0.02em;
}
.lp-hero__desc {
    font-size: 1rem;
    color: var(--fc-muted);
    line-height: 1.7;
    margin-bottom: 2rem;
    max-width: 440px;
}
.lp-hero__actions {
    display: flex;
    gap: 0.875rem;
    flex-wrap: wrap;
    margin-bottom: 2rem;
}
.lp-hero__trust {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 0.8rem;
    color: var(--fc-muted);
}
.lp-hero__avatars {
    display: flex;
}
.lp-hero__avatars span {
    width: 28px; height: 28px;
    border-radius: 50%;
    border: 2px solid white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.65rem;
    font-weight: 700;
    color: white;
    margin-left: -8px;
}
.lp-hero__avatars span:first-child { margin-left: 0; }

/* Hero image side */
.lp-hero__img-wrap {
    position: relative;
    display: flex;
    justify-content: flex-end;
    align-items: flex-end;
}
.lp-hero__img {
    width: 85%;
    border-radius: var(--radius-xl) var(--radius-xl) 0 0;
    object-fit: cover;
    height: 420px;
    display: block;
}
.lp-hero__stat-card {
    position: absolute;
    bottom: 2rem;
    left: 0;
    background: white;
    border: 1px solid var(--fc-border);
    border-radius: var(--radius-lg);
    padding: 1rem 1.25rem;
    box-shadow: var(--shadow-md);
    display: flex;
    align-items: center;
    gap: 0.75rem;
    min-width: 180px;
}
.lp-hero__stat-dot {
    width: 10px; height: 10px;
    border-radius: 50%;
    background: #28A745;
    flex-shrink: 0;
}
.lp-hero__stat-num {
    font-size: 1.3rem;
    font-weight: 800;
    color: var(--fc-black);
    line-height: 1;
}
.lp-hero__stat-label {
    font-size: 0.72rem;
    color: var(--fc-muted);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-top: 2px;
}

/* ── CORE PILLARS ── */
.lp-section {
    padding: 5rem 0;
    background: var(--fc-white);
}
.lp-section--gray { background: var(--fc-bg); }
.lp-section--dark { background: #1A1A1A; }

.lp-section__eyebrow {
    text-align: center;
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: var(--fc-muted);
    margin-bottom: 0.75rem;
}
.lp-section__title {
    text-align: center;
    font-size: clamp(1.5rem, 3vw, 2rem);
    font-weight: 800;
    color: var(--fc-black);
    margin-bottom: 0.75rem;
}
.lp-section__sub {
    text-align: center;
    font-size: 0.95rem;
    color: var(--fc-muted);
    max-width: 540px;
    margin: 0 auto 3rem;
    line-height: 1.7;
}

/* Pillars grid */
.pillars-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.5rem;
}
.pillar-card {
    padding: 2rem;
    border: 1px solid var(--fc-border);
    border-radius: var(--radius-lg);
    background: var(--fc-white);
    transition: box-shadow var(--transition-normal), transform var(--transition-normal);
}
.pillar-card:hover {
    box-shadow: var(--shadow-md);
    transform: translateY(-3px);
}
.pillar-card__icon {
    width: 44px; height: 44px;
    border-radius: var(--radius-md);
    background: rgba(255,122,26,0.10);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.3rem;
    margin-bottom: 1.25rem;
    color: var(--fc-primary);
}
.pillar-card__title {
    font-weight: 700;
    font-size: 1rem;
    color: var(--fc-black);
    margin-bottom: 0.5rem;
}
.pillar-card__desc {
    font-size: 0.875rem;
    color: var(--fc-muted);
    line-height: 1.65;
}

/* ── LIFECYCLE DARK BANNER ── */
.lifecycle {
    background: #1A1A1A;
    padding: 3.5rem 0;
}
.lifecycle__title {
    text-align: center;
    font-size: 1.4rem;
    font-weight: 800;
    color: #fff;
    margin-bottom: 2.5rem;
}
.lifecycle__steps {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
    position: relative;
}
.lifecycle__steps::before {
    content: '';
    position: absolute;
    top: 22px;
    left: 12.5%;
    right: 12.5%;
    height: 2px;
    background: rgba(255,122,26,0.3);
}
.lifecycle__step {
    text-align: center;
    position: relative;
    z-index: 1;
}
.lifecycle__step-num {
    width: 44px; height: 44px;
    border-radius: 50%;
    background: var(--fc-primary);
    color: #fff;
    font-weight: 800;
    font-size: 1rem;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 1rem;
}
.lifecycle__step-title {
    font-weight: 700;
    font-size: 0.9rem;
    color: #fff;
    margin-bottom: 0.4rem;
}
.lifecycle__step-desc {
    font-size: 0.78rem;
    color: #9CA3AF;
    line-height: 1.5;
}

/* ── TESTIMONIALS ── */
.testimonials-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 2.5rem;
}
.testimonials-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.5rem;
}
.tcard {
    background: var(--fc-white);
    border: 1px solid var(--fc-border);
    border-radius: var(--radius-lg);
    padding: 1.75rem;
}
.tcard__stars { color: #F5A623; font-size: 0.85rem; margin-bottom: 1rem; letter-spacing: 2px; }
.tcard__quote { font-size: 0.875rem; color: var(--fc-muted); line-height: 1.7; margin-bottom: 1.25rem; font-style: italic; }
.tcard__author { display: flex; align-items: center; gap: 0.75rem; }
.tcard__avatar {
    width: 38px; height: 38px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 0.85rem; color: white;
    flex-shrink: 0;
}
.tcard__name { font-weight: 700; font-size: 0.85rem; color: var(--fc-black); }
.tcard__role { font-size: 0.72rem; color: var(--fc-muted); text-transform: uppercase; letter-spacing: 0.05em; }
.trustscore { display: inline-flex; align-items: center; gap: 0.4rem; font-size: 0.85rem; font-weight: 700; color: var(--fc-primary); }

/* ── CTA ── */
.lp-cta {
    background: var(--fc-bg);
    padding: 5rem 0;
    text-align: center;
    border-top: 1px solid var(--fc-border);
}
.lp-cta__title { font-size: clamp(1.6rem, 3vw, 2.4rem); font-weight: 800; color: var(--fc-black); margin-bottom: 0.75rem; }
.lp-cta__sub { font-size: 1rem; color: var(--fc-muted); max-width: 460px; margin: 0 auto 2.25rem; line-height: 1.7; }
.lp-cta__actions { display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; }

/* ── FOOTER ── */
.lp-footer {
    background: var(--fc-white);
    border-top: 1px solid var(--fc-border);
    padding: 3rem 0 1.5rem;
}
.lp-footer__grid {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 1fr;
    gap: 2.5rem;
    margin-bottom: 2.5rem;
}
.lp-footer__brand-name {
    font-family: var(--font-heading);
    font-size: 1.1rem;
    font-weight: 800;
    color: var(--fc-black);
    display: flex; align-items: center; gap: 0.5rem;
    margin-bottom: 0.75rem;
}
.lp-footer__brand-logo {
    width: 28px; height: 28px;
    background: var(--fc-primary);
    border-radius: 6px;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.9rem;
}
.lp-footer__brand-desc { font-size: 0.82rem; color: var(--fc-muted); line-height: 1.6; max-width: 220px; }
.lp-footer__col-title { font-weight: 700; font-size: 0.85rem; color: var(--fc-black); margin-bottom: 1rem; }
.lp-footer__links { display: flex; flex-direction: column; gap: 0.5rem; }
.lp-footer__links a { font-size: 0.82rem; color: var(--fc-muted); text-decoration: none; transition: color var(--transition-fast); }
.lp-footer__links a:hover { color: var(--fc-primary); }
.lp-footer__bottom {
    border-top: 1px solid var(--fc-border);
    padding-top: 1.25rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 0.8rem;
    color: var(--fc-muted);
    flex-wrap: wrap;
    gap: 0.5rem;
}

/* Responsive */
@media (max-width: 900px) {
    .lp-hero__inner { grid-template-columns: 1fr; }
    .lp-hero__img-wrap { display: none; }
    .pillars-grid { grid-template-columns: 1fr; }
    .lifecycle__steps { grid-template-columns: repeat(2, 1fr); }
    .lifecycle__steps::before { display: none; }
    .testimonials-grid { grid-template-columns: 1fr; }
    .lp-footer__grid { grid-template-columns: 1fr 1fr; }
}
</style>

<main>

<!-- ════════════════ HERO ════════════════ -->
<section class="lp-hero">
    <div class="container">
        <div class="lp-hero__inner">

            <!-- Left: Text -->
            <div>
                <div class="lp-hero__badge">For first-time complaint reporting</div>
                <h1 class="lp-hero__title">
                    FoodCare —<br>Professional<br>Complaint<br>Management
                </h1>
                <p class="lp-hero__desc">
                    The modern standard for food safety. We bridge the gap between businesses and
                    consumers to resolve issues transparently and efficiently.
                </p>
                <div class="lp-hero__actions">
                    <a href="<?= BASE_PATH ?>/register.php" class="btn btn--primary btn--lg" id="hero-user-btn">
                        Get a User — Create / Login
                    </a>
                    <a href="<?= BASE_PATH ?>/login.php?role=admin" class="btn btn--admin btn--lg" id="hero-admin-btn">
                        Join as Admin
                    </a>
                </div>
                <div class="lp-hero__trust">
                    <div class="lp-hero__avatars">
                        <span style="background:#FF7A1A;">S</span>
                        <span style="background:#0B0B0B;">M</span>
                        <span style="background:#28A745;">E</span>
                        <span style="background:#3B82F6;">R</span>
                    </div>
                    <span>Trusted by <strong>2,500</strong> businesses worldwide</span>
                </div>
            </div>

            <!-- Right: Image + Stat Card -->
            <div class="lp-hero__img-wrap">
                <img src="<?= BASE_PATH ?>/assets/hero.png" alt="Food Safety Professional" class="lp-hero__img">
                <div class="lp-hero__stat-card">
                    <div class="lp-hero__stat-dot"></div>
                    <div>
                        <div class="lp-hero__stat-num">98.4%</div>
                        <div class="lp-hero__stat-label">Issue Resolved</div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- ════════════════ CORE PILLARS ════════════════ -->
<section class="lp-section lp-section--gray" id="how-it-works">
    <div class="container">
        <p class="lp-section__eyebrow">Our Core Pillars</p>
        <h2 class="lp-section__title">OUR CORE PILLARS</h2>
        <p class="lp-section__sub">
            Designed to make the complex process of food safety compliance simple, accountable,
            and meaningful for the public.
        </p>

        <div class="pillars-grid">
            <div class="pillar-card">
                <div class="pillar-card__icon">🛡️</div>
                <div class="pillar-card__title">Report Securely</div>
                <p class="pillar-card__desc">
                    Users can submit details of concerns with photos and timestamps. Our system stores
                    all complaint information in a structured, secure database.
                </p>
            </div>
            <div class="pillar-card">
                <div class="pillar-card__icon">🔍</div>
                <div class="pillar-card__title">Track Progress</div>
                <p class="pillar-card__desc">
                    Follow real-time information to track complaint status updates so that progress shows
                    through the administrative workflow.
                </p>
            </div>
            <div class="pillar-card">
                <div class="pillar-card__icon">✅</div>
                <div class="pillar-card__title">Resolve Efficiently</div>
                <p class="pillar-card__desc">
                    Admins get powerful tools to verify, communicate, and update cases, ensuring compliance
                    with health and safety standards.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- ════════════════ LIFECYCLE DARK ════════════════ -->
<section class="lifecycle" id="about">
    <div class="container">
        <h2 class="lifecycle__title">The FoodCare Lifecycle</h2>
        <div class="lifecycle__steps">
            <div class="lifecycle__step">
                <div class="lifecycle__step-num">1</div>
                <div class="lifecycle__step-title">Submit</div>
                <p class="lifecycle__step-desc">Customer submits a food quality or safety complaint with details and photo evidence.</p>
            </div>
            <div class="lifecycle__step">
                <div class="lifecycle__step-num">2</div>
                <div class="lifecycle__step-title">Acknowledge</div>
                <p class="lifecycle__step-desc">Admin receives and reviews the complaint, marking it as under investigation.</p>
            </div>
            <div class="lifecycle__step">
                <div class="lifecycle__step-num">3</div>
                <div class="lifecycle__step-title">Investigate</div>
                <p class="lifecycle__step-desc">The team investigates the reported food product issue and prepares a resolution.</p>
            </div>
            <div class="lifecycle__step">
                <div class="lifecycle__step-num">4</div>
                <div class="lifecycle__step-title">Close</div>
                <p class="lifecycle__step-desc">Complaint is resolved and closed with a resolution note visible to the customer.</p>
            </div>
        </div>
    </div>
</section>

<!-- ════════════════ TESTIMONIALS ════════════════ -->
<section class="lp-section" id="contact">
    <div class="container">
        <div class="testimonials-header">
            <div>
                <h2 class="lp-section__title" style="text-align:left; margin-bottom:0.5rem;">Voice of the Community</h2>
                <p class="lp-section__sub" style="text-align:left; margin:0;">
                    Hear from the health inspectors and restaurant owners who have<br>
                    transformed their operations with FoodCare.
                </p>
            </div>
            <div class="trustscore">⭐ TrustScore 4.9/5.0</div>
        </div>

        <div class="testimonials-grid">
            <div class="tcard">
                <div class="tcard__stars">★★★★★</div>
                <p class="tcard__quote">"FoodCare has reduced our response time to customer issues by 60%. The structured data makes inspections a breeze."</p>
                <div class="tcard__author">
                    <div class="tcard__avatar" style="background:#FF7A1A;">S</div>
                    <div>
                        <div class="tcard__name">Sarah Jenkins</div>
                        <div class="tcard__role">Head of QA, FreshEats Group</div>
                    </div>
                </div>
            </div>
            <div class="tcard">
                <div class="tcard__stars">★★★★★</div>
                <p class="tcard__quote">"As a customer, I finally feel heard. The tracking system actually shows me that someone is taking my health concern seriously."</p>
                <div class="tcard__author">
                    <div class="tcard__avatar" style="background:#0B0B0B;">M</div>
                    <div>
                        <div class="tcard__name">Marcus Thompson</div>
                        <div class="tcard__role">Public Health Advocate</div>
                    </div>
                </div>
            </div>
            <div class="tcard">
                <div class="tcard__stars">★★★★★</div>
                <p class="tcard__quote">"The interface is incredibly intuitive. Our team was fully trained in one afternoon. Accountability has never been this simple."</p>
                <div class="tcard__author">
                    <div class="tcard__avatar" style="background:#28A745;">E</div>
                    <div>
                        <div class="tcard__name">Ellen Rodriguez</div>
                        <div class="tcard__role">Health &amp; Safety Inspector</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ════════════════ CTA ════════════════ -->
<section class="lp-cta">
    <div class="container">
        <h2 class="lp-cta__title">Ready to improve food safety?</h2>
        <p class="lp-cta__sub">
            Join thousands of businesses and users dedicated to a safer, more transparent
            food industry. Start your journey today.
        </p>
        <div class="lp-cta__actions">
            <a href="<?= BASE_PATH ?>/register.php" class="btn btn--primary btn--lg" id="cta-user-btn">
                Get Started as a User
            </a>
            <a href="<?= BASE_PATH ?>/login.php?role=admin" class="btn btn--secondary btn--lg" id="cta-admin-btn" style="border-color:#0B0B0B; color:#0B0B0B;">
                Register as Admin
            </a>
        </div>
    </div>
</section>

<!-- ════════════════ FOOTER ════════════════ -->
<footer class="lp-footer">
    <div class="container">
        <div class="lp-footer__grid">
            <!-- Brand -->
            <div>
                <div class="lp-footer__brand-name">
                    <div class="lp-footer__brand-logo">🍊</div>
                    FoodCare
                </div>
                <p class="lp-footer__brand-desc">
                    Leading the conversation on business and food safety transparency throughout our community management.
                </p>
            </div>
            <!-- Product -->
            <div>
                <div class="lp-footer__col-title">Product</div>
                <div class="lp-footer__links">
                    <a href="#">Features</a>
                    <a href="#">How it Works</a>
                    <a href="<?= BASE_PATH ?>/register.php">Sign Up</a>
                    <a href="<?= BASE_PATH ?>/login.php">Login</a>
                    <a href="#">Pricing</a>
                </div>
            </div>
            <!-- Company -->
            <div>
                <div class="lp-footer__col-title">Company</div>
                <div class="lp-footer__links">
                    <a href="#">About Us</a>
                    <a href="#">Blog</a>
                    <a href="#">Careers</a>
                    <a href="#">Contact Us</a>
                </div>
            </div>
            <!-- Support -->
            <div>
                <div class="lp-footer__col-title">Support</div>
                <div class="lp-footer__links">
                    <a href="#">Help Center</a>
                    <a href="#">Privacy Policy</a>
                    <a href="#">Terms of Service</a>
                    <a href="#">Contact Support</a>
                </div>
            </div>
        </div>
        <div class="lp-footer__bottom">
            <span>© 2025 FoodCare. All rights reserved.</span>
            <div style="display:flex; gap:1rem;">
                <a href="#" style="color:var(--fc-muted); font-size:0.8rem;">Privacy Policy</a>
                <a href="#" style="color:var(--fc-muted); font-size:0.8rem;">Terms of Service</a>
            </div>
        </div>
    </div>
</footer>

</main>

<?php /* Footer already in lp-footer above, skip shared footer */ ?>
