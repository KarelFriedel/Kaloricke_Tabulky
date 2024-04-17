<!DOCTYPE html>
<html>
<head>
<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["Email"];
    $heslo = md5($_POST["heslo"]); 

    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "kaloricke_tabulky";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Chyba připojení k databázi: " . $conn->connect_error);
    }

    $sql = "INSERT INTO uzivatel (Email_Uzivatele, Heslo_Uzivatele) VALUES ('$email', '$heslo')";

    if ($conn->query($sql) === TRUE) {
        header("Location: Nastaveni.php");
    } else {
        echo "Chyba při registraci: " . $conn->error;
    }

    $conn->close();
}
?>
<title>Registrace</title>
<link rel="stylesheet" type="text/css" href="style.css">
<meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<div class="login-container">
    <div class="login-header">
        <h1>Registrace</h1>
    </div>
    <form method="post" action="">
        <div class="login-form">
            <input type="email" name="Email" placeholder="Email">
            <input type="password" name="heslo" placeholder="Heslo" required>
            <button type="submit">Registrovat se</button>
        </div>
        <div class="login-links">
            <a href="Login.php">Již máte účet?</a>
        </div>
    </form>
</div>
</body>
</html>