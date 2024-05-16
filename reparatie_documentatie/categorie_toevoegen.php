<?php
// Start de sessie
session_start();
if ($_SESSION['loggedIn']==="false")
    header( 'location : Reparatie_Documantatie.php');



?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toevoegen Categorie</title>
    <link rel="stylesheet" href="Style.css">
</head>
<body>
  <navbar>
        <a href="Reparatie_Documantatie.php">
            <img class="logo-home" src="afbeelding/baqmelogo.png" alt="BakfietsExpert Logo">
        </a>
    </navbar> 
    <h1>Toevoegen Categorie</h1>
    <!-- Formulier om een nieuwe categorie toe te voegen -->
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
        <!-- Invoerveld voor categorienaam -->
        <label for="category_name">Categorienaam:</label><br>
        <input type="text" id="category_name" name="category_name" required><br>
        
        <!-- Knop om de categorie toe te voegen -->
        <button type="submit">Toevoegen</button>
    </form>

    <?php
   include('DBconnect.php');

    // Controleer of het formulier is verzonden
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Ontvang en valideer de gegevens van het formulier
        $category_name = $_POST['category_name'];

        // Voeg de nieuwe categorie toe aan de database
        $query = "INSERT INTO categories (naam) VALUES (:naam)";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':naam', $category_name);
        $stmt->execute();

        // Redirect naar een andere pagina na toevoegen
        header("Location: Reparatie_Documantatie.php"); // Vervang 'index.php' door de gewenste bestemmingspagina
        exit();
    }
    ?>
</body>
</html>
