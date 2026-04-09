<?php

declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

if (is_post()) {
    verify_csrf();
    remember_old($_POST);

    $name = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $subject === '' || mb_strlen($message) < 20) {
        set_flash('error', 'Please fill every field and write a message with at least 20 characters.');
        redirect('/contact.php');
    }

    if (!db()) {
        set_flash('error', 'Database connection is required before contact form submissions can be stored.');
        redirect('/contact.php');
    }

    $insert = db()->prepare(
        'INSERT INTO contact_messages (name, email, subject, message)
         VALUES (:name, :email, :subject, :message)'
    );

    $insert->execute([
        'name' => $name,
        'email' => $email,
        'subject' => $subject,
        'message' => $message,
    ]);

    clear_old();
    set_flash('success', 'Your message has been sent successfully.');
    redirect('/contact.php');
}

$pageTitle = 'Contact';
$activePage = 'contact';

require __DIR__ . '/includes/header.php';
?>

<section class="page-banner">
    <div class="container narrow">
        <span class="eyebrow">Contact us</span>
        <h1>Have a feature idea, demo request, or travel planning question?</h1>
        <p>Use this form to submit feedback through a PHP + MySQL workflow. It also doubles as the project’s server-side contact handling feature.</p>
    </div>
</section>

<section class="section">
    <div class="container contact-grid">
        <form class="panel" method="post" data-validate-form>
            <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
            <label>
                Name
                <input type="text" name="name" value="<?= h(old('name', $currentUser['full_name'] ?? '')) ?>" required>
            </label>
            <label>
                Email
                <input type="email" name="email" value="<?= h(old('email', $currentUser['email'] ?? '')) ?>" required>
            </label>
            <label>
                Subject
                <input type="text" name="subject" value="<?= h(old('subject')) ?>" required>
            </label>
            <label>
                Message
                <textarea name="message" rows="6" required><?= h(old('message')) ?></textarea>
            </label>
            <button class="button-primary" type="submit">Send Message</button>
        </form>

        <div class="panel">
            <h3>Project highlights</h3>
            <ul class="plain-list">
                <li>Multi-page navigation with consistent styling</li>
                <li>JavaScript validation and real-time dashboard updates</li>
                <li>PHP form handling with flash messages</li>
                <li>MySQL-backed data storage for all major modules</li>
            </ul>
            <div class="info-stack">
                <p><strong>Email:</strong> hello@triptally.demo</p>
                <p><strong>Response time:</strong> Within one business day</p>
                <p><strong>Best for:</strong> Feature feedback, travel planners, and capstone demos</p>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
