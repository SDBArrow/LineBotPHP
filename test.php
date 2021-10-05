<?php
date_default_timezone_set("Asia/Taipei"); //設定時區為台北時區

include('./connect.php'); //連結資料庫設定
$sql = "SHOW COLUMNS FROM sign_table"; 
$result = mysqli_query($db_connection, $sql);
$rowtotal = mysqli_num_rows($result);
echo $rowtotal."\n";

for ($i = 0 ;  $i < $rowtotal ; $i++){
	$table_sign_table = mysqli_fetch_assoc($result);
	$Field[$i] = $table_sign_table['Field'];
	echo $Field[$i];
}

