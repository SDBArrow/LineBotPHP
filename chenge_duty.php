<?php
include('./connect.php'); //連結資料庫設定
date_default_timezone_set("Asia/Taipei"); //設定時區為台北時區
require('./function_conform.php'); //引入LINEBotXiaoFei.php發送code寫在LINEBotTiny
$work = new Linebot();

switch(true){
    //一星期執行一次、每個星期天執行
    case (date('w') == 1 && (date('H:i') == "13:52" || date('H:i') == "13:53" || date('H:i') == "13:54" || date('H:i') == "00:03" || date('H:i') == "00:04" || date('H:i') == "00:05")):
        //寄送當周工作檢核至管理員的email

        //清空工作檢核表，並匯入當周新值日生
        $time = date('Y-m-d');  //抓時間
        $duty_1 = $work ->WorkScheduleOnlyUserid($time);
        $time = date('Y-m-d', strtotime("-1 day"));  //抓時間
        $duty_0 = $work ->WorkScheduleOnlyUserid($time);
        $time = date('Y-m-d', strtotime("+1 day"));  //抓時間
        $duty_2 = $work ->WorkScheduleOnlyUserid($time);
        $time = date('Y-m-d', strtotime("+2 day"));  //抓時間
        $duty_3 = $work ->WorkScheduleOnlyUserid($time);
        $time = date('Y-m-d', strtotime("+3 day"));  //抓時間
        $duty_4 = $work ->WorkScheduleOnlyUserid($time);
        $time = date('Y-m-d', strtotime("+4 day"));  //抓時間
        $duty_5 = $work ->WorkScheduleOnlyUserid($time);
        $time = date('Y-m-d', strtotime("+5 day"));  //抓時間
        $duty_6 = $work ->WorkScheduleOnlyUserid($time);
        
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
    //每天00:00要執行的
    case (date('H:i') == "13:52" || date('H:i') == "13:53" || date('H:i') == "13:54" || date('H:i') == "00:03" || date('H:i') == "00:04" || date('H:i') == "00:05"):
        //清除昨天的值日生權限
        $sql = "update member set duty_level = 0";
        if(mysqli_query($db_connection, $sql)){ //更新到資料庫
            $ReturnMessage = "權限移除成功\n";
        } else{
            $ReturnMessage = "權限移除失敗\n";
        }
        echo $ReturnMessage;
        //查詢今天值日生
        $time = date('w');  //抓時間
        //新增權限給今日值日生
        $sql = "update sign_table, member set member.duty_level = 1 where sign_table.day_int = ".$time." and sign_table.userid = member.userid";
        if(mysqli_query($db_connection, $sql)){ //更新到資料庫
            $ReturnMessage = "權限更新成功\n";
        }else{
            $ReturnMessage = "權限更新失敗\n";
        }
        echo $ReturnMessage;
        mysqli_close($db_connection);
        break;
    default:
        break;
}