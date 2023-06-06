<?php
function getConn() {
$servername = "127.0.0.1";
$username = "root";
$password = "";
$dbname = "sudoku";
$conn = null;
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Verbindung zur Datenbank fehlgeschlagen: " . $conn->connect_error);
    }
	return $conn;
}
?>