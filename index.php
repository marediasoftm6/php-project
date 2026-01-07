<?php
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quesiono</title>
    <?php include('./client/commonFiles.php') ?>
    <link rel="icon" href="./public/colored-logo.png" type="image/x-icon">
</head>

<body>
    <?php
    include('./client/header.php');
    ?>

    <main class="container py-4">
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
    } else if (isset($_GET['profile_edit'])) {
        include('./client/profile-edit.php');
    } else if (isset($_GET['settings'])) {
        include('./client/settings.php');
    } else {
        include('./client/questions.php');
    }
    ?>
    </main>
</body>

</html>
