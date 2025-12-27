<div class="container">
    <h4 class="margin-bottom-15 margin-top-2 ">Answers</h4>
    <div class="margin-bottom-15">
        <?php
        include("./common/db.php");
        $stmt = $conn->prepare("select id, answer, user_id from answers where question_id = ?");
        $stmt->bind_param("i", $qid);
        $stmt->execute();
        $result = $stmt->get_result();
        foreach ($result as $row) {
            $answer = htmlspecialchars($row["answer"], ENT_QUOTES, 'UTF-8');
            $id = $row["id"];
            $owner = isset($_SESSION['user']['user_id']) && ((int)$_SESSION['user']['user_id'] === (int)$row['user_id']);
            $deleteLink = "./server/requests.php?deleteAnswer=" . $id . "&csrf=" . urlencode($_SESSION['csrf_token']);
            $editLink = "?q-id=$qid&edit-a=$id";
            echo "<p class='answer-card'>$id. $answer";
            echo $owner ? " <a class='answer-edit' href='$editLink'>Edit</a> <a class='answer-delete' href='$deleteLink'>Delete</a>" : "";
            echo "</p>";
            if ($owner && isset($_GET['edit-a']) && (int)$_GET['edit-a'] === (int)$id) {
                ?>
                <form class="mt-15" action="./server/requests.php" method="post">
                    <input type="hidden" name="csrf" value="<?php echo $_SESSION['csrf_token'] ?>">
                    <input type="hidden" name="answer_id" value="<?php echo $id ?>">
                    <div class="margin-bottom-15">
                        <label for="edit_answer_<?php echo $id ?>">Edit Answer</label>
                        <textarea id="edit_answer_<?php echo $id ?>" class="form-control" name="answer"><?php echo $answer ?></textarea>
                    </div>
                    <button type="submit" name="editAnswer" class="btn btn-primary">Save</button>
                </form>
                <?php
            }
        }
        ?>
    </div>
</div>
