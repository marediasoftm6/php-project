<?php
session_start();
include("../common/db.php");
include("config.php");
if (isset($_POST['signup'])) {
    if (!isset($_POST['csrf']) || $_POST['csrf'] !== $_SESSION['csrf_token']) {
        exit("Invalid request");
    }
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $storePass = (defined('AUTH_HASH_ENABLED') && AUTH_HASH_ENABLED) ? password_hash($password, PASSWORD_DEFAULT) : $password;
    $stmt = $conn->prepare("INSERT INTO `users` (`username`, `email`, `password`) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $email, $storePass);
    if ($stmt->execute()) {
        $_SESSION["user"] = ["username" => $username, "email" => $email, "user_id" => $stmt->insert_id];
        // Email verification token setup
        $ddl = "CREATE TABLE IF NOT EXISTS email_verification_tokens (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            token CHAR(64) NOT NULL,
            expires_at DATETIME NOT NULL,
            used TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_token (token),
            INDEX idx_user_expires (user_id, expires_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $conn->query($ddl);
        $token = generate_token(64);
        $expires = date('Y-m-d H:i:s', time() + 60 * 60); // 1 hour
        $ins = $conn->prepare("INSERT INTO email_verification_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
        $uid = (int)$stmt->insert_id;
        $ins->bind_param("iss", $uid, $token, $expires);
        $ins->execute();
        $verifyLink = base_url() . "/server/requests.php?verifyEmail=" . urlencode($token);
        $subject = "Verify your email for Quesiono";
        $body = "<p>Hello " . htmlspecialchars($username, ENT_QUOTES, 'UTF-8') . ",</p>
                 <p>Please verify your email address by clicking the link below:</p>
                 <p><a href=\"" . $verifyLink . "\">Verify Email</a></p>
                 <p>This link will expire in 1 hour.</p>";
        $sent = send_email($email, $subject, $body);
        $_SESSION['notice'] = $sent ? "Verification email sent." : "Could not send verification email. Use link: " . $verifyLink;
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
} else if (isset($_POST['editProfile'])) {
    if (!isset($_POST['csrf']) || $_POST['csrf'] !== $_SESSION['csrf_token']) {
        exit("Invalid request");
    }
    if (!isset($_SESSION['user']['user_id'])) {
        header("location: /Quesiono/?login=true");
        exit;
    }
    $uid = (int)$_SESSION['user']['user_id'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $upd = $conn->prepare("UPDATE users SET username=?, email=? WHERE id=?");
    $upd->bind_param("ssi", $username, $email, $uid);
    if ($upd->execute()) {
        $_SESSION['user']['username'] = $username;
        $_SESSION['user']['email'] = $email;
        header("location: /Quesiono/?profile=true");
    } else {
        echo "Profile update failed, try again";
    }
} else if (isset($_POST['changePassword'])) {
    if (!isset($_POST['csrf']) || $_POST['csrf'] !== $_SESSION['csrf_token']) {
        exit("Invalid request");
    }
    if (!isset($_SESSION['user']['user_id'])) {
        header("location: /Quesiono/?login=true");
        exit;
    }
    $uid = (int)$_SESSION['user']['user_id'];
    $old = $_POST['old_password'];
    $new = $_POST['new_password'];
    $stmt = $conn->prepare("SELECT password FROM users WHERE id=?");
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 1) {
        $row = $res->fetch_assoc();
        $valid = password_verify($old, $row['password']) || ($old === $row['password']);
        if ($valid) {
            $storePass = (defined('AUTH_HASH_ENABLED') && AUTH_HASH_ENABLED) ? password_hash($new, PASSWORD_DEFAULT) : $new;
            $upd = $conn->prepare("UPDATE users SET password=? WHERE id=?");
            $upd->bind_param("si", $storePass, $uid);
            if ($upd->execute()) {
                header("location: /Quesiono/?settings=true");
            } else {
                echo "Password update failed, try again";
            }
        } else {
            echo "Current password incorrect";
        }
    } else {
        echo "User not found";
    }
} else if (isset($_GET['verifyEmail'])) {
    $token = $_GET['verifyEmail'];
    if (!$token || strlen($token) !== 64) {
        exit("Invalid verification link");
    }
    $sel = $conn->prepare("SELECT user_id FROM email_verification_tokens WHERE token=? AND used=0 AND expires_at > NOW() LIMIT 1");
    $sel->bind_param("s", $token);
    $sel->execute();
    $res = $sel->get_result();
    if ($res->num_rows === 1) {
        $row = $res->fetch_assoc();
        $uid = (int)$row['user_id'];
        $upd = $conn->prepare("UPDATE email_verification_tokens SET used=1 WHERE token=?");
        $upd->bind_param("s", $token);
        $upd->execute();
        header("location: /Quesiono/?verified=true");
    } else {
        echo "Verification link is invalid or expired";
    }
}
