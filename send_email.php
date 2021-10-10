<?php
require 'vendor/autoload.php'; // If you're using Composer (recommended)
// Comment out the above line if not using Composer
// require("<PATH TO>/sendgrid-php.php");
// If not using Composer, uncomment the above line and
// download sendgrid-php.zip from the latest release here,
// replacing <PATH TO> with the path to the sendgrid-php.php file,
// which is included in the download:
// https://github.com/sendgrid/sendgrid-php/releases

$timestart = date('Y-m-d', strtotime("-1 day"));  //抓時間
$timeend = date('Y-m-d', strtotime("-8 day"));  //抓時間
$myemail = getenv('email');

include('./connect.php'); //連結資料庫設定
$sql = "select * from member where security = 2";
$table_member = mysqli_fetch_assoc(mysqli_query($db_connection, $sql));
$name = $table_member["name"];
$email = $table_member["email"];

$email = new \SendGrid\Mail\Mail(); 
$email->setFrom($myemail, "dogmission"); //寄件人資訊
$email->setSubject($timestart."~".$timeend." 工作檢核");
$email->addTo($email, $name);
$email->addContent("text/plain", $timestart."~".$timeend." 工作檢核");
$email->addContent(
    "text/html", "<strong>請看副檔</strong>"
);

$file_encoded = base64_encode(file_get_contents("https://dogmission.herokuapp.com/record.pdf"));
$email->addAttachment(
    $file_encoded,
    "application/pdf",
    "record.pdf",
    "attachment"
);

$sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
try {
    $response = $sendgrid->send($email);
    print $response->statusCode() . "\n";
    print_r($response->headers());
    print $response->body() . "\n";
} catch (Exception $e) {
    echo 'Caught exception: '. $e->getMessage() ."\n";
}
mysqli_close($db_connection); //關閉資料庫連線