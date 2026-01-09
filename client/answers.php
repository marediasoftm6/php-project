<div class="answers-list">
    <?php
    include("./common/db.php");
    $stmt = $conn->prepare("select a.id, a.answer, a.user_id, u.username from answers a join users u on u.id=a.user_id where a.question_id = ? order by a.id desc");
    $stmt->bind_param("i", $qid);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo "<div class='text-center p-5 text-muted'><p>No answers yet. Be the first to answer!</p></div>";
    } else {
        foreach ($result as $row) {
            $answer = htmlspecialchars($row["answer"], ENT_QUOTES, 'UTF-8');
            $id = $row["id"];
            $ansUserId = $row["user_id"];
            $username = htmlspecialchars($row["username"], ENT_QUOTES, 'UTF-8');
            $initial = strtoupper(substr($username, 0, 1));
            $owner = isset($_SESSION['user']['user_id']) && ((int)$_SESSION['user']['user_id'] === (int)$row['user_id']);
            $deleteLink = "./server/requests.php?deleteAnswer=" . $id . "&csrf=" . urlencode($_SESSION['csrf_token']);
            $editLink = "$qslug?edit-a=$id";
            ?>
            <div class="answer-card">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="user-inline">
                        <a href="<?php echo urlencode($row["username"]); ?>" class="text-decoration-none d-flex align-items-center">
                            <span class="user-avatar-initial"><?php echo $initial; ?></span>
                            <span class="user-name small fw-semibold" style="color: var(--text-muted);"><?php echo $username; ?></span>
                        </a>
                    </div>
                    <?php if ($owner && !isset($_GET['edit-a'])) { ?>
                        <div class="dropdown">
                            <button class="btn row-actions-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                <li><a class="dropdown-item py-2" href="<?php echo $editLink; ?>">
                                    <i class="bi bi-pencil me-2"></i>Edit
                                </a></li>
                                <li><a class="dropdown-item py-2 text-danger" href="<?php echo $deleteLink; ?>" onclick="return confirm('Delete this answer?')">
                                    <i class="bi bi-trash me-2"></i>Delete
                                </a></li>
                            </ul>
                        </div>
                    <?php } ?>
                </div>

                <div class="answer-content" style="color: var(--text-muted); line-height: 1.7; white-space: pre-wrap; font-size: 1.05rem;"><?php echo $answer; ?></div>

                <?php if ($owner && isset($_GET['edit-a']) && (int)$_GET['edit-a'] === (int)$id) { ?>
                    <div class="mt-4 p-3 bg-light rounded-3">
                        <form action="./server/requests.php" method="post">
                            <input type="hidden" name="csrf" value="<?php echo $_SESSION['csrf_token'] ?>">
                            <input type="hidden" name="answer_id" value="<?php echo $id ?>">
                            <div class="form-group mb-3">
                                <label class="form-label" for="edit_answer_<?php echo $id ?>">Edit Your Answer</label>
                                <textarea id="edit_answer_<?php echo $id ?>" class="form-control" name="answer" rows="5" required><?php echo $answer ?></textarea>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" name="editAnswer" class="btn btn-sm btn-primary">Save</button>
                                <a href="<?php echo $qslug; ?>" class="btn btn-sm btn-outline-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                <?php } ?>
            </div>
            <?php
        }
    }
    ?>
</div>