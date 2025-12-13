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
        header(header: "location: /quesiono");
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
        header(header: "location: /quesiono");
    } else {
        echo "Login Failed!! Try Again";
    }
} else if (isset($_GET['logout'])) {
    session_unset();
    header(header: "location: /quesiono");

    // } else if (isset($_POST["ask"])) {
    //     $title = $_POST['title'];
    //     $description = $_POST['description'];
    //     $category_id = $_POST['category'];
    //     $user_id = $_SESSION['user']['user_id'];
    //     $question = $conn->prepare(query: "INSERT INTO `questions` (`id`, `title`, `description`, `category_id`, `user_id`)
    //     values(NULL, '$title', '$description', '$category_id', '$user_id');
    //     ");
    //     $result = $question->execute();
    //     $question->insert_id;
    //     if ($result) {
    //         header(header: "location: /quesiono");
    //     } else {
    //         echo "Question not added, please check all the fields.";
    //     }
    // }

} else if (isset($_POST["ask"])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $category_id = $_POST['category'];
    $user_id = $_SESSION['user']['user_id'];

    $stmt = $conn->prepare("INSERT INTO questions (title, description, category_id, user_id)
                            VALUES (?, ?, ?, ?)");

    $stmt->bind_param("ssii", $title, $description, $category_id, $user_id);

    if ($stmt->execute()) {
        header("location: /quesiono/");
    } else {
        echo "Question not added: " . $stmt->error;
    }

    // } else if (isset($_POST["answers"])) {
    //     $answer = $_POST['answer'];
    //     $question_id = $_POST['question_id'];
    //     $user_id = $_SESSION['user']['user_id'];
    //     $query = $conn->prepare("INSERT INTO answers (id, answer, question_id, user_id)
    //                             VALUES (?, ?, ?, ?)");
    //     $query->bind_param("ssii", NULL, $answer, $question_id, $user_id);
    //     if ($query->execute()) {
    //         header("location: /quesiono");
    //     } else {
    //         echo "Answer not added: " . $query->error;
    //     }
    // }

} else if (isset($_POST["answer"])) {
    $answer = $_POST['answer'];
    $question_id = $_POST['question_id'];
    $user_id = $_SESSION['user']['user_id'];

    $query = $conn->prepare("INSERT INTO answers (answer, question_id, user_id) VALUES (?, ?, ?)");

    $query->bind_param("sii", $answer, $question_id, $user_id);

    if ($query->execute()) {
        header("location: /quesiono?q-id=$question_id");
        exit;
    } else {
        echo "Answer is not added to the website: " . $query->error;
    }
}