<?php
session_start();
include_once 'connections.php';

$sql = "SELECT sudokuId, loginData.username, scores.elapsedTime FROM scores
        INNER JOIN loginData ON scores.userId = loginData.userId
        ORDER BY scores.elapsedTime ASC LIMIT 10";
$result = getConn()->query($sql);
echo "<div class='scoreboard'>";
echo "<p>Scoreboard</p>";
echo "<table>";
echo "<tr><th>Sudoku</th><th>User</th><th>TopTime</th></tr>";

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr><td>" . $row["sudokuId"] . "</td><td>" . $row["username"] . "</td><td>" . $row["elapsedTime"] . " sec</td></tr>";
    }
} else {
    echo "<tr><td colspan='2'>No scoreboard data available</td></tr>";
}

echo "</table>";
echo "</div>"
?>