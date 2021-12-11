<?php
require 'vendor/autoload.php'; // If you're using Composer (recommended)
date_default_timezone_set("Asia/Taipei"); //設定時區為台北時區
// Comment out the above line if not using Composer
// require("<PATH TO>/sendgrid-php.php");
// If not using Composer, uncomment the above line and
// download sendgrid-php.zip from the latest release here,
// replacing <PATH TO> with the path to the sendgrid-php.php file,
// which is included in the download:
// https://github.com/sendgrid/sendgrid-php/releases

switch(true){
    //發送近三日遛狗名單和簽到表
    case (date('w') == 0 &&  date('H:i') <= "00:55"):
        $timestart = date('Y-m-d', strtotime("-7 day"));  //抓時間
        $timeend = date('Y-m-d', strtotime("-1 day"));  //抓時間
        $send_email = getenv('email');
        include('./connect.php'); //連結資料庫設定
        $sql = "select * from member where security = 2";
        $result = $db_connection->query($sql); //查詢資料

        if($db_connection->affected_rows > 0){ //檢查是否查詢成功
            $ReturSQLnMessage = "資料查詢成功\n";
        } else{
            $ReturnSQLMessage = "資料查詢失敗\n";
        }
        echo $ReturnSQLMessage;

        while ($table_member = $result->fetch_assoc()) {
            //收件者 email 和 name
            $user_name = $table_member["name"];
            $user_email = $table_member["email"];
            //設定eamil發送者、接收者、內容
            $email = new \SendGrid\Mail\Mail(); 
            $email->setFrom($send_email, "dogmission"); //寄件人資訊
            $email->setSubject($timestart." ~ ".$timeend." 工作檢核");
            $email->addTo($user_email, $user_name);
            $email->addContent("text/plain", $timestart."~".$timeend." 工作檢核");
            $email->addContent(
                "text/html", "<strong>請看副檔</strong>"
            );
            //附件檔案
            $file_encoded = base64_encode(file_get_contents("https://dogmission.herokuapp.com/record.pdf"));
            $email->addAttachment(
                $file_encoded,
                "application/pdf",
                "record.pdf",
                "attachment"
            );
            //發送email
            $sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
            try {
                $response = $sendgrid->send($email);
                print $response->statusCode() . "\n";
                print_r($response->headers());
                print $response->body() . "\n";
            } catch (Exception $e) {
                echo 'Caught exception: '. $e->getMessage() ."\n";
            }
        }
        mysqli_close($db_connection); //關閉資料庫連線
        break;
    default:
        break;
}
