<?php
include_once 'connections.php';

$start = microtime(true); 
$end = null;

//function storeGameFinishTime() {
//if(isset($end)) {
//if ($_SERVER["REQUEST_METHOD"] == "POST") {
if (isset($_POST['sudokuId'])) {
    if (!getConn()) {
        die("Connection failed: " . mysqli_connect_error());
    }
	$sudokuId = $_POST["sudokuId"];
    echo ($end - $start);                // Nanoseconds
    echo ($end - $start) / 1000000;      // Milliseconds
    echo ($end - $start) / 1000000000;   // Seconds
    $elapsedTime = $end - $start;
    $currentTime = date('Y-m-d H:i:s');
    $getUserID = "SELECT userId FROM login_data WHERE username = '$_SESSION[username]'";
	$userIdResult = mysqli_query(getConn(), $getUserID);
	if($userIdResult) {
		$row = mysqli_fetch_assoc($userIdResult);
		$userId = $row['userId'];
		$sql = "INSERT INTO scores (userId, elapsedTime, date, sudokuId) VALUES ('$userId', '$elapsedTime', '$currentTime', $sudokuId)";
    
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