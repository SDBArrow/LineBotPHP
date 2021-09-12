<?php
//官方文檔：https://developers.line.biz/en/reference/messaging-api/
/**
 * Copyright 2016 LINE Corporation
 *
 * LINE Corporation licenses this file to you under the Apache License,
 * version 2.0 (the "License"); you may not use this file except in compliance
 * with the License. You may obtain a copy of the License at:
 *
 *   https://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 */

//LineBot Notify
class LINENotifyXiaoFei
{
    private $channelAccessToken;
    public function __construct($channelAccessToken)
    {
        $this->channelAccessToken = $channelAccessToken;
    }
    //push 傳輸方式 用 notify
    public function pushtonotify($message)
    {
        $header = array(
            'Content-Type: multipart/form-data',
            'Authorization: Bearer ' . $this->channelAccessToken,
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://notify-api.line.me/api/notify");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $message);
        $result = curl_exec($ch);
        curl_close($ch);
    }
}
//官方Linebot
class LINEBotXiaoFei
{
    private $channelAccessToken;
    private $channelSecret;
    public function __construct($channelAccessToken, $channelSecret)
    {
        $this->channelAccessToken = $channelAccessToken;
        $this->channelSecret = $channelSecret;
    }
    public function parseEvents()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            error_log('Method not allowed');
            exit();
        }
        $entityBody = file_get_contents('php://input');

        if (strlen($entityBody) === 0) {
            http_response_code(400);
            error_log('Missing request body');
            exit();
        }
        if (!hash_equals($this->sign($entityBody), $_SERVER['HTTP_X_LINE_SIGNATURE'])) {
            http_response_code(400);
            error_log('Invalid signature value');
            exit();
        }
        $data = json_decode($entityBody, true);
        if (!isset($data['events'])) {
            http_response_code(400);
            error_log('Invalid request body: missing events property');
            exit();
        }
        return $data['events'];
    }
    //reply傳輸方式
    public function replyMessage($message)
    {
        $header = array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->channelAccessToken,
        );
        $context = stream_context_create([
            'http' => [
                'ignore_errors' => true,
                'method' => 'POST',
                'header' => implode("\r\n", $header),
                'content' => json_encode($message),
            ],
        ]);
        $response = file_get_contents('https://api.line.me/v2/bot/message/reply', false, $context);
        if (strpos($http_response_header[0], '200') === false) { 
            error_log('Request failed: ' . $response);  //連線失敗回傳error
        }
    }
    //push傳輸方式 免費仔一個月只能500次(群組和房間以人頭計算) ex:在一個20人的房間發一封push訊息=20次
    public function pushMessage($message)
    {
        $header = array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->channelAccessToken,
        );
        $context = stream_context_create([
            'http' => [
                'ignore_errors' => true,
                'method' => 'POST',
                'header' => implode("\r\n", $header),
                'content' => json_encode($message),
            ],
        ]);
        $response = file_get_contents('https://api.line.me/v2/bot/message/push', false, $context);
        if (strpos($http_response_header[0], '200') === false) { 
            error_log('Request failed: ' . $response);  //連線失敗回傳error
        }
    }
    //Group對話去取個人資料訊息
    public function getGroupProfile($message)
    {
        $header = array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->channelAccessToken,
        );
        $context = stream_context_create([
            'http' => [
                'ignore_errors' => true,
                'method' => 'GET', //api 不吃post
                'header' => implode("\r\n", $header),
            ],
        ]);
        //因為回傳的值是json格式，所以轉成json讀取
        $response = json_decode(file_get_contents('https://api.line.me/v2/bot/group/'.$message['GroupId'].'/member/'.$message['UserId'], false, $context));
        if (strpos($http_response_header[0], '200') === false) {   
            error_log('Request failed: ' . $response);  //連線失敗回傳error
        }else{
            return $response;   //連線成功回傳值
        }
    }
        //User對話去取個人資料訊息
    public function getUserProfile($message)
    {
        $header = array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->channelAccessToken,
        );
        $context = stream_context_create([
            'http' => [
                'ignore_errors' => true,
                'method' => 'GET', //api 不吃post
                'header' => implode("\r\n", $header),
            ],
        ]);
        //因為回傳的值是json格式，所以轉成json讀取
        $response = json_decode(file_get_contents('https://api.line.me/v2/bot/profile/'.$message['UserId'], false, $context));
        if (strpos($http_response_header[0], '200') === false) {   
            error_log('Request failed: ' . $response);  //連線失敗回傳error
        }else{
            return $response;   //連線成功回傳值
        }
    }
    private function sign($body)
    {
        /*Authentication is performed as follows:
        1.With the channel secret as the secret key, 
        your application retrieves the digest value in the request body created using the HMAC-SHA256 algorithm.
        2.The server confirms that the x-line-signature in the request header matches the Base64-encoded digest value.*/
        $hash = hash_hmac('sha256', $body, $this->channelSecret, true);
        $signature = base64_encode($hash);
        return $signature;
    }
}
