<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "globals.php";



$collation = sqlStatement("select table_name, table_collation from information_schema.tables where table_schema = 'dfw_skin' AND table_collation != 'utf8mb4_general_ci'");

while ($row = sqlFetchArray($collation)) {
    echo $row['table_name'] . " " . $row['table_collation'] . "<br>";

    if ($row['table_collation'] != 'utf8mb4_general_ci') {

        if ($row['table_name'] != 'patient_data' || $row['table_name'] != 'keys') {
           continue; 
        } 
        $sql = "ALTER TABLE " . $row['table_name'] . " CONVERT TO CHARACTER SET utf8mb4";
            sqlStatement($sql); 
    }
}


