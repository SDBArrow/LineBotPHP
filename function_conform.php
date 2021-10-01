<?php

require_once('LINEBotXiaoFei.php'); //引入LINEBotXiaoFei.php發送code寫在LINEBotTiny

class Linebot{
    function WorkSchedule($time){
        include('./connect.php'); //連結資料庫設定
        $timecount = (strtotime($time) - strtotime("2021-09-19 00:00:00")) / (60 * 60 * 24); //相隔天數
        $weekcount = floor($timecount / 7); //相隔週數
        $oddandeven = $weekcount % 2;   //今天單周還雙周
        $weekdaytempor = date('w', strtotime($time));  //今天星期幾

        //查詢值日生
        $sql = "select * from duty_list where day = " . $weekdaytempor . " and week = " . $oddandeven;
        $row_userid = mysqli_fetch_assoc(mysqli_query($db_connection, $sql));
        if (strtotime($time) - strtotime("2021-10-03 00:00:00")>=0){ //判斷新班表還舊班表
            $userid = $row_userid["new_userid"];
        }else{
            $userid = $row_userid["userid"];
        }

        $ReturnMessage = "";     //回傳變數初始化

        if ($userid == NULL) {  //檢查是否是替補日
            $tempor = 6; //初始化 2021-09-19上次替補結尾輪到6號
            $tempor = $tempor + floor($weekcount/2)*3;  //替補計算  兩個星期會有三次替補
            if (strtotime($time) - strtotime("2021-10-03 00:00:00")>=0){ //判斷新班表還舊班表
                if ( $oddandeven == 0 ){
                    $tempor = $tempor % 11;                 //兩個星期的第一次
                }elseif($oddandeven == 1 && $weekdaytempor == 0){
                    $tempor = ($tempor % 11 + 1) % 11;             //兩個星期的第二次
                }elseif($oddandeven == 0 && $weekdaytempor == 2){      
                    $tempor = ($tempor % 11 + 2) % 11;             //兩個星期的第三次
                }
            }else{
                if ( $oddandeven == 0 && $weekdaytempor == 0){
                    $tempor = $tempor % 11;                 //兩個星期的第一次
                }elseif($oddandeven == 0 && $weekdaytempor == 1){
                    $tempor = ($tempor % 11 + 1) % 11;             //兩個星期的第二次
                }else{      
                    $tempor = ($tempor % 11 + 2) % 11;             //兩個星期的第三次
                }
            }
            //查詢替補
            $sql = "select * from duty_turn where id = ".$tempor;
            $table_duty_trun = mysqli_fetch_assoc(mysqli_query($db_connection, $sql));
            $userid = $table_duty_trun["userid"];
            $ReturnMessage = $userid; // 回復訊息
        } else {   //不是替補日
            $ReturnMessage = $userid; // 回復訊息
        }
        //傳輸訊息
        mysqli_close($db_connection); //關閉資料庫連線
        return($ReturnMessage);
    }
}
