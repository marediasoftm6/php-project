<div class="container mt-15">
    <div class="row">
        <div class="col-12 col-lg-7">
            <h2 class="heading-center margin-bottom-2">Write Your Answer</h2>
            <?php
            include("./common/db.php");
            $stmt = $conn->prepare("select * from questions where id = ?");
            $stmt->bind_param("i", $qid);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $cid = $row['category_id'];
            $owner = isset($_SESSION['user']['user_id']) && ((int)$_SESSION['user']['user_id'] === (int)$row['user_id']);
            $titleSafe = htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8');
            echo "<h2 class='mt-15 question-title'>Q. " . $titleSafe . "</h2>";
            if ($owner) {
                echo "<div class='mt-15'><a class='action-btn' href='?q-id=$qid&edit-q=true'>Edit</a></div>";
            }
            if ($owner && isset($_GET['edit-q'])) {
            ?>
            <form class="mt-15" action="./server/requests.php" method="post">
                <input type="hidden" name="csrf" value="<?php echo $_SESSION['csrf_token'] ?>">
                <input type="hidden" name="question_id" value="<?php echo $qid ?>">
                <div class="margin-bottom-15">
                    <label for="edit_title">Title</label>
                    <input type="text" id="edit_title" class="form-control" name="title" value="<?php echo $titleSafe ?>">
                </div>
                <div class="margin-bottom-15">
                    <label for="edit_description">Description</label>
                    <textarea id="edit_description" class="form-control" name="description"><?php echo htmlspecialchars($row['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>
                <button type="submit" name="editQuestion" class="btn btn-primary">Save</button>
            </form>
            <?php
            }
            // echo "<p class='mt-15'>" . $row['description'] . "<p>";
            include("./client/answers.php");
            ?>
            <form action="./server/requests.php" method="post">
                <input type="hidden" name="question_id" value="<?php echo $qid ?>">
                <input type="hidden" name="csrf" value="<?php echo $_SESSION['csrf_token'] ?>">
                <textarea name="answer" class="form-control mt-15" placeholder="Write Your Answer..." rows="10" cols="10"></textarea>
                <button class="btn btn-primary mt-15">Submit Answer</button>
            </form>
        </div>
        <div class="col-lg-1 d-none d-lg-block"></div>
        <div class="col-12 col-lg-4">
            <?php
            $catStmt = $conn->prepare("select * from category where id=?");
            $catStmt->bind_param("i", $cid);
            $catStmt->execute();
            $catResult = $catStmt->get_result();
            $catRow = $catResult->fetch_assoc();
            echo "<h2 class='margin-bottom-2'>" . htmlspecialchars($catRow['name'], ENT_QUOTES, 'UTF-8') . "</h2>";
            // include("categoryList.php")
            $relStmt = $conn->prepare("select id, title from questions where category_id=? and id!=?");
            $relStmt->bind_param("ii", $cid, $qid);
            $relStmt->execute();
            $relResult = $relStmt->get_result();
            foreach ($relResult as $row) {
                $title = htmlspecialchars($row["title"], ENT_QUOTES, 'UTF-8');
                $id = $row["id"];
                echo "<div class='related-questions'><h4>
            <a href='?q-id=$id'>$title</a></h4></div>";
            }
            ?>
        </div>
    </div>
</div>
