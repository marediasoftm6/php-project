<div class="mt-15">
    <h2 class="margin-bottom-2">Categories</h2>
    <?php 
    include("./common/db.php");
    $qeury= "select * from category";
    $result= $conn->query(query:$qeury);
    foreach($result as $row){
        $name = ucfirst($row["name"]);
        $id = $row["id"];
        echo "<p class='categories-list'><a href='?c-id=$id'>$name</a></p>";
    }?>
</div>