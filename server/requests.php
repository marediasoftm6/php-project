<?php
session_start();
include("../common/db.php");
if (isset($_POST['signup'])) {
    if (!isset($_POST['csrf']) || $_POST['csrf'] !== $_SESSION['csrf_token']) {
        exit("Invalid request");
    }
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO `users` (`username`, `email`, `password`) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $email, $hashed);
    if ($stmt->execute()) {
        $_SESSION["user"] = ["username" => $username, "email" => $email, "user_id" => $stmt->insert_id];
        header("location: /Quesiono/");
    } else {
        echo "Registration Failed!! Try Again";
    }
} else if (isset($_POST['login'])) {
    if (!isset($_POST['csrf']) || $_POST['csrf'] !== $_SESSION['csrf_token']) {
        exit("Invalid request");
    }
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $user_id = "";
    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE email=? AND username=? LIMIT 1");
    $stmt->bind_param("ss", $email, $username);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 1) {
        $row = $res->fetch_assoc();
        $valid = password_verify($password, $row['password']) || ($password === $row['password']);
        if ($valid) {
            $_SESSION["user"] = ["username" => $row['username'], "email" => $email, "user_id" => $row['id']];
            header("location: /Quesiono/");
        } else {
            echo "Login Failed!! Try Again";
        }
    } else {
        echo "Login Failed!! Try Again";
    }
} else if (isset($_GET['logout'])) {
    session_unset();
    header("location: /Quesiono/");

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
    if (!isset($_POST['csrf']) || $_POST['csrf'] !== $_SESSION['csrf_token']) {
        exit("Invalid request");
    }
    if (!isset($_SESSION['user']['user_id'])) {
        header("location: /Quesiono/?login=true");
        exit;
    }
    $title = $_POST['title'];
    $description = $_POST['description'];
    $category_id = $_POST['category'];
    $user_id = $_SESSION['user']['user_id'];

    if ($title === "" || $category_id === "") {
        echo "Please fill all required fields.";
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO questions (title, description, category_id, user_id)
                            VALUES (?, ?, ?, ?)");

    $stmt->bind_param("ssii", $title, $description, $category_id, $user_id);

    if ($stmt->execute()) {
        header("location: /Quesiono/");
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
    if (!isset($_POST['csrf']) || $_POST['csrf'] !== $_SESSION['csrf_token']) {
        exit("Invalid request");
    }
    if (!isset($_SESSION['user']['user_id'])) {
        header("location: /Quesiono/?login=true");
        exit;
    }
    $answer = $_POST['answer'];
    $question_id = $_POST['question_id'];
    $user_id = $_SESSION['user']['user_id'];

    $query = $conn->prepare("INSERT INTO answers (answer, question_id, user_id) VALUES (?, ?, ?)");

    $query->bind_param("sii", $answer, $question_id, $user_id);

    if ($query->execute()) {
        header("location: /Quesiono/?q-id=$question_id");
        exit;
    } else {
        echo "Answer not added, try again!: " . $query->error;
    }
} else if (isset($_GET['delete'])) {
    if (!isset($_GET['csrf']) || $_GET['csrf'] !== $_SESSION['csrf_token']) {
        exit("Invalid request");
    }
    if (!isset($_SESSION['user']['user_id'])) {
        header("location: /Quesiono/?login=true");
        exit;
    }
    $question_id = (int)$_GET['delete'];
    $user_id = $_SESSION['user']['user_id'];
    $check = $conn->prepare("SELECT user_id FROM questions WHERE id=?");
    $check->bind_param("i", $question_id);
    $check->execute();
    $owns = $check->get_result();
    if ($owns->num_rows === 1) {
        $row = $owns->fetch_assoc();
        if ((int)$row['user_id'] === (int)$user_id) {
            $del = $conn->prepare("DELETE FROM questions WHERE id=?");
            $del->bind_param("i", $question_id);
            if ($del->execute()) {
                header("location: /Quesiono/");
            } else {
                echo "Deletion Failed!! Try Again";
            }
        } else {
            echo "Not authorized";
        }
    } else {
        echo "Question not found";
    }
} else if (isset($_GET['deleteAnswer'])) {
    if (!isset($_GET['csrf']) || $_GET['csrf'] !== $_SESSION['csrf_token']) {
        exit("Invalid request");
    }
    if (!isset($_SESSION['user']['user_id'])) {
        header("location: /Quesiono/?login=true");
        exit;
    }
    $answer_id = (int)$_GET['deleteAnswer'];
    $user_id = $_SESSION['user']['user_id'];
    $check = $conn->prepare("SELECT user_id, question_id FROM answers WHERE id=?");
    $check->bind_param("i", $answer_id);
    $check->execute();
    $owns = $check->get_result();
    if ($owns->num_rows === 1) {
        $row = $owns->fetch_assoc();
        $qid = (int)$row['question_id'];
        if ((int)$row['user_id'] === (int)$user_id) {
            $del = $conn->prepare("DELETE FROM answers WHERE id=?");
            $del->bind_param("i", $answer_id);
            if ($del->execute()) {
                header("location: /Quesiono/?q-id=$qid");
            } else {
                echo "Deletion Failed!! Try Again";
            }
        } else {
            echo "Not authorized";
        }
    } else {
        echo "Answer not found";
    }
} else if (isset($_POST['editQuestion'])) {
    if (!isset($_POST['csrf']) || $_POST['csrf'] !== $_SESSION['csrf_token']) {
        exit("Invalid request");
    }
    if (!isset($_SESSION['user']['user_id'])) {
        header("location: /Quesiono/?login=true");
        exit;
    }
    $question_id = (int)$_POST['question_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $user_id = $_SESSION['user']['user_id'];
    $check = $conn->prepare("SELECT user_id FROM questions WHERE id=?");
    $check->bind_param("i", $question_id);
    $check->execute();
    $owns = $check->get_result();
    if ($owns->num_rows === 1) {
        $row = $owns->fetch_assoc();
        if ((int)$row['user_id'] === (int)$user_id) {
            $upd = $conn->prepare("UPDATE questions SET title=?, description=? WHERE id=?");
            $upd->bind_param("ssi", $title, $description, $question_id);
            if ($upd->execute()) {
                header("location: /Quesiono/?q-id=$question_id");
            } else {
                echo "Update failed, try again";
            }
        } else {
            echo "Not authorized";
        }
    } else {
        echo "Question not found";
    }
} else if (isset($_POST['editAnswer'])) {
    if (!isset($_POST['csrf']) || $_POST['csrf'] !== $_SESSION['csrf_token']) {
        exit("Invalid request");
    }
    if (!isset($_SESSION['user']['user_id'])) {
        header("location: /Quesiono/?login=true");
        exit;
    }
    $answer_id = (int)$_POST['answer_id'];
    $answer = $_POST['answer'];
    $user_id = $_SESSION['user']['user_id'];
    $check = $conn->prepare("SELECT user_id, question_id FROM answers WHERE id=?");
    $check->bind_param("i", $answer_id);
    $check->execute();
    $owns = $check->get_result();
    if ($owns->num_rows === 1) {
        $row = $owns->fetch_assoc();
        $qid = (int)$row['question_id'];
        if ((int)$row['user_id'] === (int)$user_id) {
            $upd = $conn->prepare("UPDATE answers SET answer=? WHERE id=?");
            $upd->bind_param("si", $answer, $answer_id);
            if ($upd->execute()) {
                header("location: /Quesiono/?q-id=$qid");
            } else {
                echo "Update failed, try again";
            }
        } else {
            echo "Not authorized";
        }
    } else {
        echo "Answer not found";
    }
} else if (isset($_POST['editCategory'])) {
    if (!isset($_POST['csrf']) || $_POST['csrf'] !== $_SESSION['csrf_token']) {
        exit("Invalid request");
    }
    if (!isset($_SESSION['user']['user_id'])) {
        header("location: /Quesiono/?login=true");
        exit;
    }
    $category_id = (int)$_POST['category_id'];
    $name = $_POST['name'];
    $upd = $conn->prepare("UPDATE category SET name=? WHERE id=?");
    $upd->bind_param("si", $name, $category_id);
    if ($upd->execute()) {
        header("location: /Quesiono/?categories=true");
    } else {
        echo "Update failed, try again";
    }
} else if (isset($_GET['deleteCategory'])) {
    if (!isset($_GET['csrf']) || $_GET['csrf'] !== $_SESSION['csrf_token']) {
        exit("Invalid request");
    }
    if (!isset($_SESSION['user']['user_id'])) {
        header("location: /Quesiono/?login=true");
        exit;
    }
    $category_id = (int)$_GET['deleteCategory'];
    $cntStmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM questions WHERE category_id=?");
    $cntStmt->bind_param("i", $category_id);
    $cntStmt->execute();
    $cntRes = $cntStmt->get_result();
    $cntRow = $cntRes->fetch_assoc();
    if ((int)$cntRow['cnt'] > 0) {
        echo "Cannot delete category with existing questions";
        exit;
    }
    $del = $conn->prepare("DELETE FROM category WHERE id=?");
    $del->bind_param("i", $category_id);
    if ($del->execute()) {
        header("location: /Quesiono/?categories=true");
    } else {
        echo "Deletion Failed!! Try Again";
    }
}
