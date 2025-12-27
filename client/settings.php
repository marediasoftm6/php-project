<div class="container mt-15">
    <div class="row">
        <div class="col-12 col-lg-8 offset-lg-2">
            <h2 class="heading-center margin-bottom-2">Settings</h2>
            <?php
            include("./common/db.php");
            if (!isset($_SESSION['user']['user_id'])) {
                echo "<div class='question-list'><p>Please <a href='?login=true'>login</a> to access settings.</p></div>";
            } else {
                ?>
                <form class="question-list profile-card" method="post" action="./server/requests.php">
                    <h4>Change Password</h4>
                    <input type="hidden" name="csrf" value="<?php echo $_SESSION['csrf_token'] ?>">
                    <div class="margin-bottom-15">
                        <label for="old_password">Current Password</label>
                        <input id="old_password" name="old_password" class="form-control" type="password" placeholder="Enter current password">
                    </div>
                    <div class="margin-bottom-15">
                        <label for="new_password">New Password</label>
                        <input id="new_password" name="new_password" class="form-control" type="password" placeholder="Enter new password">
                    </div>
                    <button type="submit" name="changePassword" class="btn btn-primary">Update Password</button>
                </form>
            <?php } ?>
        </div>
    </div>
</div>
