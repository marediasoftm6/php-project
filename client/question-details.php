<div class="container">
    <h1 class="heading-center margin-bottom-2">Question</h1>
    <?php 
    include("./common/db.php");
    $query="select * from questions where id =$qid";
    $result= $conn->query(query:$query);
    $row=$result->fetch_assoc();
    echo"<h4></h4>";
    ?>
</div>