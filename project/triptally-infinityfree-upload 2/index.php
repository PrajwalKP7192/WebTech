<?php

declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

$pageTitle = 'Home';
$activePage = 'home';

require __DIR__ . '/includes/header.php';
?>

<section class="hero">
    <div class="container hero-grid">
        <div>
            <span class="eyebrow">Capstone-ready travel planning</span>
            <h1>Build dream trips without losing track of your days, money, or must-pack essentials.</h1>
            <p class="hero-copy">TripTally combines itinerary design, budget control, and packing progress into one PHP and MySQL powered workspace. It is built to satisfy a full Web Technologies capstone while still feeling like a polished real-world product.</p>
            <div class="hero-actions">
                <a class="button-primary" href="<?= $currentUser ? '/planner.php' : '/register.php' ?>">
                    <?= $currentUser ? 'Open Your Planner' : 'Create an Account' ?>
                </a>
                <a class="button-secondary" href="<?= h(url_for('/about.php')) ?>">See Features</a>
            </div>
            <div class="stats-strip">
                <article>
                    <strong>6+</strong>
                    <span>Responsive pages</span>
                </article>
                <article>
                    <strong>JS</strong>
                    <span>Live totals & filters</span>
                </article>
                <article>
                    <strong>PHP + MySQL</strong>
                    <span>Session-backed CRUD</span>
                </article>
            </div>
        </div>

        <div class="hero-panel">
            <div class="mock-card">
                <div class="mock-top">
                    <span>Upcoming escape</span>
                    <strong>Tokyo Spring Sprint</strong>
                </div>
                <ul class="mini-list">
                    <li><span>Day 1</span><strong>Shibuya food crawl</strong></li>
                    <li><span>Budget</span><strong><?= h(currency_symbol()) ?> 1,840 actual</strong></li>
                    <li><span>Packing</span><strong>17 / 22 done</strong></li>
                </ul>
            </div>
            <div class="floating-note">Interactive planner + travel dashboard</div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-heading">
            <span class="eyebrow">Why TripTally works</span>
            <h2>One app, three planning systems, zero spreadsheet chaos.</h2>
        </div>
        <div class="feature-floats clearfix">
            <article class="feature-card float-card">
                <h3>Day-wise itinerary</h3>
                <p>Organize every activity by day, time, location, and notes so your schedule remains readable from mobile to desktop.</p>
            </article>
            <article class="feature-card float-card">
                <h3>Budget by category</h3>
                <p>Track transport, stay, food, shopping, and activity costs with estimate versus actual comparisons.</p>
            </article>
            <article class="feature-card float-card">
                <h3>Packing checklist</h3>
                <p>Stay departure-ready with searchable checklist items, quantities, and live completion progress.</p>
            </article>
        </div>
    </div>
</section>

<section class="section alt-section">
    <div class="container two-col">
        <div class="callout-card">
            <span class="eyebrow">Course alignment</span>
            <h2>Every required capstone layer is already baked into the experience.</h2>
            <p>TripTally includes a multi-page structure, responsive CSS, JavaScript event handling, PHP server logic, session and cookie usage, and MySQL-backed CRUD flows.</p>
        </div>
        <div class="stack-card-group">
            <article class="stack-card">
                <h3>Frontend</h3>
                <p>Semantic HTML, expressive CSS, responsive layouts, and dynamic DOM updates for instant feedback.</p>
            </article>
            <article class="stack-card">
                <h3>Backend</h3>
                <p>PHP handles authentication, form submission, contact storage, and protected dashboard flows.</p>
            </article>
            <article class="stack-card">
                <h3>Database</h3>
                <p>MySQL stores users, trips, itinerary activities, budget entries, packing items, and contact messages.</p>
            </article>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
