<?php
session_start();

$config = require __DIR__ . "/config.php";
$pdo = null;
$databaseReady = false;

try {
    $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8mb4";
    $pdo = new PDO($dsn, $config["username"], $config["password"], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $databaseReady = true;
} catch (PDOException $exception) {
    $pdo = null;
}

$products = [
    1 => [
        "name" => "Football Studs",
        "category" => "Football",
        "price" => 4999,
        "description" => "Lightweight studs with strong grip for match-day performance.",
    ],
    2 => [
        "name" => "Cricket Bat",
        "category" => "Cricket",
        "price" => 12999,
        "description" => "Balanced bat suitable for practice sessions and competitive games.",
    ],
    3 => [
        "name" => "Basketball Shoes",
        "category" => "Basketball",
        "price" => 5999,
        "description" => "Comfortable high-top shoes with support for quick movement.",
    ],
    4 => [
        "name" => "Badminton Racket",
        "category" => "Badminton",
        "price" => 2999,
        "description" => "Reliable racket with excellent control and lightweight feel.",
    ],
];

if (!isset($_SESSION["cart"])) {
    $_SESSION["cart"] = [];
}

function get_cookie_raw(string $key, string $fallback = ""): string
{
    return isset($_COOKIE[$key]) ? (string) $_COOKIE[$key] : $fallback;
}

function get_cookie_value(string $key, string $fallback = ""): string
{
    return isset($_COOKIE[$key]) ? htmlspecialchars($_COOKIE[$key], ENT_QUOTES, "UTF-8") : $fallback;
}

function redirect_to(string $path): void
{
    header("Location: {$path}");
    exit;
}

function add_product_to_cart(int $productId): void
{
    if (!isset($_SESSION["cart"][$productId])) {
        $_SESSION["cart"][$productId] = 0;
    }

    $_SESSION["cart"][$productId]++;
}

function get_shopper_record(): ?array
{
    global $pdo, $databaseReady;

    if (!$databaseReady || !$pdo) {
        return null;
    }

    $statement = $pdo->prepare("SELECT * FROM experiment10_shoppers WHERE session_token = :session_token LIMIT 1");
    $statement->execute([
        ":session_token" => session_id(),
    ]);

    return $statement->fetch() ?: null;
}

function save_shopper_state(?string $shopperName, ?string $preferredCategory, ?string $lastViewedProduct): ?array
{
    global $pdo, $databaseReady;

    if (!$databaseReady || !$pdo) {
        return null;
    }

    $existing = get_shopper_record();

    if ($existing) {
        $statement = $pdo->prepare(
            "UPDATE experiment10_shoppers
             SET shopper_name = :shopper_name,
                 preferred_category = :preferred_category,
                 last_viewed_product = :last_viewed_product
             WHERE session_token = :session_token"
        );
    } else {
        $statement = $pdo->prepare(
            "INSERT INTO experiment10_shoppers
                (session_token, shopper_name, preferred_category, last_viewed_product)
             VALUES
                (:session_token, :shopper_name, :preferred_category, :last_viewed_product)"
        );
    }

    $statement->execute([
        ":session_token" => session_id(),
        ":shopper_name" => $shopperName !== "" ? $shopperName : null,
        ":preferred_category" => $preferredCategory !== "" ? $preferredCategory : null,
        ":last_viewed_product" => $lastViewedProduct !== "" ? $lastViewedProduct : null,
    ]);

    return get_shopper_record();
}

function load_cart_from_db(): void
{
    global $pdo, $databaseReady;

    if (!$databaseReady || !$pdo || !empty($_SESSION["cart"])) {
        return;
    }

    $shopper = get_shopper_record();

    if (!$shopper) {
        return;
    }

    $statement = $pdo->prepare(
        "SELECT product_id, quantity
         FROM experiment10_cart_items
         WHERE shopper_id = :shopper_id"
    );
    $statement->execute([
        ":shopper_id" => $shopper["id"],
    ]);

    $cart = [];

    foreach ($statement->fetchAll() as $item) {
        $cart[(int) $item["product_id"]] = (int) $item["quantity"];
    }

    $_SESSION["cart"] = $cart;
}

function sync_cart_to_db(): void
{
    global $pdo, $databaseReady, $products;

    if (!$databaseReady || !$pdo) {
        return;
    }

    $shopper = save_shopper_state(
        $_SESSION["shopper_name"] ?? "",
        get_cookie_raw("preferred_category"),
        get_cookie_raw("last_viewed_product")
    );

    if (!$shopper) {
        return;
    }

    $deleteStatement = $pdo->prepare("DELETE FROM experiment10_cart_items WHERE shopper_id = :shopper_id");
    $deleteStatement->execute([
        ":shopper_id" => $shopper["id"],
    ]);

    if (empty($_SESSION["cart"])) {
        return;
    }

    $insertStatement = $pdo->prepare(
        "INSERT INTO experiment10_cart_items
            (shopper_id, product_id, product_name, category, price, quantity)
         VALUES
            (:shopper_id, :product_id, :product_name, :category, :price, :quantity)"
    );

    foreach ($_SESSION["cart"] as $productId => $quantity) {
        if (!isset($products[$productId])) {
            continue;
        }

        $product = $products[$productId];

        $insertStatement->execute([
            ":shopper_id" => $shopper["id"],
            ":product_id" => $productId,
            ":product_name" => $product["name"],
            ":category" => $product["category"],
            ":price" => $product["price"],
            ":quantity" => $quantity,
        ]);
    }
}

function delete_shopper_state(): void
{
    global $pdo, $databaseReady;

    if (!$databaseReady || !$pdo) {
        return;
    }

    $statement = $pdo->prepare("DELETE FROM experiment10_shoppers WHERE session_token = :session_token");
    $statement->execute([
        ":session_token" => session_id(),
    ]);
}

load_cart_from_db();
$currentShopperRecord = get_shopper_record();

if ($currentShopperRecord && !isset($_SESSION["shopper_name"]) && !empty($currentShopperRecord["shopper_name"])) {
    $_SESSION["shopper_name"] = $currentShopperRecord["shopper_name"];
}
