<?php
session_start();
include('DBconnect.php');

// Verwerk het inlogformulier
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $query = "SELECT id, username, password FROM users WHERE username = :username";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['loggedIn']="true";
        header("Location: Reparatie_Documantatie.php");
        exit();
    } else {
        $login_error = "Ongeldige gebruikersnaam of wachtwoord.";
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Inloggen</title>
</head>
<body>
 <navbar style="position: relative;">
        <a href="Reparatie_Documantatie.php">
        <img class="logo-home" src="afbeelding/baqmelogo.png" alt="BakfietsExpert Logo">
    </a>
        
    </navbar >
    <h2>Inloggen</h2>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
        <label for="username">Gebruikersnaam:</label>
        <input type="text" id="username" name="username" required><br>

        <label for="password">Wachtwoord:</label>
        <input type="password" id="password" name="password" required><br>

        <button  type="submit">Inloggen</button>
        <?php if (isset($login_error)) echo "<p>$login_error</p>"; ?><br><br><br>
        <a href="register.php">Nog geen account? Registreer hier.</a>
    </form>
    
</body>
</html>
