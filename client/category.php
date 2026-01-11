<select class="form-select" name="category" id="category" required>
    <option value="">Select A Category</option>
    <?php
    include("./common/db.php");
    $qeury= "select * from category";
    $result= $conn->query(query:$qeury);
    foreach($result as $row){
        $name = $row["name"];
        $id = $row["id"];
        $selected = (isset($_GET['pre_select_category']) && $_GET['pre_select_category'] == $id) ? 'selected' : '';
        echo "<option value=$id $selected>$name</option>";
    } 
    ?>
</select>