<div class="container">
    <h4 class="margin-bottom-15 margin-top-2">Answers</h4>
    <div class="margin-bottom-2">
        <?php
        include("./common/db.php");
        $qeury = "select * from answers where question_id =$qid";
        $result = $conn->query($qeury);
        foreach ($result as $row) {
            $answer = $row["answer"];
            $id = $row["id"];
            echo "<p>$id. $answer</p>";
        }
        ?>
    </div>
</div>