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
    <h1>Sudoku</h1><span id="sudokuIdElement"></span>
    <ul>
      <li><a href="#">Home</a></li>
    </ul>
  </header>
  <main>
    <div class="control-panel">
      <button>Neues Spiel</button>
      <button onclick="solveSudoku()">Lösung zeigen</button>
      <button onclick="checkIfValid()">Prüfen</button>
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

// Konstanten
const maxLength = 9;

// Zeichne die horizontalen Linien
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

// Zeichne die vertikalen Linien
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

filename = "sudoku.txt";

var sudokuData = [];
var sudokuDataRaw = [];
var sudokuDataSplit = [];
var randomIndex = 0;
var request = new XMLHttpRequest();
request.open("GET", filename, true);
request.onload = function() {
  if (request.status >= 200 && request.status < 400) {
    // Konvertiere die Daten in ein Array von Zahlen und Leerzeichen
    sudokuDataSplit = request.responseText.split("][");
    var randomIndex = Math.floor(Math.random() * sudokuDataRaw.length); // Zufälliger Index auswählen
    sudokuDataRaw = sudokuDataSplit[randomIndex].split(";");
	sudokuData = JSON.parse(JSON.stringify(sudokuDataRaw));
    // Zeichne die Zahlen auf dem Sudoku Raster
    drawNumbers();
	
	// Zeige die ID des Sudokus an
    var sudokuId = randomIndex + 1; // Index beginnt bei 0, deshalb +1
	var sudokuIdElement = document.getElementById("sudokuIdElement");
        sudokuIdElement.textContent = "Sudoku ID: " + sudokuId;
  }
};
request.send();

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
}

// Eine Funktion, um die Zahlen auf dem Sudoku Raster zu zeichnen
function drawNumbers() {
    // Definiere die Schriftart und die Farbe für die Zahlen
    ctx.font = "50px Arial";
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
    // Setze die Hintergrundfarbe des markierten Feldes
    ctx.fillStyle = color;
    ctx.fillRect(x, y, cellSize, cellSize);

    // Zeichne die Linien um das markierte Feld herum, um den Rahmen beizubehalten
    ctx.strokeStyle = lineColor;
    ctx.lineWidth = 1;
    ctx.strokeRect(x, y, cellSize, cellSize);

    var index = row * maxLength + column;
    var value = sudokuData[index];
	if (sudokuDataRaw[index] !== value.toString()) {
    ctx.fillStyle = "#0b79e4"; // Blaue Farbe für Zahlen, die nicht in sudokuDataRaw sind
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
		if (!checkIfValidPos(row, column)) {
            sudokuData[row * maxLength + column] = " ";
        }
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
    if (sudokuDataRaw[value] !== value.toString()) {
    ctx.fillStyle = "#0b79e4"; // Blaue Farbe für Zahlen, die nicht in sudokuDataRaw sind
  } else {
    ctx.fillStyle = lineColor; // Standardfarbe für Zahlen in sudokuDataRaw
  }
    ctx.textAlign = "center";
    ctx.textBaseline = "middle";
    ctx.fillText(value, x, y);
    sudokuData[row * maxLength + column] = value.toString();
}


function isFixedCell(row, column) {
    var index = row * maxLength + column;
    var value = sudokuDataRaw[index];
    if (value !== " ") {
        return true;
    } else {
        return false;
    }
}

function checkIfValidPos(row, column) {
	ifValid=true;
	var index = row * maxLength + column;
    var value = sudokuData[index];
  // Überprüfe die Zeile (row)
  for (var c = 0; c < maxLength; c++) {
    if (sudokuData[row * maxLength + c] === value && c !== column) {
      ifValid=false; // Wert bereits in der Zeile vorhanden
    }
  }

  // Überprüfe die Spalte (column)
  for (var r = 0; r < maxLength; r++) {
    if (sudokuData[r * maxLength + column] === value && r !== row) {
      ifValid=false; // Wert bereits in der Spalte vorhanden
    }
  }

  // Überprüfe den 3x3-Kasten
  var boxRow = Math.floor(row / 3) * 3; // Erste Zeile des Kastens
  var boxColumn = Math.floor(column / 3) * 3; // Erste Spalte des Kastens

  for (var r = boxRow; r < boxRow + 3; r++) {
    for (var c = boxColumn; c < boxColumn + 3; c++) {
      if (sudokuData[r * maxLength + c] === value && (r !== row || c !== column)) {
        ifValid=false; // Wert bereits im Kasten vorhanden
      }
    }
  }
  return ifValid;
}

// Eine Funktion, um alle Zahlen im Sudoku zu überprüfen und doppelte Zahlen rot zu markieren
function checkIfValid() {
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
                markField(row, c, "#f7cfd6");
		}
    }
  }
  // Überprüfe die Spalte
  for (var r = 0; r < maxLength; r++) {
	  
    if (r !== row && sudokuData[r * maxLength + column] === value) {
		if (value !== " ") {
                markField(r, column, "#f7cfd6");
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
                            markFieldsInBox(boxRow, boxColumn, value, "#f7cfd6");
                        } else {
                            numbersInBox.add(value);
                        }
                    }
                }
            }
        }
    }

	// Eine Hilfsfunktion, um alle Felder im gleichen 3x3-Kasten mit einer bestimmten Zahl zu markieren
    function markFieldsInBox(boxRow, boxColumn, number, color) {
        for (var i = boxRow; i < boxRow + 3; i++) {
            for (var j = boxColumn; j < boxColumn + 3; j++) {
                if (sudokuData[i * maxLength + j] === number) {
                    markField(i, j, color);
                    resetLines();
                }
            }
        }
    }

	resetLines();
}



function solveSudoku() {
  // Finde die nächste leere Zelle im Sudoku
  var emptyCell = findEmptyCell();

  // Wenn keine leere Zelle gefunden wurde, ist das Sudoku gelöst
  if (!emptyCell) {
    alert("Das Sudoku wurde gelöst!");
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

      // Löse das Sudoku rekursiv, indem du zur nächsten leeren Zelle gehst
      solveSudoku();

      // Wenn das Sudoku gelöst wurde, beende die Schleife und die Funktion
      if (isSudokuSolved()) {
        return;
      }

      // Wenn das Sudoku nicht gelöst wurde, setze die Zelle zurück und probiere die nächste Zahl
      sudokuData[row * maxLength + column] = " ";
    }
  }
}

// Eine Funktion, um die nächste leere Zelle im Sudoku zu finden
function findEmptyCell() {
  for (var row = 0; row < maxLength; row++) {
    for (var column = 0; column < maxLength; column++) {
      if (sudokuData[row * maxLength + column] === " ") {
        return { row: row, column: column };
      }
    }
  }
  return null;
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
      if (sudokuData[i * maxLength + j] === " ") {
        return false;
      }
    }
  }
  resetColors();
  resetLines();
  return true;
}

// TODO:
//- Login
//- Stats
//- Methoden vereinfachen

</script>
