<?php
if (session_status() == PHP_SESSION_NONE) session_start();
include_once 'connections.php';

if (isset($_POST['sudokuIdFilter'])) {
    $_SESSION['sudokuIdFilter'] = $_POST['sudokuIdFilter'];
}

if (isset($_GET['sudokuId'])) {
    $_SESSION['sudokuIdFilter'] = $_GET['sudokuId'];
    header("Location: {$_SERVER['PHP_SELF']}");
}

if (isset($_POST['clearFilter'])) {
    unset($_SESSION['sudokuIdFilter']);
    header("Location: {$_SERVER['PHP_SELF']}");
}

$filterCondition = "";
if (isset($_SESSION['sudokuIdFilter'])) {
    $sudokuIdFilter = $_SESSION['sudokuIdFilter'];
    $filterCondition = "WHERE scores.sudokuId = '$sudokuIdFilter'";
}

$ppage = isset($_GET['ppage']) ? $_GET['ppage'] : 1;
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;
$countResult = getConn()->query(getPages($filterCondition));
$totalCount = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalCount / $limit);

function getPages() {
    return "SELECT COUNT(*) as total FROM scores INNER JOIN loginData ON scores.userId = loginData.userId $filter";
}
function getRows($filter, $limit, $offset) {
    return "SELECT sudokuId, loginData.username, scores.elapsedTime FROM scores
        INNER JOIN loginData ON scores.userId = loginData.userId
        $filter
        ORDER BY scores.elapsedTime ASC
        LIMIT $limit OFFSET $offset";
}

$result = getConn()->query(getRows($filterCondition, $limit, $offset));

echo "<div class='ranking'>";
echo "<div class='scoreboard'>";
echo "<p>Ranking</p>";
echo "<table>";
echo "<tr><th>Sudoku</th><th>User</th><th>TopTime</th></tr>";

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $elapsedTime = $row["elapsedTime"];
        $timeDisplay = ($elapsedTime > 60) ? gmdate("i:s", $elapsedTime) . " min" : gmdate("s", $elapsedTime) . " sec";
        echo "<tr><td><a href='?sudokuId={$row["sudokuId"]}'>" . $row["sudokuId"] . "</td><td>" . $row["username"] . "</td><td>" . $timeDisplay . "</td></tr>";
    }
} else {
    echo "<tr><td colspan='3'>No ranking data available</td></tr>";
}
echo "</table>";

echo "<div class='pagination'>";
if ($page > 1) {
    $prevPage = $page - 1;
    echo generatePaginationLink($prevPage, $ppage, 'Previous');
}

if (!$totalPages == 0) echo "<span>Page $page of $totalPages</span>";

if ($page < $totalPages) {
    $nextPage = $page + 1;
    echo generatePaginationLink($nextPage, $ppage, 'Next');
}
echo "</div>";
?>

<form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" autocomplete="off">
    <div class="filter-container">
        <label for="sudokuIdFilter">Enter Sudoku ID to filter:</label>
        <input type="number" name="sudokuIdFilter" id="sudokuIdFilter" min="1" value="<?php echo isset($_SESSION['sudokuIdFilter']) ? $_SESSION['sudokuIdFilter'] : ''; ?>" required>
        <button type="submit">Show Scores</button>
        <button type="submit" name="clearFilter">Clear Filter</button>
    </div>
</form>

<?php
echo "</div>";
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];

    $poffset = ($ppage - 1) * $limit;
    $loggedUserSql = "WHERE loginData.username = '$username'";
    $pcountResult = getConn()->query(getPages($loggedUserSql));
    $ptotalCount = $pcountResult->fetch_assoc()['total'];
    $ptotalPages = ceil($ptotalCount / $limit);
    $presult = getConn()->query(getRows($loggedUserSql, $limit, $offset));

    echo "<div class='scoreboard'>";
    echo "<p>Personal Ranking</p>";
    echo "<table>";
    echo "<tr><th>Sudoku</th><th>User</th><th>TopTime</th></tr>";

    if ($presult->num_rows > 0) {
        while ($prow = $presult->fetch_assoc()) {
            $elapsedTime = $prow["elapsedTime"];
            $timeDisplay = ($elapsedTime > 60) ? gmdate("i:s", $elapsedTime) . " min" : gmdate("s", $elapsedTime) . " sec";
            echo "<tr><td><a href='?sudokuId={$prow["sudokuId"]}'>" . $prow["sudokuId"] . "</td><td>" . $prow["username"] . "</td><td>" . $timeDisplay . "</td></tr>";
        }
    } else {
        echo "<tr><td colspan='3'>No ranking data available</td></tr>";
    }
    echo "</table>";
    echo "<div class='pagination'>";
    if ($ppage > 1) {
        $pprevPage = $ppage - 1;
        echo generatePaginationLink($page, $pprevPage, 'Previous');
    }

    if (!$ptotalPages == 0) echo "<span>Page $ppage of $ptotalPages</span>";

    if ($ppage < $ptotalPages) {
        $pnextPage = $ppage + 1;
        echo generatePaginationLink($page, $pnextPage, 'Next');
    }
    echo "</div>";
    echo "</div>";
}

function generatePaginationLink($page, $ppage, $label) {
    $url = "?page=$page";
    if (!empty($ppage)) {
        $url .= "&ppage=$ppage";
    }
    return "<a href='$url'>$label</a>";
}
?>