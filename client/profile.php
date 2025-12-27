<div class="container mt-15">
    <div class="row">
        <div class="col-12 col-lg-8 offset-lg-2">
            <h2 class="heading-center margin-bottom-2">Your Profile</h2>
            <?php
            include("./common/db.php");
            if (!isset($_SESSION['user']['user_id'])) {
                echo "<div class='question-list profile-card'><p>Please <a href='?login=true'>login</a> to view your profile.</p></div>";
            } else {
                $uid = (int)$_SESSION['user']['user_id'];
                $stmt = $conn->prepare("SELECT username, email FROM users WHERE id=? LIMIT 1");
                $stmt->bind_param("i", $uid);
                $stmt->execute();
                $res = $stmt->get_result();
                $row = $res->fetch_assoc();
                $username = htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8');
                $email = htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8');
                $qcntStmt = $conn->prepare("SELECT COUNT(*) as cnt FROM questions WHERE user_id=?");
                $qcntStmt->bind_param("i", $uid);
                $qcntStmt->execute();
                $qcnt = $qcntStmt->get_result()->fetch_assoc()['cnt'] ?? 0;
                $acntStmt = $conn->prepare("SELECT COUNT(*) as cnt FROM answers WHERE user_id=?");
                $acntStmt->bind_param("i", $uid);
                $acntStmt->execute();
                $acnt = $acntStmt->get_result()->fetch_assoc()['cnt'] ?? 0;
                $initial = strtoupper(substr($username, 0, 1));
                echo "<div class='question-list profile-card'>";
                echo "<div class='profile-header'><span class='avatar'>$initial</span><div><h3 class='question-title'>$username</h3><p class='mt-15'><strong>Email:</strong> $email</p></div></div>";
                echo "<div class='stats mt-15'><span class='stat-badge'>Q $qcnt</span><span class='stat-badge'>A $acnt</span></div>";
                echo "<div class='mt-15 action-group'><a class='action-btn' href='?profile_edit=true'>Edit Profile</a><a class='action-btn' href='?settings=true'>Settings</a></div>";
                echo "</div>";
            }
            ?>
        </div>
    </div>
</div>
