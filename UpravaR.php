<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upravit recept</title>
    <style>
       body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        button:hover {
            background-color: #45a049;
        }
        .popup-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }
        .popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #ffffff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }
        .popup-content {
            max-height: 300px;
            overflow-y: auto;
        }
        .close-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 20px;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="container">
        <form id="receptForm" method="post">
            <table>
                <tr>
                    <th>Název receptu</th>
                    <th>Vybrat</th>
                    <th>Smazat</th>
                </tr>
                <?php
                $servername = "localhost";
                $username = "root";
                $password = "";
                $dbname = "kaloricke_tabulky";

                $conn = new mysqli($servername, $username, $password, $dbname);
                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }

                $sql = "SELECT ID, Nazev FROM recepty";
                $result = $conn->query($sql);

                while($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row['Nazev'] . "</td>";
                    echo "<td><button type='button' onclick='showPopup(" . $row['ID'] . ")'>Upravit Recept</button></td>";
                    echo "<td><button type='submit' name='smazat_recept' value='" . $row['ID'] . "'>Smazat recept</button></td>";
                    
                    echo "</tr>";
                }
                ?>
            </table>
        </form>
    </div>

    <div id="popup" class="popup">
        <button class="close-btn" onclick="hidePopup()">×</button>
        <div class="popup-content">
            <h2>Potraviny receptu</h2>
            <table>
                <tr>
                    <th>Název potraviny</th>
                    <th>Množství (g)</th>
                </tr>
                <?php
                if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['upravit_recept'])) {
                    $receptID = $_POST['upravit_recept'];

                    $sql = "SELECT p.Nazev_Potraviny, rp.Mnozstvi_g 
                            FROM recepty_potraviny rp
                            JOIN potraviny p ON rp.Potravina_ID = p.Id_Potraviny
                            WHERE rp.Recept_ID = $receptID";
                    
                    $result = $conn->query($sql);

                    if ($result && $result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . $row['Nazev_Potraviny'] . "</td>";
                            echo "<td>" . $row['Mnozstvi_g'] . " g</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='2'>Tento recept neobsahuje žádné potraviny.</td></tr>";
                    }
                }
                ?>
            </table>
        </div>
    </div>

    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['smazat_recept'])) {
        $receptID = $_POST['smazat_recept'];

        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['smazat_recept'])) {
            $receptID = $_POST['smazat_recept'];
    
            $sql_delete_potraviny = "DELETE FROM potraviny WHERE Nazev_Potraviny IN (SELECT Nazev FROM recepty where ID = '$receptID')";
            $conn->query($sql_delete_potraviny);
    
            $sql_delete_recepty_potraviny = "DELETE FROM recepty_potraviny WHERE Recept_ID = $receptID";
            $conn->query($sql_delete_recepty_potraviny);
    
            $sql_delete_recepty = "DELETE FROM recepty WHERE ID = $receptID";
            $conn->query($sql_delete_recepty);}
        header("Location: UpravaR.php");
        exit;
    }

 
    $conn->close();
    ?>

    <script>
        function showPopup(receptID) {
            var popup = document.getElementById("popup");
            popup.style.display = "block";
        }

        function hidePopup() {
            var popup = document.getElementById("popup");
            popup.style.display = "none";
        }
    </script>
<a  href='main.php'>< <button >Přejít na hlavní stránku</button></a>
    
</body>
</html>
