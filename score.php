<?php
if (session_status() == PHP_SESSION_NONE) session_start();
include_once 'connections.php';

if (isset($_POST['sudokuId'])) {
    $sudokuId = $_POST["sudokuId"];
    $elapsedTime = $_POST["elapsedTime"];
    date_default_timezone_set('Europe/Berlin');
    $currentTime = date('Y-m-d H:i:s');
    if (!isset($_SESSION['username'])) return;
    $username = $_SESSION['username'];
    $getUserID = "SELECT userId FROM loginData WHERE username = '$username'";
    $userIdResult = mysqli_query(getConn(), $getUserID);
    if($userIdResult) {
        $row = mysqli_fetch_assoc($userIdResult);
        $userId = $row['userId'];
        $sql = "INSERT INTO scores (userId, sudokuId, elapsedTime, date) VALUES ('$userId', $sudokuId, '$elapsedTime', '$currentTime')";
        if (mysqli_query(getConn(), $sql)) {
           echo "Game finish time stored successfully.";
        } else {
            echo "Error: " . mysqli_error(getConn());
        }
    } else {
       echo "Error: " . mysqli_error(getConn());
    }
    mysqli_close(getConn());
}
?>