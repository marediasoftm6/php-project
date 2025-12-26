<div class="container mt-15">
    <h2 class="heading-center margin-bottom-2">Categories</h2>
    <?php
    include("./common/db.php");
    $canManage = isset($_SESSION['user']['user_id']);
    if ($canManage && isset($_GET['edit-c'])) {
        $editId = (int)$_GET['edit-c'];
        $one = $conn->prepare("select id, name from category where id=?");
        $one->bind_param("i", $editId);
        $one->execute();
        $oneRes = $one->get_result();
        if ($oneRes->num_rows === 1) {
            $oneRow = $oneRes->fetch_assoc();
            ?>
            <form class="margin-bottom-15" method="post" action="./server/requests.php">
                <input type="hidden" name="csrf" value="<?php echo $_SESSION['csrf_token'] ?>">
                <input type="hidden" name="category_id" value="<?php echo (int)$oneRow['id'] ?>">
                <div class="margin-bottom-15">
                    <label for="edit_cat_name">Edit Category</label>
                    <input type="text" id="edit_cat_name" class="form-control" name="name" value="<?php echo htmlspecialchars($oneRow['name'], ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <button type="submit" name="editCategory" class="btn btn-primary">Save</button>
            </form>
            <?php
        }
    }
    $stmt = $conn->prepare("select c.id, c.name, count(q.id) as cnt from category c left join questions q on q.category_id=c.id group by c.id, c.name order by c.name asc");
    $stmt->execute();
    $result = $stmt->get_result();
    foreach ($result as $row) {
        $name = htmlspecialchars(ucfirst($row["name"]), ENT_QUOTES, 'UTF-8');
        $id = (int)$row["id"];
        $cnt = (int)$row["cnt"];
        $deleteLink = "./server/requests.php?deleteCategory=" . $id . "&csrf=" . urlencode($_SESSION['csrf_token']);
        $editLink = "?categories=true&edit-c=$id";
        echo "<div class='categories-list'><h4 class='my-question'><a href='?c-id=$id'>$name</a><span class='categories-count'>$cnt</span>";
        echo $canManage ? "<a class='action-btn' href='$editLink'>Edit</a><a class='action-btn' href='$deleteLink'>Delete</a>" : "";
        echo "</h4></div>";
    }
    ?>
</div>
