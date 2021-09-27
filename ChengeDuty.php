<?php
date_default_timezone_set("Asia/Taipei"); //設定時區為台北時區
// 處理遛狗查詢 
function WorkSchedule($time)
{
    include('./connect.php'); //連結資料庫設定
    $timecount = (strtotime($time) - strtotime("2021-09-19 00:00:00")) / (60 * 60 * 24); //相隔天數
    $weekcount = floor($timecount / 7); //相隔週數

    //今天單周還雙周
    $oddandeven = $weekcount % 2;

    //今天星期幾
    $weekdaytempo= date('w');

    //查詢值日生
    $sql = "select * from duty_list where day = " . $weekdaytempor . " and week = " . $oddandeven;
    $row_name = mysqli_fetch_assoc(mysqli_query($db_connection, $sql));
    $name = $row_name["name"];

    //回傳變數初始化
    $ReturnMessage = "";

    if ($name == "") {  //檢查是否是替補日
        $tempor = 6; //初始化 上次替補結尾輪到6號
        $tempor = $tempor + floor($weekcount/2)*3;  //替補計算  兩個星期會有三次替補
        if ( $oddandeven == 0 && $weekdaytempor == 0){
            $tempor = $tempor % 11;                 //兩個星期的第一次
        }elseif($oddandeven == 0 && $weekdaytempor == 1){
            $tempor = ($tempor % 11 + 1) % 11;             //兩個星期的第二次
        }else{      
            $tempor = ($tempor % 11 + 2) % 11;             //兩個星期的第三次
        }
        //查詢替補
        $sql = "select * from duty_turn where id = " . $tempor;
        $row_dutytrun = mysqli_fetch_assoc(mysqli_query($db_connection, $sql));
        $dutytrun = $row_dutytrun["name"];
        $ReturnMessage = 0; // 回復訊息
    } else {   //不是替補日
        $ReturnMessage = 0; // 回復訊息
    }
    //傳輸訊息
    mysqli_close($db_connection); //關閉資料庫連線
    return($ReturnMessage);
}


$time = date('Y-m-d');  //抓時間
$result = WorkSchedule($time); //丟去副程式WorkSchedule
