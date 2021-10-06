<?php
require 'vendor/autoload.php'; // If you're using Composer (recommended)
// Comment out the above line if not using Composer
// require("<PATH TO>/sendgrid-php.php");
// If not using Composer, uncomment the above line and
// download sendgrid-php.zip from the latest release here,
// replacing <PATH TO> with the path to the sendgrid-php.php file,
// which is included in the download:
// https://github.com/sendgrid/sendgrid-php/releases

$email = new \SendGrid\Mail\Mail(); 
$email->setFrom("j2588965156@gmail.com", "dogmission");
$email->setSubject("20211003~20211009 工作檢核");
$email->addTo("j25889651556@gmail.com", "yang");
$email->addContent("text/plain", "20211003~20211009 工作檢核");
$email->addContent(
    "text/html", "<strong>請看副檔</strong>"
);

$file_encoded = base64_encode(file_get_contents('record.pdf'));
$email->addAttachment(
    $file_encoded,
    "application/text",
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