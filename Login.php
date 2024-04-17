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


    $sql = "SELECT * FROM uzivatel WHERE Email_Uzivatele = '$email' AND Heslo_Uzivatele = '$heslo'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
   
        session_start(); 
        $row = $result->fetch_assoc();
        $_SESSION['id_Uzivatele'] = $row['Id_Uzivatele']; 

        header("Location: Main.php");
        exit(); 
    } else {
        echo '<script>alert("Neplatné přihlašovací údaje.");</script>';
    }

    $conn->close();
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Přihlášení</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>

<body>
    <div class="login-container">
        <div class="login-header">
            <h1>Přihlášení</h1>
        </div>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="login-form">
                <input type="text" name="Email" placeholder="Email">
                <input type="password" name="heslo" placeholder="Heslo" required>
                <button type="submit">Přihlásit se</button>
            </div>
            <div class="login-links">
                <a href="Register.php">Registrace</a>
            </div>
        </form>
    </div>
</body>

</html>