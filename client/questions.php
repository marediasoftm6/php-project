<div class="container">
    <div class="row">
        <div class="col-12 col-lg-8">
            <h2 class="heading-center margin-bottom-2">Questions</h2>
            <?php
            include("./common/db.php");
            $isMyQnA = isset($uid) && isset($_SESSION['user']['user_id']) && ((int)$_SESSION['user']['user_id'] === (int)$uid);
            if (isset($_GET["c-id"])) {
                $stmt = $conn->prepare("select q.id, q.title, count(a.id) as acnt from questions q left join answers a on a.question_id=q.id where q.category_id=? group by q.id, q.title");
                $stmt->bind_param("i", $cid);
                $stmt->execute();
                $result = $stmt->get_result();
            } else if (isset($_GET["u-id"])) {
                $stmt = $conn->prepare("select q.id, q.title, count(a.id) as acnt from questions q left join answers a on a.question_id=q.id where q.user_id=? group by q.id, q.title");
                $stmt->bind_param("i", $uid);
                $stmt->execute();
                $result = $stmt->get_result();
            } else if (isset($_GET["latest"])) {
                $stmt = $conn->prepare("select q.id, q.title, count(a.id) as acnt from questions q left join answers a on a.question_id=q.id group by q.id, q.title order by q.id desc");
                $stmt->execute();
                $result = $stmt->get_result();
            } else if (isset($_GET["search"])) {
                $searchTerm = $_GET["search"];
                $like = "%" . $searchTerm . "%";
                $stmt = $conn->prepare("select q.id, q.title, count(a.id) as acnt from questions q left join answers a on a.question_id=q.id where q.title like ? group by q.id, q.title");
                $stmt->bind_param("s", $like);
                $stmt->execute();
                $result = $stmt->get_result();
            } else {
                $stmt = $conn->prepare("select q.id, q.title, count(a.id) as acnt from questions q left join answers a on a.question_id=q.id group by q.id, q.title");
                $stmt->execute();
                $result = $stmt->get_result();
            }
            foreach ($result as $row) {
                $title = htmlspecialchars($row["title"], ENT_QUOTES, 'UTF-8');
                $id = $row["id"];
                $acnt = isset($row["acnt"]) ? (int)$row["acnt"] : 0;
                $deleteLink = "./server/requests.php?delete=" . $id . "&csrf=" . urlencode($_SESSION['csrf_token']);
                $editLink = "?q-id=$id&edit-q=true";
                echo "<div class='row question-list'><h4 class='my-question'>";
                echo "<span class='q-left'><a href='?q-id=$id'>$title</a></span>";
                echo "<span class='q-right'>";
                if ($isMyQnA) {
                    echo "<div class='dropdown d-inline-block'>";
                    echo "<button class='btn row-actions-toggle' type='button' data-bs-toggle='dropdown' aria-expanded='false' aria-label='Question actions'>";
                    echo "<span class='dots'>•••</span>";
                    echo "</button>";
                    echo "<ul class='dropdown-menu dropdown-menu-end'>";
                    echo "<li><a class='dropdown-item' href='$editLink'>Edit</a></li>";
                    echo "<li><a class='dropdown-item' href='$deleteLink'>Delete</a></li>";
                    echo "</ul></div>";
                }
                echo "<span class='answers-count'>$acnt</span>";
                echo "</span></h4></div>";
            }
            ?>
        </div>
        <div class="col-12 col-lg-4">
            <?php include("categoryList.php") ?>
        </div>
    </div>
</div>
