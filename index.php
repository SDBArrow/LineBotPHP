<?php
//官方文檔：https://developers.line.biz/en/reference/messaging-api/
date_default_timezone_set("Asia/Taipei"); //設定時區為台北時區
require_once('LINEBotXiaoFei.php'); //引入LINEBotXiaoFei.php發送code寫在LINEBotTiny
$channelAccessToken = '';  $channelSecret = ''; //初始化
if (file_exists(__DIR__ . '/config.ini')) {
    $config = parse_ini_file("config.ini", true); //解析配置檔
    if ($config['Channel']['Token'] == null || $config['Channel']['Secret'] == null) {
        error_log("config.ini 配置檔未設定完全！", 0); //輸出錯誤
    } else {
        $channelAccessToken = $config['Channel']['Token'];
        $channelSecret = $config['Channel']['Secret'];
    }
} else {
    $configFile = fopen("config.ini", "w") or die("Unable to open file!");
    $configFileContent = '';
    fwrite($configFile, $configFileContent); //建立文件並寫入
    fclose($configFile); //關閉文件
    error_log("config.ini 配置檔建立成功，請編輯檔案填入資料！", 0); //輸出錯誤
}
$message = null; $event = null; //初始化   $event有資料來源所有資料
$client = new LINEBotXiaoFei($channelAccessToken, $channelSecret); //把Token,Secret丟到LINEBotXiaoFei建立連線
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
function WorkSchedule($time ,$ansdate ,$event ,$client){
    include('./connect.php');//連結資料庫設定
	$timecount=(strtotime($time) - strtotime("2021-02-21 00:00:00"))/ (60*60*24); //相隔天數
	$weekcount=floor($timecount/7); //相隔週數

    //今天單周還雙周
	$oddandeven=$weekcount%2;
    $sql = "select * from week where week_int = ".$oddandeven;   
    $row_week = mysqli_fetch_assoc(mysqli_query($con, $sql));
	$week=$row_week["week_ch"]; 

    //今天星期幾
	$weekdaytempor=date('w', strtotime($time));
    $sql = "select * from day where day_int = ".$weekdaytempor;   
    $row_day = mysqli_fetch_assoc(mysqli_query($con, $sql));
	$day=$row_day["day_ch"]; 

    //查詢值日生
    $sql = "select * from duty_list where day = ".$weekdaytempor." and week = ".$oddandeven;   
    $row_name = mysqli_fetch_assoc(mysqli_query($con, $sql));
    $name=$row_name["name"]; 

	if ( $name == ""){  //檢查是否是替補日
		$tempor = 0; //初始化
	    $tempor = $tempor + $weekcount;  //替補計算
		$tempor = $tempor % 12; //因為有12個人，所以每12次重新一次
        //查詢替補
        $sql = "select * from duty_turn where id = ".$tempor;   
        $row_trun = mysqli_fetch_assoc(mysqli_query($con, $sql));
	    $turn=$row_trun["name"]; 
        //傳輸訊息
		$client->replyMessage(array(
			'replyToken' => $event['replyToken'],
			'messages' => array(
				array(
					'type' => 'text', // 訊息類型 (文字)
					'text' => "==================\n     ".$ansdate."(".$week.")".$day."(替補)\n==================\n--->".$turn // 回復訊息
				)
			)
		));
	}else{   //不是替補日
        //傳輸訊息
		$client->replyMessage(array(
			'replyToken' => $event['replyToken'],
			'messages' => array(
				array(
					'type' => 'text', // 訊息類型 (文字)
					'text' => "==================\n     ".$ansdate."(".$week.")".$day."\n==================\n--->".$name // 回復訊息
				)
			)
		));
	}
    mysqli_close($con);
}
if ($message['text'] == "早安") {  //早安
    $client->replyMessage(array(
        'replyToken' => $event['replyToken'],
        'messages' => array(
            array(
                'type' => 'text', // 訊息類型 (文字)
                'text' => "早安!" // 回復訊息
            )
        )
    ));
}
if ($message['text'] == "注意" || $message['text'] == "注意事項") {  //注意事項
    $client->replyMessage(array(
        'replyToken' => $event['replyToken'],
        'messages' => array(
            array(
                'type' => 'text', // 訊息類型 (文字)
                'text' => '小飛之後帶下去上廁所，如果當下小飛沒馬上大號的話，要至少等小飛10分鐘再帶上來，小飛通常會下去一陣子後才上大號，其餘規則請至419_3門口查看，有任何問題請聯絡簡嘉賢學長' // 回復訊息
            )
        )
    ));
}
if ($message['text'] == "餵食規則" || $message['text'] == "餵食") {  //餵食
    $client->replyMessage(array(
        'replyToken' => $event['replyToken'],
        'messages' => array(
            array(
                'type' => 'text', // 訊息類型 (文字)
                'text' => "一餐:\n1/8罐頭+70克飼料\n1/8罐頭+200公克水" // 回復訊息
            )
        )
    ));
}
if ($message['text'] == "遛狗" || $message['text'] == "今日遛狗" || $message['text'] == "今天遛狗") {  //今日遛狗
	$time=date('Y-m-d H:i:s');  //抓時間
	$ansdate = date('m/d');  //抓時間
	$result=WorkSchedule($time , $ansdate ,$event ,$client); //丟去副程式WorkSchedule
}
if ($message['text'] == "明天遛狗" || $message['text'] == "明日遛狗") {  //明日遛狗
	$time=date('Y-m-d H:i:s',strtotime("+1 day"));  //抓時間
	$ansdate = date('m/d',strtotime("+1 day"));  //抓時間
	$result=WorkSchedule($time , $ansdate ,$event ,$client); //丟去副程式WorkSchedule
}
if ($message['text'] == "後日遛狗" || $message['text'] == "後天遛狗") {  //後天遛狗
	$time=date('Y-m-d H:i:s',strtotime("+2 day"));  //抓時間
	$ansdate = date('m/d',strtotime("+2 day"));  //抓時間
	$result=WorkSchedule($time , $ansdate ,$event ,$client); //丟去副程式WorkSchedule
}
if ($message['text'] == "排班表" || $message['text'] == "班表") {  //班表
    $client->replyMessage(array(
        'replyToken' => $event['replyToken'],
        'messages' => array(
            array(
                'type' => 'image', // 訊息類型 (圖片)
                'originalContentUrl' => 'https://dogmission.herokuapp.com/images/Class_Schedule_20210411.jpg', // 回復圖片
                'previewImageUrl' => 'https://dogmission.herokuapp.com/images/Class_Schedule_20210411.jpg' // 回復的預覽圖片
            )
        )
    ));
}
if ($message['text'] == "昨天遛狗" || $message['text'] == "前天遛狗" || $message['text'] == "大前天遛狗") {   //智障問題
    $client->replyMessage(array(
        'replyToken' => $event['replyToken'],
        'messages' => array(
            array(
                'type' => 'text', // 訊息類型 (文字)
                'text' => '不要問智障問題好嗎' // 回復訊息
            )
        )
    ));
}
if ($message['text'] == "抽女孩" || $message['text'] == "抽") {    //抽獎
    require 'vendor/autoload.php';  //引入軟件包PhpSpreadsheet
	$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx'); //資料庫副檔名
	$reader->setReadDataOnly(TRUE); //讀資料權限
	$spreadsheet = $reader->load('lottery/lottery.xlsx'); //資料位置
	$worksheet = $spreadsheet->getActiveSheet();
	$highestRow = $worksheet->getHighestRow(); // 總行数 
	$rand = rand(1,$highestRow); //產生最大數為資料數量的一個亂數
	$name = $worksheet->getCellByColumnAndRow(1, $rand)->getValue(); //取得亂數產生的相對URL
    $client->replyMessage(array(
        'replyToken' => $event['replyToken'],
        'messages' => array(
            array(
                'type' => 'image', // 訊息類型 (圖片)
                'originalContentUrl' => $name, // 回復圖片
                'previewImageUrl' => $name // 回復的預覽圖片*/
            )
        )
    ));
}
//日期查遛狗名單
if (preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $message['text'] )) {  //判斷data是否為日期
	$time=$message['text'];  //抓時間
	$__y = substr($time, 0, 4);
    $__m = substr($time, 5, 2);
    $__d = substr($time, 8, 2);
	if(checkdate($__m, $__d, $__y)){
		$ansdate = substr($time, 5);  //抓時間
		$result=WorkSchedule($time , $ansdate ,$event ,$client); //丟去副程式WorkSchedule
	}
}
//抓ID    groupId:群組id  userId:個人ID 
/*if (strtolower($message['text']) == "測試" || $message['text'] == "測試") {
    $client->replyMessage(array(
        'replyToken' => $event['replyToken'],
        'messages' => array(
            array(
                'type' => 'text', // 訊息類型 (文字)
				'text' => $event['source']['userId']
            )
        )
    ));
}*/
?>