<div class="row g-4">
    <div class="col-12 col-lg-8">
        <?php
        include("./common/db.php");
        $showHero = false;
        if (isset($_GET["c-id"])) {
            $cid = (int)$_GET["c-id"];
            $catStmt = $conn->prepare("SELECT name FROM category WHERE id=?");
            $catStmt->bind_param("i", $cid);
            $catStmt->execute();
            $catName = $catStmt->get_result()->fetch_assoc()['name'] ?? 'Category';
            $heading = "$catName Questions";

            $stmt = $conn->prepare("SELECT q.id, q.title, q.slug, q.created_at, u.id as user_id, u.username, u.profile_pic,
                                    (SELECT a.answer FROM answers a WHERE a.question_id = q.id ORDER BY a.id DESC LIMIT 1) as top_answer,
                                    (SELECT au.username FROM answers a JOIN users au ON a.user_id = au.id WHERE a.question_id = q.id ORDER BY a.id DESC LIMIT 1) as answerer_username,
                                    (SELECT au.profile_pic FROM answers a JOIN users au ON a.user_id = au.id WHERE a.question_id = q.id ORDER BY a.id DESC LIMIT 1) as answerer_profile_pic,
                                    (SELECT a.created_at FROM answers a WHERE a.question_id = q.id ORDER BY a.id DESC LIMIT 1) as answer_date,
                                    (SELECT COUNT(*) FROM answers a WHERE a.question_id = q.id) as acnt 
                                    FROM questions q 
                                    JOIN users u ON u.id = q.user_id 
                                    WHERE q.category_id=? 
                                    ORDER BY q.id DESC");
            $stmt->bind_param("i", $cid);
            $stmt->execute();
            $result = $stmt->get_result();
        } else if (isset($_GET["u-id"])) {
            $uid = (int)$_GET["u-id"];
            $uStmt = $conn->prepare("SELECT username FROM users WHERE id=?");
            $uStmt->bind_param("i", $uid);
            $uStmt->execute();
            $uName = $uStmt->get_result()->fetch_assoc()['username'] ?? 'User';
            $heading = ucfirst($uName) . "'s Questions";

            $stmt = $conn->prepare("SELECT q.id, q.title, q.slug, q.created_at, u.id as user_id, u.username, u.profile_pic, 
                                    (SELECT a.answer FROM answers a WHERE a.question_id = q.id ORDER BY a.id DESC LIMIT 1) as top_answer,
                                    (SELECT au.username FROM answers a JOIN users au ON a.user_id = au.id WHERE a.question_id = q.id ORDER BY a.id DESC LIMIT 1) as answerer_username,
                                    (SELECT au.profile_pic FROM answers a JOIN users au ON a.user_id = au.id WHERE a.question_id = q.id ORDER BY a.id DESC LIMIT 1) as answerer_profile_pic,
                                    (SELECT a.created_at FROM answers a WHERE a.question_id = q.id ORDER BY a.id DESC LIMIT 1) as answer_date,
                                    (SELECT COUNT(*) FROM answers a WHERE a.question_id = q.id) as acnt 
                                    FROM questions q 
                                    JOIN users u ON u.id = q.user_id 
                                    WHERE q.user_id=? 
                                    ORDER BY q.id DESC");
            $stmt->bind_param("i", $uid);
            $stmt->execute();
            $result = $stmt->get_result();
        } else if (isset($_GET["latest"])) {
            $heading = "Latest Questions";
            $stmt = $conn->prepare("SELECT q.id, q.title, q.slug, q.created_at, u.id as user_id, u.username, u.profile_pic, 
                                    (SELECT a.answer FROM answers a WHERE a.question_id = q.id ORDER BY a.id DESC LIMIT 1) as top_answer,
                                    (SELECT au.username FROM answers a JOIN users au ON a.user_id = au.id WHERE a.question_id = q.id ORDER BY a.id DESC LIMIT 1) as answerer_username,
                                    (SELECT au.profile_pic FROM answers a JOIN users au ON a.user_id = au.id WHERE a.question_id = q.id ORDER BY a.id DESC LIMIT 1) as answerer_profile_pic,
                                    (SELECT a.created_at FROM answers a WHERE a.question_id = q.id ORDER BY a.id DESC LIMIT 1) as answer_date,
                                    (SELECT COUNT(*) FROM answers a WHERE a.question_id = q.id) as acnt 
                                    FROM questions q 
                                    JOIN users u ON u.id = q.user_id 
                                    ORDER BY q.id DESC");
            $stmt->execute();
            $result = $stmt->get_result();
        } else if (isset($_GET["search"])) {
            $heading = "Search Results for '" . htmlspecialchars($_GET["search"]) . "'";
            $searchTerm = $_GET["search"];
            $like = "%" . $searchTerm . "%";
            $stmt = $conn->prepare("SELECT q.id, q.title, q.slug, q.created_at, u.id as user_id, u.username, u.profile_pic,
                                    (SELECT a.answer FROM answers a WHERE a.question_id = q.id ORDER BY a.id DESC LIMIT 1) as top_answer,
                                    (SELECT au.username FROM answers a JOIN users au ON a.user_id = au.id WHERE a.question_id = q.id ORDER BY a.id DESC LIMIT 1) as answerer_username,
                                    (SELECT au.profile_pic FROM answers a JOIN users au ON a.user_id = au.id WHERE a.question_id = q.id ORDER BY a.id DESC LIMIT 1) as answerer_profile_pic,
                                    (SELECT a.created_at FROM answers a WHERE a.question_id = q.id ORDER BY a.id DESC LIMIT 1) as answer_date,
                                    (SELECT COUNT(*) FROM answers a WHERE a.question_id = q.id) as acnt 
                                    FROM questions q 
                                    JOIN users u ON u.id = q.user_id 
                                    WHERE q.title LIKE ? OR q.description LIKE ? 
                                    ORDER BY q.id DESC");
            $stmt->bind_param("ss", $like, $like);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $showHero = true;
            $heading = "All Questions";
            $stmt = $conn->prepare("SELECT q.id, q.title, q.slug, q.created_at, u.id as user_id, u.username, u.profile_pic,
                                    (SELECT a.answer FROM answers a WHERE a.question_id = q.id ORDER BY a.id DESC LIMIT 1) as top_answer,
                                    (SELECT au.username FROM answers a JOIN users au ON a.user_id = au.id WHERE a.question_id = q.id ORDER BY a.id DESC LIMIT 1) as answerer_username,
                                    (SELECT au.profile_pic FROM answers a JOIN users au ON a.user_id = au.id WHERE a.question_id = q.id ORDER BY a.id DESC LIMIT 1) as answerer_profile_pic,
                                    (SELECT a.created_at FROM answers a WHERE a.question_id = q.id ORDER BY a.id DESC LIMIT 1) as answer_date,
                                    (SELECT COUNT(*) FROM answers a WHERE a.question_id = q.id) as acnt 
                                    FROM questions q 
                                    JOIN users u ON u.id = q.user_id 
                                    ORDER BY q.id DESC");
            $stmt->execute();
            $result = $stmt->get_result();
        }

        if ($showHero) {
        ?>
            <div class="hero-section mb-5 p-5 rounded-4 text-center" style="background: var(--gradient); color: var(--white);">
                <h1 class="mb-3">Share Knowledge, Find Answers</h1>
                <p class="lead mb-4 opacity-90">Join the Quesiono community and help others while learning new things.</p>
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    <?php if (!isset($_SESSION['user']['username'])) { ?>
                        <a href="about" class="btn btn-light btn-lg px-4 rounded-pill text-primary">Learn More</a>
                    <?php } ?>
                    <a href="<?php echo isset($_SESSION['user']['username']) ? 'ask-question' : 'javascript:void(0)'; ?>"
                        <?php echo !isset($_SESSION['user']['username']) ? 'data-bs-toggle="modal" data-bs-target="#loginModal"' : ''; ?>
                        class="btn btn-outline-light btn-lg px-4 rounded-pill fw-regular">Ask a Question</a>
                    <?php if (isset($_SESSION['user']['username'])) { ?>
                        <a href="<?php echo isset($_SESSION['user']['username']) ? 'create-post' : 'javascript:void(0)'; ?>" class="btn btn-light btn-lg px-4 rounded-pill text-primary fw-regular">Post Articles</a>
                    <?php } ?>
                </div>
            </div>

            <!-- Stats Section -->
            <?php include('stats.php'); ?>
            <?php
        }

        echo "<h2 class='mb-4' style='font-weight: 800; color: var(--text);'>$heading</h2>";

        $isMyQnA = isset($uid) && isset($_SESSION['user']['user_id']) && ((int)$_SESSION['user']['user_id'] === (int)$uid);

        if ($result->num_rows === 0) {
            if (isset($_GET["search"])) {
                echo "
                <div class='profile-card-modern text-center py-5'>
                    <i class='bi bi-search display-1 text-muted opacity-25 mb-3'></i>
                    <h3 class='text-muted'>No results found</h3>
                    <p class='text-muted'>We couldn't find any questions matching '" . htmlspecialchars($_GET["search"]) . "'. Try different keywords or check for typos.</p>
                    <a href='./' class='btn btn-primary rounded-pill px-4 mt-3'>View All Questions</a>
                </div>";
            } else {
                echo "<div class='profile-card-modern text-center'><p>No questions found.</p></div>";
            }
        } else {
            foreach ($result as $row) {
                $title = htmlspecialchars($row["title"], ENT_QUOTES, 'UTF-8');
                $id = $row["id"];
                $slug = $row["slug"];
                $userId = $row["user_id"];
                $acnt = isset($row["acnt"]) ? (int)$row["acnt"] : 0;
                $username = htmlspecialchars($row["username"], ENT_QUOTES, 'UTF-8');
                $initial = strtoupper(substr($username, 0, 1));

                $topAnswer = $row['top_answer'] ? htmlspecialchars(strip_tags($row['top_answer']), ENT_QUOTES, 'UTF-8') : null;
                $answererName = $row['answerer_username'] ? htmlspecialchars($row['answerer_username'], ENT_QUOTES, 'UTF-8') : null;
                $answerDate = $row['answer_date'] ?? $row['created_at'];
                $timeAgo = date('M Y', strtotime($answerDate)); // Simplified time ago

                $displayUser = $answererName ?? $username;
                $displayInitial = strtoupper(substr($displayUser, 0, 1));
                $displayProfilePic = $answererName ? ($row['answerer_profile_pic'] ?? null) : ($row['profile_pic'] ?? null);

                $deleteLink = "./server/requests.php?delete=" . $id . "&csrf=" . urlencode($_SESSION['csrf_token']);
                $editLink = "$slug?edit-q=true";
            ?>
                <div class="qa-card-reference">
                    <div class="card-header">
                        <div class="user-avatar">
                            <?php if ($displayProfilePic): ?>
                                <img src="<?php echo htmlspecialchars($displayProfilePic); ?>" alt="<?php echo $displayUser; ?>" class="w-100 h-100 object-fit-contain rounded-circle">
                            <?php else: ?>
                                <?php echo $displayInitial; ?>
                            <?php endif; ?>
                        </div>
                        <div class="user-info">
                            <div>
                                <a href="<?php echo urlencode($displayUser); ?>" class="user-name"><?php echo $displayUser; ?></a>
                                <?php if ($answererName): ?>
                                    <a href="javascript:void(0)" class="follow-btn">· Follow</a>
                                <?php endif; ?>
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
                                    <a class="dropdown-item py-2" href="javascript:void(0)" onclick="navigator.clipboard.writeText('<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/' . $slug; ?>').then(() => alert('Link copied to clipboard!'))">
                                        <i class="bi bi-share me-2"></i>Share
                                    </a>
                                </li>
                                <?php if ($isMyQnA): ?>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li><a class="dropdown-item py-2" href="<?php echo $editLink; ?>">
                                            <i class="bi bi-pencil me-2"></i>Edit
                                        </a></li>
                                    <li><a class="dropdown-item py-2 text-danger" href="<?php echo $deleteLink; ?>" onclick="return confirm('Are you sure you want to delete this question?')">
                                            <i class="bi bi-trash me-2"></i>Delete
                                        </a></li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>

                    <a href="<?php echo $slug; ?>" class="question-title"><?php echo $title; ?></a>

                    <?php if ($topAnswer): ?>
                        <div class="answer-snippet">
                            <?php echo $topAnswer; ?>
                        </div>
                        <a href="<?php echo $slug; ?>" class="read-more small">(more)</a>
                    <?php else: ?>
                        <div class="text-muted small mb-2">No answers yet.</div>
                    <?php endif; ?>

                    <div class="card-footer mt-2">
                        <a href="<?php echo $slug; ?>#answer-form" class="action-item text-decoration-none" title="Answers">
                            <i class="bi bi-chat-dots"></i>
                            <span><?php echo $acnt; ?> Answers</span>
                        </a>
                    </div>
                </div>
        <?php
            }
        }
        ?>
    </div>

    <!-- Sidebar Column -->
    <div class="col-12 col-lg-4 mt-4">
        <?php include('sidebar.php'); ?>
    </div>
</div>