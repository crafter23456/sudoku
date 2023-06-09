<?php
include_once 'connections.php';
$showPopup = false;
$message = "";

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit();
}

if (isset($_POST['submit'])) {
    $password = $_POST['password'];
    $username = $_POST['username'];
    if (!isset($_SESSION['username'])) {
        login($username, $password);
        $showPopup = true;
    }
}

if (isset($_POST['register'])) {
    $password = $_POST['password'];
    $username = $_POST['username'];
    if (!isset($_SESSION['username'])) {
        register($username, $password);
        $showPopup = true;
    }

}

function login($username, $password) {
    global $message;
    $sql = "SELECT * FROM loginData WHERE username = '$username'";
    $result = getConn()->query($sql);
    $row = $result->fetch_assoc();
    if (password_verify($password, $row['password'])) {
        $_SESSION['username'] = $username;
        $message = "Login successful!";
        $loginLogout = "Logout";
    } else {
        $message = "Invalid username or password!";
    }
    getConn()->close();
    return $message;
}

function register($username, $password) {
    global $message;
    $login = "SELECT * FROM loginData WHERE username = '$username'";
    $result = getConn()->query($login);

    if ($result->num_rows == 0) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO loginData (username, password) VALUES ('$username', '$hashedPassword')";
        $registrationResult = getConn()->query($sql);
        
        if ($registrationResult) {
            $_SESSION['username'] = $username;
            $message = "Registration successful!";
        } else {
            $message = "Registration failed. Please try again.";
        }
    } else {
        $message = "Username already exists!";
    }
    getConn()->close();
    return $message;
}
?>