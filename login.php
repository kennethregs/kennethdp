
<?php
require "db.php";

$message = "";

if (isset($_GET["registered"])) {
    $message = "Registration successful! Please log in.";
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = strtolower(trim($_POST["email"] ?? ""));
    $password = $_POST["password"] ?? "";

    if (
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        strlen($email) > 255 ||
        !is_string($password) ||
        strlen($password) > 72
    ) {
        $message = "Invalid email or password.";
    } else {
        $stmt = $conn->prepare(
            "SELECT id, name, password FROM users WHERE email = ?"
        );
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($id, $name, $hash);
            $stmt->fetch();

            if (password_verify($password, $hash)) {
                session_regenerate_id(true);
                $_SESSION["user_id"] = $id;
                $_SESSION["user_name"] = $name;

                header("Location: dashboard.php");
                exit;
            }
        }

        $message = "Invalid email or password.";
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="card">
    <h2>Welcome Back</h2>
    <p class="message"><?= htmlspecialchars($message) ?></p>

    <form method="POST">
        <label>Email</label>
        <input type="email" name="email" maxlength="255" required>

        <label>Password</label>
        <input type="password" name="password" maxlength="72"
               required autocomplete="current-password">

        <button type="submit">Login</button>
    </form>

    <p>Don't have an account? <a href="register.php">Register</a></p>
</div>
</body>
</html>
