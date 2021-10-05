<?php
date_default_timezone_set("Asia/Taipei"); //設定時區為台北時區
$time = date('w');
include('./connect.php'); //連結資料庫設定


$sql = "SHOW COLUMNS FROM sign_table"; 
$result = mysqli_query($db_connection, $sql);
$rowtotal = mysqli_num_rows($result);
echo $rowtotal."\n";

for ($i = 0; $i < $rowtotal; $i++){
	$table_sign_table = mysqli_fetch_assoc($result);
	$Field[$i] = $table_sign_table['Field'];
	echo $Field[$i];
}

$sql = "select * from sign_table,member where sign_table.userid = member.userid and day_int = ".$time;
$table_sign_table = mysqli_fetch_assoc(mysqli_query($db_connection, $sql));

$Miss = FALSE;
for ($i = 2; $i < $rowtotal; $i++){
	if ($table_sign_table[$Field[$i]] == ""){
		$MISS = True;
		$sql = "update sign_table set ".[$Field[$i]]." = '缺漏' where day_int = ".$time;
		if(mysqli_query($db_connection, $sql)){ //更新到資料庫
			$ReturnMessage = "已更新工作審核表\n";
		} else{
			$ReturnMessage = "更新失敗\n";
		}
	}
}

if ($Miss){

}

