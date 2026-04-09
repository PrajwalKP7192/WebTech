<?php
$contactErrors = [];
$registerErrors = [];
$loginErrors = [];

$contactSuccess = "";
$registerSuccess = "";
$loginSuccess = "";

$contactData = [
    "name" => "",
    "email" => "",
    "message" => "",
];

$registerData = [
    "full_name" => "",
    "email" => "",
    "phone" => "",
];

$loginData = [
    "email" => "",
];

function clean_input(string $value): string
{
    return htmlspecialchars(trim($value), ENT_QUOTES, "UTF-8");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $formType = $_POST["form_type"] ?? "";

    if ($formType === "exercise1_contact") {
        $name = trim($_POST["name"] ?? "");
        $email = trim($_POST["email"] ?? "");
        $message = trim($_POST["message"] ?? "");

        $contactData["name"] = clean_input($name);
        $contactData["email"] = clean_input($email);
        $contactData["message"] = clean_input($message);

        if (strlen($name) < 3) {
            $contactErrors[] = "Name must contain at least 3 characters.";
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $contactErrors[] = "Enter a valid email address.";
        }

        if (strlen($message) < 10) {
            $contactErrors[] = "Message must contain at least 10 characters.";
        }

        if (!$contactErrors) {
            $contactSuccess = "Exercise 1 form submitted successfully.";
        }
    }

    if ($formType === "exercise2_register") {
        $fullName = trim($_POST["full_name"] ?? "");
        $email = trim($_POST["register_email"] ?? "");
        $phone = trim($_POST["phone"] ?? "");
        $password = $_POST["password"] ?? "";
        $confirmPassword = $_POST["confirm_password"] ?? "";

        $registerData["full_name"] = clean_input($fullName);
        $registerData["email"] = clean_input($email);
        $registerData["phone"] = clean_input($phone);

        if (strlen($fullName) < 3) {
            $registerErrors[] = "Full name must contain at least 3 characters.";
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $registerErrors[] = "Enter a valid email address.";
        }

        if (!preg_match("/^\d{10}$/", $phone)) {
            $registerErrors[] = "Phone number must contain exactly 10 digits.";
        }

        if (strlen($password) < 6) {
            $registerErrors[] = "Password must contain at least 6 characters.";
        }

        if ($password !== $confirmPassword) {
            $registerErrors[] = "Password and confirm password must match.";
        }

        if (!$registerErrors) {
            $registerSuccess = "Exercise 2 registration completed successfully.";
        }
    }

    if ($formType === "exercise2_login") {
        $email = trim($_POST["login_email"] ?? "");
        $password = $_POST["login_password"] ?? "";

        $loginData["email"] = clean_input($email);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $loginErrors[] = "Enter a valid registered email address.";
        }

        if (strlen($password) < 6) {
            $loginErrors[] = "Password must contain at least 6 characters.";
        }

        if (!$loginErrors) {
            $loginSuccess = "Exercise 2 login form submitted successfully.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Experiment 8 | PHP Form Handling</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <main class="page">
        <section class="hero">
            <span class="tag">Experiment 8</span>
            <h1>Form Handling and Validation</h1>
            <p class="subtitle">
                This page handles the Exercise 1 contact form and the Exercise 2 registration
                and login forms using PHP with server-side validation.
            </p>
        </section>

        <section class="layout">
            <div class="stack">
                <section class="panel">
                    <h2>Exercise 1 Contact Form</h2>
                    <p class="panel-copy">Submit basic personal contact details and a message.</p>

                    <?php if ($contactErrors): ?>
                        <div class="alert error">
                            <?php foreach ($contactErrors as $error): ?>
                                <p><?= $error ?></p>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($contactSuccess): ?>
                        <div class="alert success">
                            <p><?= $contactSuccess ?></p>
                        </div>
                    <?php endif; ?>

                    <form method="post" class="form-grid">
                        <input type="hidden" name="form_type" value="exercise1_contact">

                        <div class="field">
                            <label for="name">Name</label>
                            <input type="text" id="name" name="name" value="<?= $contactData["name"] ?>">
                        </div>

                        <div class="field">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?= $contactData["email"] ?>">
                        </div>

                        <div class="field full">
                            <label for="message">Message</label>
                            <textarea id="message" name="message" rows="5"><?= $contactData["message"] ?></textarea>
                        </div>

                        <div class="field full">
                            <button class="primary" type="submit">Submit Contact Form</button>
                        </div>
                    </form>
                </section>

                <section class="panel">
                    <h2>Exercise 2 Registration Form</h2>
                    <p class="panel-copy">Create a customer account for the ProSport shopping pages.</p>

                    <?php if ($registerErrors): ?>
                        <div class="alert error">
                            <?php foreach ($registerErrors as $error): ?>
                                <p><?= $error ?></p>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($registerSuccess): ?>
                        <div class="alert success">
                            <p><?= $registerSuccess ?></p>
                        </div>
                    <?php endif; ?>

                    <form method="post" class="form-grid">
                        <input type="hidden" name="form_type" value="exercise2_register">

                        <div class="field">
                            <label for="full_name">Full Name</label>
                            <input type="text" id="full_name" name="full_name" value="<?= $registerData["full_name"] ?>">
                        </div>

                        <div class="field">
                            <label for="register_email">Email</label>
                            <input type="email" id="register_email" name="register_email" value="<?= $registerData["email"] ?>">
                        </div>

                        <div class="field">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" value="<?= $registerData["phone"] ?>">
                        </div>

                        <div class="field">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password">
                        </div>

                        <div class="field full">
                            <label for="confirm_password">Confirm Password</label>
                            <input type="password" id="confirm_password" name="confirm_password">
                        </div>

                        <div class="field full">
                            <button class="primary" type="submit">Submit Registration</button>
                        </div>
                    </form>
                </section>

                <section class="panel">
                    <h2>Exercise 2 Login Form</h2>
                    <p class="panel-copy">Validate login details before allowing access.</p>

                    <?php if ($loginErrors): ?>
                        <div class="alert error">
                            <?php foreach ($loginErrors as $error): ?>
                                <p><?= $error ?></p>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($loginSuccess): ?>
                        <div class="alert success">
                            <p><?= $loginSuccess ?></p>
                        </div>
                    <?php endif; ?>

                    <form method="post" class="form-grid">
                        <input type="hidden" name="form_type" value="exercise2_login">

                        <div class="field">
                            <label for="login_email">Email</label>
                            <input type="email" id="login_email" name="login_email" value="<?= $loginData["email"] ?>">
                        </div>

                        <div class="field">
                            <label for="login_password">Password</label>
                            <input type="password" id="login_password" name="login_password">
                        </div>

                        <div class="field full">
                            <button class="primary" type="submit">Submit Login</button>
                        </div>
                    </form>
                </section>
            </div>

            <aside class="stack">
                <section class="info-card">
                    <h3>What This Experiment Covers</h3>
                    <ul>
                        <li>Server-side handling of submitted form data</li>
                        <li>Validation for required fields and proper input formats</li>
                        <li>Success and error feedback after submission</li>
                        <li>Separate processing for Exercise 1 and Exercise 2 forms</li>
                    </ul>
                </section>

                <section class="info-card">
                    <h3>Submission Summary</h3>
                    <div class="summary">
                        <div class="summary-item">
                            <strong>Exercise 1 Contact</strong>
                            <?= $contactSuccess ? "Received from " . $contactData["name"] : "Waiting for submission" ?>
                        </div>
                        <div class="summary-item">
                            <strong>Exercise 2 Registration</strong>
                            <?= $registerSuccess ? "Registered: " . $registerData["full_name"] : "Waiting for submission" ?>
                        </div>
                        <div class="summary-item">
                            <strong>Exercise 2 Login</strong>
                            <?= $loginSuccess ? "Login checked for " . $loginData["email"] : "Waiting for submission" ?>
                        </div>
                    </div>
                </section>
            </aside>
        </section>
    </main>
</body>
</html>
