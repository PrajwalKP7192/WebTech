<?php

declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

if ($currentUser) {
    logout_user();
    session_start();
    set_flash('success', 'You have been logged out.');
}

redirect('/index.php');
