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

if (!function_exists('is_verified_user')) {
    function is_verified_user($conn) {
        if (!isset($_SESSION['user']['user_id'])) return false;
        $uid = (int)$_SESSION['user']['user_id'];
        $stmt = $conn->prepare("SELECT verified FROM users WHERE id=? LIMIT 1");
        $stmt->bind_param("i", $uid);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows === 1) {
            $row = $res->fetch_assoc();
            return (bool)$row['verified'];
        }
        return false;
    }
}
