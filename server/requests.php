<?php

session_start();
include("../common/db.php");


if (isset($_POST['signup'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    $user = $conn->prepare(query: "Insert into `users` (`username`, `email`, `password`)
    values('$username', '$email', '$password');
    ");

    $result = $user->execute();
    echo $user->insert_id;

    if ($result) {
        $_SESSION["user"] = ["username" => $username, "email" => $email, "password" => $password, "user_id" => $user->insert_id];
        header(header: "location: /discussworld");
    } else {
        echo "Registration Failed!! Try Again";
    }
} else if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $user_id = "";

    $qeury = "select * from users where email='$email' and username='$username' and password='$password'";
    $result = $conn->query(query: $qeury);
    if ($result->num_rows == 1) {

        foreach ($result as $row) {
            $username = $row['username'];
            $user_id = $row['id'];
        }

        $_SESSION["user"] = ["username" => $username, "email" => $email, "password" => $password, "user_id" => $user_id];
        header(header: "location: /discussworld");
    } else {
        echo "Login Failed!! Try Again";
    }
} else if (isset($_GET['logout'])) {
    session_unset();
    header(header: "location: /discussworld");
} else if (isset($_POST["ask"])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $category_id = $_POST['category_id'];
    $user_id = $_SESSION['user']['user_id'];

    $user_id = $conn->prepare(query: "Insert into `questions` (`title`, `description`, `category_id`)
    values('$title', '$description', '$category_id');");

    $result = $user->execute();
    echo $user->insert_id;

    if ($result) {
        $_SESSION["user"] = ["username" => $username, "email" => $email, "password" => $password, "user_id" => $user->insert_id];
        header(header: "location: /discussworld");
    } else {
        echo "Registration Failed!! Try Again";
    }
}
