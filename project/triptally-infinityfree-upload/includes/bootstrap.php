<?php

declare(strict_types=1);

$config = require __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

$GLOBALS['pdo'] = connect_database($config);
initialize_session();
$flash = get_flash();
$currentUser = current_user();
