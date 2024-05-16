<?php
include('SQLstatement.php');

// Start de sessie
session_start();
if (isset($_GET['logout'])) {
    $_SESSION['loggedIn'] = "false";
    header("Location: Reparatie_Documantatie.php");
}

// Haal de locatienummers op uit de database en bewaar ze in een array
if ($_SERVER['REQUEST_URI'] == "/Reparatie_Documantatie.php") {
    $locationNumbers = array(); // Array om locatienummers op te slaan
    $queryLocations = "SELECT DISTINCT location FROM media ORDER BY location ASC";
    $stmtLocations = $conn->prepare($queryLocations);
    $stmtLocations->execute();
    while ($rowLocation = $stmtLocations->fetch(PDO::FETCH_ASSOC)) {
        $locationNumbers[] = $rowLocation['location'];
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu met verschillende inhoud</title>
    <link rel="stylesheet" href="Style.css">
</head>
<body>
<!-- Header met het vaste menu en logo -->
<header class="normal-header">
    <div class="logo">
        <img src="afbeelding/baqmelogoQ.png" alt="Home Logo" width="100">
    </div>
   
    <nav class="menu">
        <h1>Menu</h1>
        <?php
        // Loop door alle categorieën en genereer menuknoppen
        $categories = getCategories();
        if (!empty($categories)) {
            foreach ($categories as $rowCategory) {
                echo '<a class="menu-button" href="#" onclick="showCategory(\'' . $rowCategory["naam"] . '\')">' . $rowCategory["naam"] . '</a>';
            }
        }
        ?>
    </nav>
 <?php if (isset($_SESSION['loggedIn']) && $_SESSION['loggedIn'] === "true"): ?>
        <div class="bottom-menu">
            <a class="bottom-menu-button" href="toevoegen.php">Nieuw Probleem Toevoegen</a>
            <a class="bottom-menu-button" href="categorie_toevoegen.php">Nieuwe Categorie Toevoegen</a>
            <a class="bottom-menu-button" href="overzicht.php">Alle problems en categorie</a>
        </div>
    <?php endif; ?>
</header>
<!-- Header met het vaste menu en logo     SmartPhone -->
<header class="phonescreen">
<div id="menu-toggle">
    <input type="checkbox" />
    <span></span>
    <span></span>
    <span></span>
    <ul id="menu">
       <?php
        // Loop door alle categorieën en genereer menuknoppen
        $categories = getCategories();
        if (!empty($categories)) {
            foreach ($categories as $rowCategory) {
                echo '<a class="menu-button" href="#" onclick="showCategory(\'' . $rowCategory["naam"] . '\')">' . $rowCategory["naam"] . '</a>';
            }
        }
        ?>

        <?php if (isset($_SESSION['loggedIn']) && $_SESSION['loggedIn'] === "true"): ?>
            <div class="bottom-menu">
                <a class="bottom-menu-button" href="toevoegen.php">Nieuw Probleem Toevoegen</a>
                <a class="bottom-menu-button" href="categorie_toevoegen.php">Nieuwe Categorie Toevoegen</a>
                 <a class="bottom-menu-button" href="overzicht.php">Alle problems en categorie</a>
            </div>
        <?php endif; ?>
    </ul>
</div>

</header>

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
    <!-- Homepagina -->
   <main>
    <?php
    // Loop door alle categorieën en genereer inhoudssecties
    if (!empty($categories)) {
        foreach ($categories as $rowCategory) {
            echo '<div class="category hidden" id="' . $rowCategory["naam"] . '" >';
            echo '<h2>' . $rowCategory["naam"] . '</h2>';
            echo '<div class="button-container">';
            
            $problems = getProblemsByCategory($rowCategory["id"]);
            // Loop door alle problemen en genereer de knoppen
            if (!empty($problems)) {
                foreach ($problems as $rowProblem) {
                    echo '<button class="button-49" onclick="showContent(\'' . $rowProblem["id"] . '\')">' . $rowProblem["titel"] . '</button>';
                }
            }
            echo '</div>';

            // Loop door alle problemen en genereer de inhoudssecties
            if (!empty($problems)) {
                foreach ($problems as $rowProblem) {
                    echo '<div class="content hidden" id="' . $rowProblem["id"] . '">';
                    echo '<div class="text">';
                    if (isset($_SESSION['loggedIn']) && $_SESSION['loggedIn'] === "true") {
                        echo '<a href="wijzigen.php?id=' . $rowProblem["id"] . '" class="button-86">Wijzigen</a>';
                    }
                    echo '<h3>Probleem ' . $rowProblem["id"] . ': ' . $rowProblem["titel"] . '</h3>';

                    // Query om alle media op te halen uit de media tabel voor dit probleem, inclusief beschrijvingen
                    $queryMedia = "SELECT * FROM media WHERE problem_id = :problem_id";
                    $stmtMedia = $conn->prepare($queryMedia);
                    $stmtMedia->bindParam(':problem_id', $rowProblem["id"]);
                    $stmtMedia->execute();

                    $media = $stmtMedia->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($media as $m) {
                        // Start een nieuwe rij
                        echo '<div class="row">';

                        // Beschrijving in de linkerkolom
                        echo '<div class="description-column">';
                        // Controleer op URL's en maak een link
                        $description = preg_replace('/(http[s]?:\/\/[^\s]+)/', '<a href="$1" target="_blank">$1</a>', $m['description']);
                        // Controleer op telefoonnummers en maak een belbare link
                        $description = preg_replace('/(\+?\d{1,4}[\s-]?\(?\d{2,5}\)?[\s-]?\d{2,5}[\s-]?\d{2,5})/', '<a href="tel:$1">$1</a>', $description);
                        // Controleer op e-mailadressen en maak een mailto-link
                        $description = preg_replace('/([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})/', '<a href="mailto:$1">$1</a>', $description);
                        echo '<p class="media-description">' . $description . '</p>';
                        echo '</div>';

                        // Afbeelding of video in de rechterkolom
                        echo '<div class="media-column">';
                        if ($m['type'] == 'image') {
                            $image_data = base64_decode($m['data']);
                            echo '<img class="media-content" src="data:image/jpeg;base64,' . base64_encode($image_data) . '" alt="Afbeelding">';
                        } elseif ($m['type'] == 'video') {
                            $video_data = base64_decode($m['data']);
                            echo '<video class="media-content" controls><source src="data:video/mp4;base64,' . base64_encode($video_data) . '" type="video/mp4">Your browser does not support the video tag.</video>';
                        }
                        echo '</div>'; // sluit de media-column

                        // Sluit de rij
                        echo '</div>'; // sluit de row
                    }
                    echo '</div>'; // sluit de text
                    echo '</div>'; // sluit de content
                }
            }
            echo '</div>'; // sluit de category container
        }

    }
    ?>
</main>
    <div class="home-content">
        <!-- Welkomstsectie met een foto en tekst -->
        <div class="welcome-section">
            <img src="afbeelding/DollySpin-speed.gif" alt="Welcome Image">
            <p>Welkom op onze website. Klik op een categorie om de problemen weer te geven.</p>
        </div>
    </div>
    <script>
        function showContent(contentId) {
            var contents = document.querySelectorAll('.content');
            contents.forEach(function(element) {
                element.classList.add('hidden');
            });

            var selectedContent = document.getElementById(contentId);
            if (selectedContent) {
                selectedContent.classList.remove('hidden');
            }
        }

        function showCategory(categoryId) {
            document.querySelector('.home-content').classList.add('hidden');

            var categories = document.querySelectorAll('.category');
            categories.forEach(function(category) {
                category.classList.add('hidden');
            });

            var selectedCategory = document.getElementById(categoryId);
            if (selectedCategory) {
                selectedCategory.classList.remove('hidden');
            }
        }
    </script>
</body>
</html>
