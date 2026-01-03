<div class="container">
    <div class="row">
        <div class="col-12 col-lg-8">
            <?php
            include("./common/db.php");
            
            $heading = "Questions";
            if (isset($_GET["c-id"])) {
                // Fetch category name
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
                // Fetch username
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
                $stmt = $conn->prepare("select q.id, q.title, u.id as user_id, u.username, count(a.id) as acnt from questions q left join answers a on a.question_id=q.id join users u on u.id=q.user_id group by q.id, q.title, u.id, u.username");
                $stmt->execute();
                $result = $stmt->get_result();
            }
            
            echo "<h2 class='heading-center margin-bottom-2'>$heading</h2>";

            $isMyQnA = isset($uid) && isset($_SESSION['user']['user_id']) && ((int)$_SESSION['user']['user_id'] === (int)$uid);
            foreach ($result as $row) {
                $title = htmlspecialchars($row["title"], ENT_QUOTES, 'UTF-8');
                $id = $row["id"];
                $userId = $row["user_id"];
                $acnt = isset($row["acnt"]) ? (int)$row["acnt"] : 0;
                $username = htmlspecialchars($row["username"], ENT_QUOTES, 'UTF-8');
                $initial = strtoupper(substr($username, 0, 1));
                $deleteLink = "./server/requests.php?delete=" . $id . "&csrf=" . urlencode($_SESSION['csrf_token']);
                $editLink = "?q-id=$id&edit-q=true";
                echo "<div class='row question-list'><h4 class='my-question'>";
                echo "<span class='q-left'><div class='user-inline'><a href='?u-id=$userId&profile=true' class='user-profile-link'><span class='avatar avatar-sm'>$initial</span><span class='user-name'>$username</span></a></div><a href='?q-id=$id'>$title</a></span>";
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
