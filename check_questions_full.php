<?php
include("common/db.php");
$result = $conn->query("SHOW CREATE TABLE questions");
$row = $result->fetch_assoc();
echo $row['Create Table'];
?>