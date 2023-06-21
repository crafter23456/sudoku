<?php
session_start();
include 'login.php';
include 'score.php';
?>
<html>
  <head>
    <link rel="stylesheet" href="style.css">
    <title>Sudoku</title>
    <meta name="viewport" content="user-scalable=no">
  </head>
  <body>
    <header>
      <link rel="preconnect" href="https://fonts.googleapis.com">
      <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
      <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
      <h1><a href="index.php">Sudoku</a></h1>
      <span id="sudokuIdElement"></span>
      <ul>
        <li><button id="scoreboard" onclick="location.href='ranking.php'">Ranking</button></li>
        <li id="loginName"><?php echo isset($_SESSION['username']) ? $_SESSION['username'] : ''; ?></li>
        <li>
          <a id="loginButton" href="<?php echo isset($_SESSION['username']) ? 'index.php?logout' : '#'; ?>" onclick="showLoginPopup()">
          <?php echo isset($_SESSION['username']) ? 'Logout' : 'Login'; ?>
          </a>
        </li>
      </ul>
    </header>
    <main>
      <div id="overlay"></div>
      <div class="popup-container popup-hidden" id="popupContainer">
        <div class="popup-content">
          <p id="popup-message"></p>
          <button id="popup-close">Schließen</button>
        </div>
      </div>
      <div class="popup-container popup-hidden" id="loginPopup">
        <div class="login-container">
          <h1> <?php echo isset($loginLogout) ? $loginLogout : 'Login'; ?> </h1>
          <form action="" method="POST">
            <label for="username">Benutzername:</label>
            <input type="text" id="username" name="username" required>
            <label for="password">Passwort:</label>
            <input type="password" id="password" name="password" required>
            <input type="submit" name="submit" value="Anmelden">
            <b id="registerAd">Neu hier?</b>
            <input type="submit" id="register" name="register" value="Registrieren">
          </form>
        </div>
      </div>
      <?php include $content; ?>
    </main>
    <footer> &copy; 2023 Sudoku-Website. Alle Rechte vorbehalten. </footer>
  </body>
</html>

<script>
const loginPopup = document.getElementById("loginPopup");
const closeButton = document.getElementById("popup-close");
const popupContainer = document.getElementById("popupContainer");

function showLoginPopup() {
    <?php if (!isset($_SESSION['username'])): ?>
        loginPopup.classList.toggle("popup-hidden");
    <?php endif; ?>
}

function showPopup(message) {
    const popUpMessage = document.getElementById("popup-message");
    popUpMessage.textContent = message;
    popupContainer.classList.toggle("popup-hidden");
}

function closePopup() {
    popupContainer.classList.toggle("popup-hidden");
}

// Popup schließen, wenn auf den Schließen-Button geklickt wird
closeButton.addEventListener("click", closePopup);

// Popup & LoginField schließen, wenn irgendwo hingeklickt wird
window.addEventListener("mousedown", function(event) {
    if (event.target == popupContainer) closePopup();
    if (event.target == loginPopup) loginPopup.classList.toggle("popup-hidden");
});

function receiveMessagePopup() {
    showPopup(<?php echo json_encode($message); ?>);
}
</script>

<?php
if ($showPopup) {
    echo "<script>receiveMessagePopup();</script>";
    $showPopup = false;
}
?>