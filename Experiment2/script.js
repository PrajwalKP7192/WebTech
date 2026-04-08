function showMessage(elementId, message, isSuccess) {
    const messageBox = document.getElementById(elementId);

    if (!messageBox) {
        return;
    }

    messageBox.textContent = message;
    messageBox.style.color = isSuccess ? "green" : "red";
}

const registerForm = document.getElementById("registerForm");
const loginForm = document.getElementById("loginForm");

if (registerForm) {
    registerForm.addEventListener("submit", function (event) {
        event.preventDefault();

        const fullName = document.getElementById("fullName").value.trim();
        const email = document.getElementById("email").value.trim();
        const phone = document.getElementById("phone").value.trim();
        const password = document.getElementById("password").value;
        const confirmPassword = document.getElementById("confirmPassword").value;

        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        const phonePattern = /^\d{10}$/;

        if (fullName.length < 3) {
            showMessage("registerMessage", "Full name must contain at least 3 characters.", false);
            return;
        }

        if (!emailPattern.test(email)) {
            showMessage("registerMessage", "Please enter a valid email address.", false);
            return;
        }

        if (!phonePattern.test(phone)) {
            showMessage("registerMessage", "Phone number must be exactly 10 digits.", false);
            return;
        }

        if (password.length < 6) {
            showMessage("registerMessage", "Password must be at least 6 characters long.", false);
            return;
        }

        if (password !== confirmPassword) {
            showMessage("registerMessage", "Password and confirm password do not match.", false);
            return;
        }

        showMessage("registerMessage", "Registration completed successfully.", true);
        registerForm.reset();
    });
}

if (loginForm) {
    loginForm.addEventListener("submit", function (event) {
        event.preventDefault();

        const email = document.getElementById("loginEmail").value.trim();
        const password = document.getElementById("loginPassword").value;
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (!emailPattern.test(email)) {
            showMessage("loginMessage", "Enter a valid registered email address.", false);
            return;
        }

        if (password.length < 6) {
            showMessage("loginMessage", "Password must be at least 6 characters long.", false);
            return;
        }

        showMessage("loginMessage", "Login successful.", true);
        loginForm.reset();
    });
}
