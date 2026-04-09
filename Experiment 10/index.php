<?php
require __DIR__ . "/common.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $shopperName = trim($_POST["shopper_name"] ?? "");
    $preferredCategory = trim($_POST["preferred_category"] ?? "");

    if ($shopperName !== "") {
        $_SESSION["shopper_name"] = $shopperName;
    }

    if ($preferredCategory !== "") {
        setcookie("preferred_category", $preferredCategory, time() + (7 * 24 * 60 * 60), "/");
        $_COOKIE["preferred_category"] = $preferredCategory;
    }

    save_shopper_state($shopperName, $preferredCategory, get_cookie_raw("last_viewed_product"));

    redirect_to("products.php");
}

$shopperName = isset($_SESSION["shopper_name"]) ? htmlspecialchars($_SESSION["shopper_name"], ENT_QUOTES, "UTF-8") : "";
$preferredCategory = get_cookie_value("preferred_category");
$databaseStatus = $databaseReady ? "Connected to MySQL and syncing shopper details." : "Database not connected. Session and cookie storage still works.";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Experiment 10 | Sessions and Cookies</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <main class="page">
        <section class="hero">
            <span class="tag">Experiment 10</span>
            <h1>Sessions and Cookies for Exercise 2</h1>
            <p class="subtitle">
                This experiment stores shopping information across multiple pages. The
                shopper name and cart are kept in the session, while the preferred sport
                category and last viewed product are remembered with cookies.
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
                <h2>Start Shopping Session</h2>
                <p class="panel-copy">
                    Enter a shopper name and select a preferred category. These details
                    will be used across the shopping pages.
                </p>

                <div class="notice">
                    <?= htmlspecialchars($databaseStatus, ENT_QUOTES, "UTF-8") ?>
                </div>

                <form method="post" class="form-grid">
                    <div class="field">
                        <label for="shopper_name">Shopper Name</label>
                        <input type="text" id="shopper_name" name="shopper_name" value="<?= $shopperName ?>" placeholder="Enter your name">
                    </div>

                    <div class="field">
                        <label for="preferred_category">Preferred Category</label>
                        <select id="preferred_category" name="preferred_category">
                            <option value="">Select a category</option>
                            <option value="Football" <?= $preferredCategory === "Football" ? "selected" : "" ?>>Football</option>
                            <option value="Cricket" <?= $preferredCategory === "Cricket" ? "selected" : "" ?>>Cricket</option>
                            <option value="Basketball" <?= $preferredCategory === "Basketball" ? "selected" : "" ?>>Basketball</option>
                            <option value="Badminton" <?= $preferredCategory === "Badminton" ? "selected" : "" ?>>Badminton</option>
                        </select>
                    </div>

                    <div class="field full">
                        <button class="primary" type="submit">Save and Continue</button>
                    </div>
                </form>
            </section>

            <aside class="stack">
                <section class="panel">
                    <h3>Stored Information</h3>
                    <div class="summary-item">
                        <strong>Session Shopper Name</strong>
                        <?= $shopperName !== "" ? $shopperName : "Not saved yet" ?>
                    </div>
                    <div class="summary-item" style="margin-top: 10px;">
                        <strong>Cookie Preferred Category</strong>
                        <?= $preferredCategory !== "" ? $preferredCategory : "Not saved yet" ?>
                    </div>
                    <div class="summary-item" style="margin-top: 10px;">
                        <strong>MySQL Shopper Record</strong>
                        <?= $currentShopperRecord ? "Saved in experiment10_shoppers" : "No database record yet" ?>
                    </div>
                </section>

                <section class="panel">
                    <h3>How It Works</h3>
                    <ul class="status-list">
                        <li>Session keeps the shopper name and cart available across pages.</li>
                        <li>Cookie remembers the preferred category after page changes.</li>
                        <li>Another cookie records the last viewed product page.</li>
                    </ul>
                </section>
            </aside>
        </section>
    </main>
</body>
</html>
