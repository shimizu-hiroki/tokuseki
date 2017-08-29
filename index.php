$accessToken = ‘gXjFBRpxI1c0n8bXUqYQW5m5k9VNuVA6ufGB7Az5RjnDdWQcrYpx5tMXrxyq+R2PLWg2f7rcFAWloEfRgHel3YL+LiRTI8hyFQgMEK0u3gDV+hA4AXFJJIGz1IOZRDsbBPe0/kdUm132IYCtyrB68QdB04t89/1O/w1cDnyilFU=’;
 
//ユーザーからのメッセージ取得
$json_string = file_get_contents('php://input');
$jsonObj = json_decode($json_string);
 
$type = $jsonObj--->{"events"}[0]->{"message"}->{"type"};
$text = $jsonObj->{"events"}[0]->{"message"}->{"text"};
$replyToken = $jsonObj->{"events"}[0]->{"replyToken"};
 
 
//ドコモの雑談データ取得
$response = chat($text);
 
$response_format_text = [
    "type" => "text",
    "text" =>  $response
  ];
 
$post_data = [
	"replyToken" => $replyToken,
	"messages" => [$response_format_text]
	];
 
$ch = curl_init("https://api.line.me/v2/bot/message/reply");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json; charser=UTF-8',
    'Authorization: Bearer ' . $accessToken
    ));
$result = curl_exec($ch);
curl_close($ch);
 
 
//ドコモの雑談APIから雑談データを取得
function chat($text) {
    // docomo chatAPI
    $api_key = ‘3378495a422f7a623734512f4e544631636949792f4a6475383834592e79704c796353587a443053515343’;
    $api_url = sprintf('https://api.apigw.smt.docomo.ne.jp/dialogue/v1/dialogue?APIKEY=%s', $api_key);
    $req_body = array('utt' => $text);
    
    $headers = array(
        'Content-Type: application/json; charset=UTF-8',
    );
    $options = array(
        'http'=>array(
            'method'  => 'POST',
            'header'  => implode("\r\n", $headers),
            'content' => json_encode($req_body),
            )
        );
    $stream = stream_context_create($options);
    $res = json_decode(file_get_contents($api_url, false, $stream));
 
    return $res->utt;

}