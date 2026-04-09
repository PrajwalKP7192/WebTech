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
        set_flash('error', 'Database connection is required before you can register.');
        redirect('/register.php');
    }

    $fullName = trim($_POST['full_name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $homeAirport = trim($_POST['home_airport'] ?? '');
    $preferredCurrency = $_POST['preferred_currency'] ?? 'USD';

    if ($fullName === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6) {
        set_flash('error', 'Enter a valid name, email, and password with at least 6 characters.');
        redirect('/register.php');
    }

    if ($password !== $confirmPassword) {
        set_flash('error', 'Password confirmation does not match.');
        redirect('/register.php');
    }

    $statement = db()->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
    $statement->execute(['email' => $email]);

    if ($statement->fetch()) {
        set_flash('error', 'An account with that email already exists.');
        redirect('/register.php');
    }

    $insert = db()->prepare(
        'INSERT INTO users (full_name, email, password_hash, home_airport, preferred_currency)
         VALUES (:full_name, :email, :password_hash, :home_airport, :preferred_currency)'
    );

    $insert->execute([
        'full_name' => $fullName,
        'email' => $email,
        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        'home_airport' => $homeAirport ?: null,
        'preferred_currency' => $preferredCurrency,
    ]);

    $userId = (int) db()->lastInsertId();
    $user = [
        'id' => $userId,
        'email' => $email,
        'preferred_currency' => $preferredCurrency,
    ];

    login_user($user);
    setcookie('preferred_currency', $preferredCurrency, time() + (86400 * 30), '/');
    clear_old();
    set_flash('success', 'Welcome to TripTally. Your account is ready.');
    redirect('/planner.php');
}

$pageTitle = 'Register';
$activePage = 'login';

require __DIR__ . '/includes/header.php';
?>

<section class="auth-section">
    <div class="container auth-grid">
        <div class="auth-copy">
            <span class="eyebrow">Start planning</span>
            <h1>Create your TripTally account.</h1>
            <p>Save trips, compare estimated and actual spending, and manage a packing checklist from any device.</p>
        </div>

        <form class="auth-card" method="post" data-validate-form>
            <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
            <label>
                Full Name
                <input type="text" name="full_name" value="<?= h(old('full_name')) ?>" required>
            </label>
            <label>
                Email Address
                <input type="email" name="email" value="<?= h(old('email')) ?>" required>
            </label>
            <label>
                Home Airport
                <input type="text" name="home_airport" value="<?= h(old('home_airport')) ?>" placeholder="Optional">
            </label>
            <label>
                Preferred Currency
                <select name="preferred_currency">
                    <option value="USD">USD</option>
                    <option value="EUR">EUR</option>
                    <option value="GBP">GBP</option>
                    <option value="INR">INR</option>
                    <option value="JPY">JPY</option>
                    <option value="AUD">AUD</option>
                </select>
            </label>
            <label>
                Password
                <input type="password" name="password" minlength="6" required>
            </label>
            <label>
                Confirm Password
                <input type="password" name="confirm_password" minlength="6" required>
            </label>
            <button class="button-primary" type="submit">Create Account</button>
            <p class="auth-meta">Already registered? <a href="<?= h(url_for('/login.php')) ?>">Sign in here</a>.</p>
        </form>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
