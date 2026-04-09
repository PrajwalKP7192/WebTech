<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

$routes = [
    '/' => '/index.php',
    '/index.php' => '/index.php',
    '/about' => '/about.php',
    '/about.php' => '/about.php',
    '/budget' => '/budget.php',
    '/budget.php' => '/budget.php',
    '/contact' => '/contact.php',
    '/contact.php' => '/contact.php',
    '/login' => '/login.php',
    '/login.php' => '/login.php',
    '/logout' => '/logout.php',
    '/logout.php' => '/logout.php',
    '/packing' => '/packing.php',
    '/packing.php' => '/packing.php',
    '/planner' => '/planner.php',
    '/planner.php' => '/planner.php',
    '/register' => '/register.php',
    '/register.php' => '/register.php',
    '/trip' => '/trip.php',
    '/trip.php' => '/trip.php',
];

$target = $routes[$requestPath] ?? null;

if ($target === null) {
    http_response_code(404);
    echo 'Not Found';
    exit;
}

require $root . $target;
