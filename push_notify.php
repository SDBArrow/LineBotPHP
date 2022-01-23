<?php
date_default_timezone_set("Asia/Taipei"); //設定時區為台北時區
require_once('LINEBotXiaoFei.php'); //引入LINEBotXiaoFei.php發送code寫在LINEBotTiny
//channelAccessToken =getenv('CAT_TEST') ;//初始化 紀錄圖片的ID
$channelAccessToken = getenv('CAT_XIAOFEI'); //初始化 小飛群的ID
$client = new LINENotifyXiaoFei($channelAccessToken); //把Token,Secret丟到LINENotifyXiaoFei建立連線
require('./function_conform.php'); //引入LINEBotXiaoFei.php發送code寫在LINEBotTiny
$work = new Linebot();

//heroku 00:00會執行此檔案，但由於heroku沒在使用會進入休眠狀態，所以正常需要一分鐘緩衝時間，最慢五分鐘過
switch(true){
    //發送近三日遛狗名單和簽到表
    case (date('H:i') <= "00:35"  && date('H:i') >= "00:30"):
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
	//檢測打卡有沒有缺漏，並補上缺漏和發送提醒
    case (date('H:i') >= "23:30" && date('H:i') <= "23:35"):
		
		include('./connect.php'); //連結資料庫設定
		$time = date('w');  //抓時間

		//查詢sign_table 資料表欄位
		$sql = "SHOW COLUMNS FROM sign_table"; 
		$result = $db_connection->query($sql); 
		$rowtotal = $result->num_rows;

		//提取sign_table欄位名稱
		for ($i = 0; $i < $rowtotal; $i++){
			$table_sign_table = $result->fetch_assoc();
			$Field[$i] = $table_sign_table['Field'];
		}

		//查詢簽到表資料
		$sql = "select * from sign_table,member where sign_table.userid = member.userid and day_int = ".$time;
		$table_sign_table = $db_connection->query($sql)->fetch_assoc();

		//檢查工作項目打卡是否有遺漏
		$Miss = FALSE;
		for ($i = 2; $i < $rowtotal; $i++){
			if ($table_sign_table[$Field[$i]] == ""){
				$Miss = True;
				$sql = "update sign_table set ".$Field[$i]." = '缺漏' where day_int = ".$time;
				$db_connection->query($sql); //更新到資料庫
				if($db_connection->affected_rows > 0){ //檢查是否更新成功
					$ReturnSQLMessage = "已更新工作審核表\n";
				}else{
					$ReturnSQLMessage = "更新失敗\n";
				}
				echo $ReturnSQLMessage;
			}
		}

		//有缺漏發送提醒訊息
		if ($Miss){
			$ReturnMessage = "\n=====工作檢核自動提醒====="; //丟去副程式WorkSchedule
			$work -> notifypushText($ReturnMessage, $client); //回傳訊息
			$ReturnMessage = "\n".$table_sign_table["name"]."\n=======================\n--->工作檢核表尚未完成或有缺漏，請盡速補正"; //丟去副程式WorkSchedule
			$work -> notifypushText($ReturnMessage, $client); //回傳訊息
		}

		$db_connection -> close();
        break;
    default:
        break;
}