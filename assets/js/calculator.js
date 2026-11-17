// assets/js/calculator.js
// Safe State-Machine Logic for QuizMaster Docked Calculator

document.addEventListener('DOMContentLoaded', () => {
    const screen = document.getElementById('calc-screen');
    const historyDisplay = document.getElementById('calc-history');
    const widget = document.querySelector('.calc-widget');

    if (!screen || !historyDisplay || !widget) {
        return;
    }

    let currentInput = '0';
    let previousInput = '';
    let operator = null;
    let shouldResetScreen = false;

    const operatorSymbols = {
        '+': '+',
        '-': '−',
        '*': '×',
        '/': '÷'
    };

    function updateScreen() {
        screen.value = currentInput;
    }

    function appendNumber(number) {
        if (currentInput === 'Error' || currentInput === 'Cannot divide by 0') {
            currentInput = '0';
        }

        if (shouldResetScreen) {
            currentInput = number === '.' ? '0.' : number;
            shouldResetScreen = false;
            updateScreen();
            return;
        }

        if (number === '.' && currentInput.includes('.')) {
            return;
        }

        if (currentInput === '0' && number !== '.') {
            currentInput = number;
        } else if (currentInput.length < 15) {
            currentInput += number;
        }

        updateScreen();
    }

    function chooseOperator(selectedOp) {
        if (currentInput === 'Error' || currentInput === 'Cannot divide by 0') {
            clearAll();
            return;
        }

        if (operator !== null && !shouldResetScreen) {
            evaluate();
        }

        previousInput = currentInput;
        operator = selectedOp;
        shouldResetScreen = true;
        historyDisplay.textContent = `${previousInput} ${operatorSymbols[operator]}`;
    }

    function evaluate() {
        if (operator === null || shouldResetScreen) {
            return;
        }

        const prev = parseFloat(previousInput);
        const current = parseFloat(currentInput);

        if (isNaN(prev) || isNaN(current)) {
            return;
        }

        if (operator === '/' && current === 0) {
            currentInput = 'Cannot divide by 0';
            historyDisplay.innerHTML = '&nbsp;';
            operator = null;
            shouldResetScreen = true;
            updateScreen();
            return;
        }

        let result = 0;
        switch (operator) {
            case '+':
                result = prev + current;
                break;
            case '-':
                result = prev - current;
                break;
            case '*':
                result = prev * current;
                break;
            case '/':
                result = prev / current;
                break;
        }

        result = Math.round(result * 1e10) / 1e10;
        historyDisplay.textContent = `${previousInput} ${operatorSymbols[operator]} ${currentInput} =`;
        currentInput = String(result);
        operator = null;
        shouldResetScreen = true;
        updateScreen();
    }

    function applyScientific(type) {
        const value = parseFloat(currentInput);
        if (isNaN(value)) {
            return;
        }

        let result = 0;
        switch (type) {
            case 'sqrt':
                if (value < 0) {
                    currentInput = 'Error';
                    historyDisplay.textContent = 'Invalid input';
                    shouldResetScreen = true;
                    updateScreen();
                    return;
                }
                historyDisplay.textContent = `√(${value})`;
                result = Math.sqrt(value);
                break;
            case 'square':
                historyDisplay.textContent = `sqr(${value})`;
                result = value * value;
                break;
            case 'percent':
                historyDisplay.textContent = `${value}%`;
                result = value / 100;
                break;
        }

        result = Math.round(result * 1e10) / 1e10;
        currentInput = String(result);
        shouldResetScreen = true;
        updateScreen();
    }

    function deleteLast() {
        if (shouldResetScreen || currentInput === 'Error' || currentInput === 'Cannot divide by 0') {
            currentInput = '0';
            shouldResetScreen = false;
            updateScreen();
            return;
        }

        currentInput = currentInput.length > 1 ? currentInput.slice(0, -1) : '0';
        updateScreen();
    }

    function clearAll() {
        currentInput = '0';
        previousInput = '';
        operator = null;
        shouldResetScreen = false;
        historyDisplay.innerHTML = '&nbsp;';
        updateScreen();
    }

    // Button Click Delegation
    widget.addEventListener('click', (event) => {
        const btn = event.target.closest('.calc-btn');
        if (!btn) {
            return;
        }

        if (btn.dataset.num !== undefined) {
            appendNumber(btn.dataset.num);
        } else if (btn.dataset.op !== undefined) {
            chooseOperator(btn.dataset.op);
        } else if (btn.dataset.sci !== undefined) {
            applyScientific(btn.dataset.sci);
        } else if (btn.dataset.action === 'equals') {
            evaluate();
        } else if (btn.dataset.action === 'delete') {
            deleteLast();
        } else if (btn.dataset.action === 'clear') {
            clearAll();
        }
    });
});