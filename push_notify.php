<?php
date_default_timezone_set("Asia/Taipei");//設定時區為台北時區
require_once('LINEBotXiaoFei.php'); //引入LINEBotXiaoFei.php發送code寫在LINEBotTiny
//$channelAccessToken =getenv('CAT_TEST') ;//初始化 紀錄圖片的ID
$channelAccessToken =getenv('CAT_XIAOFEI') ;//初始化 小飛群的ID
$client = new LINENotifyXiaoFei($channelAccessToken); //把Token,Secret丟到LINENotifyXiaoFei建立連線
function WorkSchedule($time ,$ansdate ,$client){
    include('./connect.php');//連結資料庫設定
	$timecount=(strtotime($time) - strtotime("2021-02-21 00:00:00"))/ (60*60*24); //相隔天數
	$weekcount=floor($timecount/7); //相隔週數

    //今天單周還雙周
	$oddandeven=$weekcount%2;
    $sql = "select * from week where week_int = ".$oddandeven;   
    $row_week = mysqli_fetch_assoc(mysqli_query($con, $sql));
	$week=$row_week["week_ch"]; 

    //今天星期幾
	$weekdaytempor=date('w', strtotime($time));
    $sql = "select * from day where day_int = ".$weekdaytempor;   
    $row_day = mysqli_fetch_assoc(mysqli_query($con, $sql));
	$day=$row_day["day_ch"]; 

    //查詢值日生
    $sql = "select * from duty_list where day = ".$weekdaytempor." and week = ".$oddandeven;   
    $row_name = mysqli_fetch_assoc(mysqli_query($con, $sql));
    $name=$row_name["name"]; 

	if ( $name == ""){  //檢查是否是替補日
		$tempor = 0; //初始化
	    $tempor = $tempor + $weekcount;  //替補計算
		$tempor = $tempor % 12; //因為有12個人，所以每12次重新一次
        //查詢替補
        $sql = "select * from duty_turn where id = ".$tempor;   
        $row_trun = mysqli_fetch_assoc(mysqli_query($con, $sql));
	    $turn=$row_trun["name"]; 
        //傳輸訊息
		$client->pushtonotify(
			$message = array(
				'message' => "==================\n     ".$ansdate."(".$week.")".$day."(替補)\n==================\n--->".$turn
			)
		);
	}else{   //不是替補日
        //傳輸訊息
		$client->pushtonotify(
			$message = array(
				'message' => "==================\n     ".$ansdate."(".$week.")".$day."\n==================\n--->".$name
			)
		);
	}
    mysqli_close($con);
}
//heroku 00:00會執行此檔案，但由於heroku沒在使用會進入休眠狀態，所以正常需要一分鐘緩衝時間，最慢兩分鐘過
if (date('H:i')=="00:00" || date('H:i')=="00:01" || date('H:i')=="00:02") { 
	//標頭
	$client->pushtonotify(
		$message = array(
			'message' => "\n=====每日自動提醒=====" // 回復訊息
		)
	);
	//今日遛狗
	$time=date('Y-m-d H:i:s');
	$ansdate = date('m/d');
	$result=WorkSchedule($time , $ansdate , $client); //丟去副程式WorkSchedule
	//明日遛狗
	$time=date('Y-m-d H:i:s',strtotime("+1 day"));  //抓時間
	$ansdate = date('m/d',strtotime("+1 day"));  //抓時間
	$result=WorkSchedule($time , $ansdate , $client); //丟去副程式WorkSchedule
	//後天遛狗
	$time=date('Y-m-d H:i:s',strtotime("+2 day"));  //抓時間
	$ansdate = date('m/d',strtotime("+2 day"));  //抓時間
	$result=WorkSchedule($time , $ansdate , $client); //丟去副程式WorkSchedule
}
?>