<?php
require __DIR__ . "/common.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $productId = (int) ($_POST["product_id"] ?? 0);

    if (isset($products[$productId])) {
        add_product_to_cart($productId);
        setcookie("last_viewed_product", $products[$productId]["name"], time() + (7 * 24 * 60 * 60), "/");
        $_COOKIE["last_viewed_product"] = $products[$productId]["name"];
        save_shopper_state($_SESSION["shopper_name"] ?? "", get_cookie_raw("preferred_category"), $products[$productId]["name"]);
        sync_cart_to_db();
    }

    redirect_to("cart.php");
}

$shopperName = isset($_SESSION["shopper_name"]) ? htmlspecialchars($_SESSION["shopper_name"], ENT_QUOTES, "UTF-8") : "Guest";
$preferredCategory = get_cookie_value("preferred_category");
$lastViewed = get_cookie_value("last_viewed_product");
$databaseStatus = $databaseReady ? "Current cart is also saved in MySQL." : "MySQL not connected. This page is using session and cookie data only.";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Experiment 10 | Products</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <main class="page">
        <section class="hero">
            <span class="tag">Experiment 10</span>
            <h1>Exercise 2 Product Page</h1>
            <p class="subtitle">
                Welcome, <?= $shopperName ?>. Add items to the cart and move between pages while
                keeping your shopping details active.
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
                <h2>Available Products</h2>
                <div class="notice">
                    <?= htmlspecialchars($databaseStatus, ENT_QUOTES, "UTF-8") ?>
                </div>
                <?php if ($preferredCategory !== ""): ?>
                    <div class="notice" style="margin-top: 12px;">
                        Preferred category remembered from cookie: <strong><?= $preferredCategory ?></strong>
                    </div>
                <?php endif; ?>

                <div class="product-list" style="margin-top: 16px;">
                    <?php foreach ($products as $id => $product): ?>
                        <article class="product-card">
                            <div class="product-top">
                                <span class="badge"><?= htmlspecialchars($product["category"], ENT_QUOTES, "UTF-8") ?></span>
                                <span class="badge">Product #<?= $id ?></span>
                            </div>
                            <h3><?= htmlspecialchars($product["name"], ENT_QUOTES, "UTF-8") ?></h3>
                            <p class="description"><?= htmlspecialchars($product["description"], ENT_QUOTES, "UTF-8") ?></p>
                            <p class="price">Rs. <?= number_format($product["price"], 2) ?></p>
                            <form method="post" class="button-row">
                                <input type="hidden" name="product_id" value="<?= $id ?>">
                                <button class="primary" type="submit">Add to Cart</button>
                            </form>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>

            <aside class="stack">
                <section class="panel">
                    <h3>Saved Browser Details</h3>
                    <div class="summary-item">
                        <strong>Preferred Category</strong>
                        <?= $preferredCategory !== "" ? $preferredCategory : "Not saved yet" ?>
                    </div>
                    <div class="summary-item" style="margin-top: 10px;">
                        <strong>Last Viewed Product</strong>
                        <?= $lastViewed !== "" ? $lastViewed : "No product stored yet" ?>
                    </div>
                </section>

                <section class="panel">
                    <h3>Stored Totals</h3>
                    <div class="summary-item">
                        <strong>Session Cart Count</strong>
                        <?= array_sum($_SESSION["cart"]) ?>
                    </div>
                    <div class="summary-item" style="margin-top: 10px;">
                        <strong>MySQL Cart Status</strong>
                        <?= $databaseReady ? "Synced to experiment10_cart_items" : "Database sync unavailable" ?>
                    </div>
                </section>
            </aside>
        </section>
    </main>
</body>
</html>
