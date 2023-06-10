<?php
$alreadyDone = null;
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $getUserID = "SELECT userId FROM loginData WHERE username = '$username'";
    $userIdResult = mysqli_query(getConn(), $getUserID);
    $row = mysqli_fetch_assoc($userIdResult);
    $userId = $row['userId'];
    $alreadyDone = "
            WHERE sudokuId NOT IN (
                SELECT sudokuId
                FROM scores
                WHERE userId = '$userId'
            )";
}

$sql = "SELECT sudokuId, sudokuData FROM sudoku $alreadyDone ORDER BY RAND() LIMIT 1";
$result = getConn()->query($sql);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $sudokuId = $row['sudokuId'];
    $sudokuData = $row['sudokuData'];
    // Convert sudokuData to an array
    $sudokuDataArray = explode(';', $sudokuData);
    echo "<script>";
    echo "sudokuId = '$sudokuId';";
    echo "sudokuDataRaw = " . $sudokuData . ";";
    echo "sudokuData = JSON.parse(JSON.stringify(sudokuDataRaw));";
    echo "drawNumbers();";
    echo "sudokuIdElement.textContent = `Sudoku ID: ${sudokuId}`;";
    echo "</script>";
} else {
    echo "No entries found in the sudoku table.";
}
mysqli_close(getConn());
?>