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


        switch ($msg_text) {
            case 'あそぼ':

                $reply_text = '昼休みは部室で勉強って約束したやん？';

                break;

            case 'ウソ':

                $reply_text = 'ウソつくとワシワシするよ！';

                break;

            case 'カード':

                $reply_text = 'カードがウチにそう告げるんや！！！';

                break;
            default:

                $reply_text = 'やってみればええやん。特に理由なんて必要ない。やりたいからやってみる。本当にやりたいことって、そんな感じに始まるんやない？';

                break;

        }
    } else {

        // パターンで返すシンプルなボット(4パターン程度)

        switch ($msg_text) {

            case 'あそぼ':

                $reply_text = '昼休みは部室で勉強って約束したやん？';

                break;

            case 'ウソ':

                $reply_text = 'ウソつくとワシワシするよ！';

                break;

            case 'カード':

                $reply_text = 'カードがウチにそう告げるんや！！！';

                break;
            default:

                $reply_text = 'やってみればええやん。特に理由なんて必要ない。やりたいからやってみる。本当にやりたいことって、そんな感じに始まるんやない？';

                break;

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

        'nickname' => '希',

        'nickname_y' => 'ノゾミ',

        'sex' => '女',

        'bloodtype' => 'O',

        'birthdateM' => '6',

        'birthdateD' => '9',

        'age' => '17',

        'constellations' => '双子座',

        'place' => '東京',

        't' => '20'

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

