<?php
date_default_timezone_set("Asia/Taipei"); //設定時區為台北時區
require_once('LINEBotXiaoFei.php'); //引入LINEBotXiaoFei.php發送code寫在LINEBotTiny
//$channelAccessToken =getenv('CAT_TEST') ;//初始化 紀錄圖片的ID
$channelAccessToken = getenv('CAT_XIAOFEI'); //初始化 小飛群的ID
$client = new LINENotifyXiaoFei($channelAccessToken); //把Token,Secret丟到LINENotifyXiaoFei建立連線
function WorkSchedule($time, $client)
{
	include('./connect.php'); //連結資料庫設定
	$timecount = (strtotime($time) - strtotime("2021-09-19 00:00:00")) / (60 * 60 * 24); //相隔天數
	$weekcount = floor($timecount / 7); //相隔週數

	//今天單周還雙周
	$oddandeven = $weekcount % 2;
	$sql = "select * from week where week_int = " . $oddandeven;
	$row_week = mysqli_fetch_assoc(mysqli_query($db_connection, $sql));
	$week = $row_week["week_ch"];

	//今天星期幾
	$weekdaytempor = date('w', strtotime($time));
	$sql = "select * from day where day_int = " . $weekdaytempor;
	$row_day = mysqli_fetch_assoc(mysqli_query($db_connection, $sql));
	$day = $row_day["day_ch"];

	//查詢值日生
	$sql = "select * from duty_list where day = " . $weekdaytempor . " and week = " . $oddandeven;
	$row_name = mysqli_fetch_assoc(mysqli_query($db_connection, $sql));
	$name = $row_name["name"];

    //回傳變數初始化
    $returntext = "";
	
	if ($name == "") {  //檢查是否是替補日
		$tempor = 6; //初始化 上次替補結尾輪到6號
        $tempor = $tempor + floor($weekcount/2)*3;  //替補計算  兩個星期會有三次替補
        if ( $oddandeven == 0 && $weekdaytempor == 0){
            $tempor = $tempor % 11; 
        }elseif($oddandeven == 0 && $weekdaytempor == 1){
            $tempor = $tempor % 11 + 1; 
        }else{  
            $tempor = $tempor % 11 + 2;
        }
        //查詢替補
        $sql = "select * from duty_turn where id = " . $tempor;
        $row_dutytrun = mysqli_fetch_assoc(mysqli_query($db_connection, $sql));
        $dutytrun = $row_dutytrun["name"];
        $returntext = "=======================\n     " . $time . "(" . $week . ")" . $day . "(替補)\n=======================\n--->" . $dutytrun; // 回復訊息
    } else {   //不是替補日
        $returntext = "=======================\n     " . $time . "(" . $week . ")" . $day . "\n=======================\n--->" . $name; // 回復訊息
    }
	$client->pushtonotify(
		$message = array(
			'message' => $returntext
		)
	);
	mysqli_close($db_connection);
}
//heroku 00:00會執行此檔案，但由於heroku沒在使用會進入休眠狀態，所以正常需要一分鐘緩衝時間，最慢五分鐘過
if (date('H:i') == "00:00" || date('H:i') == "00:01" || date('H:i') == "00:02" || date('H:i') == "00:03" || date('H:i') == "00:04" || date('H:i') == "00:05") {
	//標頭
	$client->pushtonotify(
		$message = array(
			'message' => "\n=====每日自動提醒=====" // 回復訊息
		)
	);
	//今日遛狗
	$time = date('Y-m-d');//抓時間
	$result = WorkSchedule($time, $client); //丟去副程式WorkSchedule
	//明日遛狗
	$time = date('Y-m-d', strtotime("+1 day"));  //抓時間
	$result = WorkSchedule($time, $client); //丟去副程式WorkSchedule
	//後天遛狗
	$time = date('Y-m-d', strtotime("+2 day"));  //抓時間
	$result = WorkSchedule($time, $client); //丟去副程式WorkSchedule
}
