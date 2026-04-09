<?php

declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

if ($currentUser) {
    redirect('/planner.php');
}

if (is_post()) {
    verify_csrf();
    remember_old($_POST);

    if (!db()) {
        set_flash('error', 'Database connection is required before you can log in.');
        redirect('/login.php');
    }

    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    $statement = db()->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
    $statement->execute(['email' => $email]);
    $user = $statement->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        set_flash('error', 'Incorrect email or password.');
        redirect('/login.php');
    }

    login_user($user);
    setcookie('preferred_currency', $user['preferred_currency'], time() + (86400 * 30), '/');
    clear_old();
    set_flash('success', 'Signed in successfully.');
    redirect('/planner.php');
}

$rememberedEmail = $_COOKIE['remembered_email'] ?? old('email');
$pageTitle = 'Login';
$activePage = 'login';

require __DIR__ . '/includes/header.php';
?>

<section class="auth-section">
    <div class="container auth-grid">
        <div class="auth-copy">
            <span class="eyebrow">Welcome back</span>
            <h1>Pick up your travel plans where you left them.</h1>
            <p>Session-based access protects your trip dashboard, while the remembered email cookie makes repeat login faster.</p>
        </div>

        <form class="auth-card" method="post" data-validate-form>
            <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
            <label>
                Email Address
                <input type="email" name="email" value="<?= h($rememberedEmail) ?>" required>
            </label>
            <label>
                Password
                <input type="password" name="password" minlength="6" required>
            </label>
            <button class="button-primary" type="submit">Login</button>
            <p class="auth-meta">Need an account? <a href="<?= h(url_for('/register.php')) ?>">Register here</a>.</p>
        </form>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
