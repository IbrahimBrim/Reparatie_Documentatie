<?php

include('DBconnect.php');
session_start();
if ($_SESSION['loggedIn'] === "false") {
    header('location: Reparatie_Documantatie.php');
    exit();
}

$title = '';
$category_id = '';
$problem_id = '';

if(isset($_GET['id'])) {
    $problem_id = $_GET['id'];
    $query = "SELECT * FROM problems WHERE id = :id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $problem_id);
    $stmt->execute();
    if($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $title = isset($row['titel']) ? $row['titel'] : '';
        $category_id = isset($row['categorie_id']) ? $row['categorie_id'] : '';
    } else {
        echo "Probleem niet gevonden.";
        exit();
    }
} else {
    echo "Geen probleem-ID gevonden.";
    exit();
}

$queryMedia = "SELECT * FROM media WHERE problem_id = :problem_id";
$stmtMedia = $conn->prepare($queryMedia);
$stmtMedia->bindParam(':problem_id', $problem_id);
$stmtMedia->execute();
$media = $stmtMedia->fetchAll(PDO::FETCH_ASSOC);

$queryCategories = "SELECT * FROM categories";
$stmtCategories = $conn->prepare($queryCategories);
$stmtCategories->execute();
$categories = $stmtCategories->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_title = $_POST['title'];
    $new_category_id = $_POST['category'];

    $updateQuery = "UPDATE problems SET titel = :title, categorie_id = :category_id WHERE id = :problem_id";
    $stmtUpdate = $conn->prepare($updateQuery);
    $stmtUpdate->bindParam(':title', $new_title);
    $stmtUpdate->bindParam(':category_id', $new_category_id);
    $stmtUpdate->bindParam(':problem_id', $problem_id);
    $stmtUpdate->execute();

 // Opslaan van beschrijvingen en locaties voor bestaande media-items
foreach ($_POST['description'] as $media_id => $description) {
    $location = $_POST['location'][$media_id];
    $updateMediaQuery = "UPDATE media SET description = :description, location = :location WHERE id = :id";
    $stmtMediaUpdate = $conn->prepare($updateMediaQuery);
    $stmtMediaUpdate->bindParam(':description', $description);
    $stmtMediaUpdate->bindParam(':location', $location);
    $stmtMediaUpdate->bindParam(':id', $media_id);
    $stmtMediaUpdate->execute();
}

// Controleer of er nieuwe media is toegevoegd
if (!empty($_FILES['new_media']['name'])) {
    // Loop door elke nieuwe media
foreach ($_FILES['new_media']['tmp_name'] as $key => $tmp_name) {
    if (!empty($tmp_name)) {
        // Haal de huidige beschrijving en locatie op voor de huidige media
        $current_media_id = $_POST['media_id'][$key]; // Veronderstel dat je een veld 'media_id' hebt om de id's van bestaande media-items op te slaan
        $current_description = isset($_POST['description'][$current_media_id]) ? $_POST['description'][$current_media_id] : '';
        $current_location = isset($_POST['location'][$current_media_id]) ? $_POST['location'][$current_media_id] : '';

        // Lees het tijdelijke bestand
        $file_tmp = $tmp_name;

        // Converteer het bestand naar base64
        $file_data = base64_encode(file_get_contents($file_tmp));

        // Bepaal het type van het bestand (afbeelding of video) op basis van de bestandsnaam
        $file_type = $_FILES['new_media']['type'][$key];
        if (strpos($file_type, 'image') !== false) {
            $media_type = 'image';
        } elseif (strpos($file_type, 'video') !== false) {
            $media_type = 'video';
        }

        // Voeg het nieuwe media-item toe aan de huidige container met de huidige beschrijving en locatie
        $insertMediaQuery = "INSERT INTO media (problem_id, type, data, description, location) VALUES (:problem_id, :type, :data, :description, :location)";
        $stmtInsertMedia = $conn->prepare($insertMediaQuery);
        $stmtInsertMedia->bindParam(':problem_id', $problem_id);
        $stmtInsertMedia->bindParam(':type', $media_type);
        $stmtInsertMedia->bindParam(':data', $file_data);
        $stmtInsertMedia->bindParam(':description', $current_description);
        $stmtInsertMedia->bindParam(':location', $current_location);
        $stmtInsertMedia->execute();
    }
}

}


}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wijzig Probleem</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <navbar>
        <a href="Reparatie_Documantatie.php">
            <img class="logo-home" src="afbeelding/baqmelogo.png" alt="BakfietsExpert Logo">
        </a>
    </navbar>

    <h1>Wijzig Probleem</h1>
    <!-- Formulier om het probleem te wijzigen -->
    <form id="editForm" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?id=' . $problem_id; ?>" method="POST" enctype="multipart/form-data">
        <!-- Invoerveld voor titel -->
        <label for="title">Titel:</label><br>
        <input type="text" id="title" name="title" value="<?php echo $title; ?>"><br>
        <input type="hidden" name="media_id[<?php echo $m['id']; ?>]" value="<?php echo $m['id']; ?>">

        <!-- Dropdown-menu voor categorie -->
        <label for="category">Categorie:</label><br>
        <select id="category" name="category">
            <?php
            // Loop door alle categorieën en genereer opties
            foreach ($categories as $cat) {
                echo '<option value="' . $cat["id"] . '"';
                if ($cat["id"] == $category_id) {
                    echo ' selected';
                }
                echo '>' . $cat["naam"] . '</option>';
            }
            ?>
        </select><br><br>
        <div>
        <!-- Loop door alle bestaande media (beschrijvingen met bijbehorende media) -->
        <?php foreach ($media as $m): ?>
            <div class="media_item" id="media_<?php echo $m['id']; ?>">
            <div class="descriptioncontainer">
                <!-- Beschrijving -->
                <label for="description_<?php echo $m['id']; ?>">Beschrijving:</label><br>
                <textarea id="description_<?php echo $m['id']; ?>" name="description[<?php echo $m['id']; ?>]"><?php echo $m['description']; ?></textarea><br>
                </div>
                <div class="mediacontainer">
                 <!-- Locatie -->
                <label for="location_<?php echo $m['id']; ?>">Locatie:</label><br>
                <input type="text" id="location_<?php echo $m['id']; ?>" name="location[<?php echo $m['id']; ?>]" value="<?php echo $m['location']; ?>"><br><br>
                <!-- Media weergeven -->
                <?php if ($m['type'] == 'image'): ?>
                    <!-- Afbeelding weergeven -->
                    <img src="data:image/jpeg;base64,<?php echo $m['data']; ?>" alt="Afbeelding"><br>
                <?php elseif ($m['type'] == 'video'): ?>
                    <!-- Video weergeven -->
                    <video controls>
                        <source src="data:video/mp4;base64,<?php echo $m['data']; ?>" type="video/mp4">
                        Your browser does not support the video tag.
                    </video><br>
                <?php endif; ?>

                <!-- Uploadknop om nieuwe media toe te voegen -->
                <label for="new_media_<?php echo $m['id']; ?>">Nieuwe media toevoegen:</label><br>
                <input type="file" id="new_media_<?php echo $m['id']; ?>" name="new_media[<?php echo $m['id']; ?>]" accept="image/*, video/*"><br><br>
                </div>
                 </div>
                <!-- Knop om dit media-item te verwijderen -->
                <button type="button" onclick="confirmDelete(<?php echo $m['id']; ?>)">Verwijder container</button><br><br>

               
           
        <?php endforeach; ?>
        </div>
        <!-- Knop om nieuwe beschrijvingen, afbeeldingen en video's toe te voegen -->
        <button type="button" onclick="addNewMedia()">Voeg nieuwe beschrijving en media toe</button><br><br>

        <!-- Knop om de wijzigingen op te slaan -->
        <button class="button-86" id="saveButton" type="submit">Opslaan</button>
    </form>

    <script>
        function confirmDelete(mediaId) {
            var confirmation = confirm("Weet u zeker dat u deze foto of video wilt verwijderen?");
            if (confirmation) {
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "delete_media.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function() {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        // Verwijder de container uit de weergave
                        var container = document.getElementById("media_" + mediaId);
                        container.parentNode.removeChild(container);

                        // Toon het bericht dat is geretourneerd door delete_media.php
                        alert(xhr.responseText);
                    }
                };
                xhr.send("media_id=" + mediaId);
            }
        }

        function addNewMedia() {
            var newMediaItem = document.createElement("div");
            var mediaId = Date.now();
            newMediaItem.classList.add("media_item");
            newMediaItem.id = "media_" + mediaId;

            newMediaItem.innerHTML = '<label for="new_description_' + mediaId + '">Nieuwe beschrijving:</label><br>' +
                                      '<textarea id="new_description_' + mediaId + '" name="new_description[' + mediaId + ']"></textarea><br>' +
                                      '<label for="new_media_' + mediaId + '">Nieuwe media:</label><br>' +
                                      '<input type="file" accept="image/*, video/*" name="new_media[' + mediaId + ']"><br><br>' +
                                      '<label for="new_location_' + mediaId + '">Locatie:</label><br>' +
                                      '<input type="text" id="new_location_' + mediaId + '" name="new_location[' + mediaId + ']"><br><br>';

            document.getElementById("editForm").appendChild(newMediaItem);
        }
          function confirmDelete(mediaId) {
            var result = confirm("Weet je zeker dat je deze container wilt verwijderen?");
            if (result) {
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "delete_media.php", true);
                xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function() {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        alert(xhr.responseText);
                        // Refresh de pagina of doe iets anders om de wijzigingen weer te geven
                        window.location.reload();
                    }
                };
                xhr.send("media_id=" + mediaId);
            }
        }
    </script>

</body>
</html>
