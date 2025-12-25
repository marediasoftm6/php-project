<div class="container">
    <div class="row">
        <div class="col-7">
            <h2 class="heading-center margin-bottom-2">Questions</h2>
            <?php
            include("./common/db.php");
            if (isset($_GET["c-id"])) {
                $qeury = "select * from questions where category_id=$cid";
            } else if (isset($_GET["u-id"])) {
                $qeury = "select * from questions where user_id=$uid";
            } else if (isset($_GET["latest"])) {
                $qeury = "select * from questions order by id desc";
            } else {
                $qeury = "select * from questions";
            }
            $result = $conn->query($qeury);
            foreach ($result as $row) {
                $title = $row["title"];
                $id = $row["id"];
                echo "<div class='row question-list'>
            <h4>
            <a href='?q-id=$id'>$title</a>
            </h4>
            </div>";
            }
            ?>
        </div>
        <div class="col-1"></div>
        <div class="col-4">
            <?php include("categoryList.php") ?>
        </div>
    </div>
</div>