<?php

$servername = "localhost"; 
$username = "root"; 
$password = ""; 
$dbname = "kaloricke_tabulky"; 

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $stmt = $conn->prepare("INSERT INTO potraviny (Nazev_Potraviny, Energeticka_Hodnota, Bílkoviny, Sacharidy, Tuky, Vláknina) 
                                VALUES (:nazev, :energie, :bilkoviny, :sacharidy, :tuky, :vlaknina)");

        $stmt->bindParam(':nazev', $nazev);
        $stmt->bindParam(':energie', $energie);
        $stmt->bindParam(':bilkoviny', $bilkoviny);
        $stmt->bindParam(':sacharidy', $sacharidy);
        $stmt->bindParam(':tuky', $tuky);
        $stmt->bindParam(':vlaknina', $vlaknina);

        $nazev = $_POST['nazev'];
        $energie = $_POST['energie'];
        $bilkoviny = $_POST['bilkoviny'];
        $sacharidy = $_POST['sacharidy'];
        $tuky = $_POST['tuky'];
        $vlaknina = $_POST['vlaknina'];

        $stmt->execute();

        // Přesměrování uživatele na main.php po úspěšném vložení dat
        header("Location: main.php");
        exit;
    }
} catch(PDOException $e) {
    echo "Chyba: " . $e->getMessage();
}

$conn = null;
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Přidat potravinu</title>
    <style>
    body {
        background-color: #078f5be1;
        font-family: Arial, sans-serif;
    }

    .login-container {
        background-color: #fff;
        max-width: 60%; 
        width: 25%;
        margin: 0 auto;
        padding: 20px;
        border-radius: 5px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }

    .login-header {
        text-align: center;
    }

    .login-header h1 {
        color: #008000;
    }

    .login-form input, .login-form select {
        width: 80%; /* Reduced width for centering */
        margin: 10px 10%; /* Added horizontal margin for centering */
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 5px;
        display: block; /* Ensures that inputs are block level to take the width correctly */
    }

    .login-form button {
        width: 80%; /* Consistent width with inputs */
        height: 50px; /* Increased height for better usability */
        margin: 10px 10%; /* Centering button */
        padding: 12px 10px; /* Increased padding for a better tactile feel */
        font-size: 18px; /* Larger font size for better visibility */
        background-color: #008000;
        color: #fff;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }

    .error {
        color: red;
    }
</style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>Přidat potravinu</h1>
        </div>
        <form class="login-form" action="" method="post">
            <label for="nazev">Název potraviny:</label><br>
            <input type="text" id="nazev" name="nazev" required><br><br>
            <label for="energie">Energetická hodnota (kcal):</label><br>
            <input type="number" id="energie" name="energie" min="0" required><br><br>
            <label for="bilkoviny">Bílkoviny (g):</label><br>
            <input type="number" id="bilkoviny" name="bilkoviny" min="0"><br><br>
            <label for="sacharidy">Sacharidy (g):</label><br>
            <input type="number" id="sacharidy" name="sacharidy" min="0"><br><br>
            <label for="tuky">Tuky (g):</label><br>
            <input type="number" id="tuky" name="tuky" min="0"><br><br>
            <label for="vlaknina">Vláknina (g):</label><br>
            <input type="number" id="vlaknina" name="vlaknina" min="0"><br><br>
            <button type="submit">Přidat potravinu</button>
        </form>
    </div>
</body>
</html>
