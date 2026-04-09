<?php

declare(strict_types=1);

$pageTitle = $pageTitle ?? 'TripTally';
$activePage = $activePage ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($pageTitle) ?> | TripTally</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700&family=Fraunces:opsz,wght@9..144,600;9..144,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= h(url_for('/assets/css/styles.css')) ?>">
</head>
<body>
    <div class="page-shell">
        <header class="site-header">
            <div class="container nav-wrap">
                <a class="brand" href="<?= h(url_for('/index.php')) ?>">
                    <span class="brand-mark">TT</span>
                    <span>
                        <strong>TripTally</strong>
                        <small>Travel Budget & Itinerary Planner</small>
                    </span>
                </a>

                <button class="nav-toggle" type="button" aria-label="Toggle navigation" data-nav-toggle>
                    <span></span>
                    <span></span>
                </button>

                <nav class="site-nav" data-nav-menu>
                    <a class="<?= $activePage === 'home' ? 'active' : '' ?>" href="<?= h(url_for('/index.php')) ?>">Home</a>
                    <a class="<?= $activePage === 'about' ? 'active' : '' ?>" href="<?= h(url_for('/about.php')) ?>">About</a>
                    <a class="<?= $activePage === 'planner' ? 'active' : '' ?>" href="<?= h(url_for('/planner.php')) ?>">Planner</a>
                    <a class="<?= $activePage === 'budget' ? 'active' : '' ?>" href="<?= h(url_for('/budget.php')) ?>">Budget</a>
                    <a class="<?= $activePage === 'packing' ? 'active' : '' ?>" href="<?= h(url_for('/packing.php')) ?>">Packing</a>
                    <a class="<?= $activePage === 'contact' ? 'active' : '' ?>" href="<?= h(url_for('/contact.php')) ?>">Contact</a>
                    <?php if ($currentUser): ?>
                        <a class="nav-chip" href="<?= h(url_for('/logout.php')) ?>">Logout</a>
                    <?php else: ?>
                        <a class="<?= $activePage === 'login' ? 'active' : '' ?>" href="<?= h(url_for('/login.php')) ?>">Login</a>
                        <a class="nav-chip" href="<?= h(url_for('/register.php')) ?>">Get Started</a>
                    <?php endif; ?>
                </nav>
            </div>
        </header>

        <main>
            <?php if ($flash): ?>
                <div class="container">
                    <div class="flash <?= h(flash_class($flash['type'])) ?>">
                        <?= h($flash['message']) ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (db_error_message()): ?>
                <div class="container">
                    <div class="flash flash-info">
                        Database not connected yet. Update your MySQL credentials in <code>config/database.php</code> or environment variables before testing live CRUD.
                    </div>
                </div>
            <?php endif; ?>
