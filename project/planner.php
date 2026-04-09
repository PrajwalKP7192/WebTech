<?php

declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';
require_login();

$user = current_user();
$pdo = db();

if (is_post()) {
    verify_csrf();

    if (!$pdo) {
        set_flash('error', 'Database connection is required before planner actions can be used.');
        redirect('/planner.php');
    }

    $action = $_POST['action'] ?? '';

    if ($action === 'create_trip') {
        $title = trim($_POST['title'] ?? '');
        $destination = trim($_POST['destination'] ?? '');
        $startDate = $_POST['start_date'] ?? '';
        $endDate = $_POST['end_date'] ?? '';
        $travelers = max(1, (int) ($_POST['travelers'] ?? 1));
        $status = trim($_POST['status'] ?? 'Planning');
        $notes = trim($_POST['notes'] ?? '');

        if ($title === '' || $destination === '' || $startDate === '' || $endDate === '' || $endDate < $startDate) {
            set_flash('error', 'Please complete all required trip fields with a valid date range.');
            redirect('/planner.php');
        }

        $insert = $pdo->prepare(
            'INSERT INTO trips (user_id, title, destination, start_date, end_date, travelers, status, notes)
             VALUES (:user_id, :title, :destination, :start_date, :end_date, :travelers, :status, :notes)'
        );
        $insert->execute([
            'user_id' => $user['id'],
            'title' => $title,
            'destination' => $destination,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'travelers' => $travelers,
            'status' => $status,
            'notes' => $notes ?: null,
        ]);

        set_flash('success', 'Trip created successfully.');
        redirect('/planner.php');
    }

    if ($action === 'delete_trip') {
        $tripId = (int) ($_POST['trip_id'] ?? 0);
        $trip = fetch_trip_for_user($tripId, (int) $user['id']);

        if ($trip) {
            $delete = $pdo->prepare('DELETE FROM trips WHERE id = :id AND user_id = :user_id');
            $delete->execute([
                'id' => $tripId,
                'user_id' => $user['id'],
            ]);
            set_flash('success', 'Trip removed successfully.');
        }

        redirect('/planner.php');
    }

    if ($action === 'set_currency') {
        $currency = $_POST['preferred_currency'] ?? 'USD';
        setcookie('preferred_currency', $currency, time() + (86400 * 30), '/');
        set_flash('success', 'Preferred currency updated.');
        redirect('/planner.php');
    }
}

$trips = [];

if ($pdo) {
    $statement = $pdo->prepare(
        'SELECT
            t.*,
            (SELECT COUNT(*) FROM itinerary_items i WHERE i.trip_id = t.id) AS itinerary_count,
            (SELECT COALESCE(SUM(CASE WHEN b.entry_type = "actual" THEN b.amount ELSE 0 END), 0) FROM budget_entries b WHERE b.trip_id = t.id) AS actual_total,
            (SELECT COALESCE(SUM(CASE WHEN b.entry_type = "estimate" THEN b.amount ELSE 0 END), 0) FROM budget_entries b WHERE b.trip_id = t.id) AS estimate_total,
            (SELECT COUNT(*) FROM packing_items p WHERE p.trip_id = t.id) AS packing_total,
            (SELECT COUNT(*) FROM packing_items p WHERE p.trip_id = t.id AND p.is_packed = 1) AS packed_total
         FROM trips t
         WHERE t.user_id = :user_id
         ORDER BY t.start_date ASC'
    );
    $statement->execute(['user_id' => $user['id']]);
    $trips = $statement->fetchAll();
}

$pageTitle = 'Planner';
$activePage = 'planner';

require __DIR__ . '/includes/header.php';
?>

<section class="page-banner">
    <div class="container banner-flex">
        <div>
            <span class="eyebrow">Trip dashboard</span>
            <h1>Hello, <?= h($user['full_name']) ?>. Ready to map your next adventure?</h1>
            <p>Create a trip, then dive into day-wise itinerary planning, budget logging, and packing organization from one place.</p>
        </div>
        <form class="currency-card" method="post">
            <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
            <input type="hidden" name="action" value="set_currency">
            <label>
                Display currency
                <select name="preferred_currency">
                    <?php foreach (['USD', 'EUR', 'GBP', 'INR', 'JPY', 'AUD'] as $code): ?>
                        <option value="<?= h($code) ?>" <?= currency_code() === $code ? 'selected' : '' ?>><?= h($code) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <button class="button-secondary" type="submit">Save</button>
        </form>
    </div>
</section>

<section class="section">
    <div class="container dashboard-grid">
        <form class="panel form-panel" method="post" data-validate-form>
            <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
            <input type="hidden" name="action" value="create_trip">
            <h2>Create a trip</h2>
            <div class="form-grid">
                <label>
                    Trip Name
                    <input type="text" name="title" required>
                </label>
                <label>
                    Destination
                    <input type="text" name="destination" required>
                </label>
                <label>
                    Start Date
                    <input type="date" name="start_date" required>
                </label>
                <label>
                    End Date
                    <input type="date" name="end_date" required>
                </label>
                <label>
                    Travelers
                    <input type="number" name="travelers" value="1" min="1" required>
                </label>
                <label>
                    Stage
                    <select name="status">
                        <option>Planning</option>
                        <option>Booked</option>
                        <option>Ready to Go</option>
                    </select>
                </label>
                <label class="span-2">
                    Notes
                    <textarea name="notes" rows="4" placeholder="Budget limits, hotel notes, visa reminders..."></textarea>
                </label>
            </div>
            <button class="button-primary" type="submit">Add Trip</button>
        </form>

        <div class="panel summary-panel">
            <h2>Quick snapshot</h2>
            <div class="summary-cards">
                <article>
                    <strong><?= count($trips) ?></strong>
                    <span>Total trips</span>
                </article>
                <article>
                    <strong><?= count(array_filter($trips, fn (array $trip): bool => trip_status_label($trip) === 'Upcoming')) ?></strong>
                    <span>Upcoming</span>
                </article>
                <article>
                    <strong><?= h(currency_symbol()) . number_format(array_sum(array_map(fn (array $trip): float => (float) $trip['actual_total'], $trips)), 2) ?></strong>
                    <span>Actual spend</span>
                </article>
            </div>
            <p class="muted">Your remembered currency preference is stored in a cookie so totals stay consistent between planner, budget, and packing pages.</p>
        </div>
    </div>
</section>

<section class="section alt-section">
    <div class="container">
        <div class="section-heading">
            <span class="eyebrow">Your trips</span>
            <h2>Open a trip to manage itinerary items, budget entries, and checklist details.</h2>
        </div>

        <?php if (!$trips): ?>
            <div class="empty-state">
                <h3>No trips yet</h3>
                <p>Create your first trip above to start building a travel plan.</p>
            </div>
        <?php endif; ?>

        <div class="trip-grid">
            <?php foreach ($trips as $trip): ?>
                <article class="trip-card">
                    <div class="trip-card-top">
                        <div>
                            <span class="trip-badge"><?= h(trip_status_label($trip)) ?></span>
                            <h3><?= h($trip['title']) ?></h3>
                        </div>
                        <form method="post" onsubmit="return confirm('Delete this trip and all linked data?');">
                            <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                            <input type="hidden" name="action" value="delete_trip">
                            <input type="hidden" name="trip_id" value="<?= (int) $trip['id'] ?>">
                            <button class="text-button" type="submit">Delete</button>
                        </form>
                    </div>
                    <p class="trip-location"><?= h($trip['destination']) ?></p>
                    <p class="trip-dates"><?= h(date('M d, Y', strtotime((string) $trip['start_date']))) ?> to <?= h(date('M d, Y', strtotime((string) $trip['end_date']))) ?> · <?= trip_duration($trip) ?> days</p>
                    <div class="trip-stats">
                        <span><?= (int) $trip['itinerary_count'] ?> activities</span>
                        <span><?= h(currency_symbol()) . number_format((float) $trip['actual_total'], 2) ?> actual</span>
                        <span><?= (int) $trip['packed_total'] ?>/<?= (int) $trip['packing_total'] ?> packed</span>
                    </div>
                    <p class="muted"><?= h($trip['notes'] ?: 'No notes added yet.') ?></p>
                    <a class="button-secondary full-width" href="<?= h(url_for('/trip.php?id=' . (int) $trip['id'])) ?>">Manage This Trip</a>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
