<?php
$server=getenv('DB_HOST');//主機
$db_username=getenv('DB_USERNAME');//你的資料庫使用者名稱
$db_password=getenv('DB_PASSWORD');//你的資料庫密碼
$con = mysqli_connect($server,$db_username,$db_password);//連結資料庫
mysqli_query($con, "SET NAMES 'UTF8'");
mysqli_select_db($con,getenv('DB_NAME'));//選擇資料庫
?>