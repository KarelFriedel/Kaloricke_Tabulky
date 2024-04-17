<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upravit recept</title>
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
        h2 {
            color: #008000;
            text-align: center;
            margin-bottom: 20px;
        }
        table {
            border-collapse: collapse;
            width: 60%;
            margin-top: 20px;
            background-color: #fff; /* Bílé pozadí tabulky */
        }
        th, td {
            border: 1px solid #ccc;
            padding: 8px;
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
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #45a049;
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
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.5);
            z-index: 9999;
        }
        .popup-content {
            max-height: 300px;
            overflow-y: auto;
        }
    </style>
</head>
<body>
  
    <form action="" method="post">
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

    <div id="popup" class="popup">
        <div class="popup-content">
            <h2>Potraviny receptu</h2>
            <ul id="potraviny-list">
                <!-- Zde budou vypsány potraviny -->
            </ul>
        </div>
        <button onclick="hidePopup()">Zavřít</button>
    </div>

    <script>
        function showPopup(receptID) {
            var popup = document.getElementById("popup");
            popup.style.display = "block";

            // AJAX volání pro získání potravin receptu
            var xhttp = new XMLHttpRequest();
            xhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    // Zpracování odpovědi a vypsání potravin do popup okna
                    var potravinyList = document.getElementById("potraviny-list");
                    potravinyList.innerHTML = this.responseText;
                }
            };
            xhttp.open("GET", "get_potraviny.php?receptID=" + receptID, true);
            xhttp.send();
        }

        function hidePopup() {
            var popup = document.getElementById("popup");
            popup.style.display = "none";
        }
    </script>

    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['smazat_recept'])) {
        $receptID = $_POST['smazat_recept'];

        $sql_delete_recepty_potraviny = "DELETE FROM recepty_potraviny WHERE Recept_ID = $receptID";
        $conn->query($sql_delete_recepty_potraviny);

        $sql_delete_recepty = "DELETE FROM recepty WHERE ID = $receptID";
        $conn->query($sql_delete_recepty);

        $sql_delete_potraviny = "DELETE FROM potraviny WHERE Nazev_Potraviny IN (SELECT Nazev FROM recepty where ID = '$receptID')";
        $conn->query($sql_delete_potraviny);

        echo "Recept byl úspěšně smazán.";
    }
    ?>
</body>
</html>