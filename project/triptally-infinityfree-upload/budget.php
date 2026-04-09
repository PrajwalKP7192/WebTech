<?php

declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';
require_login();

$user = current_user();
$pdo = db();
$tripFilter = selected_trip_id() ?: (int) ($_COOKIE['last_trip_id'] ?? 0);

if (is_post()) {
    verify_csrf();

    if (($_POST['action'] ?? '') === 'set_currency') {
        $currency = $_POST['preferred_currency'] ?? 'USD';
        setcookie('preferred_currency', $currency, time() + (86400 * 30), '/');
        set_flash('success', 'Display currency updated.');
    }

    redirect('/budget.php' . ($tripFilter ? '?trip=' . $tripFilter : ''));
}

$trips = [];
$entries = [];
$categories = [];

if ($pdo) {
    $tripStatement = $pdo->prepare('SELECT id, title FROM trips WHERE user_id = :user_id ORDER BY start_date ASC');
    $tripStatement->execute(['user_id' => $user['id']]);
    $trips = $tripStatement->fetchAll();

    $params = ['user_id' => $user['id']];
    $sql = 'SELECT b.*, t.title AS trip_title, t.destination
            FROM budget_entries b
            JOIN trips t ON t.id = b.trip_id
            WHERE t.user_id = :user_id';

    if ($tripFilter > 0) {
        $sql .= ' AND t.id = :trip_id';
        $params['trip_id'] = $tripFilter;
    }

    $sql .= ' ORDER BY b.spent_on DESC, b.id DESC';

    $entryStatement = $pdo->prepare($sql);
    $entryStatement->execute($params);
    $entries = $entryStatement->fetchAll();

    foreach ($entries as $entry) {
        $category = $entry['category'];
        $categories[$category] = ($categories[$category] ?? 0) + (float) $entry['amount'];
    }
}

$estimateTotal = array_sum(array_map(fn (array $entry): float => $entry['entry_type'] === 'estimate' ? (float) $entry['amount'] : 0.0, $entries));
$actualTotal = array_sum(array_map(fn (array $entry): float => $entry['entry_type'] === 'actual' ? (float) $entry['amount'] : 0.0, $entries));
$pageTitle = 'Budget';
$activePage = 'budget';

require __DIR__ . '/includes/header.php';
?>

<section class="page-banner">
    <div class="container banner-flex">
        <div>
            <span class="eyebrow">Budget overview</span>
            <h1>Track travel spending category by category.</h1>
            <p>Filter by trip, compare actual versus estimated cost, and present a clean financial summary during your demo.</p>
        </div>
        <form class="currency-card" method="post">
            <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
            <input type="hidden" name="action" value="set_currency">
            <label>
                Currency
                <select name="preferred_currency">
                    <?php foreach (['USD', 'EUR', 'GBP', 'INR', 'JPY', 'AUD'] as $code): ?>
                        <option value="<?= h($code) ?>" <?= currency_code() === $code ? 'selected' : '' ?>><?= h($code) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <button class="button-secondary" type="submit">Apply</button>
        </form>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="filter-row">
            <a class="filter-chip <?= $tripFilter === 0 ? 'active' : '' ?>" href="<?= h(url_for('/budget.php')) ?>">All trips</a>
            <?php foreach ($trips as $tripOption): ?>
                <a class="filter-chip <?= $tripFilter === (int) $tripOption['id'] ? 'active' : '' ?>" href="<?= h(url_for('/budget.php?trip=' . (int) $tripOption['id'])) ?>">
                    <?= h($tripOption['title']) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="detail-stats">
            <article class="metric-card">
                <span>Total estimate</span>
                <strong><?= h(currency_symbol()) . number_format($estimateTotal, 2) ?></strong>
            </article>
            <article class="metric-card">
                <span>Total actual</span>
                <strong><?= h(currency_symbol()) . number_format($actualTotal, 2) ?></strong>
            </article>
            <article class="metric-card">
                <span>Difference</span>
                <strong><?= h(currency_symbol()) . number_format($actualTotal - $estimateTotal, 2) ?></strong>
            </article>
        </div>
    </div>
</section>

<section class="section alt-section">
    <div class="container detail-layout">
        <div class="panel">
            <h2>Category breakdown</h2>
            <div class="category-stack">
                <?php foreach ($categories as $category => $total): ?>
                    <article class="category-row">
                        <span><?= h($category) ?></span>
                        <strong><?= h(currency_symbol()) . number_format($total, 2) ?></strong>
                    </article>
                <?php endforeach; ?>
                <?php if (!$categories): ?>
                    <p class="muted">No budget data available yet.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="panel" data-budget-panel>
            <div class="panel-header">
                <h2>Budget entries</h2>
                <select data-budget-filter>
                    <option value="all">All categories</option>
                    <option value="Transport">Transport</option>
                    <option value="Stay">Stay</option>
                    <option value="Food">Food</option>
                    <option value="Activities">Activities</option>
                    <option value="Shopping">Shopping</option>
                    <option value="Misc">Misc</option>
                </select>
            </div>

            <div class="budget-list" data-budget-rows>
                <?php foreach ($entries as $entry): ?>
                    <article class="budget-row" data-category="<?= h($entry['category']) ?>" data-type="<?= h($entry['entry_type']) ?>" data-amount="<?= h((string) $entry['amount']) ?>">
                        <div>
                            <span class="trip-badge"><?= h($entry['trip_title']) ?></span>
                            <h3><?= h($entry['label']) ?></h3>
                            <p><?= h($entry['category']) ?> · <?= h(ucfirst($entry['entry_type'])) ?> · <?= h($entry['destination']) ?></p>
                        </div>
                        <strong><?= h(currency_symbol()) . number_format((float) $entry['amount'], 2) ?></strong>
                    </article>
                <?php endforeach; ?>
                <?php if (!$entries): ?>
                    <p class="muted">Create budget entries from the trip workspace to see them here.</p>
                <?php endif; ?>
            </div>
            <div class="live-summary">
                <span>Filtered total</span>
                <strong data-budget-total><?= h(currency_symbol()) ?>0.00</strong>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
