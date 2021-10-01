<?php
$server = getenv('DB_HOST'); //主機
$db_username = getenv('DB_USERNAME'); //你的資料庫使用者名稱
$db_password = getenv('DB_PASSWORD'); //你的資料庫密碼
$db_name = getenv('DB_NAME'); //選擇資料庫
$cleardb_ca = getenv('CLEARDB_SSL_CA_CERT'); //SSL ca
$cleardb_cert = getenv('CLEARDB_SSL_CLI_CERT'); //SSL cert
$cleardb_key = getenv('CLEARDB_SSL_KEY'); //SSL rsa key

//檢測有沒有 openssl
if(!extension_loaded('openssl')) {
    throw new Exception('This app needs the Open SSL PHP extension and it is missing.');
}
//函數初始化
$db_connection = mysqli_init(); 
//ssh設定
$db_connection->ssl_set($cleardb_key, $cleardb_cert, $cleardb_ca, NULL, NULL); 
//連線   MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT:php新版本會有這問題
$db_connection->real_connect($server, $db_username, $db_password, $db_name, 3306, NULL, MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT);

mysqli_query($db_connection, "SET NAMES 'UTF8'"); //設定編碼

/* no ssh
$con = mysqli_connect($server,$db_username,$db_password);//連結資料庫
mysqli_select_db($con,'user');//選擇資料庫（我的是test）
*/