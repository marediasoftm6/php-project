<select class="form-control" name="category" id="category">
    <option value="">Select A Category</option>
    <?php
    include("./common/db.php");
    $qeury= "select * from category";
    $result= $conn->query(query:$qeury);
    foreach($result as $row){
        $name = $row["name"];
        $id = $row["id"];
        echo "<option value=$id>$name</option>";
    } 
    ?>
</select>