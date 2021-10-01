<?php

include('./function_conform.php'); //引入LINEBotXiaoFei.php發送code寫在LINEBotTiny
$time = date('Y-m-d');  //抓時間
$reponse = WorkSchedule($time);
echo $reponse;