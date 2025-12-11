<div class="container">
    <h1 class="heading-center margin-bottom-2">Write Your Answer</h1>
    <div class="col-8">
        <?php
        include("./common/db.php");
        $query = "select * from questions where id =$qid";
        $result = $conn->query($query);
        $row = $result->fetch_assoc();
        echo "<h3 class='mt-15 question-title'>" . $row['title'] . "</h3>";
        echo "<p class='mt-15'>" . $row['description'] . "<p>";
        ?>
        <form action="./server/requests.php" method="post">
            <input type="hidden" name="question_id" value="<?php echo $qid?>">
            <textarea name="answer" class="form-control mt-15" placeholder="Write Your Answer..."></textarea>
            <button class="btn btn-primary mt-15">Submit Answer</button>
        </form>
    </div>
</div>