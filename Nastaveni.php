<!DOCTYPE html>
<html>
<head>
<?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $birthdate = $_POST["birthdate"];
        $weight = $_POST["weight"];
        $gender = $_POST["gender"];
        $goals = $_POST["goals"];
        $height = $_POST["height"]; 

        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "kaloricke_tabulky";
        
        $conn = new mysqli($servername, $username, $password, $dbname);

        if ($conn->connect_error) {
            die("Chyba připojení k databázi: " . $conn->connect_error);
        }

        $sql = "INSERT INTO nastaveni (Datum_Narozeni, Vaha, Pohlavi, Cile,Vyska) 
                VALUES ('$birthdate', '$weight', '$gender', '$goals','$height')";

        if ($conn->query($sql) === TRUE) {

            header("Location: Main.php");
            exit();
        } else {
            echo '<p class="error">Chyba při ukládání dat: ' . $conn->error . '</p>';
        }

        $conn->close();
    }
    ?>
    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <title>Registrační formulář</title>
    <link rel="stylesheet" type="text/css" href="Nastaveni.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    </head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>Nastavení účtu</h1>
        </div>
        <form method="post" action="Main.php">
            <div class="login-form">
                <label for="birthdate">Datum narození:</label>
                <input type="date" id="birthdate" name="birthdate" required>
                <label for="height">Výška (cm):</label>
                <input type="number" id="height" name="height" required min= "0">
                

                <label for="weight">Váha (kg):</label>
                <input type="number" id="weight" name="weight" step="1" required min= "0">

                <label for="gender">Pohlaví:</label>
                <select id="gender" name="gender" required>
                    <option value="muž">Muž</option>
                    <option value="žena">Žena</option>
                </select>

                <label for="goals">Cíle:</label>
                <select id="goals" name="goals" required>
                    <option value="Zhubout">Zhubout</option>
                    <option value="Nabrat svalovou hmotu">Nabrat svalovou hmotu</option>
                    <option value="Zůstat fit">Zůstat fit</option>
                </select>
                

                <button type="submit">Potvrdit</button>
            </div>
        </form>
    </div>
</body>
</html>