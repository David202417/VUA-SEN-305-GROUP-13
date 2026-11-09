
<?php
session_start();

require_once "config/database.php";

$error = "";

if (isset($_GET["registered"])) {
    $success = "Registration successful! You can now log in.";
} else {
    $success = "";
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {

        // Find the account using the email
        $stmt = $conn->prepare(
            "SELECT id, student_id, email, password
             FROM users
             WHERE email = ?"
        );

        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows === 1) {

            $user = $result->fetch_assoc();

            // Verify the submitted password
            if (password_verify($password, $user["password"])) {

                // Prevent session fixation
                session_regenerate_id(true);

                // Store the logged-in user's details
                $_SESSION["user_id"] = $user["id"];
                $_SESSION["student_id"] = $user["student_id"];
                $_SESSION["email"] = $user["email"];

                header("Location: biodata.php");
                exit();

            } else {
                $error = "Incorrect email or password.";
            }

        } else {
            $error = "Incorrect email or password.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <h1>Student Login</h1>
    <p>Log in to access your biodata form.</p>

    <form action="login.php" method="POST">

        <h2>Login Information</h2>

        <?php if ($success !== ""): ?>
            <p style="color: green;">
                <?= htmlspecialchars($success) ?>
            </p>
        <?php endif; ?>

        <?php if ($error !== ""): ?>
            <p style="color: red;">
                <?= htmlspecialchars($error) ?>
            </p>
        <?php endif; ?>

        <label for="email">Email Address:</label>
        <input
            type="email"
            id="email"
            name="email"
            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
            required
        >

        <label for="password">Password:</label>
        <input
            type="password"
            id="password"
            name="password"
            required
        >

        <input type="submit" value="Login">

        <p>
            Don't have an account?
            <a href="register.php">Register here</a>
        </p>

    </form>

</body>
</html>
