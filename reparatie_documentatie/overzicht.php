<?php
include('DBconnect.php');

// Functie om een categorie te verwijderen met alle gerelateerde problemen
function deleteCategory($category_id) {
    global $conn;
    try {
        // Verwijder eerst alle problemen die aan de categorie zijn gekoppeld
        $deleteProblemsQuery = "DELETE FROM problems WHERE categorie_id = :category_id";
        $stmtDeleteProblems = $conn->prepare($deleteProblemsQuery);
        $stmtDeleteProblems->bindParam(':category_id', $category_id);
        $stmtDeleteProblems->execute();

        // Verwijder vervolgens de categorie zelf
        $deleteCategoryQuery = "DELETE FROM categories WHERE id = :id";
        $stmtDeleteCategory = $conn->prepare($deleteCategoryQuery);
        $stmtDeleteCategory->bindParam(':id', $category_id);
        $stmtDeleteCategory->execute();
    } catch (PDOException $e) {
        // Geef een foutbericht terug als er een fout optreedt bij het verwijderen van de categorie
        echo "Fout bij het verwijderen van de categorie: " . $e->getMessage();
    }
}

// Haal alle categorieën op
$queryCategories = "SELECT * FROM categories";
$stmtCategories = $conn->prepare($queryCategories);
$stmtCategories->execute();
$categories = $stmtCategories->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <link rel="stylesheet" href="Style.css">
    <title>Overzicht</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f2f2f2;
        }

        .delete-btn {
            background-color: #f44336;
            color: white;
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .delete-btn:hover {
            background-color: #d32f2f;
        }

        .edit-btn {
            background-color: #4CAF50;
            color: white;
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .edit-btn:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
 <navbar>
        <a href="Reparatie_Documantatie.php">
        <img class="logo-home" src="afbeelding/baqmelogo.png" alt="BakfietsExpert Logo">
    </a>
      <!-- Als de gebruiker is ingelogd, toon de uitlogknop -->
      
               <?php
        // Start de sessie als deze nog niet is gestart
        if(session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Controleer of de sessievariabele is ingesteld voordat je deze gebruikt
        if(isset($_SESSION['loggedIn']) && $_SESSION['loggedIn'] === "true") {
            echo '<a class="loggen-button" href="?logout">Uitloggen</a>';
        } else {
            echo '<a class="loggen-button" href="login.php">Inloggen</a>';
        }
        ?>

    </navbar>
    <h1>Overzicht van Categorie en Problemen</h1>
    <?php foreach ($categories as $category): ?>
    <div>
        <h3><?php echo $category['naam']; ?></h3>
        <!-- Toevoeging van de verwijderknop voor categorieën -->
        <button class="delete-btn" onclick="confirmDeleteCategory(<?php echo $category['id']; ?>)">Verwijder Categorie</button>
        </div>
        <table>
            <tr>
                <th>Titel</th>
                <th>Acties</th>
            </tr>
            <?php
            // Haal alle problemen op die aan de huidige categorie zijn gekoppeld
            $queryProblems = "SELECT * FROM problems WHERE categorie_id = :category_id";
            $stmtProblems = $conn->prepare($queryProblems);
            $stmtProblems->bindParam(':category_id', $category['id']);
            $stmtProblems->execute();
            $problems = $stmtProblems->fetchAll(PDO::FETCH_ASSOC);

            foreach ($problems as $problem): ?>
                <tr>
                    <td><?php echo $problem['titel']; ?></td>
                    <td>
                        <!-- Knop om probleem te bewerken -->
                        <a href="wijzigen.php?id=<?php echo $problem['id']; ?>" class="edit-btn">Wijzigen</a>
                        <!-- Knop om probleem te verwijderen -->
                        <button class="delete-btn" onclick="confirmDeleteProblem(<?php echo $problem['id']; ?>)">Verwijder Probleem</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <br>
    <?php endforeach; ?>

    <script>
        // JavaScript functie om bevestiging te vragen voor het verwijderen van een probleem
        function confirmDeleteProblem(problemId) {
            var confirmation = confirm("Weet u zeker dat u dit probleem wilt verwijderen?");
            if (confirmation) {
                // Als de gebruiker bevestigt, stuur een AJAX-verzoek om het probleem te verwijderen
                var xhr = new XMLHttpRequest();
                xhr.open("GET", "delete_media.php?type=problem&id=" + problemId, true);
                xhr.onreadystatechange = function() {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        // Vernieuw de pagina na succesvol verwijderen
                        window.location.reload();
                    }
                };
                xhr.send();
            }
        }

        // JavaScript functie om bevestiging te vragen voor het verwijderen van een categorie
        function confirmDeleteCategory(categoryId) {
            var confirmation = confirm("Weet u zeker dat u deze categorie wilt verwijderen? Alle bijbehorende problemen worden ook verwijderd!");
            if (confirmation) {
                // Als de gebruiker bevestigt, stuur een AJAX-verzoek om de categorie te verwijderen
                var xhr = new XMLHttpRequest();
                xhr.open("GET", "delete_media.php?type=category&id=" + categoryId, true);
                xhr.onreadystatechange = function() {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        // Vernieuw de pagina na succesvol verwijderen
                        window.location.reload();
                    }
                };
                xhr.send();
            }
        }
    </script>
</body>
</html>
