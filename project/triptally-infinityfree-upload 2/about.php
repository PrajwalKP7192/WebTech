<?php

declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

$pageTitle = 'About';
$activePage = 'about';

require __DIR__ . '/includes/header.php';
?>

<section class="page-banner">
    <div class="container narrow">
        <span class="eyebrow">About the project</span>
        <h1>TripTally is a travel planner designed to feel practical, polished, and presentation-ready.</h1>
        <p>It was structured as a capstone-friendly web application that demonstrates frontend styling, JavaScript interactivity, PHP form processing, session and cookie management, and MySQL data persistence.</p>
    </div>
</section>

<section class="section">
    <div class="container cards-grid">
        <article class="info-card">
            <h3>Problem it solves</h3>
            <p>Travel planning often gets split across notes, calculators, and checklists. TripTally unifies the whole flow so a traveler can plan the route, estimate expenses, and pack with confidence.</p>
        </article>
        <article class="info-card">
            <h3>Who it helps</h3>
            <p>Students, solo travelers, families, and anyone planning a vacation, business tour, or weekend break with tight schedules and spending limits.</p>
        </article>
        <article class="info-card">
            <h3>Why it stands out</h3>
            <p>The app blends practical use cases with academic requirements, which makes it strong for both project evaluation and live demos.</p>
        </article>
    </div>
</section>

<section class="section alt-section">
    <div class="container rubric-grid">
        <article class="rubric-card">
            <span>HTML + CSS</span>
            <h3>Responsive multi-page interface</h3>
            <p>Shared navigation, box-model spacing, positioned accents, float-based feature cards, and mobile-first adjustments.</p>
        </article>
        <article class="rubric-card">
            <span>JavaScript</span>
            <h3>Interactive, real-time UI behavior</h3>
            <p>Mobile navigation, client-side validation, packing progress, and budget filtering update the interface instantly.</p>
        </article>
        <article class="rubric-card">
            <span>PHP</span>
            <h3>Secure server-side processing</h3>
            <p>Registration, login, protected pages, flash messages, contact handling, and cookies are handled on the server.</p>
        </article>
        <article class="rubric-card">
            <span>MySQL</span>
            <h3>Travel data stored cleanly</h3>
            <p>Trips, activities, budgets, packing items, and contact messages are persisted in normalized tables.</p>
        </article>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
