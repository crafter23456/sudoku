<?php
include_once 'connections.php';
$showPopup = false;
$conn = getConn();
$message = '';

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit();
}

if ((isset($_POST['submit']) || isset($_POST['register'])) && !isset($_SESSION['username'])) {
    $password = $_POST['password'];
    $username = $_POST['username'];
    $showPopup = true;
    if (isset($_POST['submit'])) {
        $message = login($username, $password, $conn);
    } elseif (isset($_POST['register'])) {
        if (validateUsername($username) && validatePassword($password)) {
            $message = register($username, $password, $conn);
        } else $message = "Username: letters and numbers only. Password: min 8 characters, upper and lower case letters, and one number.";
    }
}

function validateUsername($username) {
    return ctype_alnum($username);
}

function validatePassword($password) {
    return strlen($password) >= 8 && preg_match('/(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])/', $password);
}

function login($username, $password, $conn) {
    $sql = "SELECT password FROM loginData WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    if (password_verify($password, $row['password'])) {
        $_SESSION['username'] = $username;
        $message = "Login successful!";
        $loginLogout = "Logout";
    } else {
        $message = "Invalid username or password!";
    }
    $stmt->close();
    return $message;
}

function register($username, $password, $conn) {
    $login = "SELECT username FROM loginData WHERE username = ?";
    $stmt = $conn->prepare($login);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows == 0) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO loginData (username, password) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $hashedPassword);
        $registrationResult = $stmt->execute();
        if ($registrationResult) {
            $_SESSION['username'] = $username;
            $message = "Registration successful!";
        } else {
            $message = "Registration failed. Please try again.";
        }
    } else {
        $message = "Username already exists!";
    }
    $stmt->close();
    return $message;
}
$conn->close();
?>