<div class="container mt-15 profile-container">
    <?php
    include("./common/db.php");
    include("./common/badge_helper.php");
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
        echo "<div class='profile-card-modern text-center'><p>Please <a href='login'>login</a> to view your profile.</p></div>";
    } else {
        // Check and award badges on view
        check_and_award_badges($conn, $uid);
        
        $stmt = $conn->prepare("SELECT username, email, gender, birthdate, created_at, profile_pic FROM users WHERE id=? LIMIT 1");
        $stmt->bind_param("i", $uid);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();

        if (!$row) {
            echo "<div class='profile-card-modern text-center'><p>User not found.</p></div>";
        } else {
            $username = htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8');
            $displayUsername = ucfirst($username);
            $email = htmlspecialchars($row['email'] ?? '', ENT_QUOTES, 'UTF-8');
            $gender = htmlspecialchars($row['gender'] ?? '', ENT_QUOTES, 'UTF-8');
            $birthdate = htmlspecialchars($row['birthdate'] ?? '', ENT_QUOTES, 'UTF-8');
            $createdAt = $row['created_at'] ?? date('Y-m-d H:i:s');
            $profilePic = $row['profile_pic'];
            $joinedDate = date('M Y', strtotime($createdAt));
            $joinedYear = date('Y', strtotime($createdAt));
            
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

            $pcntStmt = $conn->prepare("SELECT COUNT(*) as cnt FROM posts WHERE user_id=?");
            $pcntStmt->bind_param("i", $uid);
            $pcntStmt->execute();
            $pcnt = $pcntStmt->get_result()->fetch_assoc()['cnt'] ?? 0;

            // Follow Stats
            $isFollowing = false;
            if (isset($_SESSION['user']['user_id'])) {
                $checkFollow = $conn->prepare("SELECT id FROM follows WHERE follower_id = ? AND following_id = ?");
                $checkFollow->bind_param("ii", $_SESSION['user']['user_id'], $uid);
                $checkFollow->execute();
                $isFollowing = $checkFollow->get_result()->num_rows > 0;
            }

            $followersStmt = $conn->prepare("SELECT COUNT(*) as cnt FROM follows WHERE following_id = ?");
            $followersStmt->bind_param("i", $uid);
            $followersStmt->execute();
            $followersCount = $followersStmt->get_result()->fetch_assoc()['cnt'] ?? 0;
            ?>

            <div class="row">
                <!-- Main Profile Content -->
                <div class="mt-4 col-12 col-lg-8">
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger border-0 small mb-4">
                            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['notice'])): ?>
                        <div class="alert alert-info border-0 small mb-4">
                            <?php echo $_SESSION['notice']; unset($_SESSION['notice']); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($isOwnProfile && !is_verified_user($conn)): ?>
                        <div class="alert alert-warning border-0 shadow-sm mb-4 p-4 d-flex align-items-center">
                            <div class="me-3">
                                <i class="bi bi-exclamation-triangle-fill fs-2"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-1">Verify Your Account</h5>
                                <p class="mb-2 small">Your account is not verified yet. Please verify your email to unlock all features like asking questions, answering, and posting articles.</p>
                                <a href="verify-code" class="btn btn-warning btn-sm fw-bold rounded-pill px-4">Verify Now</a>
                            </div>
                        </div>
                    <?php endif; ?>
                    <div class="profile-card-modern">
                        <div class="profile-main-info">
                            <div class="profile-avatar-xl-wrapper">
                                <?php if ($profilePic): ?>
                                    <img src="<?php echo htmlspecialchars($profilePic); ?>" alt="Profile" class="profile-avatar-xl">
                                <?php else: ?>
                                    <div class="profile-avatar-xl"><?php echo $initial; ?></div>
                                <?php endif; ?>
                                
                                <?php if ($isOwnProfile): ?>
                                    <form id="profile-pic-form" action="./server/requests.php" method="POST" enctype="multipart/form-data" class="d-none">
                                        <input type="file" name="profile_pic" id="profile-pic-input" accept="image/*" onchange="document.getElementById('profile-pic-form').submit()">
                                        <input type="hidden" name="update_profile_pic" value="1">
                                    </form>
                                    <button class="profile-avatar-edit-btn" onclick="document.getElementById('profile-pic-input').click()" title="Change Profile Picture">
                                        <i class="bi bi-camera-fill"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                            <div class="profile-details">
                                <div class="profile-name-row">
                                    <h2><?php echo $displayUsername; ?></h2>
                                    <?php if ($isOwnProfile): ?>
                                        <a href="profile-edit" class="action-btn-modern">
                                            <i class="bi bi-pencil"></i> Edit Profile
                                        </a>
                                    <?php else: ?>
                                        <div class="action-group d-flex gap-2">
                                            <button id="followBtn" onclick="toggleFollow(<?php echo $uid; ?>)" class="action-btn-modern <?php echo $isFollowing ? 'btn-following' : 'action-btn-primary'; ?>">
                                                <?php echo $isFollowing ? 'Following' : 'Follow'; ?>
                                            </button>
                                            <button class="action-btn-modern"><i class="bi bi-bell<?php echo $isFollowing ? '-fill text-primary' : ''; ?>"></i></button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="profile-stats-row">
                                    <span class="stat-item"><span class="stat-value" id="followersCount"><?php echo $followersCount; ?></span> Followers</span>
                                    <span class="stat-item"><span class="stat-value"><?php echo $qcnt; ?></span> Questions</span>
                                    <span class="stat-item"><span class="stat-value"><?php echo $acnt; ?></span> Answers</span>
                                    <span class="stat-item"><span class="stat-value"><?php echo $pcnt; ?></span> Posts</span>
                                </div>

                                <div class="profile-bio">
                                    Active member of the Quesiono community since <?php echo $joinedYear; ?>. Passionate about sharing knowledge and helping others find the right answers.
                                </div>
                            </div>
                        </div>

                        <div class="profile-tabs-modern">
                            <div class="profile-tab active" onclick="switchTab('profile')">Profile Info</div>
                            <div class="profile-tab" onclick="switchTab('answers')">Answers (<?php echo $acnt; ?>)</div>
                            <div class="profile-tab" onclick="switchTab('questions')">Questions (<?php echo $qcnt; ?>)</div>
                            <div class="profile-tab" onclick="switchTab('posts')">Posts (<?php echo $pcnt; ?>)</div>
                        </div>

                        <!-- Tab Contents -->
                        <div id="profile-tab" class="tab-content active">
                            <div class="info-grid mt-4">
                                <div class="info-item mb-3">
                                    <label class="text-muted small d-block">Email Address</label>
                                    <span class="fw-semibold"><?php echo $email ?: 'Not provided'; ?></span>
                                </div>
                                <div class="info-item mb-3">
                                    <label class="text-muted small d-block">Gender</label>
                                    <span class="fw-semibold"><?php echo ucfirst($gender) ?: 'Not provided'; ?></span>
                                </div>
                                <div class="info-item mb-3">
                                    <label class="text-muted small d-block">Birthdate</label>
                                    <span class="fw-semibold"><?php echo $birthdate ?: 'Not provided'; ?></span>
                                </div>
                            </div>
                        </div>

                        <div id="answers-tab" class="tab-content">
                            <h5 class="mb-4">Recent Answers</h5>
                            <?php
                            $ansStmt = $conn->prepare("SELECT a.*, q.title as q_title, q.slug as q_slug, u.username as author_name, u.profile_pic as author_pic,
                                                      (SELECT COUNT(*) FROM answers a2 WHERE a2.question_id = q.id) as acnt
                                                      FROM answers a 
                                                      JOIN questions q ON q.id = a.question_id 
                                                      JOIN users u ON u.id = a.user_id
                                                      WHERE a.user_id = ? 
                                                      ORDER BY a.id DESC");
                            $ansStmt->bind_param("i", $uid);
                            $ansStmt->execute();
                            $ansRes = $ansStmt->get_result();
                            if ($ansRes->num_rows === 0) {
                                echo "<p class='text-muted'>No answers posted yet.</p>";
                            } else {
                                foreach ($ansRes as $ans) {
                                    $ansContent = htmlspecialchars(strip_tags($ans['answer']), ENT_QUOTES, 'UTF-8');
                                    $qTitle = htmlspecialchars($ans['q_title'], ENT_QUOTES, 'UTF-8');
                                    $qSlug = htmlspecialchars($ans['q_slug'], ENT_QUOTES, 'UTF-8');
                                    $ansAuthor = htmlspecialchars($ans['author_name'], ENT_QUOTES, 'UTF-8');
                                    $ansDate = date('M Y', strtotime($ans['created_at']));
                                    $displayInitial = strtoupper(substr($ansAuthor, 0, 1));
                                    ?>
                                    <div class="qa-card-reference">
                                        <div class="card-header">
                                            <div class="user-avatar">
                                                <?php if ($ans['author_pic']): ?>
                                                    <img src="<?php echo htmlspecialchars($ans['author_pic']); ?>" alt="<?php echo $ansAuthor; ?>" class="w-100 h-100 object-fit-contain rounded-circle">
                                                <?php else: ?>
                                                    <?php echo $displayInitial; ?>
                                                <?php endif; ?>
                                            </div>
                                            <div class="user-info">
                                                <div>
                                                    <a href="<?php echo urlencode($ansAuthor); ?>" class="user-name"><?php echo $ansAuthor; ?></a>
                                                </div>
                                                <div class="user-meta">
                                                    Answered · <?php echo $ansDate; ?>
                                                </div>
                                            </div>
                                        </div>

                                        <a href="<?php echo $qSlug; ?>" class="question-title"><?php echo $qTitle; ?></a>

                                        <div class="answer-snippet">
                                            <?php echo $ansContent; ?>
                                        </div>
                                        <a href="<?php echo $qSlug; ?>" class="read-more small">(more)</a>

                                        <div class="card-footer mt-2">
                                            <a href="<?php echo $qSlug; ?>#answer-form" class="action-item text-decoration-none" title="Answers">
                                                <i class="bi bi-chat-dots"></i>
                                                <span><?php echo $ans['acnt']; ?> Answers</span>
                                            </a>
                                        </div>
                                    </div>
                                    <?php
                                }
                            }
                            ?>
                        </div>

                        <div id="questions-tab" class="tab-content">
                            <h5 class="mb-4">Recent Questions</h5>
                            <?php
                            $qStmt = $conn->prepare("SELECT q.id, q.title, q.slug, q.created_at, u.username, u.profile_pic as author_pic,
                                                    (SELECT a.answer FROM answers a WHERE a.question_id = q.id ORDER BY a.id DESC LIMIT 1) as top_answer,
                                                    (SELECT au.username FROM answers a JOIN users au ON a.user_id = au.id WHERE a.question_id = q.id ORDER BY a.id DESC LIMIT 1) as answerer_username,
                                                    (SELECT au.profile_pic FROM answers a JOIN users au ON a.user_id = au.id WHERE a.question_id = q.id ORDER BY a.id DESC LIMIT 1) as answerer_pic,
                                                    (SELECT a.created_at FROM answers a WHERE a.question_id = q.id ORDER BY a.id DESC LIMIT 1) as answer_date,
                                                    (SELECT COUNT(*) FROM answers a WHERE a.question_id = q.id) as acnt 
                                                    FROM questions q 
                                                    JOIN users u ON u.id = q.user_id 
                                                    WHERE q.user_id = ? 
                                                    ORDER BY q.id DESC");
                            $qStmt->bind_param("i", $uid);
                            $qStmt->execute();
                            $qRes = $qStmt->get_result();
                            if ($qRes->num_rows === 0) {
                                echo "<p class='text-muted'>No questions asked yet.</p>";
                            } else {
                                foreach ($qRes as $q) {
                                    $topAnswer = $q['top_answer'] ? htmlspecialchars(strip_tags($q['top_answer']), ENT_QUOTES, 'UTF-8') : null;
                                    $answererName = $q['answerer_username'] ? htmlspecialchars($q['answerer_username'], ENT_QUOTES, 'UTF-8') : null;
                                    $answerDate = $q['answer_date'] ?? $q['created_at'];
                                    $timeAgo = date('M Y', strtotime($answerDate));
                                    
                                    $displayUser = $answererName ?? htmlspecialchars($q['username'], ENT_QUOTES, 'UTF-8');
                                    $displayInitial = strtoupper(substr($displayUser, 0, 1));
                                    $displayProfilePic = $answererName ? $q['answerer_pic'] : $q['author_pic'];
                                    ?>
                                    <div class="qa-card-reference">
                                        <div class="card-header">
                                            <div class="user-avatar">
                                                <?php if ($displayProfilePic): ?>
                                                    <img src="<?php echo htmlspecialchars($displayProfilePic); ?>" alt="<?php echo $displayUser; ?>" class="w-100 h-100 object-fit-cover rounded-circle">
                                                <?php else: ?>
                                                    <?php echo $displayInitial; ?>
                                                <?php endif; ?>
                                            </div>
                                            <div class="user-info">
                                                <div>
                                                    <a href="<?php echo urlencode($displayUser); ?>" class="user-name"><?php echo $displayUser; ?></a>
                                                </div>
                                                <div class="user-meta">
                                                    <?php echo $answererName ? "Answered" : "Asked"; ?> · <?php echo $timeAgo; ?>
                                                </div>
                                            </div>
                                            <div class="dropdown ms-auto">
                                                <button class="btn p-0 border-0 text-muted" type="button" data-bs-toggle="dropdown">
                                                    <i class="bi bi-three-dots"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                                    <li>
                                                        <a class="dropdown-item py-2" href="javascript:void(0)" onclick="navigator.clipboard.writeText('<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/' . $q['slug']; ?>').then(() => alert('Link copied to clipboard!'))">
                                                            <i class="bi bi-share me-2"></i>Share
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>

                                        <a href="<?php echo htmlspecialchars($q['slug']); ?>" class="question-title"><?php echo htmlspecialchars($q['title']); ?></a>

                                        <?php if ($topAnswer): ?>
                                            <div class="answer-snippet">
                                                <?php echo $topAnswer; ?>
                                            </div>
                                            <a href="<?php echo htmlspecialchars($q['slug']); ?>" class="read-more small">(more)</a>
                                        <?php else: ?>
                                            <div class="text-muted small mb-2">No answers yet.</div>
                                        <?php endif; ?>

                                        <div class="card-footer mt-2">
                                            <a href="<?php echo htmlspecialchars($q['slug']); ?>#answer-form" class="action-item text-decoration-none" title="Answers">
                                                <i class="bi bi-chat-dots"></i>
                                                <span><?php echo $q['acnt']; ?> Answers</span>
                                            </a>
                                        </div>
                                    </div>
                                    <?php
                                }
                            }
                            ?>
                        </div>

                        <div id="posts-tab" class="tab-content">
                            <h5 class="mb-4">Recent Rich Posts</h5>
                            <?php
                            $pStmt = $conn->prepare("SELECT * FROM posts WHERE user_id = ? ORDER BY id DESC");
                            $pStmt->bind_param("i", $uid);
                            $pStmt->execute();
                            $pRes = $pStmt->get_result();
                            if ($pRes->num_rows === 0) {
                                echo "<p class='text-muted'>No rich posts published yet.</p>";
                            } else {
                                foreach ($pRes as $p) {
                                    $templateIcon = 'bi-journal-text';
                                    if($p['template'] == 'guide') $templateIcon = 'bi-list-ol';
                                    else if($p['template'] == 'technical') $templateIcon = 'bi-code-square';
                                    else if($p['template'] == 'story') $templateIcon = 'bi-chat-quote';
                                    ?>
                                    <div class="answer-card mb-3">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="bi <?php echo $templateIcon; ?> text-primary me-2"></i>
                                            <h6 class="mb-0">
                                                <a href="<?php echo htmlspecialchars($p['slug']); ?>" class="text-decoration-none text-primary">
                                                    <?php echo htmlspecialchars($p['title']); ?>
                                                </a>
                                            </h6>
                                        </div>
                                        <?php if (!empty($p['subtitle'])): ?>
                                            <p class="small fw-bold text-dark mb-1"><?php echo htmlspecialchars($p['subtitle']); ?></p>
                                        <?php endif; ?>
                                        <p class="small mb-0 text-muted">
                                            <?php echo htmlspecialchars(substr(strip_tags($p['content']), 0, 150)) . (strlen(strip_tags($p['content'])) > 150 ? '...' : ''); ?>
                                        </p>
                                    </div>
                                    <?php
                                }
                            }
                            ?>
                        </div>
                    </div>
                </div>

                <!-- Sidebar Content -->
                <div class="mt-4 col-12 col-lg-4">
                    <div class="profile-sidebar-card">
                        <h5 class="sidebar-title">Community Activity</h5>
                        <div class="sidebar-item">
                            <i class="bi bi-chat-heart text-danger"></i>
                            <span><?php echo $acnt; ?> Helpful Answers</span>
                        </div>
                        <div class="sidebar-item">
                            <i class="bi bi-pencil-square text-success"></i>
                            <span><?php echo $pcnt; ?> Published Posts</span>
                        </div>
                        <div class="sidebar-item">
                            <i class="bi bi-people text-info"></i>
                            <span>Joined <?php echo $joinedDate; ?></span>
                        </div>
                    </div>

                    <div class="profile-sidebar-card">
                        <h5 class="sidebar-title">Badges</h5>
                        <div class="d-flex flex-wrap gap-2">
                            <?php
                            $badgeStmt = $conn->prepare("
                                SELECT b.name, b.icon, b.description 
                                FROM user_badges ub 
                                JOIN badges b ON ub.badge_id = b.id 
                                WHERE ub.user_id = ?
                            ");
                            $badgeStmt->bind_param("i", $uid);
                            $badgeStmt->execute();
                            $badgeRes = $badgeStmt->get_result();

                            if ($badgeRes->num_rows === 0) {
                                echo "<span class='text-muted small'>No badges earned yet.</span>";
                            } else {
                                while ($badge = $badgeRes->fetch_assoc()) {
                                    ?>
                                    <span class="badge bg-light text-dark border p-2" title="<?php echo htmlspecialchars($badge['description']); ?>">
                                        <i class="bi <?php echo htmlspecialchars($badge['icon']); ?> text-primary"></i> 
                                        <?php echo htmlspecialchars($badge['name']); ?>
                                    </span>
                                    <?php
                                }
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <script>
            function switchTab(tabName) {
                // Remove active class from all tabs and contents
                document.querySelectorAll('.profile-tab').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
                
                // Add active class to clicked tab and corresponding content
                if (event && event.currentTarget) {
                    event.currentTarget.classList.add('active');
                }
                document.getElementById(tabName + '-tab').classList.add('active');
            }

            function toggleFollow(followingId) {
            const btn = document.getElementById('followBtn');
            const countSpan = document.getElementById('followersCount');
            
            // Disable button during request to prevent multiple clicks
            btn.disabled = true;
            btn.style.transform = 'scale(0.95)';
            
            fetch('./server/requests.php?toggleFollow=' + followingId)
                .then(response => response.json())
                .then(data => {
                    btn.disabled = false;
                    btn.style.transform = 'scale(1)';
                    
                    if (data.success) {
                        if (data.status === 'followed') {
                            btn.innerText = 'Following';
                            btn.classList.remove('action-btn-primary');
                            btn.classList.add('btn-following');
                            countSpan.innerText = parseInt(countSpan.innerText) + 1;
                        } else {
                            btn.innerText = 'Follow';
                            btn.classList.remove('btn-following');
                            btn.classList.add('action-btn-primary');
                            countSpan.innerText = parseInt(countSpan.innerText) - 1;
                        }
                    } else if (data.error === 'not_logged_in') {
                        window.location.href = 'login';
                    }
                })
                .catch(err => {
                    btn.disabled = false;
                    btn.style.transform = 'scale(1)';
                    console.error('Error toggling follow:', err);
                });
        }
            </script>
        <?php }
    } ?>
</div>
