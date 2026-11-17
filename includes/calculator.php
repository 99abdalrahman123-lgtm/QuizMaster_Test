<?php
// includes/calculator.php
// Docked Interactive Scientific & Standard Calculator Component
?>
<aside class="calc-widget" aria-label="Scientific Calculator">
    <div class="calc-header">
        <span class="calc-title">Exam Calculator</span>
        <span class="calc-badge">Standard / Sci</span>
    </div>

    <div class="calc-display">
        <div id="calc-history" class="calc-history">&nbsp;</div>
        <input type="text" id="calc-screen" class="calc-screen" value="0" readonly aria-label="Calculator display">
    </div>

    <div class="calc-grid">
        <!-- Row 1: Scientific & Clear -->
        <button type="button" class="calc-btn btn-sci" data-sci="sqrt" title="Square Root">&radic;x</button>
        <button type="button" class="calc-btn btn-sci" data-sci="square" title="Square">x&sup2;</button>
        <button type="button" class="calc-btn btn-sci" data-sci="percent" title="Percentage">%</button>
        <button type="button" class="calc-btn btn-danger" data-action="clear" title="All Clear">AC</button>

        <!-- Row 2 -->
        <button type="button" class="calc-btn" data-num="7">7</button>
        <button type="button" class="calc-btn" data-num="8">8</button>
        <button type="button" class="calc-btn" data-num="9">9</button>
        <button type="button" class="calc-btn btn-op" data-op="/">&divide;</button>

        <!-- Row 3 -->
        <button type="button" class="calc-btn" data-num="4">4</button>
        <button type="button" class="calc-btn" data-num="5">5</button>
        <button type="button" class="calc-btn" data-num="6">6</button>
        <button type="button" class="calc-btn btn-op" data-op="*">&times;</button>

        <!-- Row 4 -->
        <button type="button" class="calc-btn" data-num="1">1</button>
        <button type="button" class="calc-btn" data-num="2">2</button>
        <button type="button" class="calc-btn" data-num="3">3</button>
        <button type="button" class="calc-btn btn-op" data-op="-">&minus;</button>

        <!-- Row 5 -->
        <button type="button" class="calc-btn" data-num="0">0</button>
        <button type="button" class="calc-btn" data-num=".">.</button>
        <button type="button" class="calc-btn btn-util" data-action="delete" title="Backspace">DEL</button>
        <button type="button" class="calc-btn btn-op" data-op="+">+</button>

        <!-- Row 6: Equals -->
        <button type="button" class="calc-btn btn-equals" data-action="equals">=</button>
    </div>
</aside>