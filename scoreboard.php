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

$filterCondition = isset($_SESSION['sudokuIdFilter']) ? "WHERE scores.sudokuId = '{$_SESSION['sudokuIdFilter']}'" : '';

function getPages($filter) {
    return "SELECT COUNT(*) as total FROM scores INNER JOIN loginData ON scores.userId = loginData.userId $filter";
}

function getRows($filter, $limit, $offset) {
    return "SELECT sudokuId, loginData.username, scores.elapsedTime FROM scores
        INNER JOIN loginData ON scores.userId = loginData.userId
        $filter
        ORDER BY scores.elapsedTime ASC
        LIMIT $limit OFFSET $offset";
}

function drawTableData($result, $personal = '') {
    echo "<div class='scoreboard'>";
    echo "<p>" . ($personal ? "Personal Ranking" : "Ranking") . "</p>";
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
}

function drawPaginator($page, $totalPages) {
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
}

function generatePaginationLink($page, $ppage, $label) {
    $url = "?page=$page";
    if (!empty($ppage)) {
        $url .= "&ppage=$ppage";
    }
    return "<a href='$url'>$label</a>";
}

$ppage = isset($_GET['ppage']) ? $_GET['ppage'] : 1;
$page = isset($_GET['page']) ? $_GET['page'] : 1;

$limit = 10;

function getData($page, $filterCondition, $limit, $personal = '') {
    $offset = ($page - 1) * $limit;
    $countResult = getConn()->query(getPages($filterCondition));
    $totalCount = $countResult->fetch_assoc()['total'];
    $totalPages = ceil($totalCount / $limit);
    $result = getConn()->query(getRows($filterCondition, $limit, $offset));
    drawTableData($result, $personal);
    drawPaginator($page, $totalPages);
}

echo "<div class='ranking'>";
getData($page, $filterCondition, $limit, '');
?>

<form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" autocomplete="off">
    <div class="filter-container">
        <label for="sudokuIdFilter">Enter Sudoku ID to filter:</label>
        <input type="number" name="sudokuIdFilter" id="sudokuIdFilter" min="1" value="<?php echo $_SESSION['sudokuIdFilter'] ?? ''; ?>" required>
        <button type="submit">Show Scores</button>
        <button type="submit" name="clearFilter">Clear Filter</button>
    </div>
</form>

<?php
echo "</div>";
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $loggedUserSql = "WHERE loginData.username = '$username'";
    getData($ppage, $loggedUserSql, $limit, 'personal');
}
echo "</div>";
?>