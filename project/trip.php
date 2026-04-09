<?php

declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';
require_login();

$user = current_user();
$pdo = db();
$tripId = (int) ($_GET['id'] ?? 0);
$trip = $tripId > 0 ? fetch_trip_for_user($tripId, (int) $user['id']) : null;

if (!$trip) {
    set_flash('error', 'Trip not found.');
    redirect('/planner.php');
}

setcookie('last_trip_id', (string) $tripId, time() + (86400 * 30), '/');

if (is_post()) {
    verify_csrf();

    if (!$pdo) {
        set_flash('error', 'Database connection is required before trip actions can be used.');
        redirect('/trip.php?id=' . $tripId);
    }

    $action = $_POST['action'] ?? '';

    if ($action === 'update_trip') {
        $status = trim($_POST['status'] ?? 'Planning');
        $notes = trim($_POST['notes'] ?? '');
        $update = $pdo->prepare('UPDATE trips SET status = :status, notes = :notes WHERE id = :id AND user_id = :user_id');
        $update->execute([
            'status' => $status,
            'notes' => $notes ?: null,
            'id' => $tripId,
            'user_id' => $user['id'],
        ]);
        set_flash('success', 'Trip details updated.');
        redirect('/trip.php?id=' . $tripId);
    }

    if ($action === 'add_itinerary') {
        $title = trim($_POST['title'] ?? '');
        $dayNumber = max(1, (int) ($_POST['day_number'] ?? 1));
        $activityTime = $_POST['activity_time'] ?? null;
        $location = trim($_POST['location'] ?? '');
        $costEstimate = posted_amount('cost_estimate');
        $notes = trim($_POST['notes'] ?? '');

        if ($title === '') {
            set_flash('error', 'Itinerary activity title is required.');
            redirect('/trip.php?id=' . $tripId);
        }

        $insert = $pdo->prepare(
            'INSERT INTO itinerary_items (trip_id, day_number, title, activity_time, location, cost_estimate, notes)
             VALUES (:trip_id, :day_number, :title, :activity_time, :location, :cost_estimate, :notes)'
        );
        $insert->execute([
            'trip_id' => $tripId,
            'day_number' => $dayNumber,
            'title' => $title,
            'activity_time' => $activityTime ?: null,
            'location' => $location ?: null,
            'cost_estimate' => $costEstimate,
            'notes' => $notes ?: null,
        ]);
        set_flash('success', 'Itinerary activity added.');
        redirect('/trip.php?id=' . $tripId);
    }

    if ($action === 'delete_itinerary') {
        $itemId = (int) ($_POST['item_id'] ?? 0);
        $delete = $pdo->prepare('DELETE FROM itinerary_items WHERE id = :id AND trip_id = :trip_id');
        $delete->execute([
            'id' => $itemId,
            'trip_id' => $tripId,
        ]);
        set_flash('success', 'Itinerary activity removed.');
        redirect('/trip.php?id=' . $tripId);
    }

    if ($action === 'add_budget') {
        $category = trim($_POST['category'] ?? '');
        $label = trim($_POST['label'] ?? '');
        $amount = posted_amount('amount');
        $entryType = $_POST['entry_type'] ?? 'estimate';
        $spentOn = $_POST['spent_on'] ?? null;

        if ($category === '' || $label === '' || $amount <= 0) {
            set_flash('error', 'Add a category, label, and amount greater than zero.');
            redirect('/trip.php?id=' . $tripId);
        }

        $insert = $pdo->prepare(
            'INSERT INTO budget_entries (trip_id, category, label, amount, entry_type, spent_on)
             VALUES (:trip_id, :category, :label, :amount, :entry_type, :spent_on)'
        );
        $insert->execute([
            'trip_id' => $tripId,
            'category' => $category,
            'label' => $label,
            'amount' => $amount,
            'entry_type' => $entryType,
            'spent_on' => $spentOn ?: null,
        ]);
        set_flash('success', 'Budget entry added.');
        redirect('/trip.php?id=' . $tripId);
    }

    if ($action === 'delete_budget') {
        $itemId = (int) ($_POST['item_id'] ?? 0);
        $delete = $pdo->prepare('DELETE FROM budget_entries WHERE id = :id AND trip_id = :trip_id');
        $delete->execute([
            'id' => $itemId,
            'trip_id' => $tripId,
        ]);
        set_flash('success', 'Budget entry removed.');
        redirect('/trip.php?id=' . $tripId);
    }

    if ($action === 'add_packing') {
        $itemName = trim($_POST['item_name'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $quantity = max(1, (int) ($_POST['quantity'] ?? 1));

        if ($itemName === '' || $category === '') {
            set_flash('error', 'Packing item name and category are required.');
            redirect('/trip.php?id=' . $tripId);
        }

        $insert = $pdo->prepare(
            'INSERT INTO packing_items (trip_id, item_name, category, quantity)
             VALUES (:trip_id, :item_name, :category, :quantity)'
        );
        $insert->execute([
            'trip_id' => $tripId,
            'item_name' => $itemName,
            'category' => $category,
            'quantity' => $quantity,
        ]);
        set_flash('success', 'Packing item added.');
        redirect('/trip.php?id=' . $tripId);
    }

    if ($action === 'toggle_packing') {
        $itemId = (int) ($_POST['item_id'] ?? 0);
        $isPacked = (int) ($_POST['is_packed'] ?? 0) === 1 ? 0 : 1;
        $update = $pdo->prepare('UPDATE packing_items SET is_packed = :is_packed WHERE id = :id AND trip_id = :trip_id');
        $update->execute([
            'is_packed' => $isPacked,
            'id' => $itemId,
            'trip_id' => $tripId,
        ]);
        set_flash('success', 'Packing status updated.');
        redirect('/trip.php?id=' . $tripId);
    }

    if ($action === 'delete_packing') {
        $itemId = (int) ($_POST['item_id'] ?? 0);
        $delete = $pdo->prepare('DELETE FROM packing_items WHERE id = :id AND trip_id = :trip_id');
        $delete->execute([
            'id' => $itemId,
            'trip_id' => $tripId,
        ]);
        set_flash('success', 'Packing item removed.');
        redirect('/trip.php?id=' . $tripId);
    }
}

$itineraryItems = [];
$budgetEntries = [];
$packingItems = [];

if ($pdo) {
    $itineraryStatement = $pdo->prepare('SELECT * FROM itinerary_items WHERE trip_id = :trip_id ORDER BY day_number ASC, activity_time ASC, id ASC');
    $itineraryStatement->execute(['trip_id' => $tripId]);
    $itineraryItems = $itineraryStatement->fetchAll();

    $budgetStatement = $pdo->prepare('SELECT * FROM budget_entries WHERE trip_id = :trip_id ORDER BY spent_on DESC, id DESC');
    $budgetStatement->execute(['trip_id' => $tripId]);
    $budgetEntries = $budgetStatement->fetchAll();

    $packingStatement = $pdo->prepare('SELECT * FROM packing_items WHERE trip_id = :trip_id ORDER BY is_packed ASC, category ASC, item_name ASC');
    $packingStatement->execute(['trip_id' => $tripId]);
    $packingItems = $packingStatement->fetchAll();
}

$estimateTotal = array_sum(array_map(fn (array $entry): float => $entry['entry_type'] === 'estimate' ? (float) $entry['amount'] : 0.0, $budgetEntries));
$actualTotal = array_sum(array_map(fn (array $entry): float => $entry['entry_type'] === 'actual' ? (float) $entry['amount'] : 0.0, $budgetEntries));
$packedTotal = count(array_filter($packingItems, fn (array $item): bool => (int) $item['is_packed'] === 1));
$packingProgress = count($packingItems) > 0 ? (int) round(($packedTotal / count($packingItems)) * 100) : 0;

$pageTitle = $trip['title'];
$activePage = 'planner';

require __DIR__ . '/includes/header.php';
?>

<section class="page-banner">
    <div class="container banner-flex">
        <div>
            <span class="eyebrow">Trip workspace</span>
            <h1><?= h($trip['title']) ?> in <?= h($trip['destination']) ?></h1>
            <p><?= h(date('M d, Y', strtotime((string) $trip['start_date']))) ?> to <?= h(date('M d, Y', strtotime((string) $trip['end_date']))) ?> · <?= trip_duration($trip) ?> days · <?= h($trip['travelers']) ?> traveler(s)</p>
        </div>
        <a class="button-secondary" href="<?= h(url_for('/planner.php')) ?>">Back to planner</a>
    </div>
</section>

<section class="section">
    <div class="container detail-stats">
        <article class="metric-card">
            <span>Estimated</span>
            <strong><?= h(currency_symbol()) . number_format($estimateTotal, 2) ?></strong>
        </article>
        <article class="metric-card">
            <span>Actual</span>
            <strong><?= h(currency_symbol()) . number_format($actualTotal, 2) ?></strong>
        </article>
        <article class="metric-card">
            <span>Packing progress</span>
            <strong><?= $packingProgress ?>%</strong>
        </article>
        <article class="metric-card">
            <span>Activities</span>
            <strong><?= count($itineraryItems) ?></strong>
        </article>
    </div>
</section>

<section class="section alt-section">
    <div class="container detail-layout">
        <div class="panel">
            <h2>Trip settings</h2>
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                <input type="hidden" name="action" value="update_trip">
                <label>
                    Status
                    <select name="status">
                        <?php foreach (['Planning', 'Booked', 'Ready to Go', 'Traveling', 'Completed'] as $status): ?>
                            <option value="<?= h($status) ?>" <?= $trip['status'] === $status ? 'selected' : '' ?>><?= h($status) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    Notes
                    <textarea name="notes" rows="5"><?= h($trip['notes']) ?></textarea>
                </label>
                <button class="button-primary" type="submit">Save Trip Notes</button>
            </form>
        </div>

        <div class="panel">
            <h2>Day-wise itinerary</h2>
            <form class="mini-form" method="post">
                <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                <input type="hidden" name="action" value="add_itinerary">
                <div class="form-grid">
                    <label>
                        Day
                        <input type="number" name="day_number" min="1" value="1" required>
                    </label>
                    <label>
                        Time
                        <input type="time" name="activity_time">
                    </label>
                    <label class="span-2">
                        Activity
                        <input type="text" name="title" required>
                    </label>
                    <label>
                        Location
                        <input type="text" name="location">
                    </label>
                    <label>
                        Est. Cost
                        <input type="number" name="cost_estimate" step="0.01" min="0" value="0">
                    </label>
                    <label class="span-2">
                        Notes
                        <textarea name="notes" rows="3"></textarea>
                    </label>
                </div>
                <button class="button-secondary" type="submit">Add Activity</button>
            </form>

            <div class="timeline-list">
                <?php foreach ($itineraryItems as $item): ?>
                    <article class="timeline-item" data-day="<?= (int) $item['day_number'] ?>">
                        <div>
                            <span class="timeline-day">Day <?= (int) $item['day_number'] ?></span>
                            <h3><?= h($item['title']) ?></h3>
                            <p><?= h($item['location'] ?: 'Location not set') ?><?php if ($item['activity_time']): ?> · <?= h(date('h:i A', strtotime((string) $item['activity_time']))) ?><?php endif; ?></p>
                            <p class="muted"><?= h($item['notes'] ?: 'No notes yet.') ?></p>
                        </div>
                        <div class="timeline-actions">
                            <strong><?= h(currency_symbol()) . number_format((float) $item['cost_estimate'], 2) ?></strong>
                            <form method="post">
                                <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                                <input type="hidden" name="action" value="delete_itinerary">
                                <input type="hidden" name="item_id" value="<?= (int) $item['id'] ?>">
                                <button class="text-button" type="submit">Remove</button>
                            </form>
                        </div>
                    </article>
                <?php endforeach; ?>
                <?php if (!$itineraryItems): ?>
                    <p class="muted">No activities added yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container detail-layout">
        <div class="panel" data-budget-panel>
            <div class="panel-header">
                <h2>Budget tracker</h2>
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
            <form class="mini-form" method="post">
                <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                <input type="hidden" name="action" value="add_budget">
                <div class="form-grid">
                    <label>
                        Category
                        <select name="category">
                            <option>Transport</option>
                            <option>Stay</option>
                            <option>Food</option>
                            <option>Activities</option>
                            <option>Shopping</option>
                            <option>Misc</option>
                        </select>
                    </label>
                    <label>
                        Type
                        <select name="entry_type">
                            <option value="estimate">Estimate</option>
                            <option value="actual">Actual</option>
                        </select>
                    </label>
                    <label class="span-2">
                        Label
                        <input type="text" name="label" required>
                    </label>
                    <label>
                        Amount
                        <input type="number" name="amount" step="0.01" min="0.01" required>
                    </label>
                    <label>
                        Date
                        <input type="date" name="spent_on">
                    </label>
                </div>
                <button class="button-secondary" type="submit">Add Budget Entry</button>
            </form>

            <div class="budget-list" data-budget-rows>
                <?php foreach ($budgetEntries as $entry): ?>
                    <article class="budget-row" data-category="<?= h($entry['category']) ?>" data-type="<?= h($entry['entry_type']) ?>" data-amount="<?= h((string) $entry['amount']) ?>">
                        <div>
                            <span class="trip-badge"><?= h(ucfirst($entry['entry_type'])) ?></span>
                            <h3><?= h($entry['label']) ?></h3>
                            <p><?= h($entry['category']) ?><?php if ($entry['spent_on']): ?> · <?= h(date('M d, Y', strtotime((string) $entry['spent_on']))) ?><?php endif; ?></p>
                        </div>
                        <div class="timeline-actions">
                            <strong><?= h(currency_symbol()) . number_format((float) $entry['amount'], 2) ?></strong>
                            <form method="post">
                                <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                                <input type="hidden" name="action" value="delete_budget">
                                <input type="hidden" name="item_id" value="<?= (int) $entry['id'] ?>">
                                <button class="text-button" type="submit">Remove</button>
                            </form>
                        </div>
                    </article>
                <?php endforeach; ?>
                <?php if (!$budgetEntries): ?>
                    <p class="muted">No budget entries added yet.</p>
                <?php endif; ?>
            </div>
            <div class="live-summary">
                <span>Visible entries total</span>
                <strong data-budget-total><?= h(currency_symbol()) ?>0.00</strong>
            </div>
        </div>

        <div class="panel" data-packing-board>
            <div class="panel-header">
                <h2>Packing checklist</h2>
                <strong data-pack-progress><?= $packingProgress ?>% packed</strong>
            </div>
            <form class="mini-form" method="post">
                <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                <input type="hidden" name="action" value="add_packing">
                <div class="form-grid">
                    <label class="span-2">
                        Item Name
                        <input type="text" name="item_name" required>
                    </label>
                    <label>
                        Category
                        <select name="category">
                            <option>Essentials</option>
                            <option>Clothing</option>
                            <option>Tech</option>
                            <option>Toiletries</option>
                            <option>Documents</option>
                        </select>
                    </label>
                    <label>
                        Quantity
                        <input type="number" name="quantity" value="1" min="1" required>
                    </label>
                </div>
                <button class="button-secondary" type="submit">Add Packing Item</button>
            </form>

            <div class="packing-list" data-pack-items>
                <?php foreach ($packingItems as $item): ?>
                    <article class="pack-row <?= (int) $item['is_packed'] === 1 ? 'is-packed' : '' ?>" data-packed="<?= (int) $item['is_packed'] ?>">
                        <form method="post" class="pack-toggle">
                            <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                            <input type="hidden" name="action" value="toggle_packing">
                            <input type="hidden" name="item_id" value="<?= (int) $item['id'] ?>">
                            <input type="hidden" name="is_packed" value="<?= (int) $item['is_packed'] ?>">
                            <button type="submit"><?= (int) $item['is_packed'] === 1 ? 'Packed' : 'Mark Packed' ?></button>
                        </form>
                        <div>
                            <h3><?= h($item['item_name']) ?></h3>
                            <p><?= h($item['category']) ?> · Qty <?= (int) $item['quantity'] ?></p>
                        </div>
                        <form method="post">
                            <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                            <input type="hidden" name="action" value="delete_packing">
                            <input type="hidden" name="item_id" value="<?= (int) $item['id'] ?>">
                            <button class="text-button" type="submit">Remove</button>
                        </form>
                    </article>
                <?php endforeach; ?>
                <?php if (!$packingItems): ?>
                    <p class="muted">No packing items added yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
