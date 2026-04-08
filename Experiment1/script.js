const display = document.getElementById("display");

function appendValue(value) {
    display.value += value;
}

function clearDisplay() {
    display.value = "";
}

function deleteLast() {
    display.value = display.value.slice(0, -1);
}

function formatExpression(expression) {
    return expression
        .replace(/pi/g, "Math.PI")
        .replace(/sqrt\(/g, "Math.sqrt(")
        .replace(/log\(/g, "Math.log10(")
        .replace(/ln\(/g, "Math.log(")
        .replace(/sin\(/g, "Math.sin(")
        .replace(/cos\(/g, "Math.cos(")
        .replace(/tan\(/g, "Math.tan(")
        .replace(/\^/g, "**");
}

function calculate() {
    if (!display.value.trim()) {
        return;
    }

    try {
        const result = Function(`"use strict"; return (${formatExpression(display.value)});`)();
        display.value = Number.isFinite(result) ? result : "Error";
    } catch (error) {
        display.value = "Error";
    }
}
