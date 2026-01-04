<div class="auth-container" style="max-width: 700px;">
    <div class="auth-header">
        <h1>Edit Profile</h1>
        <p>Update your personal information</p>
    </div>
    <?php
    include("./common/db.php");
    if (!isset($_SESSION['user']['user_id'])) {
        echo "<div class='text-center p-4'><p>Please <a href='?login=true'>login</a> to edit your profile.</p></div>";
    } else {
        $uid = (int)$_SESSION['user']['user_id'];
        try {
            $stmt = $conn->prepare("SELECT username, email, gender, birthdate FROM users WHERE id=? LIMIT 1");
            $stmt->bind_param("i", $uid);
            $stmt->execute();
            $res = $stmt->get_result();
            $row = $res->fetch_assoc();
        } catch (\Throwable $e) {
            $stmt = $conn->prepare("SELECT username FROM users WHERE id=? LIMIT 1");
            $stmt->bind_param("i", $uid);
            $stmt->execute();
            $res = $stmt->get_result();
            $row = $res->fetch_assoc();
            $row['email'] = '';
            $row['gender'] = '';
            $row['birthdate'] = '';
        }
        ?>
        <form method="post" action="./server/requests.php">
            <input type="hidden" name="csrf" value="<?php echo $_SESSION['csrf_token'] ?>">
            
            <div class="form-group mb-4">
                <label class="form-label" for="edit_username">Username</label>
                <input id="edit_username" name="username" class="form-control" type="text" value="<?php echo htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8') ?>" required>
            </div>

            <div class="form-group mb-4">
                <label class="form-label" for="edit_email">Email Address</label>
                <input id="edit_email" name="email" class="form-control" type="email" value="<?php echo htmlspecialchars($row['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-4">
                        <label class="form-label" for="edit_gender">Gender</label>
                        <select id="edit_gender" name="gender" class="form-select">
                            <?php $g = $row['gender'] ?? ''; ?>
                            <option value="" <?php echo $g === '' ? 'selected' : '' ?>>Prefer not to say</option>
                            <option value="Male" <?php echo $g === 'Male' ? 'selected' : '' ?>>Male</option>
                            <option value="Female" <?php echo $g === 'Female' ? 'selected' : '' ?>>Female</option>
                            <option value="Other" <?php echo $g === 'Other' ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-4">
                        <label class="form-label" for="edit_birthdate">Birthdate</label>
                        <input id="edit_birthdate" name="birthdate" class="form-control" type="date" value="<?php echo htmlspecialchars($row['birthdate'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2 mt-4">
                <button type="submit" name="editProfile" class="btn btn-primary btn-lg">Save Changes</button>
                <a href="?profile=true" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    <?php } ?>
</div>