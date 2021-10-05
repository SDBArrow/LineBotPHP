<?php
date_default_timezone_set("Asia/Taipei"); //設定時區為台北時區

include('./connect.php'); //連結資料庫設定
$sql = "SHOW COLUMNS FROM sign_table"; 
$table_sign_table = mysqli_fetch_array(mysqli_query($db_connection, $sql));
echo $table_sign_table[0][0];
echo $table_sign_table[1][0];
echo $table_sign_table[2][0];
echo $table_sign_table[3][0];
echo $table_sign_table[4][0];
echo $table_sign_table[5][0];
