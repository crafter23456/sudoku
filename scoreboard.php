<?php
session_start();
include_once 'connections.php';

if (isset($_POST['sudokuIdFilter'])) {
    $_SESSION['sudokuIdFilter'] = $_POST['sudokuIdFilter'];
}

if (isset($_POST['clearFilter'])) {
    unset($_SESSION['sudokuIdFilter']);
}

if (isset($_SESSION['sudokuIdFilter'])) {
    $sudokuIdFilter = $_SESSION['sudokuIdFilter'];
    $filterCondition = "WHERE scores.sudokuId = '$sudokuIdFilter'";
} else {
    $filterCondition = "";
}

$sql = "SELECT sudokuId, loginData.username, scores.elapsedTime FROM scores
        INNER JOIN loginData ON scores.userId = loginData.userId
        $filterCondition
        ORDER BY scores.elapsedTime ASC LIMIT 10";

$result = getConn()->query($sql);
echo "<div class='scoreboard'>";
echo "<p>Ranking</p>";
echo "<table>";
echo "<tr><th>Sudoku</th><th>User</th><th>TopTime</th></tr>";

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr><td>" . $row["sudokuId"] . "</td><td>" . $row["username"] . "</td><td>" . $row["elapsedTime"] . " sec</td></tr>";
    }
} else {
    echo "<tr><td colspan='3'>No ranking data available</td></tr>";
}
echo "</table>";
?>

<form method="POST" action="" autocomplete="off">
<div class="filter-container">
    <label for="sudokuIdFilter">Enter Sudoku ID to filter:</label>
    <input type="text" name="sudokuIdFilter" id="sudokuIdFilter" value="<?php echo $_SESSION['sudokuIdFilter']; ?>" required>
    <button type="submit">Show Scores</button>
    <button type="submit" name="clearFilter">Clear Filter</button></div>
</form>

<?php
echo "</div>";
?>