<?php

declare(strict_types=1);

require_once __DIR__ . '/connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('Login.html');
}

$email = strtolower(post_value('email'));
$password = (string) ($_POST['password'] ?? '');

if (filter_var($email, FILTER_VALIDATE_EMAIL) === false || $password === '') {
    redirect('Login.html', ['error' => 'invalid']);
}

$statement = db()->prepare(
    'SELECT id, first_name, email, password_hash FROM users WHERE email = :email LIMIT 1'
);
$statement->execute([':email' => $email]);
$user = $statement->fetch();

if (!is_array($user) || !password_verify($password, $user['password_hash'])) {
    redirect('Login.html', ['error' => 'invalid']);
}

if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
    $rehash = db()->prepare('UPDATE users SET password_hash = :password_hash WHERE id = :id');
    $rehash->execute([
        ':password_hash' => password_hash($password, PASSWORD_DEFAULT),
        ':id' => $user['id'],
    ]);
}

session_regenerate_id(true);
$_SESSION['user_id'] = (int) $user['id'];
$_SESSION['logged_in_at'] = time();

$updateLogin = db()->prepare('UPDATE users SET last_login_at = CURRENT_TIMESTAMP WHERE id = :id');
$updateLogin->execute([':id' => $user['id']]);

redirect('dashboard.php');

