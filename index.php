<html>
<head>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f0f0f0;
      margin: 0;
      padding: 0;
    }

    header {
      background-color: #333;
      color: white;
      height: 80px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 20px;
    }

    header h1 {
      font-size: 36px;
    }

    header ul {
      list-style: none;
      display: flex;
    }

    header li {
      margin-left: 20px;
    }

    header a {
      color: white;
      text-decoration: none;
    }

    footer {
      background-color: #333;
      color: white;
      height: 60px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    main {
      display: grid;
      grid-template-columns: 1fr 3fr;
      grid-gap: 20px;
      padding: 20px;
    }

    canvas {
      border: 1px solid black;
    }

    .control-panel {
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    .control-panel button {
      width: 100px;
      height: 40px;
      margin-bottom: 10px;
    }
	.number-selector {
    position: relative;
    display: flex;
    width: 20%;
    height: 20%;
    flex-direction: row;
    flex-wrap: nowrap;
    justify-content: space-between;
	position: absolute;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
    flex-wrap: wrap;
    justify-content: stretch;
}

.number {
    display: inline-block;
  width: 30px;
  height: 30px;
  border: 1px solid #000;
  margin: 5px;
  text-align: center;
  cursor: pointer;
}
.number:hover {
  background-color: #ccc;
}
  </style>
</head>
<body>
  <header>
  <title>Sudoku</title>
    <h1>Sudoku</h1>
    <ul>
      <li><a href="#">Home</a></li>
    </ul>
  </header>
  <main>
    <div class="control-panel">
      <button>Neues Spiel</button>
      <button onclick="solveSudoku()">Lösung zeigen</button>
      <button onclick="checkSudoku()">Prüfen</button>
    </div>
    <canvas id="sudoku-canvas" width="900px" height="900px"></canvas>
	<div id="number-selector">
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
  </main>
  <footer>
    &copy; 2023 Sudoku-Website. Alle Rechte vorbehalten.
  </footer>
</body>
</html>


<script>
// Hole die Referenz zum canvas element
var canvas = document.getElementById("sudoku-canvas");

// Hole den 2D Zeichenkontext
var ctx = canvas.getContext("2d");

// Definiere die Größe einer Zelle im Sudoku Raster
var cellSize = 100;

// Definiere die Farben für den Hintergrund und die Linien
var bgColor = "#ffffff";
var lineColor = "#000000";

// Fülle den Hintergrund mit der Hintergrundfarbe
ctx.fillStyle = bgColor;
ctx.fillRect(0, 0, canvas.width, canvas.height);

// Zeichne die Linien des Sudoku Rasters mit der Linienfarbe
ctx.strokeStyle = lineColor;
ctx.lineWidth = 1;

// Zeichne die horizontalen Linien
for (var i = 0; i <= 9; i++) {
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

// Zeichne die vertikalen Linien
for (var j = 0; j <= 9; j++) {
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

// Lese die Sudoku Daten aus der Datei sudoku.txt
var sudokuData = [];
var sudokuDataRaw = [];
var request = new XMLHttpRequest();
request.open("GET", "sudoku.txt", true);
request.onload = function() {
    if (request.status >= 200 && request.status < 400) {
        // Konvertiere die Daten in ein Array von Zahlen und Leerzeichen
        sudokuData = request.responseText.split(";");
        sudokuDataRaw = JSON.parse(JSON.stringify(sudokuData));
        // Zeichne die Zahlen auf dem Sudoku Raster
        drawNumbers();
    }
};
request.send();

function resetColors() {
    for (var i = 0; i < 9; i++) {
        for (var j = 0; j < 9; j++) {
            markField(i, j, bgColor); // Setze die Hintergrundfarbe auf die normale Farbe
        }
    }
}

function resetLines() {
    for (var i = 0; i <= 9; i++) {
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
    for (var j = 0; j <= 9; j++) {
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
}

// Eine Funktion, um die Zahlen auf dem Sudoku Raster zu zeichnen
function drawNumbers() {
    // Definiere die Schriftart und die Farbe für die Zahlen
    ctx.font = "50px Arial";
    ctx.fillStyle = lineColor;

    // Gehe durch jede Zelle des Sudoku Rasters
    for (var i = 0; i < 9; i++) {
        for (var j = 0; j < 9; j++) {
            // Hole den Index der aktuellen Zelle im sudokuData Array
            var index = i * 9 + j;
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
    }; // NEUUUU
    // Setze die Hintergrundfarbe des markierten Feldes
    ctx.fillStyle = color;
    ctx.fillRect(x, y, cellSize, cellSize);

    // Zeichne die Linien um das markierte Feld herum, um den Rahmen beizubehalten
    ctx.strokeStyle = lineColor;
    ctx.lineWidth = 1;
    ctx.strokeRect(x, y, cellSize, cellSize);

    ctx.fillStyle = "black";

    var index = row * 9 + column;
    var value = sudokuData[index];
    var x = column * cellSize + cellSize / 2;
    var y = row * cellSize + cellSize / 2;
    ctx.textAlign = "center";
    ctx.textBaseline = "middle";
    ctx.fillText(value, x, y);
}

// Eine Funktion, um die gesamte Zeile und Spalte im Sudoku zu markieren
function markRowAndColumn(row, column, color) {
    // Markiere jedes Feld in der Zeile
    for (var j = 0; j < 9; j++) {
        markField(row, j, color);
    }

    // Markiere jedes Feld in der Spalte
    for (var i = 0; i < 9; i++) {
        markField(i, column, color);
    }
}


canvas.addEventListener("click", function(event) {
    // Berechne die Position des Klicks relativ zum Canvas
    var rect = canvas.getBoundingClientRect();
    var x = event.clientX - rect.left;
    var y = event.clientY - rect.top;

    // Berechne die Zeilen- und Spaltennummer des Feldes
    var row = Math.floor(y / cellSize);
    var column = Math.floor(x / cellSize);

    // Rufe eine Funktion auf, um das Feld zu markieren oder andere Aktionen durchzuführen
    console.log("CLICK" + isFixedCell(row, column));
    if (!isFixedCell(row, column)) {
        resetColors();
        markBox(row, column, "#e2ebf3");
        markRowAndColumn(row, column, "#e2ebf3");
        markField(row, column, "#bbdefb");
        resetLines();
    }

});



// Wähle eine Zahl beim Klicken
var numberButtons = document.getElementsByClassName("number");
for (var i = 0; i < numberButtons.length; i++) {
    var numberButton = numberButtons[i];
    numberButton.addEventListener("click", function() {
        if (markedField) {
            var value = this.dataset.value;
            // Schreibe die ausgewählte Zahl in das markierte Feld
            writeNumber(markedField.row, markedField.column, value);
            markedField = null;
            //clearMarkedFields();
        }
    });
}

// Eine Funktion, um ein Feld im Sudoku zu beschreiben (Zahl eintragen)
function writeNumber(row, column, value) {
    var x = column * cellSize + cellSize / 2;
    var y = row * cellSize + cellSize / 2;
    ctx.font = "50px Arial";
    ctx.fillStyle = lineColor;
    ctx.textAlign = "center";
    ctx.textBaseline = "middle";
    ctx.fillText(value, x, y);
    sudokuData[row * 9 + column] = value.toString();
}


function isFixedCell(row, column) {
    var index = row * 9 + column;
    var value = sudokuDataRaw[index];
    if (value !== " ") {
        console.log("isFixedCell Raw" + sudokuDataRaw[index]);
        console.log("isFixedCell " + sudokuData[index]);
        return true;

    } else {
        return false;
    }
}

function checkRowAndColumn(row, column) {
  var value = sudokuData[row * 9 + column];

  // Überprüfe die Zeile
  for (var c = 0; c < 9; c++) {
    if (c !== column && sudokuData[row * 9 + c] === value) {
		if (value !== " ") {
      markField(row, c, "#f7cfd6");
                resetLines();
                if (!isFixedCell(row, column)) {
                    sudokuData[row * 9 + column] = " ";
                }
		}
		
    }
  }

  // Überprüfe die Spalte
  for (var r = 0; r < 9; r++) {
    if (r !== row && sudokuData[r * 9 + column] === value) {
		if (value !== " ") {
      markField(r, column, "#f7cfd6");
                resetLines();
                if (!isFixedCell(row, column)) {
                    sudokuData[row * 9 + column] = " ";
                }
		}
    }
  }
}

// Funktion, um das Sudoku zu überprüfen
function checkSudoku() {
  resetColors(); // Setze die Farben zurück

  // Überprüfe jede Zelle im Sudoku
  for (var row = 0; row < 9; row++) {
    for (var column = 0; column < 9; column++) {
      checkRowAndColumn(row, column);
    }
  }
}

// Eine Funktion, um alle Zahlen im Sudoku zu überprüfen und doppelte Zahlen rot zu markieren
function checkIfValid() {
    // Überprüfe die kleinen 3x3-Kästen
    for (var boxRow = 0; boxRow < 9; boxRow += 3) {
        for (var boxColumn = 0; boxColumn < 9; boxColumn += 3) {
            var numbersInBox = new Set();
            for (var i = boxRow; i < boxRow + 3; i++) {
                for (var j = boxColumn; j < boxColumn + 3; j++) {
                    var value = sudokuData[i * 9 + j];
                    if (value !== " ") {
                        if (numbersInBox.has(value)) {
                            markFieldsInBox(boxRow, boxColumn, value, "#f7cfd6");
                        } else {
                            numbersInBox.add(value);
                        }
                    }
                }
            }
        }
    }

    // Überprüfe die Zeile
    for (var row = 0; row < 9; row++) {
        var numbersInRow = new Set();
        for (var column = 0; column < 9; column++) {
            var value = sudokuData[row * 9 + column];
            if (value !== " ") {
                if (numbersInRow.has(value)) {
                    markFieldsInRow(row, value, "#f7cfd6");
                } else {
                    numbersInRow.add(value);
                }
            }
        }
    }

    // Überprüfe die Spalten
    for (var column = 0; column < 9; column++) {
        var numbersInColumn = new Set();
        for (var row = 0; row < 9; row++) {
            var value = sudokuData[row * 9 + column];
            if (value !== " ") {
				console.log("COLUMN" + value);
                if (numbersInColumn.has(value)) {
                    markFieldsInColumn(column, value, "#f7cfd6");
                } else {
                    numbersInColumn.add(value);
                }
            }
        }
    }


    // Eine Hilfsfunktion, um alle Felder im gleichen 3x3-Kasten mit einer bestimmten Zahl zu markieren
    function markFieldsInBox(boxRow, boxColumn, number, color) {
        for (var i = boxRow; i < boxRow + 3; i++) {
            for (var j = boxColumn; j < boxColumn + 3; j++) {
                if (sudokuData[i * 9 + j] === number) {
                    markField(i, j, color);
                    resetLines();
                    console.log(sudokuData[i * 9 + j]);
                    console.log("A" + sudokuDataRaw[i * 9 + j]);
                    console.log("A" + number);
                    if (!isFixedCell(i, j)) {
                        console.log(sudokuData[i * 9 + j]);
                        console.log("B" + sudokuDataRaw[i * 9 + j]);
                        console.log("B" + number);
                        sudokuData[i * 9 + j] = " ";
                    }
                }
            }
        }
    }

    // Eine Hilfsfunktion, um alle Felder in derselben Reihe mit einer bestimmten Zahl zu markieren
    function markFieldsInRow(row, number, color) {
        for (var column = 0; column < 9; column++) {
            if (sudokuData[row * 9 + column] === number) {
                markField(row, column, color);
                resetLines();
                if (!isFixedCell(row, column)) {
                    sudokuData[row * 9 + column] = " ";
                }
            }
        }
    }

    // Eine Hilfsfunktion, um alle Felder in derselben Spalte mit einer bestimmten Zahl zu markieren
    function markFieldsInColumn(column, number, color) {
        for (var row = 0; row < 9; row++) {
            if (sudokuData[row * 9 + column] === number) {
                markField(row, column, color);
                resetLines();
                if (!isFixedCell(row, column)) {
                    sudokuData[row * 9 + column] = " ";
                }
            }
        }
    }
}

// Eine Funktion, um das Sudoku zu lösen
function solveSudoku() {
    // Finde eine leere Zelle im Sudoku
    var emptyCell = findEmptyCell();

    // Wenn keine leere Zelle mehr gefunden wurde, ist das Sudoku gelöst
    if (!emptyCell) {
        return true;
    }

    var row = emptyCell.row;
    var column = emptyCell.column;

    // Probiere Zahlen von 1 bis 9 aus
    for (var number = 1; number <= 9; number++) {
        // Überprüfe, ob die Zahl an dieser Position gültig ist
        if (checkIfValid()) {
            // Setze die Zahl an dieser Position im Sudoku
            sudokuData[row * 9 + column] = number.toString();

            // Versuche, das Sudoku mit der gesetzten Zahl weiter zu lösen (rekursiver Aufruf)
            if (solveSudoku()) {
                return true; // Das Sudoku wurde gelöst
            }

            // Wenn die Zahl zu keiner Lösung führt, setze die Zelle zurück
            sudokuData[row * 9 + column] = " ";
        }
    }

    return false; // Es wurde keine Lösung gefunden
}

// Eine Hilfsfunktion, um eine leere Zelle im Sudoku zu finden
function findEmptyCell() {
    for (var row = 0; row < 9; row++) {
        for (var column = 0; column < 9; column++) {
            if (sudokuData[row * 9 + column] === " ") {
                return {
                    row: row,
                    column: column
                };
            }
        }
    }

    return null; // Es wurden keine leeren Zellen gefunden
}

</script>
