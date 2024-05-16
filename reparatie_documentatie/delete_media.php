<?php
include('DBconnect.php');

// Controleer of het media-ID is ontvangen
if(isset($_POST['media_id'])) {
    // Ontvang het media-ID
    $media_id = $_POST['media_id'];

    try {
        // Verwijder het media-item uit de database
        $deleteMediaQuery = "DELETE FROM media WHERE id = :media_id";
        $stmtDeleteMedia = $conn->prepare($deleteMediaQuery);
        $stmtDeleteMedia->bindParam(':media_id', $media_id);
        $stmtDeleteMedia->execute();

        // Geef een succesbericht terug
        echo "Media succesvol verwijderd.";
    } catch (PDOException $e) {
        // Geef een foutbericht terug als er een fout optreedt bij het verwijderen van het media-item
        echo "Fout bij het verwijderen van media: " . $e->getMessage();
    }
} else {
    // Geef een foutbericht terug als geen media-ID is doorgegeven
    echo "Geen media-ID ontvangen.";
}

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

// Haal de type en ID op uit de GET parameters
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['type']) && isset($_GET['id'])) {
    $type = $_GET['type'];
    $id = $_GET['id'];

    // Controleer het type en roep de juiste functie aan voor verwijdering
    if ($type === 'category') {
        deleteCategory($id);
        header("Location: overzicht.php");
        exit();
    } elseif ($type === 'problem') {
        deleteProblem($id);
        header("Location: overzicht.php");
        exit();
    } else {
        echo "Ongeldig type.";
        exit();
    }
} else {
    echo "Ongeldige aanvraag.";
    exit();
}

// Functie om een probleem te verwijderen
function deleteProblem($problem_id) {
    global $conn;
    try {
        // Verwijder eerst alle media-items die aan het probleem zijn gekoppeld
        $deleteMediaQuery = "DELETE FROM media WHERE problem_id = :problem_id";
        $stmtDeleteMedia = $conn->prepare($deleteMediaQuery);
        $stmtDeleteMedia->bindParam(':problem_id', $problem_id);
        $stmtDeleteMedia->execute();

        // Verwijder vervolgens het probleem zelf
        $deleteProblemQuery = "DELETE FROM problems WHERE id = :id";
        $stmtDeleteProblem = $conn->prepare($deleteProblemQuery);
        $stmtDeleteProblem->bindParam(':id', $problem_id);
        $stmtDeleteProblem->execute();
    } catch (PDOException $e) {
        // Geef een foutbericht terug als er een fout optreedt bij het verwijderen van het probleem
        echo "Fout bij het verwijderen van het probleem: " . $e->getMessage();
    }
}
?>
