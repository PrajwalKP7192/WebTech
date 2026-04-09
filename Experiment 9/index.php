<?php
$config = require __DIR__ . "/config.php";

$errors = [];
$success = "";
$connectionMessage = "";
$products = [];

$formData = [
    "product_name" => "",
    "category" => "",
    "price" => "",
    "stock" => "",
    "description" => "",
];

$pdo = null;

try {
    $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8mb4";
    $pdo = new PDO($dsn, $config["username"], $config["password"], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $connectionMessage = "Database connection established successfully.";
} catch (PDOException $exception) {
    $connectionMessage = "Database connection not available. Update Experiment 9/config.php and import schema.sql before testing storage.";
}

function clean_value(string $value): string
{
    return htmlspecialchars(trim($value), ENT_QUOTES, "UTF-8");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $productName = trim($_POST["product_name"] ?? "");
    $category = trim($_POST["category"] ?? "");
    $price = trim($_POST["price"] ?? "");
    $stock = trim($_POST["stock"] ?? "");
    $description = trim($_POST["description"] ?? "");

    $formData["product_name"] = clean_value($productName);
    $formData["category"] = clean_value($category);
    $formData["price"] = clean_value($price);
    $formData["stock"] = clean_value($stock);
    $formData["description"] = clean_value($description);

    if ($productName === "") {
        $errors[] = "Product name is required.";
    }

    if ($category === "") {
        $errors[] = "Category is required.";
    }

    if (!is_numeric($price) || (float) $price <= 0) {
        $errors[] = "Price must be a valid number greater than 0.";
    }

    if (filter_var($stock, FILTER_VALIDATE_INT) === false || (int) $stock < 0) {
        $errors[] = "Stock must be a whole number greater than or equal to 0.";
    }

    if (strlen($description) < 10) {
        $errors[] = "Description must contain at least 10 characters.";
    }

    if (!$pdo) {
        $errors[] = "Product cannot be stored until the database connection is configured.";
    }

    if (!$errors && $pdo) {
        $statement = $pdo->prepare(
            "INSERT INTO products (product_name, category, price, stock, description) VALUES (:product_name, :category, :price, :stock, :description)"
        );

        $statement->execute([
            ":product_name" => $productName,
            ":category" => $category,
            ":price" => $price,
            ":stock" => $stock,
            ":description" => $description,
        ]);

        $success = "Product stored successfully.";
        $formData = [
            "product_name" => "",
            "category" => "",
            "price" => "",
            "stock" => "",
            "description" => "",
        ];
    }
}

if ($pdo) {
    $products = $pdo->query("SELECT id, product_name, category, price, stock, description, created_at FROM products ORDER BY id DESC")->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Experiment 9 | Product Details</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <main class="page">
        <section class="hero">
            <span class="tag">Experiment 9</span>
            <h1>Create, Store, Retrieve and Display Product Details</h1>
            <p class="subtitle">
                This page manages Exercise 2 product records using PHP and MySQL, including
                product entry, database storage, retrieval, and display.
            </p>
        </section>

        <section class="status-bar">
            <div class="status-card">
                <strong>Connection Status</strong>
                <p><?= htmlspecialchars($connectionMessage, ENT_QUOTES, "UTF-8") ?></p>
            </div>
            <div class="status-card">
                <strong>Schema File</strong>
                <p>Import <code>Experiment 9/schema.sql</code> into your MySQL database before using this page.</p>
            </div>
        </section>

        <section class="layout">
            <section class="panel">
                <h2>Add Product Details</h2>
                <p class="panel-copy">Enter product information for the Exercise 2 shopping pages.</p>

                <?php if ($errors): ?>
                    <div class="alert error">
                        <?php foreach ($errors as $error): ?>
                            <p><?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert success">
                        <p><?= htmlspecialchars($success, ENT_QUOTES, "UTF-8") ?></p>
                    </div>
                <?php endif; ?>

                <form method="post" class="form-grid">
                    <div class="field">
                        <label for="product_name">Product Name</label>
                        <input type="text" id="product_name" name="product_name" value="<?= $formData["product_name"] ?>">
                    </div>

                    <div class="field">
                        <label for="category">Category</label>
                        <input type="text" id="category" name="category" value="<?= $formData["category"] ?>">
                    </div>

                    <div class="field">
                        <label for="price">Price</label>
                        <input type="number" id="price" name="price" step="0.01" value="<?= $formData["price"] ?>">
                    </div>

                    <div class="field">
                        <label for="stock">Stock</label>
                        <input type="number" id="stock" name="stock" min="0" value="<?= $formData["stock"] ?>">
                    </div>

                    <div class="field full">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="5"><?= $formData["description"] ?></textarea>
                    </div>

                    <div class="field full">
                        <button class="primary" type="submit">Store Product</button>
                    </div>
                </form>
            </section>

            <section class="panel">
                <h2>Stored Products</h2>
                <p class="panel-copy">All saved product details are displayed here after retrieval from MySQL.</p>

                <?php if (!$pdo): ?>
                    <div class="empty-state">
                        <p>Database access is not active yet. Update the connection settings and import the schema file.</p>
                    </div>
                <?php elseif (!$products): ?>
                    <div class="empty-state">
                        <p>No product records found yet. Add the first product using the form.</p>
                    </div>
                <?php else: ?>
                    <div class="product-list">
                        <?php foreach ($products as $product): ?>
                            <article class="product-card">
                                <div class="product-top">
                                    <span class="product-id">#<?= (int) $product["id"] ?></span>
                                    <span class="product-category"><?= htmlspecialchars($product["category"], ENT_QUOTES, "UTF-8") ?></span>
                                </div>
                                <h3><?= htmlspecialchars($product["product_name"], ENT_QUOTES, "UTF-8") ?></h3>
                                <p class="price">Rs. <?= number_format((float) $product["price"], 2) ?></p>
                                <p class="meta">Stock Available: <?= (int) $product["stock"] ?></p>
                                <p class="description"><?= htmlspecialchars($product["description"], ENT_QUOTES, "UTF-8") ?></p>
                                <p class="meta">Added On: <?= htmlspecialchars($product["created_at"], ENT_QUOTES, "UTF-8") ?></p>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        </section>
    </main>
</body>
</html>
