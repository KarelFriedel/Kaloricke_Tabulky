<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "kaloricke_tabulky";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Chyba připojení k databázi: " . $conn->connect_error);
}

function nactiPotraviny()
{
    global $conn;

    $result = $conn->query("SELECT * FROM potraviny");

    $potraviny = array();
    while ($row = $result->fetch_assoc()) {
        $potraviny[] = $row;
    }

    return $potraviny;
}

function pridejPotravinu($datum, $cast, $nazevPotraviny, $gramaz)
{
    global $conn;

    session_start();

    if (!isset($_SESSION['id_Uzivatele'])) {
        header("Location: login.php");
        exit();
    }

    $idUzivatele = $_SESSION['id_Uzivatele'];

    $stmtCast = $conn->prepare("SELECT ID_Casti FROM cast_dne WHERE Nazev = ?");
    $stmtCast->bind_param("s", $cast);
    $stmtCast->execute();
    $resultCast = $stmtCast->get_result();

    if ($rowCast = $resultCast->fetch_assoc()) {
        $idCasti = $rowCast['ID_Casti'];
    } else {
        echo "Error: část dne not found.";
        return;
    }

    $stmtCast->close();

    $sql = "INSERT INTO jidlo (datum, potravina, gramaz, cast_dne, Id_Uzivatele)
            VALUES (?, (SELECT Id_Potraviny FROM potraviny WHERE Nazev_Potraviny = ? LIMIT 1), ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Chyba při přípravě dotazu: " . $conn->error);
    }

    $stmt->bind_param("ssiii", $datum, $nazevPotraviny, $gramaz, $idCasti, $idUzivatele);

    if ($stmt->execute()) {
        echo "Potravina byla úspěšně přidána.";
    } else {
        echo "Chyba: " . $stmt->error;
    }

    $stmt->close();
}

$potraviny = nactiPotraviny();


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit_pridat_jidlo"])) {

    $datum = isset($_POST["datum"]) ? $_POST["datum"] : date('Y-m-d');
    $cast = isset($_POST["cast"]) ? $_POST["cast"] : "";
    $nazevPotraviny = isset($_POST["nazevPotraviny"]) ? $_POST["nazevPotraviny"] : "";
    $gramaz = isset($_POST["gramaz"]) ? $_POST["gramaz"] : "";

    pridejPotravinu($datum, $cast, $nazevPotraviny, $gramaz);
}
?>

<!DOCTYPE html>
<html lang="cs">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="Zapis.css">
    <title>Přidat jídlo</title>
</head>

<body>

<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="zapis-form">
            <h2>Přidat jídlo</h2>

            <label for="datum" class="zapis-label">Datum:</label>
            <input type="date" name="datum" value="<?php echo date('Y-m-d'); ?>" class="zapis-input" required>

            <label for="cast" class="zapis-label">Část dne:</label>
            <select name="cast" class="zapis-select" required>
                <?php
                $result = $conn->query("SELECT * FROM cast_dne");

                while ($row = $result->fetch_assoc()) {
                    $selected = ($row["Nazev"] === $cast) ? "selected" : "";
                    echo "<option value='{$row["Nazev"]}' $selected>{$row["Nazev"]}</option>";
                }
                ?>
            </select>

            <label for="nazevPotraviny" class="zapis-label">Název potraviny:</label>
            <select name="nazevPotraviny" class="zapis-select" required>
                <?php
                foreach ($potraviny as $potravina) {
                    echo "<option value='{$potravina["Nazev_Potraviny"]}'>{$potravina["Nazev_Potraviny"]}</option>";
                }
                ?>
            </select>

            <label for="gramaz" class="zapis-label">Gramáž:</label>
            <input type="text" name="gramaz" class="zapis-input" required>

            <button type="submit" name="submit_pridat_jidlo" class="zapis-button">Přidat jídlo</button>
        </form>
</body>

</html>