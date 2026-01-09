<?php
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

include('./common/db.php');

// Simple Router for SEO Friendly URLs
$request = $_SERVER['REQUEST_URI'];
$base_path = '/Quesiono/';
$path = str_replace($base_path, '', $request);
$path = strtok($path, '?'); // Remove query string
$path = trim($path, '/');

if ($path && !isset($_GET['signup']) && !isset($_GET['login']) && !isset($_GET['askQuestion']) && !isset($_GET['about']) && !isset($_GET['post']) && !isset($_GET['all-posts']) && !isset($_GET['my-posts']) && !isset($_GET['profile_edit']) && !isset($_GET['settings'])) {
    // 1. Check for Static Routes first
    if ($path === 'about') {
        $_GET['about'] = 'true';
    } else if ($path === 'privacy') {
        $_GET['privacy'] = 'true';
    } else if ($path === 'signup') {
        $_GET['signup'] = 'true';
    } else if ($path === 'login') {
        $_GET['login'] = 'true';
    } else if ($path === 'ask-question') {
        $_GET['askQuestion'] = 'true';
    } else if ($path === 'create-post') {
        $_GET['post'] = 'true';
    } else if ($path === 'my-posts') {
        $_GET['my-posts'] = 'true';
    } else if ($path === 'posts') {
        $_GET['all-posts'] = 'true';
    } else if ($path === 'categories') {
        $_GET['categories'] = 'true';
    } else if ($path === 'latest') {
        $_GET['latest'] = 'true';
    } else if ($path === 'profile-edit') {
        $_GET['profile_edit'] = 'true';
    } else if ($path === 'settings') {
        $_GET['settings'] = 'true';
    } else if ($path === 'verified') {
        $_GET['verified'] = 'true';
    } else if ($path === 'verify-code') {
        $_GET['verify-code'] = 'true';
    } else {
        // 2. Check for Dynamic Routes (Profile, Question, Category, Post)
        // Check if path matches a username (Profile)
        $decodedPath = urldecode($path);
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
        $stmt->bind_param("s", $decodedPath);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows > 0) {
            $user = $res->fetch_assoc();
            $_GET['u-id'] = $user['id'];
            $_GET['profile'] = 'true';
        } else {
            // Check if path matches a question slug
            $stmt = $conn->prepare("SELECT id FROM questions WHERE slug = ? LIMIT 1");
            $stmt->bind_param("s", $path);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res->num_rows > 0) {
                $q = $res->fetch_assoc();
                $_GET['q-id'] = $q['id'];
            } else {
                // Check if path matches a category slug
                $stmt = $conn->prepare("SELECT id FROM category WHERE slug = ? LIMIT 1");
                $stmt->bind_param("s", $path);
                $stmt->execute();
                $res = $stmt->get_result();
                if ($res->num_rows > 0) {
                    $c = $res->fetch_assoc();
                    $_GET['c-id'] = $c['id'];
                } else {
                    // Check if path matches a post slug
                    $stmt = $conn->prepare("SELECT id FROM posts WHERE slug = ? LIMIT 1");
                    $stmt->bind_param("s", $path);
                    $stmt->execute();
                    $res = $stmt->get_result();
                    if ($res->num_rows > 0) {
                        $p = $res->fetch_assoc();
                        $_GET['post-id'] = $p['id'];
                    }
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="/Quesiono/">
    <title>Quesiono</title>
    <?php include('./client/commonFiles.php') ?>
    <link rel="icon" href="./public/colored-logo.png" type="image/x-icon">
</head>

<body>
    <?php
    include('./client/header.php');
    ?>

    <main class="<?php echo isset($_GET['about']) ? '' : 'container py-4'; ?>">
        <?php
        if (isset($_GET['signup']) && !isset($_SESSION['user']['username'])) {
            include('./client/signup.php');
        } else if (isset($_GET['login']) && !isset($_SESSION['user']['username'])) {
            include('./client/login.php');
        } else if (isset($_GET['askQuestion'])) {
            include('./client/askQuestion.php');
        } else if (isset($_GET['q-id'])) {
            $qid = $_GET['q-id'];
            include('./client/question-details.php');
        } else if (isset($_GET['c-id'])) {
            $cid = $_GET['c-id'];
            include('./client/questions.php');
        } else if (isset($_GET['categories'])) {
            include('./client/categories.php');
        } else if (isset($_GET['profile'])) {
            include('./client/profile.php');
        } else if (isset($_GET['u-id']) && !isset($_GET['profile'])) {
            $uid = $_GET['u-id'];
            include('./client/questions.php');
        } else if (isset($_GET['u-id']) && isset($_GET['profile'])) {
            include('./client/profile.php');
        } else if (isset($_GET['latest'])) {
            include('./client/questions.php');
        } else if (isset($_GET['search'])) {
            include('./client/questions.php');
        } else if (isset($_GET['about'])) {
            include('./client/about.php');
        } else if (isset($_GET['privacy'])) {
            include('./client/privacy.php');
        } else if (isset($_GET['post'])) {
            include('./client/post.php');
        } else if (isset($_GET['post-id'])) {
            include('./client/post-details.php');
        } else if (isset($_GET['all-posts'])) {
            include('./client/all-posts.php');
        } else if (isset($_GET['my-posts'])) {
            include('./client/my-posts.php');
        } else if (isset($_GET['profile_edit'])) {
            include('./client/profile-edit.php');
        } else if (isset($_GET['settings'])) {
            include('./client/settings.php');
        } else if (isset($_GET['verified'])) {
            include('./client/verified.php');
        } else if (isset($_GET['verify-code'])) {
            include('./client/verify-code.php');
        } else if (isset($_GET['notifications'])) {
            include('./client/notifications.php');
        } else {
            include('./client/questions.php');
        }
        ?>
    </main>
</body>

</html>