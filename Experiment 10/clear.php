<?php
require __DIR__ . "/common.php";

delete_shopper_state();
$_SESSION = [];
session_destroy();

setcookie("preferred_category", "", time() - 3600, "/");
setcookie("last_viewed_product", "", time() - 3600, "/");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Experiment 10 | Clear Data</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <main class="page">
        <section class="hero">
            <span class="tag">Experiment 10</span>
            <h1>Session and Cookie Data Cleared</h1>
            <p class="subtitle">
                All saved shopping session information and browser cookies have been removed.
            </p>
            <nav class="nav">
                <a href="index.php">Start Again</a>
                <a href="products.php">Products</a>
                <a href="cart.php">Cart</a>
            </nav>
        </section>

        <section class="panel">
            <h2>Data Reset Complete</h2>
            <p class="panel-copy">
                The shopper name, cart session, preferred category cookie, and last viewed
                product cookie were all cleared successfully. Any matching Experiment 10
                MySQL records were removed as well.
            </p>
        </section>
    </main>
</body>
</html>
