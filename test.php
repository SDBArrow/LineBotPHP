<?php
require('vendor/autoload.php');

$hostname = 'smtp.cloudmta.net';
$username = '4d62f948c506dea7';
$password = 'AR5uSM78LDL1SiwoUUCVSQCQ';

$transport = (new Swift_SmtpTransport($hostname, 587, 'tls'))
  ->setUsername($username)
  ->setPassword($password);

$mailer = new Swift_Mailer($transport);


//Creating an sending the email
$message = (new Swift_Message())
  ->setSubject('Hello from PHP SwiftMailer')
  ->setFrom(['3b280a1883502f197cc8@cloudmailin.net'])
  ->setTo(['j25889651556@gmail.com' => 'dogmission']);

$headers = ($message->getHeaders())
  -> addTextHeader('X-CloudMTA-Class', 'standard');

$message->setBody(
  '<body>'.
  '<h1>hello from php</h1>'.
  '</body>'
);
$message->addPart('hello from PHP', 'text/plain');
$mailer->send($message);