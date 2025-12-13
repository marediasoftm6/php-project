<div class="container mt-15">
    <div class="row">
        <div class="col-7">
            <h2 class="heading-center margin-bottom-2">Write Your Answer</h2>
            <?php
            include("./common/db.php");
            $query = "select * from questions where id =$qid";
            $result = $conn->query($query);
            $row = $result->fetch_assoc();
            $cid = $row['category_id'];
            echo "<h2 class='mt-15 question-title'>Q. " . $row['title'] . "</h2>";
            // echo "<p class='mt-15'>" . $row['description'] . "<p>";
            include("./client/answers.php");
            ?>
            <form action="./server/requests.php" method="post">
                <input type="hidden" name="question_id" value="<?php echo $qid ?>">
                <textarea name="answer" class="form-control mt-15" placeholder="Write Your Answer..." rows="10" cols="10"></textarea>
                <button class="btn btn-primary mt-15">Submit Answer</button>
            </form>
        </div>
        <div class="col-1"></div>
        <div class="col-4">
            <?php
            $catQeury = "select * from category where id=$cid";
            $catResult = $conn->query($catQeury);
            $catRow = $catResult->fetch_assoc();
            echo "<h2 class='margin-bottom-2'>" . $catRow['name'] . "</h2>";
            // include("categoryList.php")
            $query = "select * from questions where category_id=$cid and id!= $qid";
            $result = $conn->query($query);
            foreach ($result as $row) {
                $title = $row["title"];
                $id = $row["id"];
                echo "<div class='related-questions'><h4>
            <a href='?q-id=$id'>$title</a></h4></div>";
            }
            ?>
        </div>
    </div>
</div>