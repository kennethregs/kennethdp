
<?php
require "db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $email = strtolower(trim($_POST["email"] ?? ""));
    $password = $_POST["password"] ?? "";

    if (
        $name === "" ||
        strlen($name) > 100 ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        strlen($email) > 255 ||
        strlen($password) < 8 ||
        strlen($password) > 72
    ) {
        $message = "Please enter valid details and a password of at least 8 characters.";
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare(
            "INSERT INTO users (name, email, password) VALUES (?, ?, ?)"
        );
        $stmt->bind_param("sss", $name, $email, $hash);

        if ($stmt->execute()) {
            header("Location: login.php?registered=1");
            exit;
        }

        if ($stmt->errno === 1062) {
            $message = "Email is already registered.";
        } else {
            $message = "Registration failed. Please try again.";
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="card">
    <h2>Create Account</h2>
    <p class="message"><?= htmlspecialchars($message) ?></p>

    <form method="POST">
        <label>Full Name</label>
        <input type="text" name="name" maxlength="100" required>

        <label>Email</label>
        <input type="email" name="email" maxlength="255" required>

        <label>Password (at least 8 characters)</label>
        <input type="password" name="password" minlength="8"
               maxlength="72" required autocomplete="new-password">

        <button type="submit">Register</button>
    </form>

    <p>Already have an account? <a href="login.php">Login</a></p>
</div>
</body>
</html>
