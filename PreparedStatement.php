<?php
include('./connect.php'); //連結資料庫設定

$name = 'grandpa'; #自訂變數
$sql = 'SELECT * FROM users WHERE username=?'; #SQL指令 ?代表參數

$stmt = $db_connection -> stmt_init(); #使用前初始化
$stmt -> prepare($sql); #將SQL進行編譯
$stmt -> bind_param('s',$name); #帶入參數值 's'代表一個string 若參數有兩個string則為'ss'以此類推
$stmt -> execute(); #執行


#使用完釋放資源
$stmt -> close();
$db_connection -> close();