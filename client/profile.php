<div class="container mt-15 profile-container">
    <?php
    include("./common/db.php");
    $isOwnProfile = true;
    $uid = 0;

    if (isset($_GET['u-id'])) {
        $uid = (int)$_GET['u-id'];
        if (isset($_SESSION['user']['user_id']) && (int)$_SESSION['user']['user_id'] === $uid) {
            $isOwnProfile = true;
        } else {
            $isOwnProfile = false;
        }
    } else if (isset($_SESSION['user']['user_id'])) {
        $uid = (int)$_SESSION['user']['user_id'];
        $isOwnProfile = true;
    }

    if ($uid === 0) {
        echo "<div class='profile-card-modern text-center'><p>Please <a href='?login=true'>login</a> to view your profile.</p></div>";
    } else {
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

        if (!$row) {
            echo "<div class='profile-card-modern text-center'><p>User not found.</p></div>";
        } else {
            $username = htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8');
            $displayUsername = ucfirst($username);
            $email = htmlspecialchars($row['email'] ?? '', ENT_QUOTES, 'UTF-8');
            $gender = htmlspecialchars($row['gender'] ?? '', ENT_QUOTES, 'UTF-8');
            $birthdate = htmlspecialchars($row['birthdate'] ?? '', ENT_QUOTES, 'UTF-8');
            
            $initial = strtoupper(substr($username, 0, 1));

            // Stats
            $qcntStmt = $conn->prepare("SELECT COUNT(*) as cnt FROM questions WHERE user_id=?");
            $qcntStmt->bind_param("i", $uid);
            $qcntStmt->execute();
            $qcnt = $qcntStmt->get_result()->fetch_assoc()['cnt'] ?? 0;
            
            $acntStmt = $conn->prepare("SELECT COUNT(*) as cnt FROM answers WHERE user_id=?");
            $acntStmt->bind_param("i", $uid);
            $acntStmt->execute();
            $acnt = $acntStmt->get_result()->fetch_assoc()['cnt'] ?? 0;
            ?>

            <div class="row">
                <!-- Main Profile Content -->
                <div class="mt-4 col-12 col-lg-8">
                    <div class="profile-card-modern">
                        <div class="profile-main-info">
                            <div class="profile-avatar-xl"><?php echo $initial; ?></div>
                            <div class="profile-details">
                                <div class="profile-name-row">
                                    <h2><?php echo $displayUsername; ?></h2>
                                    <?php if ($isOwnProfile): ?>
                                        <a href="?profile_edit=true" class="action-btn-modern">Edit Profile</a>
                                    <?php else: ?>
                                        <div class="action-group">
                                            <button class="action-btn-modern action-btn-primary">Follow</button>
                                            <button class="action-btn-modern">Notify me</button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="profile-stats-row">
                                    <span class="stat-item"><span class="stat-value"><?php echo $qcnt; ?></span> Questions</span>
                                    <span class="stat-item"><span class="stat-value"><?php echo $acnt; ?></span> Answers</span>
                                </div>

                                <div class="profile-bio">
                                    Professional enthusiast and active contributor to the Quesiono community. 
                                    Always looking for interesting questions to answer and sharing knowledge with others.
                                </div>
                            </div>
                        </div>

                        <div class="profile-tabs-modern">
                            <div class="profile-tab active" data-tab="profile">Profile</div>
                            <div class="profile-tab" data-tab="answers">Answers (<?php echo $acnt; ?>)</div>
                            <div class="profile-tab" data-tab="questions">Questions (<?php echo $qcnt; ?>)</div>
                        </div>

                        <!-- Tab Contents -->
                        <div id="profile-tab-content" class="tab-content active">
                            <div class="sidebar-item">
                                <span class="sidebar-icon">📧</span>
                                <span><?php echo ($isOwnProfile || false) ? $email : "Email hidden for privacy"; ?></span>
                            </div>
                            <div class="sidebar-item">
                                <span class="sidebar-icon">👤</span>
                                <span><?php echo $gender ?: 'Prefer not to say'; ?></span>
                            </div>
                            <?php if ($birthdate): ?>
                            <div class="sidebar-item">
                                <span class="sidebar-icon">🎂</span>
                                <span>Born on <?php echo date("F j, Y", strtotime($birthdate)); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div id="answers-tab-content" class="tab-content">
                            <?php
                            $stmt = $conn->prepare("SELECT a.answer, q.title, q.id as qid FROM answers a JOIN questions q ON a.question_id = q.id WHERE a.user_id = ? ORDER BY a.id DESC");
                            $stmt->bind_param("i", $uid);
                            $stmt->execute();
                            $answers = $stmt->get_result();
                            if ($answers->num_rows > 0):
                                while ($ans = $answers->fetch_assoc()):
                            ?>
                                <div class="question-list">
                                    <h4 style="font-size: 16px;"><a href="?q-id=<?php echo $ans['qid']; ?>"><?php echo htmlspecialchars($ans['title']); ?></a></h4>
                                    <p style="font-size: 14px; color: #555;"><?php echo htmlspecialchars(substr($ans['answer'], 0, 150)) . '...'; ?></p>
                                </div>
                            <?php endwhile; else: ?>
                                <div class="empty-state">No answers yet.</div>
                            <?php endif; ?>
                        </div>

                        <div id="questions-tab-content" class="tab-content">
                            <?php
                            $stmt = $conn->prepare("SELECT id, title FROM questions WHERE user_id = ? ORDER BY id DESC");
                            $stmt->bind_param("i", $uid);
                            $stmt->execute();
                            $questions = $stmt->get_result();
                            if ($questions->num_rows > 0):
                                while ($q = $questions->fetch_assoc()):
                            ?>
                                <div class="question-list">
                                    <h4 style="font-size: 16px;"><a href="?q-id=<?php echo $q['id']; ?>"><?php echo htmlspecialchars($q['title']); ?></a></h4>
                                </div>
                            <?php endwhile; else: ?>
                                <div class="empty-state">No questions asked yet.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Sidebar Content -->
                <div class=" mt-4 col-12 col-lg-4">
                    <div class="profile-sidebar-card">
                        <div class="sidebar-title">Credentials & Highlights</div>
                        <div class="sidebar-item">
                            <span class="sidebar-icon">📍</span>
                            <span>Lives in Earth</span>
                        </div>
                        <div class="sidebar-item">
                            <span class="sidebar-icon">🌐</span>
                            <span>Joined <?php echo date("F Y"); ?></span>
                        </div>
                    </div>

                    <div class="profile-sidebar-card">
                        <div class="sidebar-title">Knows about</div>
                        <div class="knows-about-list">
                            <div class="topic-item">
                                <div class="topic-thumb">💡</div>
                                <div class="topic-info">
                                    <h4>General Knowledge</h4>
                                    <span><?php echo $qcnt + $acnt; ?> contributions</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                document.querySelectorAll('.profile-tab').forEach(tab => {
                    tab.addEventListener('click', () => {
                        // Remove active class from all tabs and contents
                        document.querySelectorAll('.profile-tab').forEach(t => t.classList.remove('active'));
                        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
                        
                        // Add active class to clicked tab
                        tab.classList.add('active');
                        
                        // Show corresponding content
                        const tabId = tab.getAttribute('data-tab');
                        document.getElementById(tabId + '-tab-content').classList.add('active');
                    });
                });
            </script>
            <?php
        }
    }
    ?>
</div>
