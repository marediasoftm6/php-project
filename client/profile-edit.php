<div class="container mt-15">
    <div class="row">
        <div class="col-12 col-lg-8 offset-lg-2">
            <h2 class="heading-center margin-bottom-2">Edit Profile</h2>
            <?php
            include("./common/db.php");
            if (!isset($_SESSION['user']['user_id'])) {
                echo "<div class='question-list'><p>Please <a href='?login=true'>login</a> to edit your profile.</p></div>";
            } else {
                $uid = (int)$_SESSION['user']['user_id'];
                $stmt = $conn->prepare("SELECT username, email FROM users WHERE id=? LIMIT 1");
                $stmt->bind_param("i", $uid);
                $stmt->execute();
                $res = $stmt->get_result();
                $row = $res->fetch_assoc();
                ?>
                <form class="question-list profile-card" method="post" action="./server/requests.php">
                    <input type="hidden" name="csrf" value="<?php echo $_SESSION['csrf_token'] ?>">
                    <div class="margin-bottom-15">
                        <label for="edit_username">Username</label>
                        <input id="edit_username" name="username" class="form-control" type="text" value="<?php echo htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="margin-bottom-15">
                        <label for="edit_email">Email</label>
                        <input id="edit_email" name="email" class="form-control" type="email" value="<?php echo htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <button type="submit" name="editProfile" class="btn btn-primary">Save</button>
                </form>
            <?php } ?>
        </div>
    </div>
</div>
