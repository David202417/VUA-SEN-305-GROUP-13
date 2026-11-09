<?php

declare(strict_types=1);

require_once __DIR__ . '/connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('biodata.html');
}

$studentId = strtoupper(post_value('studentID'));
$firstName = post_value('firstName');
$lastName = post_value('lastName');
$middleName = post_value('middleName');
$dateOfBirth = post_value('dob');
$gender = post_value('gender');
$email = strtolower(post_value('email'));
$phone = post_value('phone');
$address = post_value('address');
$state = post_value('state');
$nationality = post_value('nationality');
$department = post_value('department');
$faculty = post_value('faculty');
$course = post_value('course');
$level = post_value('level');
$admissionYear = post_value('admissionYear');
$registrationDate = post_value('registrationDate');
$guardianName = post_value('guardianName');
$guardianPhone = post_value('guardianPhone');
$emergencyContact = post_value('emergencyContact');
$password = (string) ($_POST['password'] ?? '');
$confirmPassword = (string) ($_POST['confirmPassword'] ?? '');

$validName = static fn (string $value): bool => preg_match("/^[\\p{L}\\s'-]{2,80}$/u", $value) === 1;
$validPhone = static fn (string $value): bool => preg_match('/^\\+?[0-9 ()-]{7,20}$/', $value) === 1;
$validDate = static function (string $value): bool {
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
    return $date instanceof DateTimeImmutable && $date->format('Y-m-d') === $value;
};

$errors = [];

if (preg_match('/^VUG\/SEN\/\d{2}\/\d{5}$/', $studentId) !== 1) {
    $errors[] = 'studentID';
}
if (!$validName($firstName) || !$validName($lastName) || ($middleName !== '' && !$validName($middleName))) {
    $errors[] = 'name';
}
if (!$validDate($dateOfBirth)) {
    $errors[] = 'dob';
} else {
    $birthDate = new DateTimeImmutable($dateOfBirth);
    if ($birthDate > new DateTimeImmutable('-15 years')) {
        $errors[] = 'dob';
    }
}
if (!in_array($gender, ['male', 'female', 'other', 'prefer-not-to-say'], true)) {
    $errors[] = 'gender';
}
if (filter_var($email, FILTER_VALIDATE_EMAIL) === false || strlen($email) > 254) {
    $errors[] = 'email';
}
if (!$validPhone($phone) || !$validPhone($guardianPhone) || !$validPhone($emergencyContact)) {
    $errors[] = 'phone';
}
if (strlen($address) < 10 || strlen($address) > 300) {
    $errors[] = 'address';
}
if (!$validName($state) || !$validName($nationality) || !$validName($guardianName)) {
    $errors[] = 'contact';
}
if (strlen($department) < 2 || strlen($department) > 120 ||
    strlen($faculty) < 2 || strlen($faculty) > 120 ||
    strlen($course) < 2 || strlen($course) > 120) {
    $errors[] = 'academic';
}
if (!in_array($level, ['100', '200', '300', '400', '500', '600'], true)) {
    $errors[] = 'level';
}

$admissionYearNumber = filter_var($admissionYear, FILTER_VALIDATE_INT);
$maximumAdmissionYear = (int) date('Y') + 1;
if ($admissionYearNumber === false || $admissionYearNumber < 1990 || $admissionYearNumber > $maximumAdmissionYear) {
    $errors[] = 'admissionYear';
}
if (!$validDate($registrationDate)) {
    $errors[] = 'registrationDate';
} elseif ($admissionYearNumber !== false && $registrationDate < sprintf('%04d-01-01', $admissionYearNumber)) {
    $errors[] = 'registrationDate';
}
if (strlen($password) < 8 || strlen($password) > 72 ||
    preg_match('/[A-Za-z]/', $password) !== 1 ||
    preg_match('/\d/', $password) !== 1 ||
    !hash_equals($password, $confirmPassword)) {
    $errors[] = 'password';
}

if ($errors !== []) {
    redirect('biodata.html', ['error' => in_array('password', $errors, true) ? 'password' : 'invalid']);
}

try {
    $statement = db()->prepare(
        'INSERT INTO users (
            student_id, first_name, last_name, middle_name, date_of_birth, gender,
            email, phone, address, state_of_origin, nationality, department, faculty,
            course, level, admission_year, registration_date, guardian_name,
            guardian_phone, emergency_contact, password_hash
        ) VALUES (
            :student_id, :first_name, :last_name, :middle_name, :date_of_birth, :gender,
            :email, :phone, :address, :state_of_origin, :nationality, :department, :faculty,
            :course, :level, :admission_year, :registration_date, :guardian_name,
            :guardian_phone, :emergency_contact, :password_hash
        )'
    );

    $statement->execute([
        ':student_id' => $studentId,
        ':first_name' => $firstName,
        ':last_name' => $lastName,
        ':middle_name' => $middleName === '' ? null : $middleName,
        ':date_of_birth' => $dateOfBirth,
        ':gender' => $gender,
        ':email' => $email,
        ':phone' => $phone,
        ':address' => $address,
        ':state_of_origin' => $state,
        ':nationality' => $nationality,
        ':department' => $department,
        ':faculty' => $faculty,
        ':course' => $course,
        ':level' => $level,
        ':admission_year' => $admissionYearNumber,
        ':registration_date' => $registrationDate,
        ':guardian_name' => $guardianName,
        ':guardian_phone' => $guardianPhone,
        ':emergency_contact' => $emergencyContact,
        ':password_hash' => password_hash($password, PASSWORD_DEFAULT),
    ]);
} catch (PDOException $exception) {
    if ($exception->getCode() === '23000' || str_contains($exception->getMessage(), 'UNIQUE constraint failed')) {
        redirect('biodata.html', ['error' => 'exists']);
    }

    error_log($exception->getMessage());
    redirect('biodata.html', ['error' => 'server']);
}

redirect('Login.html', ['registered' => '1']);

