<?php
include("common/db.php");

$tables = ["questions", "category", "users"];
foreach ($tables as $table) {
    echo "Table: $table\n";
    $result = $conn->query("SHOW COLUMNS FROM $table");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            echo "  - " . $row['Field'] . " (" . $row['Type'] . ")\n";
        }
    } else {
        echo "  - TABLE DOES NOT EXIST\n";
    }
    echo "\n";
}
?>