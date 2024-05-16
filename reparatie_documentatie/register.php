<?php
include('DBconnect.php');

// Verwerk het registratieformulier
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $query = "INSERT INTO users (username, password) VALUES (:username, :password)";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':password', $password);

    if ($stmt->execute()) {
        echo "Registratie succesvol. <a href='login.php'>Log hier in</a>.";
    } else {
        echo "Er is een fout opgetreden bij het registreren.";
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Registreren</title>
</head>
<body>
 <navbar style="position: relative;">
        <a href="Reparatie_Documantatie.php">
        <img class="logo-home" src="afbeelding/baqmelogo.png" alt="BakfietsExpert Logo">
    </a>
        
    </navbar >
    <h2>Registreren</h2>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
        <label for="username">Gebruikersnaam:</label>
        <input type="text" id="username" name="username" required><br>

        <label for="password">Wachtwoord:</label>
        <input type="password" id="password" name="password" required><br>

        <button type="submit">Registreren</button>
    </form>
</body>
</html>
