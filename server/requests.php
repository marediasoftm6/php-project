<?php

session_start();
include("../common/db.php");
if (isset($_POST['signup'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $user_id = 0;

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
        foreach ($result as $row) {
            $username = $row['username'];
            $user_id = $row['user_id'];
        }
        $_SESSION["user"] = ["username" => $username, "email" => $email, "password" => $password, "user_id" => $user->insert_id];
        header(header: "location: /discussworld");
    } else {
        echo "Registration Failed!! Try Again";
    }
} else if (isset($_GET['logout'])) {
    session_unset();
    header(header: "location: /discussworld");
} else if (isset($_POST["ask"])) {
    print_r(value: $_POST);
}
