
<?php
require_once "config/database.php";

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $student_id = trim($_POST["student_id"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    // Validate student ID
    if (!preg_match('/^VUG\/SEN\/(24|25)\/\d{5}$/', $student_id)) {
        $error = "Invalid student ID format.";
    }

    // Validate email
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    }

    // Validate password
    elseif (strlen($password) < 8) {
        $error = "Password must contain at least 8 characters.";
    }

    else {
        // Check if student ID or email already exists
        $check = $conn->prepare(
            "SELECT id FROM users WHERE student_id = ? OR email = ?"
        );

        $check->bind_param("ss", $student_id, $email);
        $check->execute();

        $result = $check->get_result();

        if ($result->num_rows > 0) {
            $error = "Student ID or email is already registered.";
        } else {

            // Hash the password before saving
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert the new account
            $stmt = $conn->prepare(
                "INSERT INTO users (student_id, email, password)
                 VALUES (?, ?, ?)"
            );

            $stmt->bind_param(
                "sss",
                $student_id,
                $email,
                $hashed_password
            );

            if ($stmt->execute()) {
                header("Location: login.php?registered=1");
                exit();
            } else {
                $error = "Registration failed. Please try again.";
            }

            $stmt->close();
        }

        $check->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <h1>Create an Account</h1>
    <p>Register to access the student biodata form.</p>

    <form action="register.php" method="POST">

        <h2>Registration Information</h2>

        <?php if ($error !== ""): ?>
            <p style="color: red;">
                <?= htmlspecialchars($error) ?>
            </p>
        <?php endif; ?>

        <label for="student_id">Student ID:</label>
        <input
            type="text"
            id="student_id"
            name="student_id"
            placeholder="VUG/SEN/24/12345"
            value="<?= htmlspecialchars($_POST['student_id'] ?? '') ?>"
            required
        >

        <label for="email">Email Address:</label>
        <input
            type="email"
            id="email"
            name="email"
            placeholder="example@email.com"
            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
            required
        >

        <label for="password">Password:</label>
        <input
            type="password"
            id="password"
            name="password"
            minlength="8"
            required
        >

        <input type="submit" value="Register">

        <p>
            Already have an account?
            <a href="login.php">Login here</a>
        </p>

    </form>

</body>
</html>
