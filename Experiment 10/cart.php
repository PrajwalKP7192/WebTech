<?php
require __DIR__ . "/common.php";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["clear_cart"])) {
    $_SESSION["cart"] = [];
    sync_cart_to_db();
    redirect_to("cart.php");
}

$shopperName = isset($_SESSION["shopper_name"]) ? htmlspecialchars($_SESSION["shopper_name"], ENT_QUOTES, "UTF-8") : "Guest";
$preferredCategory = get_cookie_value("preferred_category");
$lastViewed = get_cookie_value("last_viewed_product");
$databaseStatus = $databaseReady ? "Cart items are available in the MySQL table experiment10_cart_items." : "MySQL not connected. Cart is available only in the current session.";

$cartItems = [];
$grandTotal = 0;

foreach ($_SESSION["cart"] as $productId => $quantity) {
    if (!isset($products[$productId])) {
        continue;
    }

    $product = $products[$productId];
    $lineTotal = $product["price"] * $quantity;
    $grandTotal += $lineTotal;

    $cartItems[] = [
        "name" => $product["name"],
        "category" => $product["category"],
        "quantity" => $quantity,
        "price" => $product["price"],
        "line_total" => $lineTotal,
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Experiment 10 | Cart</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <main class="page">
        <section class="hero">
            <span class="tag">Experiment 10</span>
            <h1>Shopping Cart Summary</h1>
            <p class="subtitle">
                The cart contents are stored in the session, so your selected products remain
                available while you browse other pages.
            </p>
            <nav class="nav">
                <a href="index.php">Home</a>
                <a href="products.php">Products</a>
                <a href="cart.php">Cart</a>
                <a href="clear.php">Clear Data</a>
            </nav>
        </section>

        <section class="layout">
            <section class="panel">
                <h2><?= $shopperName ?>'s Cart</h2>
                <div class="notice">
                    <?= htmlspecialchars($databaseStatus, ENT_QUOTES, "UTF-8") ?>
                </div>
                <?php if (!$cartItems): ?>
                    <div class="empty-state" style="margin-top: 14px;">No products added yet. Visit the product page and add an item to the cart.</div>
                <?php else: ?>
                    <div class="cart-list" style="margin-top: 14px;">
                        <?php foreach ($cartItems as $item): ?>
                            <article class="cart-item">
                                <div class="cart-top">
                                    <span class="badge"><?= htmlspecialchars($item["category"], ENT_QUOTES, "UTF-8") ?></span>
                                    <span class="badge">Qty: <?= (int) $item["quantity"] ?></span>
                                </div>
                                <h3><?= htmlspecialchars($item["name"], ENT_QUOTES, "UTF-8") ?></h3>
                                <p class="meta">Price: Rs. <?= number_format($item["price"], 2) ?></p>
                                <p class="price">Line Total: Rs. <?= number_format($item["line_total"], 2) ?></p>
                            </article>
                        <?php endforeach; ?>
                    </div>

                    <div class="summary-item" style="margin-top: 16px;">
                        <strong>Grand Total</strong>
                        Rs. <?= number_format($grandTotal, 2) ?>
                    </div>

                    <form method="post" class="button-row">
                        <input type="hidden" name="clear_cart" value="1">
                        <button class="secondary" type="submit">Clear Cart Session</button>
                    </form>
                <?php endif; ?>
            </section>

            <aside class="stack">
                <section class="panel">
                    <h3>Saved Across Pages</h3>
                    <div class="summary-item">
                        <strong>Shopper Name in Session</strong>
                        <?= $shopperName ?>
                    </div>
                    <div class="summary-item" style="margin-top: 10px;">
                        <strong>Preferred Category in Cookie</strong>
                        <?= $preferredCategory !== "" ? $preferredCategory : "Not saved yet" ?>
                    </div>
                    <div class="summary-item" style="margin-top: 10px;">
                        <strong>Last Viewed Product Cookie</strong>
                        <?= $lastViewed !== "" ? $lastViewed : "Not saved yet" ?>
                    </div>
                    <div class="summary-item" style="margin-top: 10px;">
                        <strong>Database Tables</strong>
                        <?= $databaseReady ? "experiment10_shoppers and experiment10_cart_items" : "Not connected" ?>
                    </div>
                </section>
            </aside>
        </section>
    </main>
</body>
</html>
