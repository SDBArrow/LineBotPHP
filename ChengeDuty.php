<?php
include('./connect.php'); //連結資料庫設定
date_default_timezone_set("Asia/Taipei"); //設定時區為台北時區

// 處理遛狗查詢 
function WorkSchedule($time)
{
    include('./connect.php'); //連結資料庫設定
    $timecount = (strtotime($time) - strtotime("2021-09-19 00:00:00")) / (60 * 60 * 24); //相隔天數
    $weekcount = floor($timecount / 7); //相隔週數
    $oddandeven = $weekcount % 2;   //今天單周還雙周
    $weekdaytempor = date('w', strtotime($time));  //今天星期幾

    //查詢值日生
    $sql = "select * from duty_list where day = " . $weekdaytempor . " and week = " . $oddandeven;
    $table_duty_list = mysqli_fetch_assoc(mysqli_query($db_connection, $sql));
    $duty = $table_duty_list["userid"];

    $ReturnMessage = "";     //回傳變數初始化

    if ($duty == "") {  //檢查是否是替補日
        $tempor = 6; //初始化 2021-09-19上次替補結尾輪到6號
        $tempor = $tempor + floor($weekcount/2)*3;  //替補計算  兩個星期會有三次替補
        if ( $oddandeven == 0 && $weekdaytempor == 0){
            $tempor = $tempor % 11;                 //兩個星期的第一次
        }elseif($oddandeven == 0 && $weekdaytempor == 1){
            $tempor = ($tempor % 11 + 1) % 11;             //兩個星期的第二次
        }else{      
            $tempor = ($tempor % 11 + 2) % 11;             //兩個星期的第三次
        }
        //查詢替補
        $sql = "select * from duty_turn where id = ".$tempor;
        $table_duty_trun = mysqli_fetch_assoc(mysqli_query($db_connection, $sql));
        $dutytrun = $table_duty_trun["userid"];
        $ReturnMessage = $dutytrun; // 回復訊息
    } else {   //不是替補日
        $ReturnMessage = $duty; // 回復訊息
    }
    //傳輸訊息
    mysqli_close($db_connection); //關閉資料庫連線
    return($ReturnMessage);
}

//時間判斷
if (date('H:i') == "00:00" || date('H:i') == "00:01" || date('H:i') == "00:02" || date('H:i') == "00:03" || date('H:i') == "00:04" || date('H:i') == "00:05") {
    //查詢今天值日生
    $time = date('Y-m-d');  //抓時間
    $today_duty = WorkSchedule($time); //丟去副程式WorkSchedule
    //新增權限給今日值日生
    $sql = "update member set duty_level = 1 where userid ='".$today_duty."'";
    if(mysqli_query($db_connection, $sql)){ //更新到資料庫
        $ReturnMessage = "權限更新成功";
    } else{
        $ReturnMessage = "權限更新失敗";
    }
    echo $ReturnMessage;
}elseif (date('H:i') == "00:26" || date('H:i') == "00:27" || date('H:i') == "00:28" || date('H:i') == "23:57" || date('H:i') == "23:58" || date('H:i') == "23:59"){
    //清除昨天的值日生權限
    $sql = "update member set duty_level = ''";
    if(mysqli_query($db_connection, $sql)){ //更新到資料庫
        $ReturnMessage = "權限更新成功";
    } else{
        $ReturnMessage = "權限更新失敗";
    }
    echo $ReturnMessage;
}


