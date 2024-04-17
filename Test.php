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

$idUzivatele = $_SESSION['id_Uzivatele'];

$sql = "SELECT u.Id_Uzivatele, n.Id_Nastaveni, n.Cile, n.Vaha AS Aktualni_Vaha
        FROM uzivatel u
        JOIN nastaveni n ON u.Nastaveni_Uzivatele = n.Id_Nastaveni
        WHERE u.Id_Uzivatele = $idUzivatele";

$result = $conn->query($sql);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nastaveni_id = $_POST["nastaveni_id"];
    $new_cile = $_POST["new_cile_" . $nastaveni_id];
    $nova_vaha = $_POST["nova_vaha_" . $nastaveni_id];

    $update_query = "UPDATE nastaveni SET Cile = '$new_cile', Vaha = '$nova_vaha' WHERE Id_Nastaveni = $nastaveni_id";
    $conn->query($update_query);
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="cs">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Změna cíle</title>
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
            max-width: 500px;
            padding: 20px;
            background-color: #fff;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
        }
        h1 {
            text-align: center;
            color: #008000;
            margin-bottom: 20px;
        }
        label, p {
            text-align: center;
            display: block;
            margin-top: 20px;
        }
        input[type="text"], select {
            width: 100%;
            padding: 10px;
            margin: 5px 0 20px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .zapis-button {
            width: 100%;
            padding: 10px 0;
            background-color: #4CAF50;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .zapis-button:hover {
            background-color: #45a049;
        }
    </style>
</head>

<body>
<div class="container">
    <h1>Změna cíle</h1>
    <?php
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
    ?>
          
            <p>Aktuální cíl: <?php echo $row["Cile"]; ?></p>
            <p>Aktuální váha: <?php echo $row["Aktualni_Vaha"]; ?></p>

            <form method="post" action="">
                <input type="hidden" name="nastaveni_id" value="<?php echo $row["Id_Nastaveni"]; ?>">
                <label for="new_cile">Nový cíl:</label>
                <select id="new_cile" name="new_cile_<?php echo $row["Id_Nastaveni"]; ?>" required>
                    <option value="Zhubout">Zhubout</option>
                    <option value="Nabrat svalovou hmotu">Nabrat svalovou hmotu</option>
                    <option value="Zůstat fit">Zůstat fit</option>
                </select>
                <label for="nova_vaha_<?php echo $row["Id_Nastaveni"]; ?>">Nová váha:</label>
                <input type="text" id="nova_vaha_<?php echo $row["Id_Nastaveni"]; ?>" name="nova_vaha_<?php echo $row["Id_Nastaveni"]; ?>" value="<?php echo $row["Aktualni_Vaha"]; ?>" required>

                <button type="submit" class="zapis-button">Uložit změny v cíli</button>
            </form>
    <?php
        }
    } else {
        echo "Žádná data k zobrazení.";
    }
    ?>
</div>
</body>
<a  href='main.php'> <button >Přejít na hlavní stránku</button></a>
</html>
