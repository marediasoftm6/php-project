<?php
$host = "localhost";
$username = "root";
$password = null;
$database = "discuss";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("not connected with db" . $conn->connect_error);
}
$conn->set_charset("utf8mb4");
