<?php
include_once('push_notify.php');
file_get_contents('https://dogmission.herokuapp.com/record.php'); //產生pdf
include_once('send_email.php');
include_once('chenge_duty.php');