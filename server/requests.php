<?php
session_start();
include("../common/db.php");
include("config.php");

if (isset($_GET['toggleFollow'])) {
    if (!isset($_SESSION['user']['user_id'])) {
        echo json_encode(['success' => false, 'error' => 'not_logged_in']);
        exit;
    }
    $followerId = $_SESSION['user']['user_id'];
    $followingId = (int)$_GET['toggleFollow'];

    if ($followerId === $followingId) {
        echo json_encode(['success' => false, 'error' => 'self_follow']);
        exit;
    }

    $check = $conn->prepare("SELECT id FROM follows WHERE follower_id = ? AND following_id = ?");
    $check->bind_param("ii", $followerId, $followingId);
    $check->execute();
    $res = $check->get_result();

    if ($res->num_rows > 0) {
        // Unfollow
        $del = $conn->prepare("DELETE FROM follows WHERE follower_id = ? AND following_id = ?");
        $del->bind_param("ii", $followerId, $followingId);
        $del->execute();
        echo json_encode(['success' => true, 'status' => 'unfollowed']);
    } else {
        // Follow
        $ins = $conn->prepare("INSERT INTO follows (follower_id, following_id) VALUES (?, ?)");
        $ins->bind_param("ii", $followerId, $followingId);
        $ins->execute();

        // Create Notification
        $followerName = $_SESSION['user']['username'];
        $msg = "<strong>" . htmlspecialchars($followerName) . "</strong> started following you.";
        $notif = $conn->prepare("INSERT INTO notifications (user_id, type, source_id, message) VALUES (?, 'follow', ?, ?)");
        $notif->bind_param("iis", $followingId, $followerId, $msg);
        $notif->execute();

        echo json_encode(['success' => true, 'status' => 'followed']);
    }
    exit;
}

if (isset($_GET['markRead'])) {
    if (!isset($_SESSION['user']['user_id'])) {
        header("location: /Quesiono/");
        exit;
    }
    $uid = $_SESSION['user']['user_id'];
    if ($_GET['markRead'] === 'all') {
        $upd = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
        $upd->bind_param("i", $uid);
        $upd->execute();
    }
    header("location: " . $_SERVER['HTTP_REFERER']);
    exit;
}

/**
 * Checks if the current user is verified.
 */
function is_verified($conn) {
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

if (isset($_POST['signup'])) {
    if (!isset($_POST['csrf']) || $_POST['csrf'] !== $_SESSION['csrf_token']) {
        exit("Invalid request");
    }
    $username = $_POST['username'];
    $email = $_POST['email'];
    $gender = $_POST['gender'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        exit("Passwords do not match");
    }

    // Check if username already exists
    $checkUser = $conn->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
    $checkUser->bind_param("s", $username);
    $checkUser->execute();
    if ($checkUser->get_result()->num_rows > 0) {
        $_SESSION['error'] = "Username is already taken";
        header("location: /Quesiono/signup");
        exit;
    }

    // Check if email already exists
    $checkEmail = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $checkEmail->bind_param("s", $email);
    $checkEmail->execute();
    if ($checkEmail->get_result()->num_rows > 0) {
        $_SESSION['error'] = "Email is already taken";
        header("location: /Quesiono/signup");
        exit;
    }

    $storePass = (defined('AUTH_HASH_ENABLED') && AUTH_HASH_ENABLED) ? password_hash($password, PASSWORD_DEFAULT) : $password;
    $stmt = $conn->prepare("INSERT INTO `users` (`username`, `email`, `gender`, `password`) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $email, $gender, $storePass);
    if ($stmt->execute()) {
        $uid = (int)$stmt->insert_id;
        // Do NOT set $_SESSION["user"] here anymore.
        // The user must verify first, then log in manually.
        
        // Email verification token setup
        $ddl = "CREATE TABLE IF NOT EXISTS email_verification_tokens (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            token CHAR(64) NOT NULL,
            code CHAR(6) NOT NULL,
            expires_at DATETIME NOT NULL,
            used TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_token (token),
            INDEX idx_code (code),
            INDEX idx_user_expires (user_id, expires_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $conn->query($ddl);

        // Check if code column exists (migration for existing table)
        $res = $conn->query("SHOW COLUMNS FROM email_verification_tokens LIKE 'code'");
        if ($res->num_rows === 0) {
            $conn->query("ALTER TABLE email_verification_tokens ADD COLUMN code CHAR(6) NOT NULL AFTER token");
        }

        $token = generate_token(64);
        $code = sprintf("%06d", mt_rand(1, 999999));
        $expires = date('Y-m-d H:i:s', time() + 60 * 60); // 1 hour
        $ins = $conn->prepare("INSERT INTO email_verification_tokens (user_id, token, code, expires_at) VALUES (?, ?, ?, ?)");
        $ins->bind_param("isss", $uid, $token, $code, $expires);
        $ins->execute();

        $verifyLink = base_url() . "/server/requests.php?verifyEmail=" . urlencode($token);
        $subject = "Verify your email for Quesiono";
        $body = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Verify your email</title>
        </head>
        <body style='margin: 0; padding: 0; background-color: #f4f7f9; font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif;'>
            <table border='0' cellpadding='0' cellspacing='0' width='100%' style='background-color: #f4f7f9; padding: 40px 0;'>
                <tr>
                    <td align='center'>
                        <table border='0' cellpadding='0' cellspacing='0' width='600' style='background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05);'>
                            <!-- Header -->
                            <tr>
                                <td align='center' style='padding: 40px 40px 20px 40px;'>
                                    <h1 style='margin: 0; color: #0d6efd; font-size: 28px; font-weight: 800; letter-spacing: -0.5px;'>Quesiono</h1>
                                </td>
                            </tr>
                            
                            <!-- Hero Section -->
                            <tr>
                                <td style='padding: 0 40px 20px 40px; text-align: center;'>
                                    <h2 style='margin: 0; color: #1a1a1a; font-size: 24px; font-weight: 700; line-height: 1.3;'>Welcome to our community!</h2>
                                    <p style='margin: 12px 0 0 0; color: #666666; font-size: 16px; line-height: 1.5;'>Hi " . htmlspecialchars($username, ENT_QUOTES, 'UTF-8') . ", we're excited to have you on board. Please verify your email to get started.</p>
                                </td>
                            </tr>

                            <!-- Main Button -->
                            <tr>
                                <td align='center' style='padding: 20px 40px;'>
                                    <a href=\"" . $verifyLink . "\" style='display: inline-block; background-color: #0d6efd; color: #ffffff; padding: 16px 32px; text-decoration: none; border-radius: 12px; font-weight: 700; font-size: 16px; transition: background-color 0.2s;'>Verify Email Address</a>
                                </td>
                            </tr>

                            <!-- Divider -->
                            <tr>
                                <td style='padding: 20px 40px;'>
                                    <div style='height: 1px; background-color: #eeeeee; width: 100%;'></div>
                                </td>
                            </tr>

                            <!-- Alternate Code -->
                            <tr>
                                <td align='center' style='padding: 10px 40px 40px 40px;'>
                                    <p style='margin: 0 0 16px 0; color: #666666; font-size: 14px;'>Or enter this code on the verification page:</p>
                                    <div style='background-color: #f8f9fa; border: 2px dashed #dee2e6; border-radius: 12px; padding: 20px; display: inline-block;'>
                                        <span style='font-family: \"Courier New\", Courier, monospace; font-size: 36px; font-weight: 800; color: #333333; letter-spacing: 8px;'>" . $code . "</span>
                                    </div>
                                    <p style='margin: 20px 0 0 0; color: #999999; font-size: 12px;'>This link and code will expire in 1 hour.</p>
                                </td>
                            </tr>

                            <!-- Footer -->
                            <tr>
                                <td style='padding: 30px 40px; background-color: #fafafa; text-align: center;'>
                                    <p style='margin: 0; color: #999999; font-size: 12px; line-height: 1.5;'>
                                        &copy; " . date('Y') . " Quesiono. All rights reserved.<br>
                                        If you didn't create an account, you can safely ignore this email.
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>";
        
        $sent = send_email($email, $subject, $body);
        $_SESSION['notice'] = $sent ? "Verification email sent. Please check your inbox." : "Verification email failed to send. Your confirmation code is: <strong>" . $code . "</strong>";
        header("location: /Quesiono/verify-code");
    } else {
        echo "Registration Failed!! Try Again";
    }
} else if (isset($_POST['login'])) {
    if (!isset($_POST['csrf']) || $_POST['csrf'] !== $_SESSION['csrf_token']) {
        exit("Invalid request");
    }
    if (isset($_SESSION['user'])) {
        header("location: /Quesiono/");
        exit;
    }
    $email = $_POST['email'];
    $password = $_POST['password'];
    $stmt = $conn->prepare("SELECT id, username, password, verified FROM users WHERE email=? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 1) {
        $row = $res->fetch_assoc();
        $valid = password_verify($password, $row['password']) || ($password === $row['password']);
        if ($valid) {
            if (!(int)$row['verified']) {
                // For unverified users, we only store minimal info for verification page
                $_SESSION["temp_verify_user"] = ["email" => $email, "user_id" => $row['id']];
                $_SESSION['notice'] = "Please verify your email to continue.";
                header("location: /Quesiono/verify-code");
                exit;
            }
            $_SESSION["user"] = ["username" => $row['username'], "email" => $email, "user_id" => $row['id']];
            header("location: /Quesiono/");
            exit;
        }
    }
    $_SESSION['error'] = "Login Failed!! Try Again";
    header("location: /Quesiono/login");
    exit;
} else if (isset($_GET['logout'])) {
    session_unset();
    header("location: /Quesiono/");
} else if (isset($_POST["ask"])) {
    if (!isset($_POST['csrf']) || $_POST['csrf'] !== $_SESSION['csrf_token']) {
        exit("Invalid request");
    }
    if (!isset($_SESSION['user']['user_id'])) {
        header("location: /Quesiono/login");
        exit;
    }
    if (!is_verified($conn)) {
        $_SESSION['error'] = "Please verify your email to ask questions.";
        header("location: /Quesiono/verify-code");
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
        header("location: /Quesiono/login");
        exit;
    }
    if (!is_verified($conn)) {
        $_SESSION['error'] = "Please verify your email to post answers.";
        header("location: /Quesiono/verify-code");
        exit;
    }
    $answer = $_POST['answer'];
    $question_id = $_POST['question_id'];
    $user_id = $_SESSION['user']['user_id'];

    $query = $conn->prepare("INSERT INTO answers (answer, question_id, user_id) VALUES (?, ?, ?)");

    $query->bind_param("sii", $answer, $question_id, $user_id);

    if ($query->execute()) {
        check_and_award_badges($conn, $user_id);
        header("location: /Quesiono/$question_id");
        exit;
    } else {
        echo "Answer not added, try again!: " . $query->error;
    }
} else if (isset($_POST["createRichPost"])) {
    if (!isset($_POST['csrf']) || $_POST['csrf'] !== $_SESSION['csrf_token']) {
        exit("Invalid request");
    }
    if (!isset($_SESSION['user']['user_id'])) {
        header("location: /Quesiono/login");
        exit;
    }
    if (!is_verified($conn)) {
        $_SESSION['error'] = "Please verify your email to create rich posts.";
        header("location: /Quesiono/verify-code");
        exit;
    }

    $title = $_POST['title'];
    $subtitle = $_POST['subtitle'];
    $content = $_POST['content'];
    $template = $_POST['template'];
    $category_id = (!empty($_POST['category']) && is_numeric($_POST['category'])) ? (int)$_POST['category'] : null;
    $user_id = $_SESSION['user']['user_id'];

    // Generate unique SEO-friendly slug
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
    $slug = preg_replace('/-+/', '-', $slug); // Remove double dashes
    $slug = $slug . '-' . substr(uniqid(), -5); // Add short unique ID instead of timestamp for cleaner URLs

    // Handle links from dynamic inputs
    $links = [];
    if (isset($_POST['link_texts']) && isset($_POST['link_urls'])) {
        foreach ($_POST['link_texts'] as $index => $text) {
            $url = $_POST['link_urls'][$index];
            if (!empty($text) && !empty($url)) {
                // Basic URL cleanup
                if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
                    $url = "https://" . $url;
                }
                $links[] = ['text' => htmlspecialchars($text), 'url' => filter_var($url, FILTER_SANITIZE_URL)];
            }
        }
    }
    $links_json = json_encode($links);
    
    $stmt = $conn->prepare("INSERT INTO posts (user_id, category_id, title, slug, subtitle, content, links, template) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iissssss", $user_id, $category_id, $title, $slug, $subtitle, $content, $links_json, $template);

    if ($stmt->execute()) {
        check_and_award_badges($conn, $user_id);
        $_SESSION['notice'] = "Your rich post has been published successfully!";
        header("location: /Quesiono/");
        exit;
    } else {
        $_SESSION['error'] = "Post creation failed: " . $stmt->error;
        header("location: /Quesiono/create-post");
        exit;
    }
} else if (isset($_GET['delete'])) {
    if (!isset($_GET['csrf']) || $_GET['csrf'] !== $_SESSION['csrf_token']) {
        exit("Invalid request");
    }
    if (!isset($_SESSION['user']['user_id'])) {
        header("location: /Quesiono/login");
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
        header("location: /Quesiono/login");
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
                header("location: /Quesiono/$qid");
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
        header("location: /Quesiono/login");
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
                header("location: /Quesiono/$question_id");
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
        header("location: /Quesiono/login");
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
                header("location: /Quesiono/$qid");
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
        header("location: /Quesiono/login");
        exit;
    }
    $category_id = (int)$_POST['category_id'];
    $name = $_POST['name'];
    $upd = $conn->prepare("UPDATE category SET name=? WHERE id=?");
    $upd->bind_param("si", $name, $category_id);
    if ($upd->execute()) {
        header("location: /Quesiono/categories");
    } else {
        echo "Update failed, try again";
    }
} else if (isset($_GET['deleteCategory'])) {
    if (!isset($_GET['csrf']) || $_GET['csrf'] !== $_SESSION['csrf_token']) {
        exit("Invalid request");
    }
    if (!isset($_SESSION['user']['user_id'])) {
        header("location: /Quesiono/login");
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
        header("location: /Quesiono/categories");
    } else {
        echo "Deletion Failed!! Try Again";
    }
} else if (isset($_POST['editProfile'])) {
    if (!isset($_POST['csrf']) || $_POST['csrf'] !== $_SESSION['csrf_token']) {
        exit("Invalid request");
    }
    if (!isset($_SESSION['user']['user_id'])) {
        header("location: /Quesiono/login");
        exit;
    }
    $uid = (int)$_SESSION['user']['user_id'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $gender = $_POST['gender'];
    $birthdate = $_POST['birthdate'];

    $upd = $conn->prepare("UPDATE users SET username=?, email=?, gender=?, birthdate=? WHERE id=?");
    $upd->bind_param("ssssi", $username, $email, $gender, $birthdate, $uid);
    if ($upd->execute()) {
        $_SESSION['user']['username'] = $username;
        $_SESSION['user']['email'] = $email;
        header("location: /Quesiono/" . $username);
    } else {
        echo "Profile update failed, try again";
    }
} else if (isset($_POST['changePassword'])) {
    if (!isset($_POST['csrf']) || $_POST['csrf'] !== $_SESSION['csrf_token']) {
        exit("Invalid request");
    }
    if (!isset($_SESSION['user']['user_id'])) {
        header("location: /Quesiono/login");
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
                header("location: /Quesiono/settings");
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
    $token = trim($_GET['verifyEmail']);
    if (!$token || strlen($token) !== 64) {
        exit("Invalid verification link");
    }
    
    // First check if the token exists and is not used
    $sel = $conn->prepare("SELECT id, user_id, expires_at FROM email_verification_tokens WHERE token=? AND used=0 LIMIT 1");
    $sel->bind_param("s", $token);
    $sel->execute();
    $res = $sel->get_result();
    
    if ($res->num_rows === 1) {
        $row = $res->fetch_assoc();
        $tid = (int)$row['id'];
        $uid = (int)$row['user_id'];
        $expiresAt = strtotime($row['expires_at']);

        // Check if expired using PHP time to avoid MySQL timezone issues
        if ($expiresAt > time()) {
            // Mark user as verified
            $updUser = $conn->prepare("UPDATE users SET verified=1 WHERE id=?");
            $updUser->bind_param("i", $uid);
            $updUser->execute();

            // Mark token as used
            $updToken = $conn->prepare("UPDATE email_verification_tokens SET used=1 WHERE id=?");
            $updToken->bind_param("i", $tid);
            $updToken->execute();

            // Clear any existing session to force fresh login after verification
            unset($_SESSION['user']);
            unset($_SESSION['temp_verify_user']);
            
            header("location: /Quesiono/verified");
            exit;
        } else {
            exit("This verification link has expired.");
        }
    } else {
        exit("Invalid or already used verification link");
    }
} else if (isset($_POST['verifyCode'])) {
    if (!isset($_POST['csrf']) || $_POST['csrf'] !== $_SESSION['csrf_token']) {
        exit("Invalid request");
    }
    $code = trim($_POST['code']);
    $email = trim($_POST['email']);

    // Get user id from email
    $uStmt = $conn->prepare("SELECT id FROM users WHERE email=? LIMIT 1");
    $uStmt->bind_param("s", $email);
    $uStmt->execute();
    $uRes = $uStmt->get_result();
    
    if ($uRes->num_rows === 1) {
        $uRow = $uRes->fetch_assoc();
        $uid = (int)$uRow['id'];

        // First check if the code exists and is not used
        $sel = $conn->prepare("SELECT id, expires_at FROM email_verification_tokens WHERE user_id=? AND code=? AND used=0 LIMIT 1");
        $sel->bind_param("is", $uid, $code);
        $sel->execute();
        $res = $sel->get_result();

        if ($res->num_rows === 1) {
            $tokenRow = $res->fetch_assoc();
            $tid = (int)$tokenRow['id'];
            $expiresAt = strtotime($tokenRow['expires_at']);

            // Check if expired using PHP time to avoid MySQL timezone issues
            if ($expiresAt > time()) {
                // Mark user as verified
                $updUser = $conn->prepare("UPDATE users SET verified=1 WHERE id=?");
                $updUser->bind_param("i", $uid);
                $updUser->execute();

                // Mark token as used
                $updToken = $conn->prepare("UPDATE email_verification_tokens SET used=1 WHERE id=?");
                $updToken->bind_param("i", $tid);
                $updToken->execute();

                // Clear any existing session to force fresh login after verification
                unset($_SESSION['user']);
                unset($_SESSION['temp_verify_user']);

                header("location: /Quesiono/verified");
                exit;
            } else {
                $_SESSION['error'] = "This verification code has expired. Please sign up again.";
            }
        } else {
            $_SESSION['error'] = "The code you entered is incorrect. Please check and try again.";
        }
    } else {
        $_SESSION['error'] = "No account found with that email address.";
    }
    header("location: " . $_SERVER['HTTP_REFERER']);
    exit;
}

/**
 * Checks if a user meets conditions for any badges and awards them.
 */
function check_and_award_badges($conn, $user_id) {
    // 1. Rising Star: minimum 5 answers
    $ansCountQuery = $conn->prepare("SELECT COUNT(*) as count FROM answers WHERE user_id = ?");
    $ansCountQuery->bind_param("i", $user_id);
    $ansCountQuery->execute();
    $ansCount = $ansCountQuery->get_result()->fetch_assoc()['count'];

    if ($ansCount >= 5) {
        award_badge_if_not_exists($conn, $user_id, 'Rising Star');
    }

    // 2. Quick Responder: maximum time 1 hour to answer each question out of minimum 5 questions
    // Interpreted as: at least 5 answers where response time was <= 1 hour
    $quickCountQuery = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM answers a 
        JOIN questions q ON a.question_id = q.id 
        WHERE a.user_id = ? AND TIMESTAMPDIFF(MINUTE, q.created_at, a.created_at) <= 60
    ");
    $quickCountQuery->bind_param("i", $user_id);
    $quickCountQuery->execute();
    $quickCount = $quickCountQuery->get_result()->fetch_assoc()['count'];

    if ($quickCount >= 5) {
        award_badge_if_not_exists($conn, $user_id, 'Quick Responder');
    }

    // 3. Top Contributor: minimum 50 answers and 10 posts
    $postCountQuery = $conn->prepare("SELECT COUNT(*) as count FROM posts WHERE user_id = ?");
    $postCountQuery->bind_param("i", $user_id);
    $postCountQuery->execute();
    $postCount = $postCountQuery->get_result()->fetch_assoc()['count'];

    if ($ansCount >= 50 && $postCount >= 10) {
        award_badge_if_not_exists($conn, $user_id, 'Top Contributor');
    }
}

/**
 * Helper to award a badge by name if the user doesn't already have it.
 */
function award_badge_if_not_exists($conn, $user_id, $badge_name) {
    // Get badge ID
    $badgeQuery = $conn->prepare("SELECT id FROM badges WHERE name = ?");
    $badgeQuery->bind_param("s", $badge_name);
    $badgeQuery->execute();
    $badgeRes = $badgeQuery->get_result();
    
    if ($badgeRes->num_rows === 1) {
        $badge_id = $badgeRes->fetch_assoc()['id'];
        
        // Insert into user_badges (INSERT IGNORE handles the UNIQUE constraint)
        $awardQuery = $conn->prepare("INSERT IGNORE INTO user_badges (user_id, badge_id) VALUES (?, ?)");
        $awardQuery->bind_param("ii", $user_id, $badge_id);
        $awardQuery->execute();
    }
}
