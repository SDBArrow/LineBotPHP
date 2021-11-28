<?php
include('push_notify.php');
file_get_contents('https://dogmission.herokuapp.com/record.php'); //產生pdf
include('send_email.php');
include('chenge_duty.php');