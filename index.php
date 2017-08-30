<?php

require_once __DIR__.'/vendor/autoload.php';



use Symfony\Component\HttpFoundation\Request;



date_default_timezone_set('Asia/Tokyo');



$app = new Silex\Application();

$app->post('/callback', function (Request $request) use ($app) {

    // 環境変数の値を取得する

    $access_token = getenv('LINE_CHANNEL_ACCESS_TOKEN');

    $redis_url = getenv('REDIS_URL');

    $bot_mode = getenv('BOT_MODE');



    error_log($bot_mode);



    //ユーザーからのメッセージ取得する

    $contents = file_get_contents('php://input');

    error_log($contents);

    $msg_obj = json_decode($contents);



    // Webhook Event Objectから必要なデータを取得する

    // https://devdocs.line.me/ja/#webhook-event-object

    $reply_token = $msg_obj->{'events'}[0]->{'replyToken'};

    $user_id = $msg_obj->{'events'}[0]->{'source'}->{'userId'};

    $msg_type = $msg_obj->{'events'}[0]->{'message'}->{'type'};

    $msg_text = $msg_obj->{'events'}[0]->{'message'}->{'text'};



    // メッセージ種別がテキスト以外のときは何も返さず終了する

    if ($msg_type != 'text') {

        return 0;

    }



    $reply_text = '';

    if ($bot_mode == 'DOCOMO') {

        // DOCOMOの雑談対話APIを使用するボット

        $redis = new Predis\Client($redis_url);

        $context = $redis->get($user_id);

        $response = dialogue($msg_text, $context);

        $redis->set($user_id, $response->context);

        $reply_text = $response->utt;

	

        if (strpos($msg_text, 'ガソリンスタンド') !== false) {


                $reply_text = "最寄りのガソリンスタンドはここをチェック！\n";
		$reply_text .= "http://www.tokuseki.co.jp/sssearch/sssearch.php";


        } else if (strpos($msg_text, '配達') !== false) { 


                $reply_text = "灯油配達を希望ですか？それならこちらをご覧ください！\n";
		$reply_text .= "http://www.tokuseki.co.jp/service/kr_delivery/kr_delivery.php";


        } else if (strpos($msg_text, '太陽光') !== false) {


                $reply_text = "太陽光発電の見積もりもやってます！\n詳しくはこちらをチェック!\n";
		$reply_text .= "http://www.tokuseki.co.jp/service/solar/solar_order_popup.php";
        }
    }



    error_log($reply_text);



    $message = [

        'type' => 'text',

        'text' => $reply_text

    ];



    $post_data = [

        'replyToken' => $reply_token,

        'messages' => [$message]

    ];



    $ch = curl_init('https://api.line.me/v2/bot/message/reply');

    curl_setopt($ch, CURLOPT_POST, true);

    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));

    curl_setopt($ch, CURLOPT_HTTPHEADER, [

        'Content-Type: application/json; charser=UTF-8',

        'Authorization: Bearer ' . $access_token

    ]);


    $result = curl_exec($ch);

    error_log($result);

    curl_close($ch);


    return 0;
});



/**

 * 雑談対話APIを呼び出す

 *

 * @param $message

 * @param $context

 * @return JSON形式のオブジェクト

 * @link https://dev.smt.docomo.ne.jp/?p=docs.api.page&api_name=dialogue&p_name=api_1#tag01

 */


function dialogue($message, $context) {

    // 環境変数の取得

    $chat_api_key = getenv('DOCOMO_CHAT_API_KEY');



    $post_data = [

        'utt' => $message,

        'context' => $context,

        'nickname' => 'トクセキ君',


        'sex' => '男',

        'bloodtype' => 'AB',

        'birthdateM' => '12',

        'birthdateD' => '31',

        'age' => '19',

        'constellations' => 'やぎ座',

        'place' => '徳島県'

    ];


    $ch = curl_init('https://api.apigw.smt.docomo.ne.jp/dialogue/v1/dialogue?APIKEY=' . $chat_api_key);

    curl_setopt($ch, CURLOPT_POST, true);

    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));

    curl_setopt($ch, CURLOPT_HTTPHEADER, [

        'Content-Type: application/json; charser=UTF-8'

    ]);

    $result = curl_exec($ch);

    curl_close($ch);

    return json_decode($result);

}



$app->run();

