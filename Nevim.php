<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vytvořit recept</title>
</head>
<body>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #078f5be1;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        input[type="text"], textarea, select {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        input[type="submit"], button {
            background-color: #4CAF50;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        input[type="submit"]:hover, button:hover {
            background-color: #45a049;
        }
        .input-group {
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
        }
        .input-group button {
            width: 20%;
        }
    </style>

    <div class="container">
        <h2>Vytvořit nový recept</h2>
        <form action="Nevim.php" method="post">
            <label for="nazev">Název receptu:</label>
            <input type="text" id="nazev" name="nazev" required>
            <label for="postup">Postup přípravy:</label>
            <textarea id="postup" name="postup" rows="6" required></textarea>
            <div id="potraviny">
                <div class="input-group">
                    <input type="text" name="potravina[]" id="potravina" list="potravinyList" placeholder="Název potraviny" required>
                    <datalist id="potravinyList">
                    <?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "kaloricke_tabulky";

$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nazev = $_POST["nazev"];
    $postup = $_POST["postup"];
    $potravina = $_POST["potravina"];
    $mnozstvi = $_POST["mnozstvi"];


    $sql = "INSERT INTO Recepty (Nazev, Postup) VALUES ('$nazev', '$postup')";
    $conn->query($sql);

    
    $recept_id = $conn->insert_id;

    $celkova_energie = 0.0;
    $celkove_bilkoviny = 0.0;
    $celkove_sacharidy = 0.0;
    $celkove_tuky = 0.0;
    $celkova_vlaknina = 0.0;

    for ($i = 0; $i < count($potravina); $i++) {
        $nazev_potraviny = $potravina[$i];
        $mnozstvi_potraviny = $mnozstvi[$i];

        $sql_potravina = "SELECT Id_Potraviny, Energeticka_Hodnota, Bílkoviny, Sacharidy, Tuky, Vláknina FROM Potraviny WHERE Nazev_Potraviny = '$nazev_potraviny'";
        $result = $conn->query($sql_potravina);
        $row = $result->fetch_assoc();
        $potravina_id = $row["Id_Potraviny"];
        $energie_potraviny = $row["Energeticka_Hodnota"];
        $bilkoviny_potraviny = $row["Bílkoviny"];
        $sacharidy_potraviny = $row["Sacharidy"];
        $tuky_potraviny = $row["Tuky"];
        $vlaknina_potraviny = $row["Vláknina"];

        $celkova_energie += $energie_potraviny * ($mnozstvi_potraviny / 100);
        $celkove_bilkoviny += $bilkoviny_potraviny * ($mnozstvi_potraviny / 100);
        $celkove_sacharidy += $sacharidy_potraviny * ($mnozstvi_potraviny / 100);
        $celkove_tuky += $tuky_potraviny * ($mnozstvi_potraviny / 100);
        $celkova_vlaknina += $vlaknina_potraviny * ($mnozstvi_potraviny / 100);

        $sql_recepty_potraviny = "INSERT INTO Recepty_Potraviny (Recept_ID, Potravina_ID, Mnozstvi_g) VALUES ('$recept_id', '$potravina_id', '$mnozstvi_potraviny')";
        $conn->query($sql_recepty_potraviny);
    }

   
    $kalorie_na_100g = (4 * $celkove_bilkoviny) + (4 * $celkove_sacharidy) + (9 * $celkove_tuky) + (2 * $celkova_vlaknina);

    $sql_nova_potravina = "INSERT INTO Potraviny (Nazev_Potraviny, Energeticka_Hodnota, Bílkoviny, Sacharidy, Tuky, Vláknina) VALUES ('$nazev', '$kalorie_na_100g', '$celkove_bilkoviny', '$celkove_sacharidy', '$celkove_tuky', '$celkova_vlaknina')";
    $conn->query($sql_nova_potravina);

    echo "Recept byl úspěšně uložen.";
} else {
    echo "Něco se pokazilo při odesílání formuláře.";
}

$conn->close();
?>

                    </datalist>
                    <input type="text" name="mnozstvi[]" placeholder="Množství (g)" required>
                    <button type="button" onclick="removeFood(this)">Odebrat</button>
                </div>
            </div>
            <button type="button" onclick="addFood()">Přidat potravinu</button>
            <input type="submit" value="Vytvořit recept">
        </form>
        <br>
        <form action="UpravaR.php" method="get">
            <input type="submit" value="Upravit recepty">
        </form>
    </div>

    <script>
        function addFood() {
            var potravinyDiv = document.getElementById('potraviny');
            var inputGroup = document.createElement('div');
            inputGroup.classList.add('input-group');
            inputGroup.innerHTML = `
                <input type="text" name="potravina[]" id="potravina" list="potravinyList" placeholder="Název potraviny" required>
                <input type="text" name="mnozstvi[]" placeholder="Množství (g)" required>
                <button type="button" onclick="removeFood(this)">Odebrat</button>
            `;
            potravinyDiv.appendChild(inputGroup);
        }

        function removeFood(button) {
            var inputGroup = button.parentElement;
            inputGroup.remove();
        }
    </script>
</body>
</html>
