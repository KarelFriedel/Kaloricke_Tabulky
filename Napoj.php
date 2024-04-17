<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "kaloricke_tabulky";

$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Chyba připojení k databázi: " . $conn->connect_error);
}


function nactiNapoje()
{
    
 
    global $conn;

    $result = $conn->query("SELECT * FROM piti");

    $napoje = array();
    while ($row = $result->fetch_assoc()) {
        $napoje[] = $row;
    }

    return $napoje;
}

function pridejNapoj($datum, $cast, $nazevPiti, $gramaz)
{
    global $conn;
    session_start();

    if (!isset($_SESSION['id_Uzivatele'])) {
        header("Location: login.php");
        exit();
    }


    $idUzivatele = $_SESSION['id_Uzivatele'];

    $sql = "INSERT INTO napoje (datum, piti, gramaz, cast_dne, Id_Uzivatele)
            VALUES (?, (SELECT id_Piti FROM piti WHERE Nazev_Piti = ? LIMIT 1), ?, (SELECT ID_Casti FROM cast_dne WHERE Nazev = ? LIMIT 1), ?)";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Chyba při přípravě dotazu: " . $conn->error);
    }

    $stmt->bind_param("ssisi", $datum, $nazevPiti, $gramaz, $cast, $idUzivatele);

    if ($stmt->execute()) {
        echo "Nápoj byl úspěšně přidán.";
    } else {
        echo "Chyba: " . $stmt->error;
    }

    $stmt->close();
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit_pridat_napoj"])) {

    $datum = isset($_POST["datum"]) ? $_POST["datum"] : date("Y-m-d");
    $cast = $_POST["cast"];
    $nazevPiti = $_POST["nazevPiti"];
    $gramaz = $_POST["gramaz"];

    pridejNapoj($datum, $cast, $nazevPiti, $gramaz);
}

$napoje = nactiNapoje();
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="Zapis.css">
    <title>Přidat pití</title>
</head>
<body>

<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="zapis-form">
    <h2 class="zapis-heading">Přidat pití</h2>

    <label for="datum" class="zapis-label">Datum:</label>
    <input type="date" name="datum" value="<?php echo date('Y-m-d'); ?>"class="zapis-input" required>

    <label for="cast" class="zapis-label">Část dne:</label>
    <select name="cast" class="zapis-select" required>
        <?php
        $result = $conn->query("SELECT * FROM cast_dne");

        while ($row = $result->fetch_assoc()) {
            echo "<option value='{$row["Nazev"]}' class='zapis-option'>{$row["Nazev"]}</option>";
        }
        ?>
    </select>

    <label for="nazevPiti" class="zapis-label">Název pití:</label>
    <select name="nazevPiti" class="zapis-select" required>
        <?php
        foreach ($napoje as $napoj) {
            echo "<option value='{$napoj["Nazev_Piti"]}' class='zapis-option'>{$napoj["Nazev_Piti"]}</option>";
        }
        ?>
    </select>

    <label for="gramaz" class="zapis-label">Množství (ml):</label>
    <input type="text" name="gramaz" class="zapis-input" required>

    <button type="submit" name="submit_pridat_napoj" class="zapis-button">Přidat pití</button>
</form>

</body>
</html>
