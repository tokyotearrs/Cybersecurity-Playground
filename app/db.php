<?php
$host = getenv('DB_HOST') ?: 'db';
$db = getenv('DB_NAME') ?: 'cyber_playground';
$user = getenv('DB_USER') ?: 'playground_user';
$pass = getenv('DB_PASSWORD') ?: 'playground_pass';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die('Datenbankverbindung fehlgeschlagen.');
}

$conn->set_charset('utf8mb4');
