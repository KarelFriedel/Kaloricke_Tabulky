<?php
session_start();
if (!isset($_SESSION['id_Uzivatele'])) {
    header("Location: login.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "kaloricke_tabulky";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Chyba připojení k databázi: " . $conn->connect_error);
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if(isset($_POST["nazevAktivity"]) && isset($_POST["spaleneKalorie"]) && isset($_POST["datum"])) {
        $nazevAktivity = $_POST["nazevAktivity"];
        $spaleneKalorie = $_POST["spaleneKalorie"];
        $datum = $_POST["datum"];

        if(!empty($nazevAktivity) && !empty($spaleneKalorie) && !empty($datum)) {
            pridejAktivituACviceni($nazevAktivity, $spaleneKalorie, $datum);
        } else {
            echo "Prosím vyplňte všechny pole.";
        }
    } else {
        echo "Něco se pokazilo s přijetím dat.";
    }
}

function pridejAktivituACviceni($nazevAktivity, $spaleneKalorie, $datum)
{
    global $conn;

    $sqlAktivity = "INSERT INTO aktivity (Nazev_Aktivity, Spalene_kalorie)
                    VALUES (?, ?)";
    $stmtAktivity = $conn->prepare($sqlAktivity);

    if (!$stmtAktivity) {
        die("Chyba při přípravě dotazu pro vkládání aktivity: " . $conn->error);
    }

    $stmtAktivity->bind_param("sd", $nazevAktivity, $spaleneKalorie);

    try {
        if ($stmtAktivity->execute()) {
            $idAktivity = $stmtAktivity->insert_id; // Získání ID nově vložené aktivity

            // Vložení záznamu do tabulky cviceni s použitím zadaného data a doby cvičení nastavené na 1
            $sqlCviceni = "INSERT INTO cviceni (datum, id_aktivity, doba_cviceni, Id_Uzivatele)
                           VALUES (?, ?, ?, ?)";
            $stmtCviceni = $conn->prepare($sqlCviceni);

            if (!$stmtCviceni) {
                throw new Exception("Chyba při přípravě dotazu pro vkládání cvičení: " . $conn->error);
            }

            $idUzivatele = $_SESSION['id_Uzivatele']; // ID aktuálního uživatele
            $dobaCviceni = 1; // Doba cvičení je vždy 1 minuta

            $stmtCviceni->bind_param("sddi", $datum, $idAktivity, $dobaCviceni, $idUzivatele);

            if ($stmtCviceni->execute()) {
                header("Location: Main.php"); 
                exit();
            } else {
                throw new Exception("Chyba při vkládání cvičení: " . $stmtCviceni->error);
            }
        } else {
            throw new Exception("Chyba při vkládání aktivity: " . $stmtAktivity->error);
        }
    } catch (Exception $e) {
        echo "Chyba: " . $e->getMessage();
    }

    $stmtAktivity->close();
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Přidat potravinu</title>
<style>
    body, html {
        margin: 0;
        padding: 0;
        height: 100%;
        width: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
        background-color: #078f5be1;
        font-family: Arial, sans-serif;
    }
    .container {
        width: 100%;
        max-width: 500px; /* Adjust width as needed */
        padding: 20px;
        background-color: #fff;
        border: 1px solid #ccc;
        border-radius: 5px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
    }
    h1 {
        text-align: center;
        color: #008000; /* Zelená barva nadpisu */
        margin-bottom: 20px; /* Dodává mezery mezi nadpisem a formulářem */
    }
    input[type="text"], input[type="number"] {
        width: 100%;
        padding: 10px;
        margin: 5px 0 20px;
        box-sizing: border-box;
        border: 1px solid #ccc;
        border-radius: 5px;
    }
    .form-submit-button {
        width: 100%;
        padding: 10px 0;
        background-color: #4CAF50;
        color: #fff;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 16px;
    }
    .form-submit-button:hover {
        background-color: #45a049;
    }
</style>
</head>
<body>

<div class="container">
    <h1>Nová potravina</h1>
    <form action="" method="post">
        <label for="nazev">Název potraviny:</label>
        <input type="text" id="nazev" name="nazev" required>
        <label for="energie">Energetická hodnota (kcal):</label>
        <input type="number" id="energie" name="energie" required>
        <label for="bilkoviny">Bílkoviny (g):</label>
        <input type="number" id="bilkoviny" name="bilkoviny">
        <label for="sacharidy">Sacharidy (g):</label>
        <input type="number" id="sacharidy" name="sacharidy">
        <label for="tuky">Tuky (g):</label>
        <input type="number" id="tuky" name="tuky">
        <label for="vlaknina">Vláknina (g):</label>
        <input type="number" id="vlaknina" name="vlaknina">
        <button type="submit" class="form-submit-button">Přidat potravinu</button>
    </form>
</div>

</body>
</html>
