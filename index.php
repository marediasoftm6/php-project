<?php
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Quesiono</title>
    <?php include('./client/commonFiles.php') ?>
</head>

<body style="padding-inline: 20px;">
    <?php
    include('./client/header.php');
    ?>

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
    } else if (isset($_GET['u-id'])) {
        $uid = $_GET['u-id'];
        include('./client/questions.php');
    } else if (isset($_GET['latest'])) {
        include('./client/questions.php');
    } else if (isset($_GET['search'])) {
        include('./client/questions.php');
    } else if (isset($_GET['about'])) {
        include('./client/about.php');
    } else if (isset($_GET['profile'])) {
        include('./client/profile.php');
    } else if (isset($_GET['profile_edit'])) {
        include('./client/profile-edit.php');
    } else if (isset($_GET['settings'])) {
        include('./client/settings.php');
    } else {
        include('./client/questions.php');
    }
    ?>
</body>

</html>
