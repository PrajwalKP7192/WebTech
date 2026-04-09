<?php

declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';
require_login();

$user = current_user();
$pdo = db();
$tripFilter = selected_trip_id() ?: (int) ($_COOKIE['last_trip_id'] ?? 0);

if (is_post()) {
    verify_csrf();

    if (!$pdo) {
        set_flash('error', 'Database connection is required before packing actions can be used.');
        redirect('/packing.php' . ($tripFilter ? '?trip=' . $tripFilter : ''));
    }

    $action = $_POST['action'] ?? '';

    if ($action === 'add_item') {
        $tripId = (int) ($_POST['trip_id'] ?? 0);
        $trip = fetch_trip_for_user($tripId, (int) $user['id']);
        $itemName = trim($_POST['item_name'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $quantity = max(1, (int) ($_POST['quantity'] ?? 1));

        if (!$trip || $itemName === '' || $category === '') {
            set_flash('error', 'Choose a valid trip and enter both item name and category.');
            redirect('/packing.php' . ($tripFilter ? '?trip=' . $tripFilter : ''));
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
        redirect('/packing.php?trip=' . $tripId);
    }

    if ($action === 'toggle_item') {
        $tripId = (int) ($_POST['trip_id'] ?? 0);
        $itemId = (int) ($_POST['item_id'] ?? 0);
        $isPacked = (int) ($_POST['is_packed'] ?? 0) === 1 ? 0 : 1;
        $trip = fetch_trip_for_user($tripId, (int) $user['id']);

        if ($trip) {
            $update = $pdo->prepare('UPDATE packing_items SET is_packed = :is_packed WHERE id = :id AND trip_id = :trip_id');
            $update->execute([
                'is_packed' => $isPacked,
                'id' => $itemId,
                'trip_id' => $tripId,
            ]);
            set_flash('success', 'Packing item updated.');
        }

        redirect('/packing.php?trip=' . $tripId);
    }

    if ($action === 'delete_item') {
        $tripId = (int) ($_POST['trip_id'] ?? 0);
        $itemId = (int) ($_POST['item_id'] ?? 0);
        $trip = fetch_trip_for_user($tripId, (int) $user['id']);

        if ($trip) {
            $delete = $pdo->prepare('DELETE FROM packing_items WHERE id = :id AND trip_id = :trip_id');
            $delete->execute([
                'id' => $itemId,
                'trip_id' => $tripId,
            ]);
            set_flash('success', 'Packing item removed.');
        }

        redirect('/packing.php?trip=' . $tripId);
    }
}

$trips = [];
$items = [];
$selectedTrip = null;

if ($pdo) {
    $tripStatement = $pdo->prepare('SELECT * FROM trips WHERE user_id = :user_id ORDER BY start_date ASC');
    $tripStatement->execute(['user_id' => $user['id']]);
    $trips = $tripStatement->fetchAll();

    if ($tripFilter > 0) {
        $selectedTrip = fetch_trip_for_user($tripFilter, (int) $user['id']);
    }

    if (!$selectedTrip && $trips) {
        $selectedTrip = $trips[0];
        $tripFilter = (int) $selectedTrip['id'];
    }

    if ($selectedTrip) {
        $itemStatement = $pdo->prepare('SELECT * FROM packing_items WHERE trip_id = :trip_id ORDER BY is_packed ASC, category ASC, item_name ASC');
        $itemStatement->execute(['trip_id' => $selectedTrip['id']]);
        $items = $itemStatement->fetchAll();
    }
}

$packedTotal = count(array_filter($items, fn (array $item): bool => (int) $item['is_packed'] === 1));
$packingProgress = count($items) > 0 ? (int) round(($packedTotal / count($items)) * 100) : 0;
$pageTitle = 'Packing';
$activePage = 'packing';

require __DIR__ . '/includes/header.php';
?>

<section class="page-banner">
    <div class="container narrow">
        <span class="eyebrow">Packing center</span>
        <h1>Make sure nothing important gets left behind.</h1>
        <p>Choose a trip, add checklist items, and watch the completion percentage update as you pack.</p>
    </div>
</section>

<section class="section">
    <div class="container detail-layout">
        <div class="panel">
            <h2>Select a trip</h2>
            <div class="filter-row">
                <?php foreach ($trips as $tripOption): ?>
                    <a class="filter-chip <?= $tripFilter === (int) $tripOption['id'] ? 'active' : '' ?>" href="<?= h(url_for('/packing.php?trip=' . (int) $tripOption['id'])) ?>">
                        <?= h($tripOption['title']) ?>
                    </a>
                <?php endforeach; ?>
                <?php if (!$trips): ?>
                    <p class="muted">Create a trip first from the planner page.</p>
                <?php endif; ?>
            </div>

            <?php if ($selectedTrip): ?>
                <form class="mini-form" method="post" data-validate-form>
                    <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                    <input type="hidden" name="action" value="add_item">
                    <input type="hidden" name="trip_id" value="<?= (int) $selectedTrip['id'] ?>">
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
                            <input type="number" name="quantity" min="1" value="1" required>
                        </label>
                    </div>
                    <button class="button-primary" type="submit">Add Checklist Item</button>
                </form>
            <?php endif; ?>
        </div>

        <div class="panel" data-packing-board>
            <div class="panel-header">
                <h2><?= h($selectedTrip['title'] ?? 'Packing list') ?></h2>
                <strong data-pack-progress><?= $packingProgress ?>% packed</strong>
            </div>
            <div class="packing-list" data-pack-items>
                <?php foreach ($items as $item): ?>
                    <article class="pack-row <?= (int) $item['is_packed'] === 1 ? 'is-packed' : '' ?>" data-packed="<?= (int) $item['is_packed'] ?>">
                        <form method="post" class="pack-toggle">
                            <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                            <input type="hidden" name="action" value="toggle_item">
                            <input type="hidden" name="trip_id" value="<?= (int) $selectedTrip['id'] ?>">
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
                            <input type="hidden" name="action" value="delete_item">
                            <input type="hidden" name="trip_id" value="<?= (int) $selectedTrip['id'] ?>">
                            <input type="hidden" name="item_id" value="<?= (int) $item['id'] ?>">
                            <button class="text-button" type="submit">Remove</button>
                        </form>
                    </article>
                <?php endforeach; ?>
                <?php if (!$items): ?>
                    <p class="muted">No checklist items added for this trip yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
