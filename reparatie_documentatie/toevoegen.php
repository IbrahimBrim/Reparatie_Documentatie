<?php
include('DBconnect.php');
// Start de sessie
session_start();
if ($_SESSION['loggedIn']==="false")
    header( 'location : Reparatie_Documantatie.php');


// Controleer of het formulier is verzonden
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ontvang en valideer de gegevens van het formulier
    $titel = $_POST['titel'];
    $category_id = $_POST['category'];

    // Voeg het probleem toe aan de database
    $query = "INSERT INTO problems (titel, categorie_id) VALUES (:titel, :category_id)";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':titel', $titel);
    $stmt->bindParam(':category_id', $category_id);
    $stmt->execute();
    $problem_id = $conn->lastInsertId();

 // Afbeeldingen uploaden
$image_data = [];
if (!empty($_FILES['images']['name'][0])) {
    foreach ($_FILES['images']['tmp_name'] as $image_tmp_name) {
        if (!empty($image_tmp_name)) {
            $image_data[] = base64_encode(file_get_contents($image_tmp_name));
        }
    }
}

// Video's uploaden
$video_data = [];
if (!empty($_FILES['videos']['name'][0])) {
    foreach ($_FILES['videos']['tmp_name'] as $video_tmp_name) {
        if (!empty($video_tmp_name)) {
            $video_data[] = base64_encode(file_get_contents($video_tmp_name));
        }
    }
}


    // Voeg de media toe aan de database
    for ($i = 0; $i < count($image_data); $i++) {
        $query = "INSERT INTO media (problem_id, type, data, location, description) VALUES (:problem_id, 'image', :data, :location, :description)";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':problem_id', $problem_id);
        $stmt->bindParam(':data', $image_data[$i]);
        $stmt->bindParam(':location', $_POST['locations'][$i]);
        $stmt->bindParam(':description', $_POST['descriptions'][$i]);
        $stmt->execute();
    }

    for ($i = 0; $i < count($video_data); $i++) {
        $query = "INSERT INTO media (problem_id, type, data, location) VALUES (:problem_id, 'video', :data, :location)";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':problem_id', $problem_id);
        $stmt->bindParam(':data', $video_data[$i]);
        $stmt->bindParam(':location', $_POST['locations'][$i + count($image_data)]); // Verplaats de index naar het einde van de afbeeldingarray
        $stmt->execute();
    }

    // Redirect naar een andere pagina na toevoegen
    header("Location: Reparatie_Documantatie.php");
    exit();
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
    <title>toevoegen Probleem</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <navbar>
        <a href="Reparatie_Documantatie.php">
            <img class="logo-home" src="afbeelding/baqmelogo.png" alt="BakfietsExpert Logo">
        </a>
    </navbar>    <h1>Toevoegen Probleem</h1>
    <!-- Formulier om een nieuw probleem toe te voegen -->
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" enctype="multipart/form-data">
        <!-- Invoerveld voor titel -->
       <label for="titel">Titel:</label><br>
        <input type="text" id="titel" name="titel" required><br>

        <!-- Dropdown-menu voor categorie -->
        <label for="category">Categorie:</label><br>
        <select id="category" name="category" required>
            <?php
            // Loop door alle categorieën en genereer opties
            foreach ($categories as $cat) {
                echo '<option value="' . $cat["id"] . '">' . $cat["naam"] . '</option>';
            }
            ?>
        </select><br>

        <!-- Container voor media-inhoud -->
        <div id="media-container">
            <!-- Initiële media-container, leeg -->
           
        </div>
         
        <!-- Knop om meer mediacontainers toe te voegen -->
        <button class="button" type="button" onclick="addMediaContainer()">Nog een container toevoegen</button>

        <!-- Knop om het nieuwe probleem toe te voegen -->
        <button class="button" type="submit">Toevoegen</button>
    </form>

    <!-- JavaScript voor het toevoegen van mediacontainers -->
  <script>
    var containerCount = 0; // Houdt het aantal containers bij

    function addMediaContainer() {
        containerCount++; // Verhoog het aantal containers

        // Creëer een nieuwe media-container
        var mediaContainer = document.createElement('div');
        mediaContainer.classList.add('media-upload');

        // Voeg invoervelden voor afbeeldingen, video's en omschrijving toe
        mediaContainer.innerHTML = '<div class="toevoegencontainermedia">'+ 
                                    '<div class="descriptioncontainer">' +
                                    '<label for="description">Beschrijving:</label><br>' +
                                    '<textarea name="descriptions[]" placeholder="Omschrijving"></textarea><br>' +
                                    '</div>'+
                                    '<div class="mediacontainer"> ' +
                                    '<label  for="images">Afbeeldingen uploaden:</label><br>' +
                                    '<input  type="file" accept="image/*" name="images[]"><br>' +
                                    '<label for="videos">Video uploaden:</label><br>' +
                                    '<input type="file" accept="video/*" name="videos[]"><br>' +
                                    '</div>'+
                                    '</div>'+
                                    '<input  type="button" value="Verwijderen" onclick="removeMediaContainer(this.parentNode)"><br>' + // Verwijderknop
                                    '<input type="hidden" name="locations[]" value="' + containerCount + '">'; // Automatische locatienummer

        // Voeg de nieuwe media-container toe aan de media-container
        document.getElementById('media-container').appendChild(mediaContainer);
    }
     function removeMediaContainer(container) {
        // Verwijder de opgegeven container
        container.parentNode.removeChild(container);
    }
</script>


</body>
</html>
