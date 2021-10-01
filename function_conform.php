<?php
require_once('LINEBotXiaoFei.php'); //引入LINEBotXiaoFei.php發送code寫在LINEBotTiny

class Linebot{
    // 處理遛狗查詢 
    function WorkSchedule($time){
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
        $row_userid = mysqli_fetch_assoc(mysqli_query($db_connection, $sql));
        
        if (strtotime($time) - strtotime("2021-10-03 00:00:00")>=0){ //判斷新班表還舊班表
            $userid = $row_userid["new_userid"];
        }else{
            $userid = $row_userid["userid"];
        }
    
        //回傳變數初始化
        $ReturnMessage = "";
        
        if ($userid == NULL) {  //檢查是否是替補日
            $tempor = 6; //初始化 上次替補結尾輪到6號
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
            $sql = "select * from member,duty_turn where id = " . $tempor." and member.userid = duty_turn.userid";
            $row_dutytrun = mysqli_fetch_assoc(mysqli_query($db_connection, $sql));
            $dutytrun = $row_dutytrun["name"];
            $ReturnMessage = "=======================\n     " . $time . "(" . $week . ")" . $day . "(替補)\n=======================\n--->" . $dutytrun; // 回復訊息
        } else {   //不是替補日
            $sql = "select * from member,duty_list where duty_list.day = " . $weekdaytempor . " and duty_list.week = " . $oddandeven." and member.userid = ".$userid;
            $table_member = mysqli_fetch_assoc(mysqli_query($db_connection, $sql));
            $name = $table_member["name"];
            $ReturnMessage = "=======================\n     " . $time . "(" . $week . ")" . $day . "\n=======================\n--->" . $name; // 回復訊息
        }
        //傳輸訊息
        mysqli_close($db_connection); //關閉資料庫連線
        return($ReturnMessage);
    }

    // 處理遛狗查詢 
    function WorkScheduleOnlyUserid($time)
    {
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

    // Notify_push
    function notifypushText($ReturnMessage, $client){
        $client->pushtonotify(
            $message = array(
                'message' => $ReturnMessage
            )
        );
    }

    // 回覆文字訊息
    function ReplyText($ReturnMessage, $event, $client){
        $client->replyMessage(array(
            'replyToken' => $event['replyToken'],
            'messages' => array(
                array(
                    'type' => 'text', // 訊息類型 (文字)
                    'text' => $ReturnMessage // 回復訊息
                )
            )
        ));
    }

    // 回覆圖片訊息
    function ReplyImage($ReturnImageUrl, $event, $client){
        $client->replyMessage(array(
            'replyToken' => $event['replyToken'],
            'messages' => array(
                array(
                    'type' => 'image', // 訊息類型 (圖片)
                    'originalContentUrl' => $ReturnImageUrl, // 回復圖片
                    'previewImageUrl' => $ReturnImageUrl // 回復的預覽圖片
                )
            )
        ));
    }

    // 回覆模板訊息
    function ReplayTemplate($ReturnTitle, $ReturnOptionsLabel1, $ReturnOptionsLabel2, $ReturnOptions1, $ReturnOptions2, $event, $client){
        $client->replyMessage(array(
            'replyToken' => $event['replyToken'],
            'messages' => array(
                array(
                    'type' => 'template', //訊息類型 (模板)
                    'altText' => '工作自我檢核', //替代文字
                    'template' => array(
                        'type' => 'confirm', //類型 (確認)
                        'text' => $ReturnTitle, //文字
                        'actions' => array(
                            array(
                                'type' => 'message', //類型 (訊息)
                                'label' => $ReturnOptionsLabel1, //標籤 1
                                'text' => $ReturnOptions1, //用戶發送文字 1
                            ),
                            array(
                                'type' => 'message', //類型 (訊息)
                                'label' => $ReturnOptionsLabel2, //標籤 2
                                'text' => $ReturnOptions2, //用戶發送文字 2
                            )
                        )
                    )
                )
            )
        ));
    }

    // 查詢是否有值日生權限
    function checkduty($UserId)
    {
        include('./connect.php'); //連結資料庫設定
        $sql = "select * from member where lineuid = '" . $UserId . "'"; 
        $table_member = mysqli_fetch_assoc(mysqli_query($db_connection, $sql));  //查詢結果
        $duty_level = $table_member["duty_level"]; //取出權限等級
        return $duty_level;
    }

    // 查詢是否為管理員
    function checksecurity($UserId)
    {
        include('./connect.php'); //連結資料庫設定
        $sql = "select * from member where lineuid = '" . $UserId . "'"; 
        $table_member = mysqli_fetch_assoc(mysqli_query($db_connection, $sql));  //查詢結果
        $Security = $table_member["security"]; //取出權限等級
        return $Security;
    }
}
