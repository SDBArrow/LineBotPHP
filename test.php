<?php
date_default_timezone_set("Asia/Taipei"); //設定時區為台北時區

include('./connect.php'); //連結資料庫設定
$sql = "SHOW COLUMNS FROM sign_table"; 
$result = mysqli_query($db_connection, $sql);

for ($i = 0 ;  $i <= 7 ; $i++){
	$table_sign_table = mysqli_fetch_assoc($result);
	echo $table_sign_table['Field'];
}

