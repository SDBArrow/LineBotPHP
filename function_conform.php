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
        $row_week = $db_connection->query($sql)->fetch_assoc();
        $week = $row_week["week_ch"];
    
        //今天星期幾
        $weekdaytempor = date('w', strtotime($time));
        $sql = "select * from day where day_int = " . $weekdaytempor;
        $row_day = $db_connection->query($sql)->fetch_assoc();
        $day = $row_day["day_ch"];
    
        //查詢值日生
        $sql = "select * from duty_list where day = " . $weekdaytempor . " and week = " . $oddandeven;
        $row_userid = $db_connection->query($sql)->fetch_assoc();
        
        if (strtotime($time) - strtotime("2021-10-03 00:00:00")>=0){ //判斷新班表還舊班表
            $userid = $row_userid["new_userid"];
        }else{
            $userid = $row_userid["userid"];
        }
    
        //回傳變數初始化
        $ReturnMessage = "";
        
        if ($userid == NULL) {  //檢查是否是替補日

            //------此段代碼開始，可能因為替補數量不一樣而需要改公式
            $tempor = 6; //初始化 上次替補結尾輪到6號
            $tempor = $tempor + floor($weekcount/2)*3;  //替補計算  兩個星期會有三次替補
            if (strtotime($time) - strtotime("2021-10-03 00:00:00")>=0){ //判斷新班表還舊班表
                if ( $oddandeven == 0 ){
                    $tempor = $tempor % 11;                 //兩個星期的第一次
                }elseif($oddandeven == 1 && $weekdaytempor == 0){
                    $tempor = ($tempor % 11 + 1) % 11;             //兩個星期的第二次
                }elseif($oddandeven == 1 && $weekdaytempor == 2){      
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
            //------會隨替補數量更改公式的代碼到此為止

            //查詢替補
            $sql = "select * from member,duty_turn where id = " . $tempor." and member.userid = duty_turn.userid";
            $row_dutytrun = $db_connection->query($sql)->fetch_assoc();
            $dutytrun = $row_dutytrun["name"];
            $ReturnMessage = "=======================\n     " . $time . "(" . $week . ")" . $day . "(替補)\n=======================\n--->" . $dutytrun; // 回復訊息
        } else {   //不是替補日
            $sql = "select * from member,duty_list where duty_list.day = " . $weekdaytempor . " and duty_list.week = " . $oddandeven." and member.userid = ".$userid;
            $table_member = $db_connection->query($sql)->fetch_assoc();
            $name = $table_member["name"];
            $ReturnMessage = "=======================\n     " . $time . "(" . $week . ")" . $day . "\n=======================\n--->" . $name; // 回復訊息
        }
        //傳輸訊息
        $db_connection -> close(); //關閉資料庫連線
        return($ReturnMessage);
    }

    // 處理遛狗查詢只抓userid 
    function WorkScheduleOnlyUserid($time)
    {
        include('./connect.php'); //連結資料庫設定
        $timecount = (strtotime($time) - strtotime("2021-09-19 00:00:00")) / (60 * 60 * 24); //相隔天數
        $weekcount = floor($timecount / 7); //相隔週數
        $oddandeven = $weekcount % 2;   //今天單周還雙周
        $weekdaytempor = date('w', strtotime($time));  //今天星期幾

        //查詢值日生
        $sql = "select * from duty_list where day = " . $weekdaytempor . " and week = " . $oddandeven;
        $row_userid = $db_connection->query($sql)->fetch_assoc(); 
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
                }elseif($oddandeven == 1 && $weekdaytempor == 2){      
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
            $table_duty_trun = $db_connection->query($sql)->fetch_assoc(); 
            $userid = $table_duty_trun["userid"];
            $ReturnMessage = $userid; // 回復訊息
        } else {   //不是替補日
            $ReturnMessage = $userid; // 回復訊息
        }
        //傳輸訊息
        $db_connection -> close(); //關閉資料庫連線
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

    // LineBot_push 一個月只有五百則訊息扣搭
    function PushText($ReturnMessage, $id, $client){
        $client->replyMessage(array(
            'to' => $id,
            'messages' => array(
                array(
                    'type' => 'text', // 訊息類型 (文字)
                    'text' => $ReturnMessage // 回復訊息
                )
            )
        ));
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
    function ReplayTemplate($ReturnTitle, $ReturnExplain, $ReturnOptionsLabel1, $ReturnOptionsLabel2, $ReturnOptions1, $ReturnOptions2, $event, $client){
        $client->replyMessage(array(
            'replyToken' => $event['replyToken'],
            'messages' => array(
                array(
                    'type' => 'template', //訊息類型 (模板)
                    'altText' => $ReturnTitle, //替代文字
                    'template' => array(
                        'type' => 'confirm', //類型 (確認)
                        'text' => $ReturnExplain, //文字
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
        $table_member = $db_connection->query($sql)->fetch_assoc();  //查詢結果
        $duty_level = $table_member["duty_level"]; //取出權限等級
        $db_connection -> close();
        return $duty_level;
    }

    // 查詢是否為管理員
    function checksecurity($UserId)
    {
        include('./connect.php'); //連結資料庫設定
        $sql = "select * from member where lineuid = '" . $UserId . "'"; 
        $table_member = $db_connection->query($sql)->fetch_assoc();  //查詢結果
        $Security = $table_member["security"]; //取出權限等級
        $db_connection -> close();
        return $Security;
    }

    // 查詢名字
    function linename($UserId, $GroupId, $client){
        // 查詢名字
        if ($GroupId == "") {   //判斷是否有群組，必須使用不同的API
            $response = $client->getUserProfile(array(
                'UserId' => $UserId,
            ));
            $Name = $response->displayName;  //取名字欄位
        } else {
            $response = $client->getGroupProfile(array(
                'UserId' => $UserId,
                'GroupId' => $GroupId,
            ));
            $Name = $response->displayName;
        }
        return $Name;
    }
}