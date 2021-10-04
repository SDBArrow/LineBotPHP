<?php
date_default_timezone_set("Asia/Taipei"); //設定時區為台北時區
require_once('LINEBotXiaoFei.php'); //引入LINEBotXiaoFei.php發送code寫在LINEBotTiny
$channelAccessToken =getenv('CAT_TEST') ;//初始化 紀錄圖片的ID
//$channelAccessToken = getenv('CAT_XIAOFEI'); //初始化 小飛群的ID
$client = new LINENotifyXiaoFei($channelAccessToken); //把Token,Secret丟到LINENotifyXiaoFei建立連線
require('./function_conform.php'); //引入LINEBotXiaoFei.php發送code寫在LINEBotTiny
$work = new Linebot();

switch(true){
    //heroku 00:00會執行此檔案，但由於heroku沒在使用會進入休眠狀態，所以正常需要一分鐘緩衝時間，最慢五分鐘過
    case (date('H:i') == "00:00" || date('H:i') == "00:01" || date('H:i') == "00:02" || date('H:i') == "00:03" || date('H:i') == "00:04" || date('H:i') == "00:05"):
  		//標頭
		$ReturnMessage = "\n=====每日自動提醒====="; //丟去副程式WorkSchedule
		$work -> notifypushText($ReturnMessage, $client); //回傳訊息
		//今日遛狗
		$time = date('Y-m-d');//抓時間
		$ReturnMessage = "\n".$work -> WorkSchedule($time); //丟去副程式WorkSchedule
		$work -> notifypushText($ReturnMessage, $client); //回傳訊息
		//明日遛狗
		$time = date('Y-m-d', strtotime("+1 day"));  //抓時間
		$ReturnMessage = "\n".$work -> WorkSchedule($time); //丟去副程式WorkSchedule
		$work -> notifypushText($ReturnMessage, $client); //回傳訊息
		//後天遛狗
		$time = date('Y-m-d', strtotime("+2 day"));  //抓時間
		$ReturnMessage = "\n".$work -> WorkSchedule($time); //丟去副程式WorkSchedule
		$work -> notifypushText($ReturnMessage, $client); //回傳訊息
		//工作檢核
		$ReturnMessage = "\n=======================\n          本周工作檢核紀錄\n=======================\n--->https://reurl.cc/WXqqYk";
		$work -> notifypushText($ReturnMessage, $client); //回傳訊息
		break;
	//檢測打卡有沒有缺漏
    case (date('H:i') == "00:39" || date('H:i') == "00:40" || date('H:i') == "00:41" || date('H:i') == "00:42" || date('H:i') == "00:43" || date('H:i') == "00:44"):
		//標頭
		include('./connect.php'); //連結資料庫設定
		$time = date('w');  //抓時間
		$sql = "select * from sign_table,member where sign_table.userid = member.userid and day_int = ".$time; 
        $table_sign_table = mysqli_fetch_assoc(mysqli_query($db_connection, $sql));
		if ($table_sign_table["e419_refrigerator"] == "" || $table_sign_table["e419_ashcan"] == "" || $table_sign_table["e419_corridor"] == "" || $table_sign_table["e419_conditioner_light"] == "" || $table_sign_table["e420_corridor"] == "" || $table_sign_table["e420_equipment"] == "" || $table_sign_table["e420_chair"] == "" || $table_sign_table["e420_conditioner_light"] == "" || $table_sign_table["e420_Shoebox"] == "" || $table_sign_table["room_conditioner_light"] == ""){
			$ReturnMessage = "\n=====工作檢核自動提醒====="; //丟去副程式WorkSchedule
			$work -> notifypushText($ReturnMessage, $client); //回傳訊息
			$ReturnMessage = "\n".$table_sign_table["name"]."\n=======================\n--->工作檢核表尚未完成或有缺漏，請盡速補正"; //丟去副程式WorkSchedule
			$work -> notifypushText($ReturnMessage, $client); //回傳訊息
		}
		mysqli_close($db_connection);
        break;
    default:
        break;
}