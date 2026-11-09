
<?php
require_once "includes/auth.php";
require_once "config/database.php";

$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $user_id = $_SESSION["user_id"];

    $first_name = trim($_POST["firstName"]);
    $last_name = trim($_POST["lastName"]);
    $middle_name = trim($_POST["middleName"] ?? "");
    $dob = $_POST["dob"];
    $gender = $_POST["gender"];
    $email = trim($_POST["email"]);
    $phone = trim($_POST["phone"]);
    $address = trim($_POST["address"]);
    $state = trim($_POST["state"]);
    $nationality = trim($_POST["nationality"]);
    $department = trim($_POST["department"]);
    $faculty = trim($_POST["faculty"]);
    $course = trim($_POST["course"]);
    $level = $_POST["level"];
    $admission_year = (int) $_POST["admissionYear"];
    $registration_date = $_POST["registrationDate"];
    $guardian_name = trim($_POST["guardianName"]);
    $guardian_phone = trim($_POST["guardianPhone"]);
    $emergency_contact = trim($_POST["emergencyContact"]);

    $sql = "INSERT INTO biodata (
        user_id, first_name, last_name, middle_name,
        dob, gender, email, phone, address,
        state_of_origin, nationality, department,
        faculty, course, level, admission_year,
        registration_date, guardian_name,
        guardian_phone, emergency_contact
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        $error = "Database error: " . $conn->error;
    } else {

        $stmt->bind_param(
            "issssssssssssssissss",
            $user_id,
            $first_name,
            $last_name,
            $middle_name,
            $dob,
            $gender,
            $email,
            $phone,
            $address,
            $state,
            $nationality,
            $department,
            $faculty,
            $course,
            $level,
            $admission_year,
            $registration_date,
            $guardian_name,
            $guardian_phone,
            $emergency_contact
        );

        if ($stmt->execute()) {
            $success = "Biodata submitted successfully!";
        } else {
            $error = "Submission failed: " . $stmt->error;
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

    <title>Student Biodata Form</title>

    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <h1>Student Biodata Form</h1>
    <p>Please fill in your personal and academic information.</p>

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

    <form action="biodata.php" method="POST">

        <h2>Personal Information</h2>

        <label for="studentID">Student ID:</label>
        <input
            type="text"
            id="studentID"
            name="studentID"
            value="<?= htmlspecialchars($_SESSION['student_id']) ?>"
            readonly
            required
        >
        <br><br>

        <label for="firstName">First Name:</label>
        <input type="text" id="firstName" name="firstName" required>
        <br><br>

        <label for="lastName">Last Name:</label>
        <input type="text" id="lastName" name="lastName" required>
        <br><br>

        <label for="middleName">Middle Name:</label>
        <input type="text" id="middleName" name="middleName">
        <br><br>

        <label for="dob">Date of Birth:</label>
        <input type="date" id="dob" name="dob" required>
        <br><br>

        <label for="gender">Gender:</label>
        <select id="gender" name="gender" required>
            <option value="">Select Gender</option>
            <option value="male">Male</option>
            <option value="female">Female</option>
            <option value="other">Other</option>
        </select>
        <br><br>

        <label for="email">Email Address:</label>
        <input type="email" id="email" name="email" required>
        <br><br>

        <label for="phone">Phone Number:</label>
        <input type="tel" id="phone" name="phone" required>
        <br><br>

        <label for="address">Home Address:</label>
        <textarea id="address" name="address" rows="4" cols="30" required></textarea>
        <br><br>

        <label for="state">State of Origin:</label>
        <input type="text" id="state" name="state" required>
        <br><br>

        <label for="nationality">Nationality:</label>
        <input type="text" id="nationality" name="nationality" required>
        <br><br>

        <h2>Academic Information</h2>

        <label for="department">Department:</label>
        <input type="text" id="department" name="department" required>
        <br><br>

        <label for="faculty">Faculty:</label>
        <input type="text" id="faculty" name="faculty" required>
        <br><br>

        <label for="course">Course of Study:</label>
        <input type="text" id="course" name="course" required>
        <br><br>

        <label for="level">Level:</label>
        <select id="level" name="level" required>
            <option value="">Select Level</option>
            <option value="100">100 Level</option>
            <option value="200">200 Level</option>
            <option value="300">300 Level</option>
            <option value="400">400 Level</option>
            <option value="500">500 Level</option>
            <option value="600">600 Level</option>
        </select>
        <br><br>

        <label for="admissionYear">Admission Year:</label>
        <input type="number" id="admissionYear" name="admissionYear"
            min="1900" max="2100" required>
        <br><br>

        <label for="registrationDate">Date of Registration:</label>
        <input type="date" id="registrationDate" name="registrationDate" required>
        <br><br>

        <h2>Guardian Information</h2>

        <label for="guardianName">Guardian's Name:</label>
        <input type="text" id="guardianName" name="guardianName" required>
        <br><br>

        <label for="guardianPhone">Guardian's Phone Number:</label>
        <input type="tel" id="guardianPhone" name="guardianPhone" required>
        <br><br>

        <h2>Emergency Contact Information</h2>

        <label for="emergencyContact">Emergency Contact:</label>
        <input type="tel" id="emergencyContact" name="emergencyContact" required>
        <br><br>

        <input type="submit" value="Submit">
        <input type="reset" value="Clear Form">

    </form>

    <p><a href="logout.php">Logout</a></p>

    <script src="js/script.js"></script>

</body>
</html>
