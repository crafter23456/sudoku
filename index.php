<?php 
session_start();
include 'login.php';
include 'score.php';
#include 'scoreboard.php';
?>
<html>
  <head>
    <link rel="stylesheet" href="style.css">
    <title>Sudoku</title>
	<meta name="viewport" content="user-scalable=no">
  </head>
  <body>
    <header>
      <link rel="preconnect" href="https://fonts.googleapis.com">
      <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
      <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
      <h1>Sudoku</h1>
      <span id="sudokuIdElement"></span>
      <ul>
        <li id="loginName"><?php echo isset($_SESSION['username']) ? $_SESSION['username'] : ''; ?></li>
        <li>
          <a id="loginButton" href="<?php echo isset($_SESSION['username']) ? 'index.php?logout' : '#'; ?>" onclick="showLoginPopup()">
          <?php echo isset($_SESSION['username']) ? 'Logout' : 'Login'; ?>
          </a>
        </li>
      </ul>
    </header>
    <main>
	  <?php //include 'scoreboard.php';?>
      <div>
        <canvas id="sudoku-canvas" width="900px" height="900px"></canvas>
      </div>
      <div id="panel">
        <div class="timer" id="timer">Time: 0:00</div>
        <div class="control-panel">
          <button onClick="window.location.reload()">Neues Spiel</button>
          <button onclick="solveSudoku()">Lösung zeigen</button>
          <button onclick="checkIfValid()">Prüfen</button>
          <button onclick="stopTimer()">Stop</button>
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
		  <div></div>
		  <div class="number" id="clear" data-value=" ">Clear</div>
        </div>
      </div>
      <div class="popup-container" id="loginPopup" style="display: none;">
        <div class="login-container">
          <h1><?php echo isset($loginLogout) ? $loginLogout : 'Login'; ?></h1>
          <form action="" method="POST">
            <label for="username">Benutzername:</label>
            <input type="text" id="username" name="username" required>
            <label for="password">Passwort:</label>
            <input type="password" id="password" name="password" required>
            <input type="submit" name="submit" value="Anmelden">
            <b id="registerAd">Neu hier?</b>
            <input type="submit" id="register" name="register" value="Registrieren">
          </form>
        </div>
      </div>
      <div id="overlay"></div>
      <div class="popup-container" id="popupContainer" style="display: none;">
        <div class="popup-content">
          <p id="popup-message"></p>
          <button id="popup-close">Schließen</button>
        </div>
      </div>
    </main>
    <footer> &copy; 2023 Sudoku-Website. Alle Rechte vorbehalten. </footer>

<script>
// Konstanten
const canvas = document.getElementById("sudoku-canvas");
const sudokuIdElement = document.getElementById("sudokuIdElement");
const loginPopup = document.getElementById("loginPopup");
const popUpMessage = document.getElementById("popup-message");
const closeButton = document.getElementById("popup-close");
const popupContainer = document.getElementById("popupContainer");
const numberButtons = document.getElementsByClassName("number");
const clearButton = document.getElementById("clear");
const timerElement = document.getElementById("timer");
const maxLength = 9;
const sudokuFont = "50px Arial";
const filename = "sudoku.txt";
const newNumbersColor = "#0b79e4";
const matchingNumbersColor = "#c3d7ea";
const rowColumnBoxColor = "#e2ebf3";
const markedFieldColor = "#bbdefb";
const invalidColor = "#f7cfd6";
const disabledButtonColor = "#b2b2b2";
const lightGrayColor = "#d1d1d1";
const bgColor = "#ffffff";
const lineColor = "#000000";

// Variablen
let startTime;
let timerInterval;
var ctx = canvas.getContext("2d");
var getSolved = false;
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
var markedField2 = JSON.parse(JSON.stringify(markedField));
var request = new XMLHttpRequest();
var sudokuId;
ctx.fillStyle = bgColor;
ctx.fillRect(0, 0, canvas.width, canvas.height);
ctx.strokeStyle = lineColor;
ctx.lineWidth = 1;

resetLines();

request.open("GET", filename, true);
request.onload = function() {
    if (request.status >= 200 && request.status < 400) {
        // Konvertiere die Daten in ein Array von Zahlen und Leerzeichen
        var sudokuText = request.responseText;
        var chunkSize = 164;
        var numChunks = Math.ceil(sudokuText.length / chunkSize);

        for (var i = 0; i < numChunks; i++) {
            var start = i * chunkSize;
            var chunk = sudokuText.substr(start, chunkSize);
            var sudokuDataPart = chunk.split(";");
            sudokuDataSplit.push(sudokuDataPart);
        }

        var randomIndex = Math.floor(Math.random() * sudokuDataSplit.length); // Zufälliger Index auswählen
        sudokuDataRaw = sudokuDataSplit[randomIndex];
        sudokuData = JSON.parse(JSON.stringify(sudokuDataRaw));
        drawNumbers();
        console.log(randomIndex);
        sudokuId = randomIndex + 1; // Index beginnt bei 0, deshalb +1

        sudokuIdElement.innerText = "Sudoku ID: " + sudokuId;
    }
};
request.send();

window.addEventListener('resize', handleResize);

function handleResize() {
    canvasStyle = window.getComputedStyle(canvas);
    canvasWidth = parseInt(canvasStyle.getPropertyValue("width"), 10);
    calcCellSize = canvasWidth / maxLength;
}

function resetColors() {
    for (var i = 0; i < maxLength; i++) {
        for (var j = 0; j < maxLength; j++) {
            markField(i, j, bgColor); // Setze die Hintergrundfarbe auf die normale Farbe
        }
    }
}

function resetLines() {
    for (var i = 0; i <= maxLength; i++) {
        var y = i * cellSize;
        ctx.beginPath();
        ctx.moveTo(0, y);
        ctx.lineTo(canvas.width, y);
        ctx.stroke();
        if (i % 3 === 2) {
            ctx.lineWidth = 3; // Erhöhe die Linienbreite auf 3
        } else {
            ctx.lineWidth = 1; // Setze die Linienbreite auf den Standardwert 1
        }
    }
    for (var j = 0; j <= maxLength; j++) {
        var x = j * cellSize;
        ctx.beginPath();
        ctx.moveTo(x, 0);
        ctx.lineTo(x, canvas.height);
        ctx.stroke();
        if (j % 3 === 2) {
            ctx.lineWidth = 3; // Erhöhe die Linienbreite auf 3
        } else {
            ctx.lineWidth = 1; // Setze die Linienbreite auf den Standardwert 1
        }
    }
    updateButtonColors();
}

// Eine Funktion, um die Zahlen auf dem Sudoku Raster zu zeichnen
function drawNumbers() {
    // Definiere die Schriftart und die Farbe für die Zahlen
    ctx.font = sudokuFont;
    ctx.fillStyle = lineColor;
    // Gehe durch jede Zelle des Sudoku Rasters
    for (var i = 0; i < maxLength; i++) {
        for (var j = 0; j < maxLength; j++) {
            // Hole den Index der aktuellen Zelle im sudokuData Array
            var index = i * maxLength + j;
            // Hole den Wert der aktuellen Zelle im sudokuData Array
            var value = sudokuData[index];
            // Wenn der Wert nicht leer ist, zeichne ihn in der Mitte der Zelle

            if (value !== " " || value !== "[") {
                var x = j * cellSize + cellSize / 2;
                var y = i * cellSize + cellSize / 2;
                ctx.textAlign = "center";
                ctx.textBaseline = "middle";
                ctx.fillText(value, x, y);
            }
        }
    }
}

// Eine Funktion, um das gesamte Dreierfeld im Sudoku zu markieren
function markBox(row, column, color) {
    // Berechne die Startposition des Dreierfeldes
    var boxRow = Math.floor(row / 3) * 3;
    var boxColumn = Math.floor(column / 3) * 3;

    // Markiere jedes Feld im Dreierfeld
    for (var i = boxRow; i < boxRow + 3; i++) {
        for (var j = boxColumn; j < boxColumn + 3; j++) {
            markField(i, j, color);
        }
    }
}

function markField(row, column, color) {
    // Berechne die Koordinaten des markierten Feldes
    var x = column * cellSize;
    var y = row * cellSize;
    markedField = {
        row: row,
        column: column
    };
    markedField2 = JSON.parse(JSON.stringify(markedField));

    // Setze die Hintergrundfarbe des markierten Feldes
    ctx.fillStyle = color;
    ctx.fillRect(x, y, cellSize, cellSize);

    // Zeichne die Linien um das markierte Feld herum, um den Rahmen beizubehalten
    ctx.strokeRect(x, y, cellSize, cellSize);

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
    // Markiere jedes Feld in der Zeile
    for (var j = 0; j < maxLength; j++) {
        markField(row, j, color);
    }

    // Markiere jedes Feld in der Spalte
    for (var i = 0; i < maxLength; i++) {
        markField(i, column, color);
    }
}

function updateButtonColors() {
    const numberCounts = {};
    for (let i = 0; i < maxLength; i++) {
        for (let j = 0; j < maxLength; j++) {
            const index = i * maxLength + j;
            const value = sudokuData[index];
            if (value !== " ") {
                if (!numberCounts[value]) {
                    numberCounts[value] = 1;
                } else {
                    numberCounts[value]++;
                }
            }
        }
    }

    for (let i = 0; i < numberButtons.length; i++) {
        const button = numberButtons[i];
        const value = button.getAttribute("data-value");
        if (numberCounts[value] && numberCounts[value] >= 9) {
            button.style.backgroundColor = disabledButtonColor;
            button.style.pointerEvents = "none";
        } else {
            button.style.backgroundColor = lightGrayColor;
            button.style.pointerEvents = "auto";
        }
    }
}

canvas.addEventListener("click", function(event) {
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
        clearButton.style.backgroundColor = disabledButtonColor;
        clearButton.style.pointerEvents = "none";
        showPopup();
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
        for (var r = 0; r < maxLength; r++) {
            for (var c = 0; c < maxLength; c++) {
                if (sudokuData[r * maxLength + c] === value) {
                    markField(r, c, color);
                }
            }
        }
    }
}

// Wähle eine Zahl beim Klicken
for (var i = 0; i < numberButtons.length; i++) {
    var numberButton = numberButtons[i];
    numberButton.addEventListener("click", function() { // Markiere die passenden Zahlen
        row = markedField2.row;
        column = markedField2.column;
        console.log(row, column);
        console.log(markedField2, markedField);
        if (markedField2 && !isFixedCell(row, column)) {
            var value = this.dataset.value;
            sudokuData[row * maxLength + column] = " ";
            resetColors();
            markBox(row, column, rowColumnBoxColor);
            markRowAndColumn(row, column, rowColumnBoxColor);
            // Schreibe die ausgewählte Zahl in das markierte Feld
            writeNumber(row, column, value);
            resetLines();
        }
    });
}

// Feld im Sudoku zu beschreiben (Zahl eintragen)
function writeNumber(row, column, value) {
    var x = column * cellSize + cellSize / 2;
    var y = row * cellSize + cellSize / 2;
    ctx.font = sudokuFont;
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
        popUpMessage.innerHTML = "Das Sudoku wurde gelöst!";
        showPopup();
        stopTimer();
        return;
    }
}


function isFixedCell(row, column) {
    var index = row * maxLength + column;
    var value = sudokuDataRaw[index];
    return value !== " ";
}

function checkIfValidPos(row, column) {
    ifValid = true;
    var index = row * maxLength + column;
    var value = sudokuData[index];
    // Überprüfe die Zeile (row)
    for (var c = 0; c < maxLength; c++) {
        if (sudokuData[row * maxLength + c] === value && c !== column) {
            ifValid = false; // Wert bereits in der Zeile vorhanden
        }
    }

    // Überprüfe die Spalte (column)
    for (var r = 0; r < maxLength; r++) {
        if (sudokuData[r * maxLength + column] === value && r !== row) {
            ifValid = false; // Wert bereits in der Spalte vorhanden
        }
    }

    // Überprüfe den 3x3-Kasten
    var boxRow = Math.floor(row / 3) * 3; // Erste Zeile des Kastens
    var boxColumn = Math.floor(column / 3) * 3; // Erste Spalte des Kastens

    for (var r = boxRow; r < boxRow + 3; r++) {
        for (var c = boxColumn; c < boxColumn + 3; c++) {
            if (sudokuData[r * maxLength + c] === value && (r !== row || c !== column)) {
                ifValid = false; // Wert bereits im Kasten vorhanden
            }
        }
    }
    return ifValid;
}

// Eine Funktion, um alle Zahlen im Sudoku zu überprüfen und doppelte Zahlen rot zu markieren
function checkIfValid() {
    const {
        row: tempRow,
        column: tempColumn
    } = markedField;
    ifValidPopUp = true;
    // Überprüfe jede Zelle im Sudoku
    for (var row = 0; row < maxLength; row++) {
        for (var column = 0; column < maxLength; column++) {
            checkRowAndColumn(row, column);
        }
    }

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
            for (var i = boxRow; i < boxRow + 3; i++) {
                for (var j = boxColumn; j < boxColumn + 3; j++) {
                    var value = sudokuData[i * maxLength + j];
                    if (value !== " ") {
                        if (numbersInBox.has(value)) {
                            markFieldsInBox(boxRow, boxColumn, value, invalidColor);
                        } else {
                            numbersInBox.add(value);
                        }
                    }
                }
            }
        }
    }

    // Alle Felder im gleichen 3x3-Kasten mit einer bestimmten Zahl zu markieren
    function markFieldsInBox(boxRow, boxColumn, number, color) {
        for (var i = boxRow; i < boxRow + 3; i++) {
            for (var j = boxColumn; j < boxColumn + 3; j++) {
                if (sudokuData[i * maxLength + j] === number) {
                    markField(i, j, color);
                    ifValidPopUp = false;
                    if (getSolved && !isFixedCell(i, j)) {
                        sudokuData[i * maxLength + j] = " ";
                    }
                }
            }
        }
    }

    if (ifValidPopUp) {
        popUpMessage.innerHTML = "Das Sudoku ist gültig!";
    } else {
        popUpMessage.innerHTML = "Das Sudoku ist ungültig!";
    }
    showPopup();
    markedField = {
        ...markedField,
        row: tempRow,
        column: tempColumn
    };
    resetLines();
}

function solveSudoku() {
    getSolved = true;
    checkIfValid();
    const {
        row: tempRow,
        column: tempColumn
    } = markedField;
    // Finde die nächste leere Zelle im Sudoku
    var emptyCell = findEmptyCell();

    // Wenn keine leere Zelle gefunden wurde, ist das Sudoku gelöst
    if (!emptyCell) {
        popUpMessage.innerHTML = "Das Sudoku wurde gelöst!";
        showPopup();
        stopTimer();
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

            // Löse das Sudoku rekursiv, indem man zur nächsten leeren Zelle gehst
            solveSudoku();

            // Wenn das Sudoku gelöst wurde, beende die Schleife und die Funktion
            if (isSudokuSolved()) {
                popUpMessage.innerHTML = "Das Sudoku wurde gelöst!";
                clearButton.style.backgroundColor = disabledButtonColor;
                clearButton.style.pointerEvents = "none";
                showPopup();
                stopTimer();
                //return;
                continue;
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

// Eine Funktion, um die nächste leere Zelle im Sudoku zu finden
function findEmptyCell() {
    for (var row = 0; row < maxLength; row++) {
        for (var column = 0; column < maxLength; column++) {
            if (sudokuData[row * maxLength + column] === " " || (!checkIfValidPos(row, column) && !isFixedCell())) {
                return {
                    row: row,
                    column: column
                };
            }
        }
    }
    return false;
}

// Eine Funktion, um zu überprüfen, ob eine Zahl in einer bestimmten Zelle gültig ist
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

// Eine Funktion, um zu überprüfen, ob das Sudoku gelöst wurde
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

function showLoginPopup() {
    <
    ? php
    if (!isset($_SESSION['username'])): ? >
        loginPopup.style.display = "flex"; <
    ? php endif; ? >
}

window.addEventListener("click", function(event) {
    if (event.target == loginPopup) {
        loginPopup.style.display = "none";
    }
});

// Funktion zum Anzeigen des Popups
function showPopup() {
    popupContainer.style.display = "flex";
}

// Funktion zum Schließen des Popups
function closePopup() {
    popupContainer.style.display = "none";
}

// Schließen Sie das Popup, wenn auf den Schließen-Button geklickt wird
closeButton.addEventListener("click", closePopup);

// Schließen Sie das Popup, wenn irgendwo hingeklickt wird
window.addEventListener("click", function(event) {
    if (event.target == popupContainer) {
        closePopup();
    }
});

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

// Stop timer function
function stopTimer() {
    clearInterval(timerInterval); <
    ? php $end = microtime(true); ? >
    //$.ajax({
    //	type: "POST",
    //	url: "score.php",
    //	data: { sudokuId: sudokuId },
    //	success:success
    //});
    request.open("POST", "score.php", true);
    request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    request.send("sudokuId=" + sudokuId);
}

// Add event listener to start timer when the page loads
window.addEventListener("load", startTimer);

function receiveMessagePopup() {
    popUpMessage.innerHTML = < ? php echo json_encode($message); ? > ;
    showPopup();
}
// TODO:
//- Stats
//- Sudoku Creator
//- Notizfunktion
//- Pause Funktion
//- Besserer Button disable check
</script>

<?php 
if ($showPopup) {
    echo "<script>receiveMessagePopup();</script>";
    $showPopup = false;
}
?>

</body>
</html>
