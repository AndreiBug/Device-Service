<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "service";

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $email = $conn->real_escape_string($_POST['email']);
    $password = $conn->real_escape_string($_POST['password']);

    $sql = "SELECT ID_User, Nume, Prenume, email, Parola, tip_utilizator FROM users WHERE email=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        if (password_verify($password, $row['Parola'])) {
            $_SESSION["id"] = $row['ID_User'];
            $_SESSION['email'] = $row["email"];
            $_SESSION['name'] = $row["Nume"];
            $_SESSION['user_type'] = $row["tip_utilizator"];
        
            // Setare cookie pentru autentificare persistentă (7 zile)
            setcookie("user_id", $row['ID_User'], time() + (7 * 24 * 60 * 60), "/");
        
            header("Location: index.php");
            exit();
        } else {
            echo "<script>alert('Invalid email or password.');</script>";
        }
    } else {
        echo "<script>alert('Invalid email or password.');</script>";
    }

    $stmt->close();
    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Login</title>
</head>
<body>
    <div class="container">
        <h2>Login</h2>
        <form action="login.php" method="POST">
            <label for="email">Email: </label>
            <input type="email" name="email" id="email" required><br><br>

            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required><br><br>

            <button type="submit">Login</button>
        </form>
        <p>Don't have an account? <a href="register.php"><b>Register here</b></a></p>
        <p><a href="index.php"><b>Home Page</b></a></p>
    </div>
</body>
</html>
