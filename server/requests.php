<?php

session_start();
include("../common/db.php");
if (isset($_POST['signup'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    $user = $conn->prepare("Insert into `users` (`username`, `email`, `password`)
    values('$username', '$email', '$password');
    ");

    $result = $user->execute();

    if ($result) {
        $_SESSION["user"] = ["username" => $username, "email" => $email, "password" => $password];
        header(header: "location: /discussworld");
    } else {
        echo "Registration Failed!! Try Again";
    }
} else if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $qeury = "select * from users where email='$email' and username='$username' and password='$password'";
    $result = $conn->query(query: $qeury);
    if ($result->num_rows == 1) {
        $_SESSION["user"] = ["username" => $username, "email" => $email, "password" => $password];
        header(header: "location: /discussworld");
    } else {
        echo "Registration Failed!! Try Again";
    }
} else if (isset($_GET['logout'])) {
    session_unset();
    header(header: "location: /discussworld");
}
