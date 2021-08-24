<?php
//官方文檔：https://developers.line.biz/en/reference/messaging-api/
if (!function_exists('hash_equals')) {
    defined('USE_MB_STRING') or define('USE_MB_STRING', function_exists('mb_strlen'));
    function hash_equals($knownString, $userString)
    {
        $strlen = function ($string) {
            if (USE_MB_STRING) {
                return mb_strlen($string, '8bit');
            }
            return strlen($string);
        };
        // Compare string lengths
        if (($length = $strlen($knownString)) !== $strlen($userString)) {
            return false;
        }
        $diff = 0;
        // Calculate differences
        for ($i = 0; $i < $length; $i++) {
            $diff |= ord($knownString[$i]) ^ ord($userString[$i]);
        }
        return $diff === 0;
    }
}

class LINENotifyXiaoFei
{
	private $channelAccessToken;
    public function __construct($channelAccessToken)
    {
        $this->channelAccessToken = $channelAccessToken;
    }
	//push to notify
	public function pushtonotify($message)
    {
        $header = array(
			'Content-Type: multipart/form-data',
			'Authorization: Bearer '. $this->channelAccessToken,
        );
		$ch = curl_init();
		curl_setopt($ch , CURLOPT_URL , "https://notify-api.line.me/api/notify");
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $message);
		$result = curl_exec($ch);
		curl_close($ch);
    }
}

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
            error_log('Request failed: ' . $response);
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
            error_log('Request failed: ' . $response);
        }
    }
    private function sign($body)
    {
        $hash = hash_hmac('sha256', $body, $this->channelSecret, true);
        $signature = base64_encode($hash);
        return $signature;
    }
}