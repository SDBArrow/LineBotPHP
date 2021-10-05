<?php
date_default_timezone_set("Asia/Taipei"); //設定時區為台北時區

include('./connect.php'); //連結資料庫設定
$sql = "SHOW COLUMNS FROM sign_table"; 
$table_sign_table = mysqli_fetch_assoc(mysqli_query($db_connection, $sql));
echo $table_sign_table['Field'][0];