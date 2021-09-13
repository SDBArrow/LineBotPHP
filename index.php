<?php
//官方文檔：https://developers.line.biz/en/reference/messaging-api/
date_default_timezone_set("Asia/Taipei"); //設定時區為台北時區
require_once('LINEBotXiaoFei.php'); //引入LINEBotXiaoFei.php發送code寫在LINEBotTiny
include("./config.php");
$message = null;
$event = null; //初始化   $event有資料來源所有資料
$client = new LINEBotXiaoFei($channelAccessToken, $channelSecret); //把Token,Secret丟到LINEBotXiaoFei建立連線
//加入的處理
foreach ($client->parseEvents() as $event) {
    switch ($event['type']) {
        case 'message':
            $message = $event['message'];
            break;
        case 'postback':
            //require_once('postback.php'); //postback
            break;
        case 'follow': //加為好友觸發
            $client->replyMessage(array(
                'replyToken' => $event['replyToken'],
                'messages' => array(
                    array(
                        'type' => 'text',
                        'text' => '您好，我是小飛的溫泉指揮官'
                    )
                )
            ));
            break;
        case 'join': //加入群組觸發
            $client->replyMessage(array(
                'replyToken' => $event['replyToken'],
                'messages' => array(
                    array(
                        'type' => 'text',
                        'text' => '您好，我是小飛的溫泉指揮官'
                    )
                )
            ));
            break;
        default:
            //error_log("Unsupporeted event type: " . $event['type']);
            break;
    }
}
//處理遛狗查詢 
function WorkSchedule($time, $event, $client)
{
    include('./connect.php'); //連結資料庫設定
    $timecount = (strtotime($time) - strtotime("2021-02-21 00:00:00")) / (60 * 60 * 24); //相隔天數
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

    if ($name == "") {  //檢查是否是替補日
        $tempor = 0; //初始化
        $tempor = $tempor + $weekcount;  //替補計算
        $tempor = $tempor % 12; //因為有12個人，所以每12次重新一次
        //查詢替補
        $sql = "select * from duty_turn where id = " . $tempor;
        $row_trun = mysqli_fetch_assoc(mysqli_query($db_connection, $sql));
        $turn = $row_trun["name"];
        //傳輸訊息
        $client->replyMessage(array(
            'replyToken' => $event['replyToken'],
            'messages' => array(
                array(
                    'type' => 'text', // 訊息類型 (文字)
                    'text' => "=======================\n     " . $time . "(" . $week . ")" . $day . "(替補)\n=======================\n--->" . $turn // 回復訊息
                )
            )
        ));
    } else {   //不是替補日
        //傳輸訊息
        $client->replyMessage(array(
            'replyToken' => $event['replyToken'],
            'messages' => array(
                array(
                    'type' => 'text', // 訊息類型 (文字)
                    'text' => "=======================\n     " . $time . "(" . $week . ")" . $day . "\n=======================\n--->" . $name // 回復訊息
                )
            )
        ));
    }
    mysqli_close($db_connection);
}
//訊息判斷
switch (true) {
    case $message['text'] == "早安": //早安
        $client->replyMessage(array(
            'replyToken' => $event['replyToken'],
            'messages' => array(
                array(
                    'type' => 'text', // 訊息類型 (文字)
                    'text' => "早安!" // 回復訊息
                )
            )
        ));
        break;
    case ($message['text'] == "指令查詢" || $message['text'] == "指令" || $message['text'] == "指令介紹"): //指令介紹
        $client->replyMessage(array(
            'replyToken' => $event['replyToken'],
            'messages' => array(
                array(
                    'type' => 'text', // 訊息類型 (文字)
                    'text' => "指令表\n1.今日遛狗\n2.註冊\n3.更新名字\n4.班表\n5.日期查詢範例：2022-01-01\n6.注意事項\n7.明日遛狗\n8.餵食規則\n9.座位表\n10.地板物品\n11.抽" // 回復訊息
                )
            )
        ));
        break;
    case ($message['text'] == "注意" || $message['text'] == "注意事項"): //注意事項
        $client->replyMessage(array(
            'replyToken' => $event['replyToken'],
            'messages' => array(
                array(
                    'type' => 'text', // 訊息類型 (文字)
                    'text' => '小飛之後帶下去上廁所，如果當下小飛沒馬上大號的話，要至少等小飛5~10分鐘再帶上來，小飛通常會下去一陣子後才上大號，其餘規則請至419_3門口查看' // 回復訊息
                )
            )
        ));
        break;
    case ($message['text'] == "餵食規則" || $message['text'] == "餵食"): //餵食
        $client->replyMessage(array(
            'replyToken' => $event['replyToken'],
            'messages' => array(
                array(
                    'type' => 'text', // 訊息類型 (文字)
                    'text' => "一餐:\n1/8罐頭+70克飼料\n1/8罐頭+200公克水" // 回復訊息
                )
            )
        ));
        break;
    case ($message['text'] == "遛狗" || $message['text'] == "今日遛狗" || $message['text'] == "今天遛狗"): //今天遛狗
        $time = date('Y-m-d');  //抓時間
        $result = WorkSchedule($time, $event, $client); //丟去副程式WorkSchedule
        break;
    case ($message['text'] == "明日遛狗" || $message['text'] == "明天遛狗"):  //明天遛狗
        $time = date('Y-m-d', strtotime("+1 day"));  //抓時間
        $result = WorkSchedule($time, $event, $client); //丟去副程式WorkSchedule
        break;
    case ($message['text'] == "後日遛狗" || $message['text'] == "後天遛狗"):  //後天遛狗
        $time = date('Y-m-d', strtotime("+2 day"));  //抓時間
        $result = WorkSchedule($time, $event, $client); //丟去副程式WorkSchedule
        break;
    case ($message['text'] == "班表"): //班表
        $client->replyMessage(array(
            'replyToken' => $event['replyToken'],
            'messages' => array(
                array(
                    'type' => 'image', // 訊息類型 (圖片)
                    'originalContentUrl' => 'https://dogmission.herokuapp.com/images/Class_Schedule_20210905.jpg', // 回復圖片
                    'previewImageUrl' => 'https://dogmission.herokuapp.com/images/Class_Schedule_20210905.jpg' // 回復的預覽圖片
                )
            )
        ));
        break;
    case ($message['text'] == "地板" || $message['text'] == "地板物品"): //地板物品
        $client->replyMessage(array(
            'replyToken' => $event['replyToken'],
            'messages' => array(
                array(
                    'type' => 'image', // 訊息類型 (圖片)
                    'originalContentUrl' => 'https://dogmission.herokuapp.com/images/floor_20210905.jpg', // 回復圖片
                    'previewImageUrl' => 'https://dogmission.herokuapp.com/images/floor_20210905.jpg' // 回復的預覽圖片
                )
            )
        ));
        break;
    case ($message['text'] == "座位" || $message['text'] == "座位表"): //座位
        $client->replyMessage(array(
            'replyToken' => $event['replyToken'],
            'messages' => array(
                array(
                    'type' => 'image', // 訊息類型 (圖片)
                    'originalContentUrl' => 'https://dogmission.herokuapp.com/images/seat_20210908.jpg', // 回復圖片
                    'previewImageUrl' => 'https://dogmission.herokuapp.com/images/seat_20210908.jpg' // 回復的預覽圖片
                )
            )
        ));
        break;

    case ($message['text'] == "昨天遛狗" || $message['text'] == "前天遛狗" || $message['text'] == "大前天遛狗"): //智障問題
        $client->replyMessage(array(
            'replyToken' => $event['replyToken'],
            'messages' => array(
                array(
                    'type' => 'text', // 訊息類型 (文字)
                    'text' => '不會自己往上看嗎' // 回復訊息
                )
            )
        ));
        break;
    case $message['text'] == "抽":  //抽獎
        require 'vendor/autoload.php';  //引入軟件包PhpSpreadsheet
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx'); //資料庫副檔名
        $reader->setReadDataOnly(TRUE); //讀資料權限
        $spreadsheet = $reader->load('lottery/lottery.xlsx'); //資料位置
        $worksheet = $spreadsheet->getActiveSheet();
        $highestRow = $worksheet->getHighestRow(); // 總行数 
        $rand = rand(1, $highestRow); //產生最大數為資料數量的一個亂數
        $name = $worksheet->getCellByColumnAndRow(1, $rand)->getValue(); //取得亂數產生的相對URL
        $client->replyMessage(array(
            'replyToken' => $event['replyToken'],
            'messages' => array(
                array(
                    'type' => 'image', // 訊息類型 (圖片)
                    'originalContentUrl' => $name, // 回復圖片
                    'previewImageUrl' => $name // 回復的預覽圖片
                )
            )
        ));
        break;
    case (preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $message['text']) || preg_match("/^[0-9]{2}-[0-9]{2}$/", $message['text']))://日期查遛狗名單，判斷inputdata是否為日期
        $time = $message['text'];  //抓時間
        $__y = "";
        $__m = "";
        $__d = ""; //初始化
        if (preg_match("/^[0-9]{2}-[0-9]{2}$/", $time)) { //如果沒有提供年份，自動以今年為主
            $__y = date('Y');
            $__m = substr($time, 0, 2);
            $__d = substr($time, 3, 2);
            $time = $__y . "-" . $time;  //把年份補上
        } else {
            $__y = substr($time, 0, 4);
            $__m = substr($time, 5, 2);
            $__d = substr($time, 8, 2);
        }
        if (checkdate($__m, $__d, $__y)) { //確認時間是否有效
            $result = WorkSchedule($time, $event, $client); //丟去副程式WorkSchedule
        }
        break;
    case ($message['text'] == "註冊資料" || $message['text'] == "註冊"): //添加用戶 UID、name 到資料庫

        $UserId = $event['source']['userId']; //抓該訊息的發送者
        $GroupId = $event['source']['groupId']; //抓該訊息的群組
        $Name = ""; //初始化

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

        //連線到資料庫取資料
        include('./connect.php'); //連結資料庫設定
        $sql = "select * from member where lineuid = '" . $UserId . "'"; //資料庫的name不能重複
        $mysqlreturn = mysqli_query($db_connection, $sql);  //查詢結果
        $rowtotal = mysqli_num_rows($mysqlreturn); //總資料比數

        if ($rowtotal < 1) {    //筆數 = 0 代表無資料
            $sql = "insert into member (name, lineuid) value ('".$Name."','".$UserId."');";
            if (mysqli_query($db_connection, $sql)){    //新增到資料庫
                $returnmessage = "國家感謝您的貢獻\nName:" . $Name . "\n已新增到資料庫";
            } else{
                $returnmessage = "新增失敗，請洽管理員";
            }
        } else {  //無此人名字
            $returnmessage = "已經註冊過";
        }
        // 回傳名字到原本發訊息的地方(群組或機器人私訊)
        $client->replyMessage(array(
            'replyToken' => $event['replyToken'],
            'messages' => array(
                array(
                    'type' => 'text', // 訊息類型 (文字)
                    'text' => $returnmessage, // 回復訊息
                )
            )
        ));
        mysqli_close($db_connection);
        break;
    case ($message['text'] == "更新" || $message['text'] == "更新名字"): //更新 line 名稱

        $UserId = $event['source']['userId']; //抓該訊息的發送者
        $GroupId = $event['source']['groupId']; //抓該訊息的群組
        $Name = ""; //初始化

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

        //連線到資料庫取資料
        include('./connect.php'); //連結資料庫設定
        $sql = "select * from member where lineuid = '" . $UserId . "'"; //資料庫的name不能重複
        $mysqlreturn = mysqli_query($db_connection, $sql);  //查詢結果
        $rowtotal = mysqli_num_rows($mysqlreturn); //總資料比數

        if ($rowtotal > 0) {    //筆數 = 0 代表無資料
            $sql = "update member set name = '" .$Name. "'where lineuid ='".$UserId ."'";
            if(mysqli_query($db_connection, $sql)){ //更新到資料庫
                $returnmessage = "已更新資料";
            } else{
                $returnmessage = "更新失敗，請洽管理員";
            }
        } else {  //無此人名字
            $returnmessage = "請先註冊";
        }
        // 回傳名字到原本發訊息的地方(群組或機器人私訊)
        $client->replyMessage(array(
            'replyToken' => $event['replyToken'],
            'messages' => array(
                array(
                    'type' => 'text', // 訊息類型 (文字)
                    'text' => $returnmessage, // 回復訊息
                )
            )
        ));
        mysqli_close($db_connection);
        break;/*
    case (substr($message['text'],0,2) == "排班"): //更新 line 名稱， 用於更改值日生
        // 回傳名字到原本發訊息的地方(群組或機器人私訊)
        $client->replyMessage(array(
            'replyToken' => $event['replyToken'],
            'messages' => array(
                array(
                    'type' => 'text', // 訊息類型 (文字)
                    'text' => "成功", // 回復訊息
                )
            )
        ));
        break;*/
    default:
        break;
}

if (mb_substr($message['text'] ,0,2,"UTF-8") == "排班") { //substr會出現亂碼需用mb_substr設定編碼
    $UserId = $event['source']['userId']; //抓該訊息的發送者
    
    // 查詢是否為管理員
    include('./connect.php'); //連結資料庫設定
    $sql = "select * from member where lineuid = '" . $UserId . "'"; 
    $row = mysqli_fetch_assoc(mysqli_query($db_connection, $sql));  //查詢結果
    $Security = $row["security"]; //取出權限等級

    //判斷權限
    if ($Security == 1){ 
        //查詢資料庫的個人流水號
        $name = mb_substr($message['text'] ,6,"UTF-8");
        $sql = "select * from member where name = '" . $name . "'"; 
        if ($row = mysqli_fetch_assoc(mysqli_query($db_connection, $sql))){
            $returnmessage = "名字存在";
        }else{
            $returnmessage = "名字不存在";
        }  //查詢結果
        /*
        $returnuserid = $row["userid"]; //取出資料庫的流水號
        $duty_id = mb_substr($message['text'] ,3,2,"UTF-8");  
        $returnmessage = "時段：".$duty_id."\n userid = ".$returnuserid; */
    }else{
        $returnmessage = "你不是管理員";
    }

    // 回傳名字到原本發訊息的地方(群組或機器人私訊)
    $client->replyMessage(array(
        'replyToken' => $event['replyToken'],
        'messages' => array(
            array(
                'type' => 'text', // 訊息類型 (文字)
                'text' => $returnmessage, // 回復訊息
            )
        )
    ));  
    mysqli_close($db_connection);
}

if ($message['text'] == "管理員" || $message['text'] == "管理員檢測") {
    $UserId = $event['source']['userId']; //抓該訊息的發送者
    
    // 查詢是否為管理員
    include('./connect.php'); //連結資料庫設定
    $sql = "select * from member where lineuid = '" . $UserId . "'"; 
    $row = mysqli_fetch_assoc(mysqli_query($db_connection, $sql));  //查詢結果
    $Security = $row["security"]; //取出權限等級

    //判斷權限
    if ($Security == 1){
        $returnmessage = "你是管理員"; 
    }else{
        $returnmessage = "你不是管理員";
    }

    // 回傳名字到原本發訊息的地方(群組或機器人私訊)
    $client->replyMessage(array(
        'replyToken' => $event['replyToken'],
        'messages' => array(
            array(
                'type' => 'text', // 訊息類型 (文字)
                'text' => $returnmessage, // 回復訊息
            )
        )
    ));  
    mysqli_close($db_connection);
}
