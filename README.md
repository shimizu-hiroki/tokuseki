# sample-line-bot

LINE向けチャットボット作成用のサンプルコード

## 概要

LINE向けチャットボットのサンプルです。
サーバーにはHerokuを使用しています。

[![Deploy](https://www.herokucdn.com/deploy/button.png)](https://heroku.com/deploy)

* heroku上にデプロイされます。
* Redisは追加されません。自身での追加をお願いします。

*DOCOMOの雑談対話APIでのやりとりを記憶するためにRedisを使用しています。*


## 手順

### sample-line-bot を動作させるまでの手順

#### LINE Messaging APIの利用登録

1. 「LINE BUSINESS CENTER」へアクセスします。
1. 「サービス」タブから「Messaging API」を選択します。
1. 「Developer Trialを始める」を選択します。

LINE BUSSINESS CENTER：https://business.line.me/ja/

#### LINEアカウントの設定

1. 「LINE BUSINESS CENTER」のアカウントリストから「LINE@ MANAGER」を押下します。
1. Bot設定から下記の設定を行います。
    * Webhook送信 「利用する」
    * 自動応答メッセージ 「利用しない」

#### Messagin API の設定

1. 「LINE BUSINESS CENTER」のアカウントリストから「LINE Developers」を選択します。
1. 「Webhook URL」を下記のように設定します。
     * https://[アプリ名].herokuapp.com/callback
1. 「Channel Access Token」を発行します。

[アプリ名]にはHerokuで作成する予定のアプリ名を入れてください。

#### Herokuアカウントの作成

1. 「Heroku」へアクセスします。
1. 「Sign Up」からHerokuアカウントを作成します。

Heroku:https://www.heroku.com/

#### サーバーアプリのデプロイ

1. 下記の Deployボタンを押下します。
    * [![Deploy](https://www.herokucdn.com/deploy/button.png)](https://heroku.com/deploy)
    * Heroku上にサーバーアプリがデプロイされます。
1. 環境変数を設定します。
    * LINE_CHANNEL_ACCESS_TOKEN に「Messagin API の設定」で発行した「Channel Access Token」を設定してください。

#### 動作確認

1. 「LINE BUSINESS CENTER」のアカウントリストから「LINE Developers」を選択します。
1. 「Basic information」のQR CodeをスマホアプリのLINEで友だち追加します。
1. 追加した友だちに対して下記のように呟きます。
    * あそぼ
1. 下記のように返答が返ってくれば成功です。
    * 昼休みは部室で勉強って約束したやん？

### DOCOMO 雑談対話APIを使用した sample-line-bot を動作させるまでの手順

*本手順は「DOCOMO 雑談API」を使用しない場合は不要です。*

#### 「docomo Developer support」の開発者アカウントの作成

1. 「docomo Developer support」へアクセスします。
1. 「ログイン/新規登録」タブから「アカウントを登録作成します。

docomo Developer support：https://dev.smt.docomo.ne.jp/

#### DOCOMO 雑談対話APIの利用申請

1. 「docomo Developer support」へログインします。
1. 「新規API利用申請」を押下します。
1. 手順に従って登録します。
   * アプリケーション登録
       * コールバックURLは、https://dummy で構いません。
       * アプリケーションタイプは、ウェブアプリケーションで作成してください。
   * API機能選択
       *「雑談対話」を選択してください。
1. 「docomo Developer support」の「API利用申請・管理」のアプリケーション情報でAPI Keyを確認してください。

#### サーバーアプリの環境変数の更新

1. 「Heroku」へログインします。
1. 対象のアプリを選択し、「Setting」タブの「Reveal Config Vars」を押下します。
   1. 「BOT_MODE」に「DOCOMO」と設定してください。
   1. 「DOCOMO_CHAT_API_KEY」に、上記のAPIキーを設定してください。

#### Redisの追加

*Redisを使うためにはクレジットカードの登録が必要になります。*
*クレジットカードの登録は「Account Setting」「Billing」から設定可能です。*

1. 対象のアプリの「Overview」タブを選択し、「configure Add-ons」を押下してください。
1. 「Add-ons」の下方にあるテキストボックスで「redis」と打ち込み「Heroku Redis」を選択してください。
1. 「Plan name」で「Hobby Dev」を選択して、「Provision」を押下してください。


### カスタマイズ

#### ソースコードの取得から反映

*対象のアプリの「Deploy」タブの「Deploy using Heroku Git」に参考にしてください。*

以下、簡単にまとめたものを記載します。

1. Heroku CLI をインストールします。
1. ソースコードをクローンします。
    * コンソール（コマンドプロンプト）から下記のコマンドを打ち込んでください。
   ```
      heroku git:clone -a <<アプリ名>>
   ```
1. ソースコードを修正します。
   * index.php を修正してください。
1. HerokuへPUSHします。
    * コンソール（コマンドプロンプト）から下記のコマンドを打ち込んでください。
   ```
      git add .
      git commit -am "<<コメント>>"
      git push heroku master
   ```

#### 動作のデバッグ（ログの確認）

1. コンソール（コマンドプロンプト）から下記のコマンドを打つと実行時のログが閲覧できます。
    ```
       heroku logs -t
    ```

## 環境変数

本アプリでは下記の環境変数を使用します。

|項目|説明|
|:--|:--|
|LINE_CHANNEL_ACCESS_TOKEN|Messaging APIを使用するために必要なAccess Tokenを設定してください。|
|BOT_MODE|Botのモードです。DOCOMOと設定するとDOCOMOの雑談対話APIを使用するモードになります。|
|DOCOMO_CHAT_API_KEY|DOCOMOの雑談対話APIを使用するために必要なAPI Keyを設定してください。|
|REDIS_URL|RedisのURLです。HerokuアプリでAdd-Onを追加した際に自動的に設定されます。|

## ライセンス

MITライセンスです。

## 注意事項

本文の内容及びアプリを使用したことによる如何なる問題に関しても@ryohosは責任を負いません。
ご理解の上、確認及びご使用ください。

また、本文は2017年6月28日の内容を最新として記載しております。
