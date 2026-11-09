<?php

$host = "127.0.0.1";
$username = "root";
$password = "";
$database = "student_biodata";
$port = 3307;

$conn = new mysqli($host, $username, $password, $database, $port);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

?>