<?php
include('DBconnect.php');
function getCategories() {
    global $conn;
    $queryCategories = "SELECT * FROM categories";
    $stmtCategories = $conn->query($queryCategories);
    return $stmtCategories->fetchAll(PDO::FETCH_ASSOC);
}

function getProblemsByCategory($categoryId) {
    global $conn;
    $queryProblems = "SELECT * FROM problems WHERE categorie_id = :category_id";
    $stmtProblems = $conn->prepare($queryProblems);
    $stmtProblems->bindParam(':category_id', $categoryId);
    $stmtProblems->execute();
    return $stmtProblems->fetchAll(PDO::FETCH_ASSOC);
}
?>