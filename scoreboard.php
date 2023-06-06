<?php
include_once 'connections.php';

$sql = "SELECT userId, elapsedTime FROM scores ORDER BY elapsedTime ASC";
$result = getConn()->query($sql);

echo "<h2>Scoreboard</h2>";
echo "<table>";
echo "<tr><th>User</th><th>TopTime</th></tr>";

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr><td>" . $row["userId"] . "</td><td>" . $row["elapsedTime"] . "</td></tr>";
    }
} else {
    echo "<tr><td colspan='2'>No scoreboard data available</td></tr>";
}

echo "</table>";
?>