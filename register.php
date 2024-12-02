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

    $nume = $conn->real_escape_string(trim($_POST['nume']));
    $prenume = $conn->real_escape_string(trim($_POST['prenume']));
    $telefon = $conn->real_escape_string(trim($_POST['telefon']));
    $email = $conn->real_escape_string(trim($_POST['email']));
    $password = $conn->real_escape_string(trim($_POST['password']));
    $confirm_password = $conn->real_escape_string(trim($_POST['confirm_password']));
    $user_type = $conn->real_escape_string(trim($_POST['user_type']));
    $adresa = $conn->real_escape_string(trim($_POST['adresa'])); // Nou

    if ($password !== $confirm_password) {
        echo "<script>alert('Passwords do not match.');</script>";
    } else {
        $check_email_sql = "SELECT * FROM users WHERE Email = ?";
        $stmt_check = $conn->prepare($check_email_sql);
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows > 0) {
            echo "<script>alert('Email already in use. Please use a different email.');</script>";
        } else {
            $stmt = $conn->prepare("INSERT INTO users (Nume, Prenume, Telefon, Email, Parola, Tip_utilizator, Adresa) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $stmt->bind_param("sssssss", $nume, $prenume, $telefon, $email, $hashed_password, $user_type, $adresa);

            if ($stmt->execute()) {
                $user_id = $stmt->insert_id;

                if ($user_type == 'Client') {
                    $stmt_client = $conn->prepare("INSERT INTO clients (ID_User, Nume, Prenume, Telefon, Email, Adresa) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt_client->bind_param("isssss", $user_id, $nume, $prenume, $telefon, $email, $adresa);
                    $stmt_client->execute();
                } elseif ($user_type == 'Employee') {
                    $stmt_employee = $conn->prepare("INSERT INTO employees (ID_User, Nume, Prenume, Telefon, Email, Adresa) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt_employee->bind_param("isssss", $user_id, $nume, $prenume, $telefon, $email, $adresa);
                    $stmt_employee->execute();
                }

                echo "<script>alert('Registration successful. Redirecting to home page.');</script>";
                echo "<script>window.location.href='index.php';</script>";
                exit();
            } else {
                echo "Error: " . $stmt->error;
            }

            $stmt->close();
        }
        $stmt_check->close();
    }
    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Registration</title>
</head>
<body>
    <div class="container">
        <h2>Register</h2>
        <form action="register.php" method="POST">
            <label for="nume">Surname:</label>
<input type="text" id="nume" name="nume" required><br><br>

<label for="prenume">First name:</label>
<input type="text" id="prenume" name="prenume" required><br><br>

<label for="telefon">Phone:</label>
<input type="text" id="telefon" name="telefon" required><br><br>

<label for="email">Email:</label>
<input type="email" id="email" name="email" required><br><br>

<label for="adresa">Address:</label>
<input type="text" id="adresa" name="adresa" required><br><br>

<label for="password">Password:</label>
<input type="password" id="password" name="password" required><br><br>

<label for="confirm_password">Confirm password:</label>
<input type="password" id="confirm_password" name="confirm_password" required><br><br>

<label for="user_type">User type:</label>
<select id="user_type" name="user_type" required>
    <option value="Client">Client</option>
    <option value="Employee">Employee</option>
</select><br><br>

<button type="submit">Register</button>

        </form>
        <p>Already have an account? <a href="login.php"><b>Login here</b></a></p>
        <p><a href="index.php"><b>Home Page</b></a></p>
    </div>
</body>
</html>
