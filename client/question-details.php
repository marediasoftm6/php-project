<div class="row">
    <div class="col-12 col-lg-8">
        <?php
        include("./common/db.php");
        $stmt = $conn->prepare("select q.*, u.username from questions q join users u on u.id = q.user_id where q.id = ?");
        $stmt->bind_param("i", $qid);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        if ($row) {
            $cid = $row['category_id'];
            $owner = isset($_SESSION['user']['user_id']) && ((int)$_SESSION['user']['user_id'] === (int)$row['user_id']);
            $titleSafe = htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8');
            $descSafe = htmlspecialchars($row['description'] ?? '', ENT_QUOTES, 'UTF-8');
            $username = htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8');
            $initial = strtoupper(substr($username, 0, 1));
            ?>
            
            <div class="question-detail-card">
                <div class="d-flex justify-content-between align-items-start mb-4">
                    <div class="user-inline">
                        <a href="?u-id=<?php echo $row['user_id']; ?>&profile=true" class="text-decoration-none d-flex align-items-center">
                            <span class="user-avatar-initial" style="width: 40px; height: 40px; font-size: 1rem;"><?php echo $initial; ?></span>
                            <div>
                                <span class="user-name d-block fw-bold" style="color: var(--text);"><?php echo $username; ?></span>
                                <small class="text-muted">Asked a question</small>
                            </div>
                        </a>
                    </div>
                    <?php 
                    $catQuery = $conn->prepare("SELECT name FROM category WHERE id = ?");
                    $catQuery->bind_param("i", $cid);
                    $catQuery->execute();
                    $catResult = $catQuery->get_result()->fetch_assoc();
                    if ($catResult) {
                        echo '<span class="badge-category">' . htmlspecialchars($catResult['name']) . '</span>';
                    }
                    ?>
                </div>

                <h1 class="mb-4" style="font-weight: 800; color: var(--text); line-height: 1.3; font-size: 2rem;"><?php echo $titleSafe; ?></h1>
                
                <div class="question-body mb-4" style="font-size: 1.1rem; color: var(--text-muted); line-height: 1.8; white-space: pre-wrap;"><?php echo $descSafe; ?></div>

                <?php if ($owner && !isset($_GET['edit-q'])) { ?>
                    <div class="mt-4 pt-3 border-top d-flex gap-2">
                        <a href="?q-id=<?php echo $qid; ?>&edit-q=true" class="btn btn-sm btn-outline-primary px-3">
                            <i class="bi bi-pencil me-1"></i> Edit
                        </a>
                        <a href="./server/requests.php?deleteQuestion=<?php echo $qid; ?>&csrf=<?php echo $_SESSION['csrf_token']; ?>" 
                           onclick="return confirm('Are you sure you want to delete this question?')"
                           class="btn btn-sm btn-outline-danger px-3 align-content-center">
                            <i class="bi bi-trash me-1"></i> Delete
                        </a>
                    </div>
                <?php } ?>

                <?php if ($owner && isset($_GET['edit-q'])) { ?>
                    <div class="mt-4 p-4 bg-light rounded-3">
                        <h4 class="mb-3">Edit Your Question</h4>
                        <form action="./server/requests.php" method="post">
                            <input type="hidden" name="csrf" value="<?php echo $_SESSION['csrf_token'] ?>">
                            <input type="hidden" name="question_id" value="<?php echo $qid ?>">
                            <div class="form-group mb-3">
                                <label class="form-label" for="edit_title">Title</label>
                                <input type="text" id="edit_title" class="form-control" name="title" value="<?php echo $titleSafe ?>" required>
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label" for="edit_description">Description</label>
                                <textarea id="edit_description" class="form-control" name="description" rows="5" required><?php echo $descSafe ?></textarea>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" name="editQuestion" class="btn btn-primary">Save Changes</button>
                                <a href="?q-id=<?php echo $qid; ?>" class="btn btn-outline-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                <?php } ?>
            </div>

            <div class="answers-section mt-5">
                <h3 class="mb-4" style="font-weight: 700;">Answers</h3>
                <?php include("./client/answers.php"); ?>
            </div>

            <?php if (isset($_SESSION['user']['username'])) { ?>
                <div class="answer-input-card mt-4">
                    <h4 class="mb-3" style="font-weight: 700;">Your Answer</h4>
                    <form action="./server/requests.php" method="post">
                        <input type="hidden" name="question_id" value="<?php echo $qid ?>">
                        <input type="hidden" name="csrf" value="<?php echo $_SESSION['csrf_token'] ?>">
                        <div class="form-group mb-3">
                            <textarea name="answer" class="form-control" placeholder="Share your knowledge or perspective..." rows="6" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Post Your Answer</button>
                    </form>
                </div>
            <?php } else { ?>
                <div class="profile-card-modern text-center mt-4">
                    <p class="mb-3">Please login to answer this question.</p>
                    <a href="?login=true" class="btn btn-primary">Login Now</a>
                </div>
            <?php } ?>

        <?php } else { ?>
            <div class="profile-card-modern text-center">
                <h2>Question not found</h2>
                <p>The question you are looking for does not exist or has been removed.</p>
                <a href="./" class="btn btn-primary mt-3">Back to Home</a>
            </div>
        <?php } ?>
    </div>

    <!-- Sidebar Column -->
    <div class="col-12 col-lg-4 mt-4 mt-lg-0">
        <?php include('sidebar.php'); ?>
    </div>
</div>