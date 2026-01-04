<div class="container mt-4">
    <div class="row">
        <div class="col-12 col-lg-8">
            <?php
            include("./common/db.php");
            
            $heading = "Questions";
            $showHero = false;
            if (isset($_GET["c-id"])) {
                $cid = (int)$_GET["c-id"];
                $catStmt = $conn->prepare("SELECT name FROM category WHERE id=?");
                $catStmt->bind_param("i", $cid);
                $catStmt->execute();
                $catName = $catStmt->get_result()->fetch_assoc()['name'] ?? 'Category';
                $heading = "$catName Questions";
                
                $stmt = $conn->prepare("select q.id, q.title, u.id as user_id, u.username, count(a.id) as acnt from questions q left join answers a on a.question_id=q.id join users u on u.id=q.user_id where q.category_id=? group by q.id, q.title, u.id, u.username");
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

                $stmt = $conn->prepare("select q.id, q.title, u.id as user_id, u.username, count(a.id) as acnt from questions q left join answers a on a.question_id=q.id join users u on u.id=q.user_id where q.user_id=? group by q.id, q.title, u.id, u.username");
                $stmt->bind_param("i", $uid);
                $stmt->execute();
                $result = $stmt->get_result();
            } else if (isset($_GET["latest"])) {
                $heading = "Latest Questions";
                $stmt = $conn->prepare("select q.id, q.title, u.id as user_id, u.username, count(a.id) as acnt from questions q left join answers a on a.question_id=q.id join users u on u.id=q.user_id group by q.id, q.title, u.id, u.username order by q.id desc");
                $stmt->execute();
                $result = $stmt->get_result();
            } else if (isset($_GET["search"])) {
                $heading = "Search Results for '" . htmlspecialchars($_GET["search"]) . "'";
                $searchTerm = $_GET["search"];
                $like = "%" . $searchTerm . "%";
                $stmt = $conn->prepare("select q.id, q.title, u.id as user_id, u.username, count(a.id) as acnt from questions q left join answers a on a.question_id=q.id join users u on u.id=q.user_id where q.title like ? group by q.id, q.title, u.id, u.username");
                $stmt->bind_param("s", $like);
                $stmt->execute();
                $result = $stmt->get_result();
            } else {
                $showHero = true;
                $stmt = $conn->prepare("select q.id, q.title, u.id as user_id, u.username, count(a.id) as acnt from questions q left join answers a on a.question_id=q.id join users u on u.id=q.user_id group by q.id, q.title, u.id, u.username");
                $stmt->execute();
                $result = $stmt->get_result();
            }

            if ($showHero && !isset($_GET['search']) && !isset($_GET['latest']) && !isset($_GET['u-id']) && !isset($_GET['c-id'])) {
            ?>
                <div class="hero-section mb-5 p-5 rounded-4 text-center" style="background: linear-gradient(135deg, var(--primary) 0%, #4f46e5 100%); color: #fff;">
                    <h1 class="display-4 fw-bold mb-3">Share Knowledge, Find Answers</h1>
                    <p class="lead mb-4 opacity-90">Join the Quesiono community and help others while learning new things.</p>
                    <?php if (!isset($_SESSION['user']['username'])) { ?>
                        <div class="d-flex justify-content-center gap-3">
                            <a href="?signup=true" class="btn btn-light btn-lg px-4 fw-bold" style="color: var(--primary);">Get Started</a>
                            <a href="?about=true" class="btn btn-outline-light btn-lg px-4">Learn More</a>
                        </div>
                    <?php } else { ?>
                        <a href="?askQuestion=true" class="btn btn-light btn-lg px-4 fw-bold" style="color: var(--primary);">Ask a Question</a>
                    <?php } ?>
                </div>
            <?php
            }
            
            echo "<h2 class='mb-4' style='font-weight: 800; color: var(--text);'>$heading</h2>";

            $isMyQnA = isset($uid) && isset($_SESSION['user']['user_id']) && ((int)$_SESSION['user']['user_id'] === (int)$uid);
            
            if ($result->num_rows === 0) {
                echo "<div class='profile-card-modern text-center'><p>No questions found.</p></div>";
            } else {
                foreach ($result as $row) {
                    $title = htmlspecialchars($row["title"], ENT_QUOTES, 'UTF-8');
                    $id = $row["id"];
                    $userId = $row["user_id"];
                    $acnt = isset($row["acnt"]) ? (int)$row["acnt"] : 0;
                    $username = htmlspecialchars($row["username"], ENT_QUOTES, 'UTF-8');
                    $initial = strtoupper(substr($username, 0, 1));
                    $deleteLink = "./server/requests.php?delete=" . $id . "&csrf=" . urlencode($_SESSION['csrf_token']);
                    $editLink = "?q-id=$id&edit-q=true";
                    ?>
                    <div class="question-list">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="user-inline">
                                <a href="?u-id=<?php echo $userId; ?>&profile=true" class="text-decoration-none d-flex align-items-center">
                                    <span class="user-avatar-initial"><?php echo $initial; ?></span>
                                    <span class="user-name small fw-semibold" style="color: var(--text-muted);"><?php echo $username; ?></span>
                                </a>
                            </div>
                            <?php if ($isMyQnA): ?>
                                <div class="dropdown">
                                    <button class="btn row-actions-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                        <li><a class="dropdown-item py-2" href="<?php echo $editLink; ?>">
                                            <i class="bi bi-pencil me-2"></i>Edit
                                        </a></li>
                                        <li><a class="dropdown-item py-2 text-danger" href="<?php echo $deleteLink; ?>" onclick="return confirm('Are you sure you want to delete this question?')">
                                            <i class="bi bi-trash me-2"></i>Delete
                                        </a></li>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        </div>
                        <a href="?q-id=<?php echo $id; ?>" class="question-title text-decoration-none"><?php echo $title; ?></a>
                        
                        <div class="question-stats">
                            <div class="stat-item">
                                <i class="bi bi-chat-left-text"></i>
                                <span><?php echo $acnt; ?> <?php echo $acnt === 1 ? 'Answer' : 'Answers'; ?></span>
                            </div>
                            <div class="stat-item ms-auto">
                                <a href="?q-id=<?php echo $id; ?>" class="btn btn-sm btn-primary py-1 px-3">View Details</a>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            }
            ?>
        </div>
        <div class="col-12 col-lg-4">
            <?php include("categoryList.php") ?>
        </div>
    </div>
</div>

