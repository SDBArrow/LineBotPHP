<?php
include('./connect.php'); //連結資料庫設定
date_default_timezone_set("Asia/Taipei"); //設定時區為台北時區
require('./function_conform.php'); //引入LINEBotXiaoFei.php發送code寫在LINEBotTiny
$work = new Linebot();

switch(true){
    //一星期執行一次、每個星期天執行簽到表清除及匯入
    case (date('w') == 1 && date('H:i') <= "23:05"):
       
        //查詢下星期每天的值日生userid
        for ($var = -1; $var < 6; $var++){
            $time = date('Y-m-d', strtotime($var." day"));  //抓時間
            $duty[$var] = $work ->WorkScheduleOnlyUserid($time);
        }
        
        //清空資料表
        $SQL[0] = "update sign_table set userid = null, e419_refrigerator = '', e419_refrigerator = '', e419_ashcan = '', e419_corridor = '', e419_conditioner_light = '', e420_corridor = '', e420_equipment = '', e420_chair = '', e420_conditioner_light = '', e420_Shoebox = '', room_conditioner_light = ''";
        //匯入值日生
        for ($var = 1; $var <= count($duty); $var++){
            $SQL[$var] = "update sign_table set userid = ".$duty[$var-1]." where day_int = ".$var-1;
        }

        //資料庫更新
        for($var = 0; $var < count($SQL); $var++){
            $db_connection->query($SQL[$var]); //更新到資料庫
            if($db_connection->affected_rows > 0){ //檢查是否更新成功
                $ReturnSQLMessage = "檢核表更新成功\n";
            } else{
                $ReturnSQLMessage = "檢核表更新失敗\n";
            }
            echo $ReturnSQLMessage;
        }
    //每天00:00要執行的
    case (date('H:i') <= "23:05"):
        //清除昨天的值日生權限
        $sql = "update member set duty_level = 0";
        $db_connection->query($sql); //更新到資料庫
        if($db_connection->affected_rows > 0){ //檢查是否更新成功
            $ReturnSQLMessage = "權限移除成功\n";
        } else{
            $ReturnSQLMessage = "權限移除失敗\n";
        }
        echo $ReturnSQLMessage;
        //查詢今天值日生
        $time = date('w');  //抓時間
        //新增權限給今日值日生
        $sql = "update sign_table, member set member.duty_level = 1 where sign_table.day_int = ".$time." and sign_table.userid = member.userid";
        $db_connection->query($sql); //更新到資料庫
        if($db_connection->affected_rows > 0){ //檢查是否更新成功
            $ReturnSQLMessage = "權限更新成功\n";
        }else{
            $ReturnSQLMessage = "權限更新失敗\n";
        }
        echo $ReturnSQLMessage;
    default:
        mysqli_close($db_connection);
        break;
}