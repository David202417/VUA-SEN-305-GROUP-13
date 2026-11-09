<?php

declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ]);
    session_start();
}

/**
 * Return the application database connection.
 *
 * SQLite keeps this small project runnable without a separate database server.
 * The database and table are created automatically on the first request.
 */
function db(): PDO
{
    static $connection = null;

    if ($connection instanceof PDO) {
        return $connection;
    }

    $dataDirectory = __DIR__ . DIRECTORY_SEPARATOR . 'data';
    if (!is_dir($dataDirectory) && !mkdir($dataDirectory, 0700, true) && !is_dir($dataDirectory)) {
        throw new RuntimeException('Unable to create the data directory.');
    }

    $connection = new PDO('sqlite:' . $dataDirectory . DIRECTORY_SEPARATOR . 'students.sqlite');
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $connection->exec('PRAGMA foreign_keys = ON');
    $connection->exec('PRAGMA busy_timeout = 5000');

    $connection->exec(
        'CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            student_id TEXT NOT NULL UNIQUE,
            first_name TEXT NOT NULL,
            last_name TEXT NOT NULL,
            middle_name TEXT,
            date_of_birth TEXT NOT NULL,
            gender TEXT NOT NULL,
            email TEXT NOT NULL UNIQUE,
            phone TEXT NOT NULL,
            address TEXT NOT NULL,
            state_of_origin TEXT NOT NULL,
            nationality TEXT NOT NULL,
            department TEXT NOT NULL,
            faculty TEXT NOT NULL,
            course TEXT NOT NULL,
            level TEXT NOT NULL,
            admission_year INTEGER NOT NULL,
            registration_date TEXT NOT NULL,
            guardian_name TEXT NOT NULL,
            guardian_phone TEXT NOT NULL,
            emergency_contact TEXT NOT NULL,
            password_hash TEXT NOT NULL,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            last_login_at TEXT
        )'
    );

    return $connection;
}

function redirect(string $path, array $query = []): never
{
    $location = $path;
    if ($query !== []) {
        $location .= '?' . http_build_query($query);
    }

    header('Location: ' . $location, true, 303);
    exit;
}

function post_value(string $key): string
{
    return trim((string) ($_POST[$key] ?? ''));
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

