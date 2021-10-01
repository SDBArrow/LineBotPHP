<?php
date_default_timezone_set("Asia/Taipei"); //設定時區為台北時區
require_once('LINEBotXiaoFei.php'); //引入LINEBotXiaoFei.php發送code寫在LINEBotTiny
//$channelAccessToken =getenv('CAT_TEST') ;//初始化 紀錄圖片的ID
$channelAccessToken = getenv('CAT_XIAOFEI'); //初始化 小飛群的ID
$client = new LINENotifyXiaoFei($channelAccessToken); //把Token,Secret丟到LINENotifyXiaoFei建立連線
require('./function_conform.php'); //引入LINEBotXiaoFei.php發送code寫在LINEBotTiny
$work = new Linebot();

//heroku 00:00會執行此檔案，但由於heroku沒在使用會進入休眠狀態，所以正常需要一分鐘緩衝時間，最慢五分鐘過
if (date('H:i') == "00:00" || date('H:i') == "00:01" || date('H:i') == "00:02" || date('H:i') == "00:03" || date('H:i') == "00:04" || date('H:i') == "00:05") {
	//標頭
	$ReturnMessage = "\n=====每日自動提醒====="; //丟去副程式WorkSchedule
	$work -> notifypushText($ReturnMessage, $client); //回傳訊息
	//今日遛狗
	$time = date('Y-m-d');//抓時間
	$ReturnMessage = $work -> WorkSchedule($time); //丟去副程式WorkSchedule
	$work -> notifypushText($ReturnMessage, $client); //回傳訊息
	//明日遛狗
	$time = date('Y-m-d', strtotime("+1 day"));  //抓時間
	$ReturnMessage = $work -> WorkSchedule($time); //丟去副程式WorkSchedule
	$work -> notifypushText($ReturnMessage, $client); //回傳訊息
	//後天遛狗
	$time = date('Y-m-d', strtotime("+2 day"));  //抓時間
	$ReturnMessage = $work -> WorkSchedule($time); //丟去副程式WorkSchedule
	$work -> notifypushText($ReturnMessage, $client); //回傳訊息
}