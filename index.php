<?php
//官方文檔：https://developers.line.biz/en/reference/messaging-api/
date_default_timezone_set("Asia/Taipei"); //設定時區為台北時區
require_once('LINEBotXiaoFei.php'); //引入LINEBotXiaoFei.php發送code寫在LINEBotTiny
include("./config.php");
include('./connect.php'); //連結資料庫設定
$message = null;
$event = null; //初始化   $event有資料來源所有資料
$client = new LINEBotXiaoFei($channelAccessToken, $channelSecret); //把Token,Secret丟到LINEBotXiaoFei建立連線


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

// 處理遛狗查詢 
function WorkSchedule($time, $event, $client)
{
    include('./connect.php'); //連結資料庫設定
    $timecount = (strtotime($time) - strtotime("2021-09-19 00:00:00")) / (60 * 60 * 24); //相隔天數
    $weekcount = floor($timecount / 7); //相隔週數

    //查詢日期單周還雙周
    $oddandeven = $weekcount % 2;
    $sql = "select * from week where week_int = " . $oddandeven;
    $row_week = mysqli_fetch_assoc(mysqli_query($db_connection, $sql));
    $week = $row_week["week_ch"];

    //查詢日期星期幾
    $weekdaytempor = date('w', strtotime($time));
    $sql = "select * from day where day_int = " . $weekdaytempor;
    $row_day = mysqli_fetch_assoc(mysqli_query($db_connection, $sql));
    $day = $row_day["day_ch"];

    //查詢值日生
    $sql = "select * from duty_list where day = " . $weekdaytempor . " and week = " . $oddandeven;
    $row_userid = mysqli_fetch_assoc(mysqli_query($db_connection, $sql));
    $userid = $row_userid["userid"];


    //回傳變數初始化
    $ReturnMessage = "";

    if ($userid == NULL) {  //檢查是否是替補日
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
        $sql = "select * from member,duty_turn where id = " . $tempor." and member.userid = duty_turn.userid";
        $row_dutytrun = mysqli_fetch_assoc(mysqli_query($db_connection, $sql));
        $dutytrun = $row_dutytrun["name"];
        $ReturnMessage = "=======================\n     " . $time . "(" . $week . ")" . $day . "(替補)\n=======================\n--->" . $dutytrun; // 回復訊息
    } else {   //不是替補日
        $sql = "select * from member,duty_list where duty_list.day = " . $weekdaytempor . " and duty_list.week = " . $oddandeven." and member.userid = duty_list.userid";
        $table_member = mysqli_fetch_assoc(mysqli_query($db_connection, $sql));
        $name = $table_member["name"];
        $ReturnMessage = "=======================\n     " . $time . "(" . $week . ")" . $day . "\n=======================\n--->" . $name; // 回復訊息
    }
    //傳輸訊息
    ReplyText($ReturnMessage, $event, $client); //回傳訊息
    mysqli_close($db_connection); //關閉資料庫連線
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
//加入的處理
foreach ($client->parseEvents() as $event) {
    switch ($event['type']) {
        case 'message':
            $message = $event['message'];
            break;
        case 'postback': //隱藏訊息
            /*
            $ReturnMessage = $event['postback']['data'];
            ReplyText($ReturnMessage, $event, $client); //回傳訊息*/
            break;
        case 'follow': //加為好友觸發
            $ReturnMessage = "您好，我是小飛的溫泉指揮官";
            ReplyText($ReturnMessage, $event, $client); //回傳訊息
            break;
        case 'join': //加入群組觸發
            $ReturnMessage = "您好，我是小飛的溫泉指揮官";
            ReplyText($ReturnMessage, $event, $client); //回傳訊息
            break;
        default:
            //error_log("Unsupporeted event type: " . $event['type']);
            break;
    }
}

//一般訊息判斷
switch (true) {
    case $message['text'] == "早安": //早安
        $ReturnMessage = "早安!";
        ReplyText($ReturnMessage, $event, $client); //回傳訊息
        break;
    case ($message['text'] == "指令查詢" || $message['text'] == "指令" || $message['text'] == "指令介紹"): //指令介紹
        $ReturnMessage = "指令表\n1.今日遛狗\n2.註冊\n3.工作檢核\n4.值日生權限 @名字(當日值日生有交換需由原本值日生給權限)\n5.更新註冊名字(Line有改名的話)\n6.班表\n7.日期查詢範例：2022-01-01\n8.注意事項\n9.明日遛狗\n10.餵食規則\n11.座位表\n12.地板物品\n13.排班 代碼 @人名\n14.抽";
        ReplyText($ReturnMessage, $event, $client); //回傳訊息
        break;
    case ($message['text'] == "注意" || $message['text'] == "注意事項"): //注意事項
        $ReturnMessage = "小飛之後帶下去上廁所，如果當下小飛沒馬上大號的話，要至少等小飛5~10分鐘再帶上來，小飛通常會下去一陣子後才上大號，其餘規則請至419_3門口查看";
        ReplyText($ReturnMessage, $event, $client); //回傳訊息
        break;
    case ($message['text'] == "餵食規則" || $message['text'] == "餵食"): //餵食
        $ReturnMessage = "一餐:\n1/8罐頭+70克飼料\n1/8罐頭+200公克水";
        ReplyText($ReturnMessage, $event, $client); //回傳訊息
        break;
    case ($message['text'] == "遛狗" || $message['text'] == "今日遛狗" || $message['text'] == "今天遛狗"): //今天遛狗
        $time = date('Y-m-d');  //抓時間
        WorkSchedule($time, $event, $client); //丟去副程式WorkSchedule
        break;
    case ($message['text'] == "明日遛狗" || $message['text'] == "明天遛狗"):  //明天遛狗
        $time = date('Y-m-d', strtotime("+1 day"));  //抓時間
        WorkSchedule($time, $event, $client); //丟去副程式WorkSchedule
        break;
    case ($message['text'] == "後日遛狗" || $message['text'] == "後天遛狗"):  //後天遛狗
        $time = date('Y-m-d', strtotime("+2 day"));  //抓時間
        WorkSchedule($time, $event, $client); //丟去副程式WorkSchedule
        break;
    case ($message['text'] == "班表"): //班表
        $ReturnImageUrl = "https://dogmission.herokuapp.com/images/Class_Schedule_20210915.jpg";
        ReplyImage($ReturnImageUrl, $event, $client); //回傳訊息
        break;
    case ($message['text'] == "地板" || $message['text'] == "地板物品"): //地板物品
        $ReturnImageUrl = "https://dogmission.herokuapp.com/images/floor_20210905.jpg";
        ReplyImage($ReturnImageUrl, $event, $client); //回傳訊息
        break;
    case ($message['text'] == "座位" || $message['text'] == "座位表"): //座位
        $ReturnImageUrl = "https://dogmission.herokuapp.com/images/seat_20210908.jpg";
        ReplyImage($ReturnImageUrl, $event, $client); //回傳訊息
        break;
    case ($message['text'] == "昨天遛狗" || $message['text'] == "前天遛狗" || $message['text'] == "大前天遛狗"): //智障問題
        $ReturnMessage = "不會自己往上看嗎";
        ReplyText($ReturnMessage, $event, $client); //回傳訊息
        break;
    case $message['text'] == "抽":  //抽獎
        require 'vendor/autoload.php';  //引入軟件包PhpSpreadsheet
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx'); //資料庫副檔名
        $reader->setReadDataOnly(TRUE); //讀資料權限
        $spreadsheet = $reader->load('lottery/lottery.xlsx'); //資料位置
        $worksheet = $spreadsheet->getActiveSheet();
        $highestRow = $worksheet->getHighestRow(); // 總行数 
        $rand = rand(1, $highestRow); //產生最大數為資料數量的一個亂數
        $ReturnImageUrl = $worksheet->getCellByColumnAndRow(1, $rand)->getValue(); //取得亂數產生的相對URL
        ReplyImage($ReturnImageUrl, $event, $client); //回傳訊息
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
            WorkSchedule($time, $event, $client); //丟去副程式WorkSchedule
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
        $tabel_member = mysqli_query($db_connection, $sql);  //查詢結果
        $rowtotal = mysqli_num_rows($tabel_member); //總資料比數

        if ($rowtotal < 1) {    //筆數 = 0 代表無資料
            $sql = "insert into member (name, lineuid) value ('".$Name."','".$UserId."');";
            if (mysqli_query($db_connection, $sql)){    //新增到資料庫
                $ReturnMessage = "國家感謝您的貢獻\nName:" . $Name . "\n已新增到資料庫";
            } else{
                $ReturnMessage = "新增失敗，請洽管理員";
            }
        } else {  //無此人名字
            $ReturnMessage = "已經註冊過";
        }
        // 回傳名字到原本發訊息的地方(群組或機器人私訊)
        ReplyText($ReturnMessage, $event, $client); //回傳訊息
        mysqli_close($db_connection);
        break;
    case ($message['text'] == "更新" || $message['text'] == "更新註冊名字"): //更新 line 名稱

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
        $sql = "select * from member where lineuid = '" . $UserId . "'"; //資料庫的name不能重複
        $mysqlreturn = mysqli_query($db_connection, $sql);  //查詢結果
        $rowtotal = mysqli_num_rows($mysqlreturn); //總資料比數

        if ($rowtotal > 0) {    //筆數 = 0 代表無資料
            $sql = "select * from member where name = '" . $Name . "'"; //資料庫的name不能重複
            $mysqlreturn = mysqli_query($db_connection, $sql);  //查詢結果
            $rowtotal = mysqli_num_rows($mysqlreturn); //總資料比數
            if ($rowtotal < 1){
                $sql = "update member set name = '" .$Name. "'where lineuid ='".$UserId ."'";
                if(mysqli_query($db_connection, $sql)){ //更新到資料庫
                    $ReturnMessage = "已更新資料";
                } else{
                    $ReturnMessage = "更新失敗，請洽管理員";
                }
            }else{
                $sql = "select * from member where lineuid = '" . $UserId . "'";
                $table_member = mysqli_fetch_assoc(mysqli_query($db_connection, $sql));
                $name = $table_member["name"];
                if ($name == $Name){
                    $ReturnMessage = "名字無更動";
                }else{
                    $ReturnMessage = "名字重複，請選擇其他名字";
                }
            }
        } else {  //無此人名字
            $ReturnMessage = "請先註冊";
        }
        // 回傳名字到原本發訊息的地方(群組或機器人私訊)
        ReplyText($ReturnMessage, $event, $client); //回傳訊息
        mysqli_close($db_connection);
        break;
    case (mb_substr($message['text'] ,0,2,"UTF-8") == "排班"): //更新 line 名稱， 用於更改值日生
        $UserId = $event['source']['userId']; //抓該訊息的發送者
        //判斷權限
        if (checksecurity($UserId)){ // 查詢是否為管理員
            //查詢資料庫的個人流水號
            $name = mb_substr($message['text'], 7, null, "UTF-8");  // 取輸入的名字
            $sql = "select * from member where name = '" . $name . "'"; 
            $table_member = mysqli_query($db_connection, $sql);  //查詢結果
            $rowtotal = mysqli_num_rows($table_member); //總資料比數
        
            if ($rowtotal > 0){  //如果有這個人
                $table_member_userid =  mysqli_fetch_assoc($table_member)["userid"]; //取出流水號
                $duty_id = mb_substr($message['text'], 3, 2, "UTF-8");  // 取出輸入的工作日編號
                $sql = "update duty_list set userid = '" .$table_member_userid. "' where duty_id ='".$duty_id ."'"; 
                if(mysqli_query($db_connection, $sql)){ //更新到資料庫
                    $ReturnMessage = "已更新到工作日";
                } else{
                    $ReturnMessage = "更新失敗";
                }
            }else{
                $ReturnMessage = "被排班的人員尚未註冊";
            } 
        }else{
            $ReturnMessage = "你不是管理員";
        }
    
        // 回傳名字到原本發訊息的地方(群組或機器人私訊)
        ReplyText($ReturnMessage, $event, $client); //回傳訊息
        mysqli_close($db_connection);
        break;
    case (mb_substr($message['text'] ,0,5,"UTF-8") == "值日生權限" || mb_substr($message['text'] ,0,5,"UTF-8") == "值日生交換" ): //分享當日值日生權限
        $UserId = $event['source']['userId']; //抓該訊息的發送者
        //判斷權限
        if (checkduty($UserId)){ // 查詢是否為值日生
            //查詢資料庫的個人流水號
            $name = mb_substr($message['text'], 7, null, "UTF-8");  // 取輸入的名字
            $sql = "select * from member where name = '" . $name . "'"; 
            $table_member = mysqli_query($db_connection, $sql);  //查詢結果
            $rowtotal = mysqli_num_rows($table_member); //總資料比數
            
            if ($rowtotal > 0){  //如果有這個人
                $table_member_userid =  mysqli_fetch_assoc($table_member)["userid"]; //取出userid流水號
                $sql = "update member set duty_level = 1 where userid = ".$table_member_userid;
                if(mysqli_query($db_connection, $sql)){ //更新到資料庫
                    $ReturnMessage = "已分享權限";
                } else{
                    $ReturnMessage = "更新失敗";
                }
            }else{
                $ReturnMessage = "資料庫查無此人";
            }  
        }else{
            $ReturnMessage = "你不是值日生";
        }
        // 回傳名字到原本發訊息的地方(群組或機器人私訊)
        ReplyText($ReturnMessage, $event, $client); //回傳訊息
        mysqli_close($db_connection);
        break;
    case ($message['text'] == "工作檢核"): //工作檢核功能
        $client->replyMessage(array(
            'replyToken' => $event['replyToken'],
            'messages' => array(
                array(
                    'type' => 'template', //訊息類型 (模板)
                    'altText' => '工作檢核表', //替代文字
                    'template' => array(
                        'type' => 'carousel', //類型 (輪播)
                        'columns' => array(
                            array(
                                'title' => 'E419冰箱(檢查有無發臭過期食物)', //標題 1 <不一定需要>
                                'text' => '請確實執行', //文字 1
                                'actions' => array(
                                    array(
                                        'type' => 'postback', //類型 (回傳)
                                        'label' => '完成', //標籤 1
                                        'data' => '工作檢核 完成 e419_refrigerator' //資料
                                    ),
                                    array(
                                        'type' => 'postback', //類型 (回傳)
                                        'label' => '尚未完成', //標籤 1
                                        'data' => '工作檢核 尚未完成 e419_refrigerator' //資料
                                    ),
                                )
                            ),
                            array(
                                'title' => 'E419倒垃圾', //標題 2 <不一定需要>
                                'text' => '請確實執行', //文字 1
                                'actions' => array(
                                    array(
                                        'type' => 'postback', //類型 (回傳)
                                        'label' => '完成', //標籤 1
                                        'data' => '工作檢核 完成 e419_ashcan' //資料
                                    ),
                                    array(
                                        'type' => 'postback', //類型 (回傳)
                                        'label' => '尚未完成', //標籤 1
                                        'data' => '工作檢核 尚未完成 e419_ashcan' //資料
                                    ),
                                )
                            ),
                            array(
                                'title' => 'E419走廊整潔', //標題 2 <不一定需要>
                                'text' => '請確實執行', //文字 1
                                'actions' => array(
                                    array(
                                        'type' => 'postback', //類型 (回傳)
                                        'label' => '完成', //標籤 1
                                        'data' => '工作檢核 完成 e419_corridor' //資料
                                    ),
                                    array(
                                        'type' => 'postback', //類型 (回傳)
                                        'label' => '尚未完成', //標籤 1
                                        'data' => '工作檢核 尚未完成 e419_corridor' //資料
                                    ),
                                )
                            ),
                            array(
                                'title' => '關E419冷氣、電燈', //標題 2 <不一定需要>
                                'text' => '請確實執行', //文字 1
                                'actions' => array(
                                    array(
                                        'type' => 'postback', //類型 (回傳)
                                        'label' => '完成', //標籤 1
                                        'data' => '工作檢核 完成 e419_conditioner_light' //資料
                                    ),
                                    array(
                                        'type' => 'postback', //類型 (回傳)
                                        'label' => '還有人在使用', //標籤 1
                                        'data' => '工作檢核 還有人在使用 e419_conditioner_light' //資料
                                    ),
                                )
                            ),
                            array(
                                'title' => 'E420走廊整潔', //標題 2 <不一定需要>
                                'text' => '請確實執行', //文字 1
                                'actions' => array(
                                    array(
                                        'type' => 'postback', //類型 (回傳)
                                        'label' => '完成', //標籤 1
                                        'data' => '工作檢核 完成 e420_corridor' //資料
                                    ),
                                    array(
                                        'type' => 'postback', //類型 (回傳)
                                        'label' => '尚未完成', //標籤 1
                                        'data' => '工作檢核 尚未完成 e420_corridor' //資料
                                    ),
                                )
                            ),
                            array(
                                'title' => 'E420檢查設備', //標題 2 <不一定需要>
                                'text' => '請確實執行', //文字 1
                                'actions' => array(
                                    array(
                                        'type' => 'postback', //類型 (回傳)
                                        'label' => '完成', //標籤 1
                                        'data' => '工作檢核 完成 e420_equipment' //資料
                                    ),
                                    array(
                                        'type' => 'postback', //類型 (回傳)
                                        'label' => '尚未完成', //標籤 1
                                        'data' => '工作檢核 尚未完成 e420_equipment' //資料
                                    ),
                                )
                            ),
                            array(
                                'title' => 'E420整理桌椅', //標題 2 <不一定需要>
                                'text' => '請確實執行', //文字 1
                                'actions' => array(
                                    array(
                                        'type' => 'postback', //類型 (回傳)
                                        'label' => '完成', //標籤 1
                                        'data' => '工作檢核 完成 e420_chair' //資料
                                    ),
                                    array(
                                        'type' => 'postback', //類型 (回傳)
                                        'label' => '尚未完成', //標籤 1
                                        'data' => '工作檢核 尚未完成 e420_chair' //資料
                                    ),
                                )
                            ),
                            array(
                                'title' => '關E420電燈和冷氣', //標題 2 <不一定需要>
                                'text' => '請確實執行', //文字 1
                                'actions' => array(
                                    array(
                                        'type' => 'postback', //類型 (回傳)
                                        'label' => '完成', //標籤 1
                                        'data' => '工作檢核 完成 e420_conditioner_light' //資料
                                    ),
                                    array(
                                        'type' => 'postback', //類型 (回傳)
                                        'label' => '還有人在使用', //標籤 1
                                        'data' => '工作檢核 還有人在使用 e420_conditioner_light' //資料
                                    ),
                                )
                            ),
                            array(
                                'title' => 'E420整理桌椅', //標題 2 <不一定需要>
                                'text' => '請確實執行', //文字 1
                                'actions' => array(
                                    array(
                                        'type' => 'postback', //類型 (回傳)
                                        'label' => '完成', //標籤 1
                                        'data' => '工作檢核 完成 e420_Shoebox' //資料
                                    ),
                                    array(
                                        'type' => 'postback', //類型 (回傳)
                                        'label' => '尚未完成', //標籤 1
                                        'data' => '工作檢核 尚未完成 e420_Shoebox' //資料
                                    ),
                                )
                            ),
                            array(
                                'title' => '關小房間冷氣和電燈', //標題 2 <不一定需要>
                                'text' => '請確實執行', //文字 1
                                'actions' => array(
                                    array(
                                        'type' => 'postback', //類型 (回傳)
                                        'label' => '完成', //標籤 1
                                        'data' => '工作檢核 完成 room_conditioner_light' //資料
                                    ),
                                    array(
                                        'type' => 'postback', //類型 (回傳)
                                        'label' => '還有人在使用', //標籤 1
                                        'data' => '工作檢核 還有人在使用 room_conditioner_light' //資料
                                    ),
                                )
                            ),
                        )
                    )
                )
            )
        ));
    default:
        break;
}

//postback 訊息判斷
switch (true) {
    case (mb_substr($event['postback']['data'], 0, 4, "UTF-8") == "工作檢核"): //工作檢核結果判斷
        switch (true){
            case (mb_substr($event['postback']['data'], 5, 2, "UTF-8") == "完成"):
                $UserId = $event['source']['userId']; //抓該訊息的發送者
                if(checkduty($UserId)){
                    $item = mb_substr($event['postback']['data'], 8, null, "UTF-8"); // 取出打卡的工作項目
                    $weekdaytempor = date('w'); // 取出今天星期幾
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
                    include('./connect.php'); //連結資料庫設定
                    $sql = "update sign_table set ".$item." = '完成：".$Name."' where day_int = ".$weekdaytempor; 
                    if(mysqli_query($db_connection, $sql)){ //更新到資料庫
                        $ReturnMessage = "打卡成功：".$item;
                    } else{
                        $ReturnMessage = "該項目不存在";
                    }
                    ReplyText($ReturnMessage, $event, $client); //回傳訊息
                    mysqli_close($db_connection);  //關閉資料庫連線
                }else{
                    $ReturnMessage = "你不是今天值日生";
                    ReplyText($ReturnMessage, $event, $client); //回傳訊息
                }
                break; 
            case (mb_substr($event['postback']['data'], 5, 4, "UTF-8") == "尚未完成"):
                $ReturnMessage = "請完成後再重新選擇";
                ReplyText($ReturnMessage, $event, $client); //回傳訊息
                break;   
            case (mb_substr($event['postback']['data'], 5, 6, "UTF-8") == "還有人在使用"):
                $UserId = $event['source']['userId']; //抓該訊息的發送者
                if(checkduty($UserId)){
                    $item = mb_substr($event['postback']['data'], 12, null, "UTF-8"); // 取出打卡的工作項目
                    $weekdaytempor = date('w'); // 取出今天星期幾

                    // 查詢名字
                    $Name = ""; //初始化
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

                    include('./connect.php'); //連結資料庫設定
                    $sql = "update sign_table set ".$item." = '還有人在使用：".$Name."' where day_int = ".$weekdaytempor; 
                    if(mysqli_query($db_connection, $sql)){ //更新到資料庫
                        $ReturnMessage = "打卡成功：.$item";
                    } else{
                        $ReturnMessage = "該項目不存在";
                    }
                    ReplyText($ReturnMessage, $event, $client); //回傳訊息
                    mysqli_close($db_connection);  //關閉資料庫連線
                }else{
                    $ReturnMessage = "你不是今天值日生";
                    ReplyText($ReturnMessage, $event, $client); //回傳訊息
                }
                break; 
            default:
                break;
        }
        break;
    default:
        break;
}

/* //測試用
if ($message['text'] == "測試" || $message['text'] == "測試") {
}*/