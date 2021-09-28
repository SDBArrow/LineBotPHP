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

switch(true){
    //每天00:00要執行的
    case (date('H:i') == "00:00" || date('H:i') == "00:01" || date('H:i') == "00:02" || date('H:i') == "00:03" || date('H:i') == "00:04" || date('H:i') == "00:05"):
        //清除昨天的值日生權限
        $sql = "update member set duty_level = ''";
        if(mysqli_query($db_connection, $sql)){ //更新到資料庫
            $ReturnMessage = "權限移除成功";
        } else{
            $ReturnMessage = "權限移除失敗";
        }
        //查詢今天值日生
        $time = date('w');  //抓時間
        $sql = "select * from sign_table where day_int = ".$time; 
        $row = mysqli_fetch_assoc(mysqli_query($db_connection, $sql));
        $today_duty= $row["userid"];
        //新增權限給今日值日生
        $sql = "update member set duty_level = 1 where userid =".$today_duty;
        if(mysqli_query($db_connection, $sql)){ //更新到資料庫
            $ReturnMessage = "權限更新成功";
        }else{
            $ReturnMessage = "權限更新失敗";
        }
        echo $ReturnMessage;
    //一星期執行一次、每個星期天執行
    case (date('w') == 0):
        $time = date('Y-m-d');  //抓時間
        $duty_0 = WorkSchedule($time);
        $time = date('Y-m-d', strtotime("+1 day"));  //抓時間
        $duty_1 = WorkSchedule($time);
        $time = date('Y-m-d', strtotime("+2 day"));  //抓時間
        $duty_2 = WorkSchedule($time);
        $time = date('Y-m-d', strtotime("+3 day"));  //抓時間
        $duty_3 = WorkSchedule($time);
        $time = date('Y-m-d', strtotime("+4 day"));  //抓時間
        $duty_4 = WorkSchedule($time);
        $time = date('Y-m-d', strtotime("+5 day"));  //抓時間
        $duty_5 = WorkSchedule($time);
        $time = date('Y-m-d', strtotime("+6 day"));  //抓時間
        $duty_6 = WorkSchedule($time);
        
        $sql[0] = "update sign_table set userid = null, e419_refrigerator = '', e419_refrigerator = '', e419_ashcan = '', e419_corridor = '', e419_conditioner_light = '', e420_corridor = '', e420_equipment = '', e420_chair = '', e420_conditioner_light = '', e420_Shoebox = '', room_conditioner_light = ''";
        $sql[1] = "update sign_table set userid = ".$duty_0." where day_int = 0";
        $sql[2] = "update sign_table set userid = ".$duty_1." where day_int = 1";
        $sql[3] = "update sign_table set userid = ".$duty_2." where day_int = 2";
        $sql[4] = "update sign_table set userid = ".$duty_3." where day_int = 3";
        $sql[5] = "update sign_table set userid = ".$duty_4." where day_int = 4";
        $sql[6] = "update sign_table set userid = ".$duty_5." where day_int = 5";
        $sql[7] = "update sign_table set userid = ".$duty_6." where day_int = 6";

        for($var = 0; $var < count($sql); $var++){
            if(mysqli_query($db_connection, $sql[$var])){ //更新到資料庫
                $ReturnMessage = "檢核表更新成功\n";
            } else{
                $ReturnMessage = "檢核表更新失敗\n";
            }
            echo $ReturnMessage;
        }
    case (date('w') == 2):
        $weekdaytempor = 0;
        $oddandeven = 0;
        $sql = "select * from duty_list where day = " . $weekdaytempor . " and week = " . $oddandeven;
        $row_userid = mysqli_fetch_assoc(mysqli_query($db_connection, $sql));
        $userid = $row_userid["userid"];
        echo $userid;
        if($userid == NULL){
            echo "NULL";
        }elseif($userid == ""){
            echo "空";
        }

    default:
        break;
}


