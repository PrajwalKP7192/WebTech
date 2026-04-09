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
            <span class="eyebrow">Travel better</span>
            <h2>Everything you need for a smoother trip lives in one calm, organized space.</h2>
            <p>TripTally helps you map the days ahead, stay on top of spending, and feel prepared before departure without bouncing between notes, spreadsheets, and checklists.</p>
        </div>
        <div class="stack-card-group">
            <article class="stack-card">
                <h3>Plan with clarity</h3>
                <p>Lay out each day in a simple flow so activities, timings, and stops stay easy to follow at a glance.</p>
            </article>
            <article class="stack-card">
                <h3>Spend with confidence</h3>
                <p>Keep an eye on expected and actual costs so your trip budget stays realistic from the first booking to the last day.</p>
            </article>
            <article class="stack-card">
                <h3>Pack with less stress</h3>
                <p>Track what is ready, what is missing, and what still needs attention before it turns into a last-minute rush.</p>
            </article>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
