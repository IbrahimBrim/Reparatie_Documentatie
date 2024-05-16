<?php
// Inclusief het bestand voor databaseverbinding
include('DBconnect.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Controleer of de vereiste velden zijn ingevuld
    if (isset($_POST['problem_id']) && isset($_POST['title']) && isset($_POST['image']) && isset($_POST['description']) && isset($_POST['category'])) {
        // Ontvang de gegevens van het formulier
        $problem_id = $_POST['problem_id'];
        $title = $_POST['title'];
        $image = $_POST['image'];
        $description = $_POST['description'];
        $category_id = $_POST['category'];

        // Query om het probleem bij te werken in de database
        $query = "UPDATE problems SET titel='$title', afbeelding='$image', beschrijving='$description', categorie_id=$category_id WHERE id=$problem_id";

        if ($conn->query($query) === TRUE) {
            // Als het probleem succesvol is bijgewerkt, keer dan terug naar de reparatiedocumentatiepagina
            header("Location: Reparatie_Documentatie.php");
            exit();
        } else {
            echo "Er is een fout opgetreden bij het bijwerken van het probleem: " . $conn->error;
        }
    } else {
        echo "Niet alle vereiste velden zijn ingevuld.";
    }
} else {
    echo "Ongeldige methode om het script te bereiken.";
}
?>
