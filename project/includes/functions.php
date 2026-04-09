<?php

declare(strict_types=1);

final class DatabaseSessionHandler implements SessionHandlerInterface
{
    public function __construct(private PDO $pdo)
    {
    }

    public function open(string $path, string $name): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read(string $id): string|false
    {
        $statement = $this->pdo->prepare(
            'SELECT session_data
             FROM app_sessions
             WHERE session_id = :session_id
             AND last_activity >= :last_activity
             LIMIT 1'
        );
        $statement->execute([
            'session_id' => $id,
            'last_activity' => time() - (int) ini_get('session.gc_maxlifetime'),
        ]);

        $session = $statement->fetch();

        return $session['session_data'] ?? '';
    }

    public function write(string $id, string $data): bool
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO app_sessions (session_id, session_data, last_activity)
             VALUES (:session_id, :session_data, :last_activity)
             ON DUPLICATE KEY UPDATE
                session_data = VALUES(session_data),
                last_activity = VALUES(last_activity)'
        );

        return $statement->execute([
            'session_id' => $id,
            'session_data' => $data,
            'last_activity' => time(),
        ]);
    }

    public function destroy(string $id): bool
    {
        $statement = $this->pdo->prepare('DELETE FROM app_sessions WHERE session_id = :session_id');

        return $statement->execute(['session_id' => $id]);
    }

    public function gc(int $max_lifetime): int|false
    {
        $statement = $this->pdo->prepare('DELETE FROM app_sessions WHERE last_activity < :last_activity');
        $statement->execute(['last_activity' => time() - $max_lifetime]);

        return $statement->rowCount();
    }
}

function connect_database(array $config): ?PDO
{
    try {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $config['host'],
            $config['port'],
            $config['name'],
            $config['charset']
        );

        return new PDO($dsn, $config['user'], $config['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } catch (PDOException $exception) {
        $GLOBALS['db_error_message'] = $exception->getMessage();

        return null;
    }
}

function db(): ?PDO
{
    return $GLOBALS['pdo'] ?? null;
}

function db_error_message(): ?string
{
    return $GLOBALS['db_error_message'] ?? null;
}

function app_base_path(): string
{
    $configured = getenv('APP_BASE_PATH');

    if ($configured !== false && $configured !== '') {
        return rtrim($configured, '/');
    }

    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';

    if ($scriptName === '' || str_ends_with($scriptName, '/api/index.php')) {
        return '';
    }

    $basePath = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');

    return $basePath === '/' ? '' : $basePath;
}

function url_for(string $path = '/'): string
{
    if (preg_match('/^https?:\/\//i', $path) === 1) {
        return $path;
    }

    $normalizedPath = '/' . ltrim($path, '/');
    $basePath = app_base_path();

    return ($basePath === '' ? '' : $basePath) . $normalizedPath;
}

function initialize_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $secureCookie = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $secureCookie,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    $pdo = db();

    if ($pdo) {
        session_set_save_handler(new DatabaseSessionHandler($pdo), true);
    }

    session_start();
}

function h(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): never
{
    header('Location: ' . url_for($path));
    exit;
}

function current_internal_url(): string
{
    $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
    $path = parse_url($requestUri, PHP_URL_PATH) ?: '/';
    $query = parse_url($requestUri, PHP_URL_QUERY);
    $basePath = app_base_path();

    if ($basePath !== '') {
        if ($path === $basePath) {
            $path = '/';
        } elseif (str_starts_with($path, $basePath . '/')) {
            $path = substr($path, strlen($basePath));
        }
    }

    if ($path === '' || $path[0] !== '/') {
        $path = '/';
    }

    return $path . ($query ? '?' . $query : '');
}

function is_post(): bool
{
    return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = compact('type', 'message');
}

function get_flash(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);

    return $flash;
}

function old(string $key, string $fallback = ''): string
{
    return $_SESSION['old'][$key] ?? $fallback;
}

function remember_old(array $values): void
{
    $_SESSION['old'] = $values;
}

function clear_old(): void
{
    unset($_SESSION['old']);
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(24));
    }

    return $_SESSION['csrf_token'];
}

function verify_csrf(): void
{
    $token = $_POST['csrf_token'] ?? '';

    if (!hash_equals(csrf_token(), $token)) {
        set_flash('error', 'The form expired. Please try again.');
        redirect($_SERVER['PHP_SELF']);
    }
}

function login_user(array $user): void
{
    $_SESSION['user_id'] = (int) $user['id'];
    setcookie('remembered_email', $user['email'], time() + (86400 * 30), '/');
}

function logout_user(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }

    session_destroy();
}

function current_user(): ?array
{
    static $user = null;
    static $loaded = false;

    if ($loaded) {
        return $user;
    }

    $loaded = true;
    $pdo = db();
    $userId = $_SESSION['user_id'] ?? null;

    if (!$pdo || !$userId) {
        return null;
    }

    $statement = $pdo->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
    $statement->execute(['id' => $userId]);
    $user = $statement->fetch() ?: null;

    return $user;
}

function is_logged_in(): bool
{
    return current_user() !== null;
}

function require_login(): void
{
    if (!is_logged_in()) {
        $_SESSION['intended_url'] = current_internal_url();
        set_flash('error', 'Please sign in to manage your trips.');
        redirect('/login.php');
    }
}

function take_intended_url(string $fallback = '/planner.php'): string
{
    $url = $_SESSION['intended_url'] ?? $fallback;
    unset($_SESSION['intended_url']);

    if (!is_string($url) || $url === '' || $url[0] !== '/') {
        return $fallback;
    }

    return $url;
}

function currency_code(): string
{
    return $_COOKIE['preferred_currency'] ?? (current_user()['preferred_currency'] ?? 'USD');
}

function currency_symbol(?string $code = null): string
{
    return [
        'USD' => '$',
        'EUR' => '€',
        'GBP' => '£',
        'INR' => '₹',
        'JPY' => '¥',
        'AUD' => 'A$',
    ][$code ?? currency_code()] ?? '$';
}

function flash_class(string $type): string
{
    return [
        'success' => 'flash-success',
        'error' => 'flash-error',
        'info' => 'flash-info',
    ][$type] ?? 'flash-info';
}

function posted_amount(string $field): float
{
    return round((float) ($_POST[$field] ?? 0), 2);
}

function selected_trip_id(): ?int
{
    return isset($_GET['trip']) ? (int) $_GET['trip'] : null;
}

function fetch_trip_for_user(int $tripId, int $userId): ?array
{
    $pdo = db();

    if (!$pdo) {
        return null;
    }

    $statement = $pdo->prepare('SELECT * FROM trips WHERE id = :id AND user_id = :user_id LIMIT 1');
    $statement->execute([
        'id' => $tripId,
        'user_id' => $userId,
    ]);

    return $statement->fetch() ?: null;
}

function trip_duration(array $trip): int
{
    $start = strtotime((string) $trip['start_date']);
    $end = strtotime((string) $trip['end_date']);

    if (!$start || !$end || $end < $start) {
        return 1;
    }

    return (int) floor(($end - $start) / 86400) + 1;
}

function trip_status_label(array $trip): string
{
    $today = strtotime(date('Y-m-d'));
    $start = strtotime((string) $trip['start_date']);
    $end = strtotime((string) $trip['end_date']);

    if ($today < $start) {
        return 'Upcoming';
    }

    if ($today > $end) {
        return 'Completed';
    }

    return 'In Progress';
}
