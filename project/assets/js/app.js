const navToggle = document.querySelector("[data-nav-toggle]");
const navMenu = document.querySelector("[data-nav-menu]");

if (navToggle && navMenu) {
    navToggle.addEventListener("click", () => {
        navMenu.classList.toggle("is-open");
    });
}

document.querySelectorAll("[data-validate-form]").forEach((form) => {
    form.addEventListener("submit", (event) => {
        const requiredFields = form.querySelectorAll("[required]");
        let firstInvalid = null;

        requiredFields.forEach((field) => {
            const value = field.value.trim();
            const tooShort = field.hasAttribute("minlength") && value.length < Number(field.getAttribute("minlength"));

            if (!value || tooShort) {
                field.style.borderColor = "#9b4439";
                firstInvalid ??= field;
                return;
            }

            field.style.borderColor = "";
        });

        const password = form.querySelector('input[name="password"]');
        const confirmPassword = form.querySelector('input[name="confirm_password"]');

        if (password && confirmPassword && password.value !== confirmPassword.value) {
            event.preventDefault();
            confirmPassword.style.borderColor = "#9b4439";
            confirmPassword.focus();
            alert("Passwords do not match.");
            return;
        }

        if (firstInvalid) {
            event.preventDefault();
            firstInvalid.focus();
            alert("Please complete the required fields before submitting.");
        }
    });
});

document.querySelectorAll("[data-budget-panel]").forEach((panel) => {
    const filter = panel.querySelector("[data-budget-filter]");
    const rows = Array.from(panel.querySelectorAll("[data-budget-rows] .budget-row"));
    const totalOutput = panel.querySelector("[data-budget-total]");

    const updateBudgetView = () => {
        const selected = filter ? filter.value : "all";
        let total = 0;
        let prefix = "";

        rows.forEach((row) => {
            const matches = selected === "all" || row.dataset.category === selected;
            row.dataset.hidden = matches ? "false" : "true";

            if (matches) {
                total += Number(row.dataset.amount || 0);
                if (!prefix) {
                    const strong = row.querySelector("strong");
                    prefix = strong ? strong.textContent.trim().replace(/[0-9.,\-\s]/g, "") : "";
                }
            }
        });

        if (totalOutput) {
            const currency = totalOutput.textContent.trim().replace(/[0-9.,\-\s]/g, "") || prefix || "$";
            totalOutput.textContent = `${currency}${total.toFixed(2)}`;
        }
    };

    filter?.addEventListener("change", updateBudgetView);
    updateBudgetView();
});

document.querySelectorAll("[data-packing-board]").forEach((board) => {
    const rows = Array.from(board.querySelectorAll("[data-pack-items] .pack-row"));
    const progress = board.querySelector("[data-pack-progress]");

    const updatePackingProgress = () => {
        if (!progress) {
            return;
        }

        if (!rows.length) {
            progress.textContent = "0% packed";
            return;
        }

        const packedCount = rows.filter((row) => row.dataset.packed === "1").length;
        const percentage = Math.round((packedCount / rows.length) * 100);
        progress.textContent = `${percentage}% packed`;
    };

    updatePackingProgress();
});
