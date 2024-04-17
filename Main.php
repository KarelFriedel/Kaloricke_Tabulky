<?php
session_start();
if (!isset($_SESSION['id_Uzivatele'])) {
    header("Location: login.php");
    exit();
    print_r($_SESSION);
}

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

function pridejPotravinu($datum, $cast, $nazevPotraviny, $gramaz) {
    global $conn;

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
        header("Location: Main.php"); 
        exit();
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

    $idUzivatele = $_SESSION['id_Uzivatele'];

    $sql = "INSERT INTO napoje (datum, piti, gramaz, cast_dne, Id_Uzivatele)
            VALUES (?, (SELECT id_Piti FROM piti WHERE Nazev_Piti = ? LIMIT 1), ?, (SELECT ID_Casti FROM cast_dne WHERE Nazev = ? LIMIT 1), ?)";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Chyba při přípravě dotazu: " . $conn->error);
    }

    $stmt->bind_param("ssisi", $datum, $nazevPiti, $gramaz, $cast, $idUzivatele);

    if ($stmt->execute()) {
        $stmt->close();
        header("Location: main.php"); 
        exit();
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

function nactiNazvyAktivit()
{
    global $conn;

    $result = $conn->query("SELECT * FROM aktivity WHERE Id_Aktivity BETWEEN 1 AND 32;");

    $nazvyAktivit = array();
    while ($row = $result->fetch_assoc()) {
        $nazvyAktivit[] = $row['Nazev_Aktivity'];
    }

    return $nazvyAktivit;
}

$nazvyAktivit = nactiNazvyAktivit();

function pridejAktivitu($nazevAktivity, $casMinut, $datum)
{
    global $conn;

    $sql = "INSERT INTO cviceni (id_aktivity, doba_cviceni, datum, Id_Uzivatele)
    VALUES ((SELECT Id_Aktivity FROM aktivity WHERE Nazev_Aktivity = ? LIMIT 1), ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Chyba při přípravě dotazu: " . $conn->error);
    }

    $stmt->bind_param("sisi", $nazevAktivity, $casMinut, $datum, $_SESSION['id_Uzivatele']);

    if ($stmt->execute()) {
        $stmt->close();
        header("Location: " . $_SERVER['PHP_SELF']); // Přesměrování na stejnou stránku
        exit();
    } else {
        echo "Chyba: " . $stmt->error;
    }

    $stmt->close();    
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit_pridat_aktivitu"])) {
    $nazevAktivity = isset($_POST["nazevAktivity"]) ? $_POST["nazevAktivity"] : "";
    $casMinut = isset($_POST["casMinut"]) ? $_POST["casMinut"] : "";
    $datum = isset($_POST["datum"]) ? $_POST["datum"] : date("Y-m-d");

    pridejAktivitu($nazevAktivity, $casMinut, $datum);
}

$userId = $_SESSION['id_Uzivatele'];

$sql1 = "SELECT u.Nastaveni_Uzivatele 
         FROM uzivatel u
         WHERE u.Id_Uzivatele = ?";

$stmt1 = $conn->prepare($sql1);
if (!$stmt1) {
    die("Error preparing statement: " . $conn->error);
}

$stmt1->bind_param("i", $userId);

if (!$stmt1->execute()) {
    die("Error executing statement: " . $stmt1->error);
}

$stmt1->bind_result($nastaveniId);

if (!$stmt1->fetch()) {
    die("Error fetching Nastaveni_Uzivatele");
}

$stmt1->close();

$sql2 = "SELECT n.Cil_Snezenych_Kalorii 
         FROM nastaveni n
         WHERE n.Id_Nastaveni = ?";

$stmt2 = $conn->prepare($sql2);
if (!$stmt2) {
    die("Error preparing statement: " . $conn->error);
}

$stmt2->bind_param("i", $nastaveniId);

if (!$stmt2->execute()) {
    die("Error executing statement: " . $stmt2->error);
}

$stmt2->bind_result($totalCaloriesGoal);

if ($stmt2->fetch()) {
   
} else {
    echo "Error fetching Cil_Snezenych_Kalorii";
}

$stmt2->close();

$sql3 = "SELECT n.pitny_rezim 
         FROM nastaveni n
         WHERE n.Id_Nastaveni = ?";

$stmt3 = $conn->prepare($sql3);
if (!$stmt3) {
    die("Error preparing statement: " . $conn->error);
}

$stmt3->bind_param("i", $nastaveniId);

if (!$stmt3->execute()) {
    die("Error executing statement: " . $stmt3->error);
}

$stmt3->bind_result($pitnyRezim);

if ($stmt3->fetch()) {
    
} else {
    echo "Error fetching pitny_rezim";
}

$stmt3->close();

$userId = $_SESSION['id_Uzivatele'];

$sqlMakro = "SELECT m.Bilkoviny, m.Sacharidy, m.Tuky, m.Vlaknina 
             FROM makra m
             JOIN uzivatel u ON m.ID_Makra = u.Makra_Uzivatele
             WHERE u.Id_Uzivatele = ?";
$stmtMakro = $conn->prepare($sqlMakro);
$stmtMakro->bind_param("i", $userId);

if ($stmtMakro->execute()) {
    $resultMakro = $stmtMakro->get_result();
    $rowMakro = $resultMakro->fetch_assoc();

    if ($rowMakro) {
        $bilkoviny = $rowMakro['Bilkoviny'];
        $sacharidy = $rowMakro['Sacharidy'];
        $tuky = $rowMakro['Tuky'];
        $vlaknina = $rowMakro['Vlaknina'];
    } else {
        echo "Žádná data nebyla nalezena pro makroživiny.";
    }
} else {
    echo "Chyba při vykonávání dotazu na makroživiny: " . $stmtMakro->error;
}

$stmtMakro->close();

$selectedDate = isset($_GET['datum']) ? $_GET['datum'] : date('Y-m-d');

$idUzivatele = $_SESSION['id_Uzivatele'];

$sql = "CALL SpocitejCelkovouEnergetickouHodnotuJidla(?, ?, @celkovaEnergetickaHodnota)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $idUzivatele, $selectedDate);

if ($stmt->execute()) {
    $result = $conn->query("SELECT @celkovaEnergetickaHodnota AS celkovaEnergetickaHodnota");
    $row = $result->fetch_assoc();

    if ($row) {
        $celkovaEnergetickaHodnota = $row['celkovaEnergetickaHodnota'];
    } else {
        echo "Žádná data nebyla nalezena.";
    }
} else {
    echo "Chyba při vykonávání procedury: " . $stmt->error;
}

$stmt->close();
$selectedDate = isset($_GET['datum']) ? $_GET['datum'] : date('Y-m-d');

$idUzivatele = $_SESSION['id_Uzivatele'];

$sql = "CALL SpocitejSpaleneKalorieDne(?, ?, @SpaleneKalorie)";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Chyba při přípravě dotazu: " . $conn->error);
}

$stmt->bind_param("si", $selectedDate, $idUzivatele);

if (!$stmt->execute()) {
    die("Chyba při vykonávání dotazu: " . $stmt->error);
}

// Získání výsledku z výstupní proměnné
$result = $conn->query("SELECT @SpaleneKalorie AS SpaleneKalorie");
$row = $result->fetch_assoc();
$SpaleneKalorie = $row['SpaleneKalorie'];



$stmt->close();



$idUzivatele = $_SESSION['id_Uzivatele'];
$sqlMakro = "CALL SpocitejCelkoveMakroJidla(?, ?, @celkoveBilkoviny, @celkoveSacharidy, @celkoveTuky, @celkovaVlaknina)";
$stmtMakro = $conn->prepare($sqlMakro);
$stmtMakro->bind_param("is", $idUzivatele, $selectedDate);

if ($stmtMakro->execute()) {
    $resultMakro = $conn->query("SELECT @celkoveBilkoviny AS celkoveBilkoviny, @celkoveSacharidy AS celkoveSacharidy, @celkoveTuky AS celkoveTuky, @celkovaVlaknina AS celkovaVlaknina");
    $rowMakro = $resultMakro->fetch_assoc();

    if ($rowMakro) {
        $celkoveBilkoviny = $rowMakro['celkoveBilkoviny'];
        $celkoveSacharidy = $rowMakro['celkoveSacharidy'];
        $celkoveTuky = $rowMakro['celkoveTuky'];
        $celkovaVlaknina = $rowMakro['celkovaVlaknina'];
    } else {
        echo "Žádná data nebyla nalezena pro makroživiny.";
    }
} else {
    echo "Chyba při vykonávání procedury pro makroživiny: " . $stmtMakro->error;
}

$stmtMakro->close();

$idUzivatele = $_SESSION['id_Uzivatele'];

$sqlMakroNapoje = "CALL SpocitejCelkoveMakroNapoje(?, ?, @celkoveBilkoviny, @celkoveSacharidy, @celkoveTuky, @celkovaVlaknina)";
$stmtMakroNapoje = $conn->prepare($sqlMakroNapoje);
$stmtMakroNapoje->bind_param("is", $idUzivatele, $selectedDate);

if ($stmtMakroNapoje->execute()) {
    // Získání výstupních hodnot
    $sqlGetOutput = "SELECT @celkoveBilkoviny AS celkoveBilkoviny, @celkoveSacharidy AS celkoveSacharidy, @celkoveTuky AS celkoveTuky, @celkovaVlaknina AS celkovaVlaknina";
    $result = $conn->query($sqlGetOutput);
    $row = $result->fetch_assoc();

    $celkoveBilkovinyNapoje = $row['celkoveBilkoviny'];
    $celkoveSacharidyNapoje = $row['celkoveSacharidy'];
    $celkoveTukyNapoje = $row['celkoveTuky'];
    $celkovaVlakninaNapoje = $row['celkovaVlaknina'];

    $result->free();
} else {
    echo "Chyba při vykonávání procedury pro makroživiny v nápojích: " . $stmtMakroNapoje->error;
}


$stmtMakroNapoje->close();
$sql = "CALL SpocitejCelkovouEnergetickouHodnotuPiti(?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $idUzivatele, $selectedDate);

if ($stmt->execute()) {
    // Zpracování výsledků
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row) {
        $celkovaEnergetickaHodnotaPiti = $row['celkovaEnergetickaHodnota'];
        $celkovaGramazPiti = $row['celkovaGramaz'];
        
        $result->free();
    } else {
        echo "Žádná data nebyla nalezena pro pití.";
    }
} else {
    echo "Chyba při vykonávání procedury pro pití: " . $stmt->error;
}

$stmt->close();

$vlakninag = $vlaknina / 2;
$bilkovinyg = $bilkoviny / 4;
$tukyg = $tuky / 9;
$sacharidyg = $sacharidy / 4;
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vaše jídlo</title>
    <link rel="stylesheet" href="Main.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200">
    <link rel="stylesheet" href="Zapis.css">
</head>

<body>
<form method="get" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
    <input type="date" id="datum" name="datum" value="<?php echo date('Y-m-d'); ?>" max="<?php echo date('Y-m-d'); ?>">
    <button type="submit">Zobrazit</button>
</form>

<div class="pie animate Celkem" style="--p:<?php echo intval(substr(((( $celkovaEnergetickaHodnota + $celkovaEnergetickaHodnotaPiti - $SpaleneKalorie ) / $totalCaloriesGoal) * 100), 0, 3)); ?>;--c:lightgreen"><?php echo intval(substr(((( $celkovaEnergetickaHodnota + $celkovaEnergetickaHodnotaPiti - $SpaleneKalorie) / $totalCaloriesGoal) * 100), 0, 3)); ?>%</div>

<h2>Celkem</h2>
 
<div class="pie animate Sacharidy" style="--p:<?php echo intval(substr(((( $celkoveSacharidy + $celkoveSacharidyNapoje ) / $sacharidyg) * 100), 0, 3))?>;--c:lightgreen"><?php echo intval(substr(((( $celkoveSacharidy + $celkoveSacharidyNapoje ) / $sacharidyg) * 100), 0, 3))?>%</div>
<h2>Sacharidy</h2>

<div class="pie animate Vlaknina" style="--p:<?php echo intval(substr(((( $celkovaVlaknina + $celkovaVlakninaNapoje ) / $vlakninag) * 100), 0, 3))?>;--c:lightgreen"><?php echo intval(substr(((( $celkovaVlaknina + $celkovaVlakninaNapoje ) / $vlakninag) * 100), 0, 3))?>%</div>
<h2>Vláknina</h2>

<div class="pie animate Tuky" style="--p:<?php echo intval(substr(((( $celkoveTuky  + + $celkoveTukyNapoje) / $tukyg) * 100), 0, 3))?>;--c:lightgreen"><?php echo intval(substr(((( $celkoveTuky  + + $celkoveTukyNapoje) / $tukyg) * 100), 0, 3))?>%</div>
<h2>Tuky</h2>

<div class="pie animate Bílkoviny" style="--p:<?php echo intval(substr(((( $celkoveBilkoviny + + $celkoveBilkovinyNapoje ) / $bilkovinyg) * 100), 0, 3))?>;--c:lightgreen"><?php echo intval(substr(((( $celkoveBilkoviny + + $celkoveBilkovinyNapoje ) / $bilkovinyg) * 100), 0, 3))?>%</div>
<h2>bílkoviny</h2>

<div class="pie animate " style="--p:<?php echo intval(substr(((( $celkovaGramazPiti + ($SpaleneKalorie / 2) ) /$pitnyRezim ) * 100), 0, 3)); ?>;--c:lightgreen"><?php echo intval(substr(((( $celkovaGramazPiti) /$pitnyRezim ) * 100), 0, 3)); ?> %</div>



<div class="container">
    <div class="stripe"><h2>Snídaně</h2></div>
    <div class="stripe"><h2>Dopolední svačina</h2></div>
    <div class="stripe"><h2>Oběd</h2></div>
    <div class="stripe"><h2>Odpolední svačina</h2></div>
    <div class="stripe"><h2>Večeře</h2></div>
    <div class="stripe"><h2>Aktivity</h2></div>
    
</div>


<a href="Test.php"><button class="my-button" onclick="ZmenaCile()">Změnit nastavení</button></a>
<button class="my-button" onclick="PridatJidlo()">Přidat jídlo</button>
<button class="my-button" onclick="PridatNapoj()">Přidat Nápoj</button>
<button class="my-button" onclick="OdhlasitUzivatele()">Odhlásit se</button>
<button class="my-button" onclick="PridatAktivitu()">Přidat aktivitu</button>
<a href="Potravina.php"><button class="my-button" onclick="PridatPotravinu()">Vlastní potravina</button></a>
<a href="Vlastniaktivita.php"><button class="my-button" onclick="PridatPotravinu()">Vlastní aktivita</button></a>
<a href="Nevim.php"><button class="my-button">Přidat recept</button></a>

<div id="popup-zmena-cile" class="popup popup-zmena-cile">
     
    <button onclick="ZavriZmenaCile()">Zavřít</button>
</div>

<div id="popup-pridat-jidlo" class="popup popup-pridat-jidlo">
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
    <button onclick="ZavriPridatJidlo()">Zavřít</button>
</div>
<div id="myForm" class="popup zapis-label">
    <form action="Main.php" method="post">
        <label for="nazevPotraviny">Název potraviny:</label>
        <input type="text" id="nazevPotraviny" name="nazevPotraviny" required>
        <label for="energetickaHodnota">Energetická hodnota:</label>
        <input type="text" id="energetickaHodnota" name="energetickaHodnota" required>
        <label for="bilkoviny">Bílkoviny:</label>
        <input type="text" id="bilkoviny" name="bilkoviny">
        <label for="sacharidy">Sacharidy:</label>
        <input type="text" id="sacharidy" name="sacharidy">
        <label for="tuky">Tuky:</label>
        <input type="text" id="tuky" name="tuky">
        <label for="vlaknina">Vláknina:</label>
        <input type="text" id="vlaknina" name="vlaknina">
        <button type="submit" class="form-submit-button">Přidat potravinu</button>
        <button type="button" class="form-submit-button" onclick="ZavriPridatPotravinu()">Zavřít</button>
    </form>
</div>

<div id="popup-pridat-napoj" class="popup popup-pridat-jidlo">

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
    <button onclick="ZavriPridatNapoj()">Zavřít</button>
</div>

<div id="popup-pridat-aktivitu" class="popup popup-pridat-jidlo">
<form method="post" action="Main.php"<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="zapis-form">
<h2>Přidat aktivitu</h2>

<label for="nazevAktivity" class="zapis-label">Název aktivity:</label>
<select name="nazevAktivity" class="zapis-select" required>
    <?php
    foreach ($nazvyAktivit as $nazevAktivity) {
        echo "<option value='{$nazevAktivity}'>{$nazevAktivity}</option>";
    }
    ?>
</select>

<label for="casMinut" class="zapis-label">Doba trvání (minuty):</label>
<input type="number" name="casMinut" class="zapis-input" required min="0">

<label for="datum" class="zapis-label">Datum:</label>
<input type="date" name="datum" value="<?php echo date('Y-m-d'); ?>"class="zapis-input" required>

<button type="submit" name="submit_pridat_aktivitu" class="zapis-button">Přidat aktivitu</button>
</form>
<button onclick="ZavriPridatAktivitu()">Zavřít</button>
</div>

    <script>
        function ZmenaCile() {
            var popup = document.getElementById('popup-zmena-cile');
            popup.style.display = 'block';
        }

        function ZavriZmenaCile() {
            var popup = document.getElementById('popup-zmena-cile');
            popup.style.display = 'none';
        }

        function PridatJidlo() {
            var popup = document.getElementById('popup-pridat-jidlo');
            popup.style.display = 'block';
        }

        function ZavriPridatJidlo() {
            var popup = document.getElementById('popup-pridat-jidlo');
            popup.style.display = 'none';
        }

        function PridatNapoj() {
            var popup = document.getElementById('popup-pridat-napoj');
            popup.style.display = 'block';
        }

        function ZavriPridatNapoj() {
            var popup = document.getElementById('popup-pridat-napoj');
            popup.style.display = 'none';
        }

        function OdhlasitUzivatele() {
            window.location.href = 'Logout.php';
        }

        function PridatAktivitu() {
            var popup = document.getElementById('popup-pridat-aktivitu');
            popup.style.display = 'block';
        }

        function ZavriPridatAktivitu() {
            var popup = document.getElementById('popup-pridat-aktivitu');
            popup.style.display = 'none';
        }
       
    </script>

</body>
</html>
