<?php
require_once('push_notify.php');
file_get_contents('https://dogmission.herokuapp.com/record.php'); //產生pdf
require_once('send_email.php');
require_once('chenge_duty.php');