<?php
//官方文檔：https://developers.line.biz/en/reference/messaging-api/
date_default_timezone_set("Asia/Taipei"); //設定時區為台北時區
require("./config.php");
require_once('LINEBotXiaoFei.php'); //引入LINEBotXiaoFei.php發送code寫在LINEBotTiny
$client = new LINEBotXiaoFei($channelAccessToken, $channelSecret); //把Token,Secret丟到LINEBotXiaoFei建立連線
$message = null;
$event = null; //初始化   $event有資料來源所有資料
require('./function_conform.php'); //引入LINEBotXiaoFei.php發送code寫在LINEBotTiny
$work = new Linebot();
require('./connect.php'); //連結資料庫設定

//加入的處理
foreach ($client->parseEvents() as $event) {
    switch ($event['type']) {
        case 'message':
            $message = $event['message'];
            break;
        case 'postback': //隱藏訊息
            /*
            $ReturnMessage = $event['postback']['data'];
            $work -> ReplyText($ReturnMessage, $event, $client); //回傳訊息*/
            break;
        case 'follow': //加為好友觸發
            $ReturnMessage = "您好，我是小飛的溫泉指揮官";
            $work -> ReplyText($ReturnMessage, $event, $client); //回傳訊息
            break;
        case 'join': //加入群組觸發
            $ReturnMessage = "您好，我是小飛的溫泉指揮官";
            $work -> ReplyText($ReturnMessage, $event, $client); //回傳訊息
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
        $work -> ReplyText($ReturnMessage, $event, $client); //回傳訊息
        break;
    case $message['text'] == "本周紀錄": //早安
        $ReturnMessage = "=======================\n          本周工作檢核紀錄\n=======================\n--->https://reurl.cc/WXqqYk";
        $work -> ReplyText($ReturnMessage, $event, $client); //回傳訊息
        break;
    case ($message['text'] == "指令查詢" || $message['text'] == "指令" || $message['text'] == "指令介紹"): //指令介紹
        $ReturnMessage = "指令表\n1.今日遛狗\n2.註冊\n3.工作檢核\n4.本周紀錄\n5.值日生權限 @名字(當日值日生有交換需由原本值日生給權限)\n6.更新註冊名字(Line有改名的話)\n7.班表\n8.日期查詢範例：2022-01-01\n9.注意事項\n10.明日遛狗\n11.後天遛狗\n12.換班規則\n13.餵食規則\n14.座位表\n15.地板物品\n16.排班 代碼 @人名\n17.抽";
        $work -> ReplyText($ReturnMessage, $event, $client); //回傳訊息
        break;
    case ($message['text'] == "注意" || $message['text'] == "注意事項"): //注意事項
        $ReturnMessage = "小飛之後帶下去上廁所，如果當下小飛沒馬上大號的話，要至少等小飛5~10分鐘再帶上來，小飛通常會下去一陣子後才上大號，其餘規則請至419_3門口查看";
        $work -> ReplyText($ReturnMessage, $event, $client); //回傳訊息
        break;
    case ($message['text'] == "餵食規則" || $message['text'] == "餵食"): //餵食
        $ReturnMessage = "一餐:\n1/8罐頭+70克飼料\n1/8罐頭+200公克水";
        $work -> ReplyText($ReturnMessage, $event, $client); //回傳訊息
        break;
    case $message['text'] == "換班規則": //換班規則
        $ReturnMessage = "換班規則：雙方同意即可申請換班，若更換至替補。將會於申請後完成最後一次原班表值班再做更換。";
        $work -> ReplyText($ReturnMessage, $event, $client); //回傳訊息
        break;
    case ($message['text'] == "遛狗" || $message['text'] == "今日遛狗" || $message['text'] == "今天遛狗"): //今天遛狗
        $time = date('Y-m-d');  //抓時間
        $ReturnMessage = $work -> WorkSchedule($time); //丟去副程式WorkSchedule
        $work -> ReplyText($ReturnMessage, $event, $client); //回傳訊息
        break;
    case ($message['text'] == "明日遛狗" || $message['text'] == "明天遛狗"):  //明天遛狗
        $time = date('Y-m-d', strtotime("+1 day"));  //抓時間
        $ReturnMessage = $work -> WorkSchedule($time); //丟去副程式WorkSchedule
        $work -> ReplyText($ReturnMessage, $event, $client); //回傳訊息
        break;
    case ($message['text'] == "後日遛狗" || $message['text'] == "後天遛狗"):  //後天遛狗
        $time = date('Y-m-d', strtotime("+2 day"));  //抓時間
        $ReturnMessage = $work -> WorkSchedule($time); //丟去副程式WorkSchedule
        $work -> ReplyText($ReturnMessage, $event, $client); //回傳訊息
        break;
    case ($message['text'] == "班表"): //班表
        $ReturnImageUrl = "https://dogmission.herokuapp.com/images/Class_Schedule_20211001.jpg";
        $work -> ReplyImage($ReturnImageUrl, $event, $client); //回傳訊息
        break;
    case ($message['text'] == "地板" || $message['text'] == "地板物品"): //地板物品
        $ReturnImageUrl = "https://dogmission.herokuapp.com/images/floor_20210905.jpg";
        $work -> ReplyImage($ReturnImageUrl, $event, $client); //回傳訊息
        break;
    case ($message['text'] == "座位" || $message['text'] == "座位表"): //座位
        $ReturnImageUrl = "https://dogmission.herokuapp.com/images/seat_20210908.jpg";
        $work -> ReplyImage($ReturnImageUrl, $event, $client); //回傳訊息
        break;
    case ($message['text'] == "昨天遛狗" || $message['text'] == "前天遛狗" || $message['text'] == "大前天遛狗"): //智障問題
        $ReturnMessage = "不會自己往上看嗎";
        $work -> ReplyText($ReturnMessage, $event, $client); //回傳訊息
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
        $work -> ReplyImage($ReturnImageUrl, $event, $client); //回傳訊息
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
            $ReturnMessage = $work -> WorkSchedule($time); //丟去副程式WorkSchedule
            $work -> ReplyText($ReturnMessage, $event, $client); //回傳訊息
        }
        break;
    case ($message['text'] == "註冊資料" || $message['text'] == "註冊"): //添加用戶 UID、name 到資料庫

        $UserId = $event['source']['userId']; //抓該訊息的發送者
        $GroupId = $event['source']['groupId']; //抓該訊息的群組
        $Name = $work -> linename($UserId, $GroupId, $client); //查詢名字

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
        $work -> ReplyText($ReturnMessage, $event, $client); //回傳訊息
        mysqli_close($db_connection);
        break;
    case ($message['text'] == "更新" || $message['text'] == "更新註冊名字"): //更新 line 名稱

        $UserId = $event['source']['userId']; //抓該訊息的發送者
        $GroupId = $event['source']['groupId']; //抓該訊息的群組
        $Name = $work -> linename($UserId, $GroupId, $client); //初始化
        
        //連線到資料庫取資料
        $sql = "select * from member where lineuid = '" . $UserId . "'"; 
        $table_member = mysqli_query($db_connection, $sql);  //查詢結果
        $rowtotal = mysqli_num_rows($table_member); //總資料比數

        //查詢有沒有註冊
        if ($rowtotal > 0) {    //有註冊
            $sql = "select * from member where name = '" . $Name . "'"; //資料庫的name不能重複
            $table_member = mysqli_query($db_connection, $sql);  //查詢結果
            $rowtotal = mysqli_num_rows($table_member); //總資料比數
            //判斷此名字有沒有存在
            if ($rowtotal < 1){  //沒有存在，更新成這名字
                $sql = "update member set name = '" .$Name. "'where lineuid ='".$UserId ."'";
                if(mysqli_query($db_connection, $sql)){ //更新到資料庫
                    $ReturnMessage = "已更新資料";
                } else{
                    $ReturnMessage = "更新失敗，請洽管理員";
                }
            }else{ //有存在，繼續判斷是沒更動還是有人使用
                $sql = "select * from member where lineuid = '" . $UserId . "'";
                $table_member = mysqli_fetch_assoc(mysqli_query($db_connection, $sql));
                $table_member_name = $table_member["name"];
                if ($Name == $table_member_name ){   //判斷名字是重複還是沒有更動
                    $ReturnMessage = "名字無更動";
                }else{
                    $ReturnMessage = "名字重複，請選擇其他名字";
                }
            }
        } else {  //沒註冊
            $ReturnMessage = "請先註冊";
        }
        // 回傳名字到原本發訊息的地方(群組或機器人私訊)
        $work -> ReplyText($ReturnMessage, $event, $client); //回傳訊息
        mysqli_close($db_connection);
        break;
    case (mb_substr($message['text'] ,0,2,"UTF-8") == "排班"): //更新 line 名稱， 用於更改值日生
        $UserId = $event['source']['userId']; //抓該訊息的發送者
        //判斷權限
        if ($work -> checksecurity($UserId)){ // 查詢是否為管理員
            //查詢資料庫的個人流水號
            $name = mb_substr($message['text'], 7, null, "UTF-8");  // 取輸入的名字
            $sql = "select * from member where name = '" . $name . "'"; 
            $table_member = mysqli_query($db_connection, $sql);  //查詢結果
            $rowtotal = mysqli_num_rows($table_member); //總資料比數
        
            if ($rowtotal > 0){  //如果有這個人
                $table_member_userid =  mysqli_fetch_assoc($table_member)["userid"]; //取出流水號
                $duty_id = mb_substr($message['text'], 3, 2, "UTF-8");  // 取出輸入的工作日編號
                $sql = "update duty_list set new_userid = '" .$table_member_userid. "' where duty_id ='".$duty_id ."'"; 
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
        $work -> ReplyText($ReturnMessage, $event, $client); //回傳訊息
        mysqli_close($db_connection);
        break;
    case (mb_substr($message['text'] ,0,5,"UTF-8") == "值日生權限" || mb_substr($message['text'] ,0,5,"UTF-8") == "值日生交換" ): //分享當日值日生權限
        $UserId = $event['source']['userId']; //抓該訊息的發送者
        //判斷權限
        if ($work -> checkduty($UserId)){ // 查詢是否為值日生
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
        $work -> ReplyText($ReturnMessage, $event, $client); //回傳訊息
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
                                'title' => 'E420整理鞋櫃', //標題 2 <不一定需要>
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
                $GroupId = $event['source']['groupId']; //抓該訊息的群組
                $Name = $work -> linename($UserId, $GroupId, $client); //查詢名字
                if($work -> checkduty($UserId)){
                    $item = mb_substr($event['postback']['data'], 8, null, "UTF-8"); // 取出打卡的工作項目
                    $weekdaytempor = date('w'); // 取出今天星期幾
                    include('./connect.php'); //連結資料庫設定
                    $sql = "update sign_table set ".$item." = '完成：".$Name."' where day_int = ".$weekdaytempor; 
                    if(mysqli_query($db_connection, $sql)){ //更新到資料庫
                        $ReturnMessage = $Name."，打卡成功：".$item;
                    } else{
                        $ReturnMessage = $Name."，該項目不存在";
                    }
                    $work -> ReplyText($ReturnMessage, $event, $client); //回傳訊息
                    mysqli_close($db_connection);  //關閉資料庫連線
                }else{
                    $ReturnMessage = $Name."，你不是今天值日生";
                    $work -> ReplyText($ReturnMessage, $event, $client); //回傳訊息
                }
                break; 
            case (mb_substr($event['postback']['data'], 5, 4, "UTF-8") == "尚未完成"):
                $UserId = $event['source']['userId']; //抓該訊息的發送者
                $GroupId = $event['source']['groupId']; //抓該訊息的群組
                $Name = $work -> linename($UserId, $GroupId, $client); //初始化
                $ReturnMessage = $Name."，請完成後再重新選擇";
                $work -> ReplyText($ReturnMessage, $event, $client); //回傳訊息
                break;   
            case (mb_substr($event['postback']['data'], 5, 6, "UTF-8") == "還有人在使用"):
                $UserId = $event['source']['userId']; //抓該訊息的發送者
                $GroupId = $event['source']['groupId']; //抓該訊息的群組
                $Name = $work -> linename($UserId, $GroupId, $client); //查詢名字
                if($work -> checkduty($UserId)){
                    $item = mb_substr($event['postback']['data'], 12, null, "UTF-8"); // 取出打卡的工作項目
                    $weekdaytempor = date('w'); // 取出今天星期幾
                    include('./connect.php'); //連結資料庫設定
                    $sql = "update sign_table set ".$item." = '還有人在使用：".$Name."' where day_int = ".$weekdaytempor; 
                    if(mysqli_query($db_connection, $sql)){ //更新到資料庫
                        $ReturnMessage = $Name."，打卡成功：.$item";
                    } else{
                        $ReturnMessage = $Name."，該項目不存在";
                    }
                    $work -> ReplyText($ReturnMessage, $event, $client); //回傳訊息
                    mysqli_close($db_connection);  //關閉資料庫連線
                }else{
                    $ReturnMessage = $Name."，你不是今天值日生";
                    $work -> ReplyText($ReturnMessage, $event, $client); //回傳訊息
                }
                break; 
            default:
                break;
        }
        break;
    default:
        break;
}