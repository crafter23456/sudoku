      <div class="gameContainer">
        <div id="pauseOverlay">
          <svg class="resumeIconBig" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 60 60"><g fill="none" fill-rule="evenodd"><circle cx="30" cy="30" r="30" fill="#0072E3"></circle><path fill="#FFF" d="M39.12 31.98l-12.56 8.64a2.4 2.4 0 01-3.76-1.98V21.36a2.4 2.4 0 013.76-1.97l12.56 8.63a2.4 2.4 0 010 3.96z"></path></g></svg>
        </div>
        <canvas id="sudoku-canvas" width="900px" height="900px"></canvas>
      </div>
      <div id="panel">
        <div class="timerBox">
          <div class="timer" id="timer">Time: 0:00</div>
          <button id="pauseButton" onclick="pauseTimer()">
            <svg id="pauseIcon" xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 320 512"><path d="M48 64C21.5 64 0 85.5 0 112V400c0 26.5 21.5 48 48 48H80c26.5 0 48-21.5 48-48V112c0-26.5-21.5-48-48-48H48zm192 0c-26.5 0-48 21.5-48 48V400c0 26.5 21.5 48 48 48h32c26.5 0 48-21.5 48-48V112c0-26.5-21.5-48-48-48H240z"/></svg>
            <svg id="resumeIcon" xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 384 512"><path d="M73 39c-14.8-9.1-33.4-9.4-48.5-.9S0 62.6 0 80V432c0 17.4 9.4 33.4 24.5 41.9s33.7 8.1 48.5-.9L361 297c14.3-8.7 23-24.2 23-41s-8.7-32.2-23-41L73 39z"/></svg>
          </button>
        </div>
        <div class="control-panel">
          <button onclick="window.location.reload()">Neues Spiel</button>
          <button onclick="checkIfSolved()">Lösung zeigen</button>
          <button onclick="checkIfValid()">Prüfen</button>
          <button class="number" id="clear" data-value=" ">Clear</button>
        </div>
        <div class="number-selector">
          <div class="number" data-value="1">1</div>
          <div class="number" data-value="2">2</div>
          <div class="number" data-value="3">3</div>
          <div class="number" data-value="4">4</div>
          <div class="number" data-value="5">5</div>
          <div class="number" data-value="6">6</div>
          <div class="number" data-value="7">7</div>
          <div class="number" data-value="8">8</div>
          <div class="number" data-value="9">9</div>
        </div>
      </div>
<script>
// Konstanten
const canvas = document.getElementById("sudoku-canvas");
const numberButtons = document.querySelectorAll('[data-value]');
const clearButton = document.getElementById("clear");
const timerElement = document.getElementById("timer");
const pauseButton = document.getElementById("pauseButton");
const pauseOverlay = document.getElementById("pauseOverlay");
const maxLength = 9;
const sudokuFont = "50px Arial";
const newNumbersColor = "#0b79e4";
const matchingNumbersColor = "#c3d7ea";
const rowColumnBoxColor = "#e2ebf3";
const markedFieldColor = "#bbdefb";
const invalidColor = "#f7cfd6";
const lightGrayColor = "#d1d1d1";
const bgColor = "#ffffff";
const lineColor = "#000000";
const sudokuSolvedMessage = "Das Sudoku wurde gelöst!";
const sudokuValidMessage = "Das Sudoku ist gültig!";
const sudokuNotValidMessage = "Das Sudoku ist ungültig!";

// Variablen
let startTime;
let timerInterval;
let timerStopped = false;
let paused = false;
let pausedTime = 0;
let solved = false;
var getSolved = false;
var ctx = canvas.getContext("2d");
var canvasStyle = window.getComputedStyle(canvas);
var canvasWidth = parseInt(canvasStyle.getPropertyValue("width"), 10);
var calcCellSize = canvasWidth / maxLength;
var cellSize = canvas.width / maxLength;
var sudokuData = [];
var sudokuDataRaw = [];
var sudokuDataSplit = [];
var markedField = {
    row: 0,
    column: 0
};
var markedField2 = { row: markedField.row, column: markedField.column };
var timeRequest = new XMLHttpRequest();

ctx.fillStyle = bgColor;
ctx.fillRect(0, 0, canvas.width, canvas.height);
resetLines();

window.addEventListener('resize', handleResize);

function handleResize() {
    canvasStyle = window.getComputedStyle(canvas);
    canvasWidth = parseInt(canvasStyle.getPropertyValue("width"), 10);
    calcCellSize = canvasWidth / maxLength;
}

function iterateCells(callback) {
    for (let i = 0; i < maxLength; i++) {
        for (let j = 0; j < maxLength; j++) {
            callback(i, j);
        }
    }
}

function iterateCellsInBox(row, column, callback) {
    let boxRow = Math.floor(row / 3) * 3; // Erste Zeile des Kastens
    let boxColumn = Math.floor(column / 3) * 3; // Erste Spalte des Kastens
    for (let r = boxRow; r < boxRow + 3; r++) {
        for (let c = boxColumn; c < boxColumn + 3; c++) {
            callback(r, c);
        }
    }
}

function resetColors() {
    iterateCells(function(i, j) {
        markField(i, j, bgColor); // Setze die Hintergrundfarbe auf die normale Farbe
    });
}

function resetLines() {
    for (var i = 0; i <= maxLength; i++) {
        var y = x = i * cellSize;
        ctx.beginPath();
        ctx.moveTo(0, y);
        ctx.lineTo(canvas.width, y);
        ctx.stroke();

        ctx.beginPath();
        ctx.moveTo(x, 0);
        ctx.lineTo(x, canvas.height);
        ctx.stroke();
        ctx.lineWidth = (i % 3 === 2) ? 3 : 1;
    }
    updateButtonColors();
}

// Zahlen auf dem Sudoku Raster zeichnen
function drawNumbers() {
    ctx.font = sudokuFont;
    ctx.fillStyle = lineColor;
    // Gehe durch jede Zelle des Sudoku Rasters
    iterateCells(function(i, j) {
        // Hole den Index der aktuellen Zelle im sudokuData Array
        var index = i * maxLength + j;
        // Hole den Wert der aktuellen Zelle im sudokuData Array
        var value = sudokuData[index];
        // Wenn der Wert nicht leer ist, zeichne ihn in der Mitte der Zelle
        if (value !== " ") {
            var x = j * cellSize + cellSize / 2;
            var y = i * cellSize + cellSize / 2;
            ctx.textAlign = "center";
            ctx.textBaseline = "middle";
            ctx.fillText(value, x, y);
        }
    });
}

// Gesamte Dreierfeld im Sudoku markieren
function markBox(row, column, color) {
    iterateCellsInBox(row, column, function(i, j) {
        markField(i, j, color);
    });
}

function markField(row, column, color) {
    // Berechne die Koordinaten des markierten Feldes
    var x = column * cellSize;
    var y = row * cellSize;
    markedField = {
        row: row,
        column: column
    };
    markedField2 = { row: markedField.row, column: markedField.column };

    // Setze die Hintergrundfarbe des markierten Feldes
    ctx.fillStyle = color;
    ctx.fillRect(x, y, cellSize, cellSize);

    // Zeichne die Linien um das markierte Feld herum, um den Rahmen beizubehalten
    // ctx.strokeRect(x, y, cellSize, cellSize);

    var index = row * maxLength + column;
    var value = sudokuData[index];
    if (sudokuDataRaw[index] !== value.toString()) {
        ctx.fillStyle = newNumbersColor; // Blaue Farbe für Zahlen, die nicht in sudokuDataRaw sind
    } else {
        ctx.fillStyle = lineColor; // Standardfarbe für Zahlen in sudokuDataRaw
    }
    var x = column * cellSize + cellSize / 2;
    var y = row * cellSize + cellSize / 2;
    ctx.textAlign = "center";
    ctx.textBaseline = "middle";
    ctx.fillText(value, x, y);
}

// Eine Funktion, um die gesamte Zeile und Spalte im Sudoku zu markieren
function markRowAndColumn(row, column, color) {
    iterateCells(function(i, j) {
        markField(i, column, color);
        markField(row, j, color);
    });
}

function updateButtonColors() {
    const numberCounts = {};

    iterateCells(function(i, j) {
        const index = i * maxLength + j;
        const value = sudokuData[index];
        if (value !== " ") {
            if (!numberCounts[value]) {
                numberCounts[value] = 1;
            } else {
                numberCounts[value]++;
            }
        }
    });

    for (let i = 0; i < numberButtons.length; i++) {
        const button = numberButtons[i];
        const value = button.getAttribute("data-value");
        if (numberCounts[value] && numberCounts[value] >= 9) {
            button.classList.add("number-blocked");
        } else if (getSolved) {
            button.classList.add("number-blocked");
        } else {
            button.classList.remove("number-blocked");
        }
    }
}

function handleSolve(){
    clearButton.classList.add("number-blocked");
    stopTimer();
    showPopup(sudokuSolvedMessage);
    solved = true;
}

canvas.addEventListener("click", function(event) {
    if (paused) return;
    // Berechne die Position des Klicks relativ zum Canvas
    var rect = canvas.getBoundingClientRect();
    var x = event.clientX - rect.left;
    var y = event.clientY - rect.top;

    // Berechne die Zeilen- und Spaltennummer des Feldes
    var row = Math.floor(y / calcCellSize);
    var column = Math.floor(x / calcCellSize);

    // Rufe eine Funktion auf, um das Feld zu markieren oder andere Aktionen durchzuführen
    console.log("CLICK" + isFixedCell(row, column));
    if (isSudokuSolved()) {
        handleSolve();
        return;
    }
    resetColors();
    markMatchingNumbers(row, column, matchingNumbersColor);
    if (!isFixedCell(row, column)) {
        markBox(row, column, rowColumnBoxColor);
        markRowAndColumn(row, column, rowColumnBoxColor);
        markField(row, column, markedFieldColor);
    }
    resetLines();
});

function markMatchingNumbers(row, column, color) {
    var index = row * maxLength + column;
    var value = sudokuData[index];
    if (value !== " ") {
        iterateCells(function(r, c) {
            if (sudokuData[r * maxLength + c] === value) {
                markField(r, c, color);
            }
        });
    }
}

// Wähle eine Zahl beim Klicken
for (var i = 0; i < numberButtons.length; i++) {
    var numberButton = numberButtons[i];
    numberButton.addEventListener("click", function() {
        row = markedField2.row;
        column = markedField2.column;
        console.log(markedField2, markedField);
        if (markedField2 && !isFixedCell(row, column)) {
            var value = this.dataset.value;
            sudokuData[row * maxLength + column] = " ";
            resetColors();
            markBox(row, column, rowColumnBoxColor);
            markRowAndColumn(row, column, rowColumnBoxColor);
            writeNumber(row, column, value);
            resetLines();
        }
    });
}

// Zahl eintragen
function writeNumber(row, column, value) {
    var x = column * cellSize + cellSize / 2;
    var y = row * cellSize + cellSize / 2;
    var index = row * maxLength + column;
    var value2 = sudokuData[index];
    if (sudokuDataRaw[value2] !== value.toString()) {
        ctx.fillStyle = newNumbersColor; // Blaue Farbe für Zahlen, die nicht in sudokuDataRaw sind
    } else {
        ctx.fillStyle = lineColor; // Standardfarbe für Zahlen in sudokuDataRaw
    }
    ctx.textAlign = "center";
    ctx.textBaseline = "middle";
    ctx.fillText(value, x, y);
    sudokuData[row * maxLength + column] = value.toString();
    markMatchingNumbers(row, column, matchingNumbersColor);
    markField(row, column, markedFieldColor);
    if (isSudokuSolved()) {
        handleSolve();
        return;
    }
}


function isFixedCell(row, column) {
    var index = row * maxLength + column;
    var value = sudokuDataRaw[index];
    return value !== " ";
}

function checkIfValidPos(row, column) {
    var index = row * maxLength + column;
    var value = sudokuData[index];

    // Row & Column check
    iterateCells(function(r, c) {
        if ((r === row || c === column) && sudokuData[r * maxLength + c] === value && (r !== row || c !== column)) {
            return false;
        }
    });

    // Überprüfe den 3x3-Kasten
    iterateCellsInBox(row, column, function(r, c) {
        if (sudokuData[r * maxLength + c] === value && (r !== row || c !== column)) {
            return false;
        }
    });
    return true;
}

// Alle Zahlen im Sudoku  überprüfen und doppelte Zahlen rot markieren
function checkIfValid() {
    const {
        row: tempRow,
        column: tempColumn
    } = markedField2;
    ifValidPopUp = true;

    // Überprüfe jede Zelle im Sudoku
    iterateCells(function(r, c) {
        checkRowAndColumn(r, c);
    });

    function checkRowAndColumn(row, column) {
        var value = sudokuData[row * maxLength + column];
        // Überprüfe die Zeile
        for (var c = 0; c < maxLength; c++) {
            if (c !== column && sudokuData[row * maxLength + c] === value) {
                if (value !== " ") {
                    markField(row, c, invalidColor);
                    ifValidPopUp = false;
                    if (getSolved && !isFixedCell(row, c)) {
                        sudokuData[row * maxLength + c] = " ";
                    }
                }
            }
        }
        // Überprüfe die Spalte
        for (var r = 0; r < maxLength; r++) {
            if (r !== row && sudokuData[r * maxLength + column] === value) {
                if (value !== " ") {
                    markField(r, column, invalidColor);
                    ifValidPopUp = false;
                    if (getSolved && !isFixedCell(r, column)) {
                        sudokuData[r * maxLength + column] = " ";
                    }

                }
            }
        }
    }

    // Überprüfe die kleinen 3x3-Kästen
    for (var boxRow = 0; boxRow < maxLength; boxRow += 3) {
        for (var boxColumn = 0; boxColumn < maxLength; boxColumn += 3) {
            var numbersInBox = new Set();
            iterateCellsInBox(boxRow, boxColumn, function(i, j) {
                var value = sudokuData[i * maxLength + j];
                if (value !== " ") {
                    if (numbersInBox.has(value)) {
                        markFieldsInBox(boxRow, boxColumn, value, invalidColor);
                    } else {
                        numbersInBox.add(value);
                    }
                }
            });
        }
    }

    // Alle Felder im gleichen 3x3-Kasten mit einer bestimmten Zahl markieren
    function markFieldsInBox(boxRow, boxColumn, number, color) {
        iterateCellsInBox(boxRow, boxColumn, function(i, j) {
            if (sudokuData[i * maxLength + j] === number) {
                markField(i, j, color);
                ifValidPopUp = false;
                if (getSolved && !isFixedCell(i, j)) {
                    sudokuData[i * maxLength + j] = " ";
                }
            }
        });
    }

    showPopup(ifValidPopUp ? sudokuValidMessage : sudokuNotValidMessage);

    markedField2 = {
        ...markedField2,
        row: tempRow,
        column: tempColumn
    };
    resetLines();
}

function checkIfSolved() {
    if (getSolved) {
        handleSolve();
        return;
    }
    solveSudoku();
}

function solveSudoku() {
    getSolved = true;
    checkIfValid();
    const {
        row: tempRow,
        column: tempColumn
    } = markedField;

    var emptyCell = findEmptyCell();

    // Wenn keine leere Zelle gefunden wurde, ist das Sudoku gelöst
    if (!emptyCell) {
        handleSolve();
        return;
    }

    var row = emptyCell.row;
    var column = emptyCell.column;

    // Probiere Zahlen von 1 bis maxLength aus, um die leere Zelle zu füllen
    for (var number = 1; number <= maxLength; number++) {
        // Überprüfe, ob die aktuelle Zahl in der aktuellen Zelle gültig ist
        if (isValidNumber(row, column, number)) {
            // Setze die Zahl in die aktuelle Zelle
            sudokuData[row * maxLength + column] = number.toString();

            // Löse das Sudoku rekursiv, indem man zur nächsten leeren Zelle geht
            solveSudoku();

            // Wenn das Sudoku gelöst wurde, beende die Schleife und die Funktion
            if (isSudokuSolved()) {
                handleSolve();
                return;
            }

            // Wenn das Sudoku nicht gelöst wurde, setze die Zelle zurück und probiere die nächste Zahl
            sudokuData[row * maxLength + column] = " ";
        }
    }
    markedField = {
        ...markedField,
        row: tempRow,
        column: tempColumn
    };

}

// Nächste leere Zelle im Sudoku finden
function findEmptyCell() {
    var result = false;
    iterateCells(function(row, column) {
        if (sudokuData[row * maxLength + column] === " " || (!checkIfValidPos(row, column) && !isFixedCell())) {
                result = {row, column};
        }
        return !result;
    });
    return result;
}

// Überprüfen, ob eine Zahl in einer bestimmten Zelle gültig ist
function isValidNumber(row, column, number) {
    // Überprüfe die Zeile und Spalte
    for (var i = 0; i < maxLength; i++) {
        if (
            sudokuData[row * maxLength + i] === number.toString() ||
            sudokuData[i * maxLength + column] === number.toString()
        ) {
            return false;
        }
    }
    // Überprüfe das 3x3-Unterquadrat
    var boxRow = Math.floor(row / 3) * 3;
    var boxColumn = Math.floor(column / 3) * 3;
    for (var i = boxRow; i < boxRow + 3; i++) {
        for (var j = boxColumn; j < boxColumn + 3; j++) {
            if (sudokuData[i * maxLength + j] === number.toString()) {
                return false;
            }
        }
    }

    return true;
}

// Überprüfen, ob das Sudoku gelöst wurde
function isSudokuSolved() {
    for (var i = 0; i < maxLength; i++) {
        for (var j = 0; j < maxLength; j++) {
            if (sudokuData[i * maxLength + j] === " " || (!checkIfValidPos(i, j) && !isFixedCell())) {
                return false;
            }
        }
    }

    resetColors(); // Um das gelöste Sukodu zu laden
    resetLines();
    return true;
}

pauseOverlay.addEventListener("click", pauseTimer);

function pauseTimer() {
    if (paused) {
        // Resume timer
        paused = false;
        resetColors();
        startTime = Date.now() - pausedTime;
        if (!solved) timerInterval = setInterval(updateTimer, 1000);
    } else {
        // Pause timer
        paused = true;
        ctx.fillStyle = bgColor;
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        pausedTime = Date.now() - startTime;
        if (!solved) clearInterval(timerInterval);
    }
    pauseButton.classList.toggle("paused");
    pauseOverlay.classList.toggle("showPauseOverlay");
    resetLines();
}

// Timer function
function startTimer() {
    startTime = Date.now();
    timerInterval = setInterval(updateTimer, 1000);
}

// Update timer display
function updateTimer() {
    var elapsedTime = Math.floor((Date.now() - startTime) / 1000);
    var minutes = Math.floor(elapsedTime / 60);
    var seconds = elapsedTime % 60;
    timerElement.textContent = `Time: ${minutes}:${seconds.toString().padStart(2, "0")}`;
}

// Stop timer
function stopTimer() {
    if (!timerStopped) {
        timerStopped = true;
        var elapsedTime = ((Date.now() - startTime) / 1000).toFixed(2);
        clearInterval(timerInterval);
        if (!getSolved) {
            timeRequest.open("POST", "score.php", true);
            timeRequest.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            timeRequest.send(`sudokuId=${sudokuId}&elapsedTime=${elapsedTime}`);
        }
    }
}

window.addEventListener("load", startTimer);

function receiveMessagePopup() {
    showPopup(<?php echo json_encode($message); ?>);
}
</script>