<?php
//--------------------------
//メール設定
//--------------------------
require_once __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

//--------------------------
//デバッグ設定
//--------------------------

//ログをとるか
ini_set('error_log','on');
ini_set('errlr_log','php.log');

$debug_flg = true;

//デバッグログ吐き出し用
function debug($str) {

  global $debug_flg;

  if($debug_flg) {
    error_log('デバッグ ' . $str);
  }
}

//画面表示開始時のログ吐き出し用
function debugLogStart() {
  debug('>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> 画面表示処理開始');
  debug('セッションID' .session_id());
  debug('セッション変数の中身' . print_r($_SESSION,true));
  debug('現在日時タイムスタンプ' . time());
}

//ログを取るか
ini_set('log_errors','on');
//ログの出力ファイルを指定
ini_set('error_log','php.log');

//-------------------------------------
//セッション準備：セッションの有効期限を延ばす
//-------------------------------------
//セッションの置き場所を変更する
session_save_path("/var/tmp/");
//ガーベジコレクションが削除するセッションの有効期限を設定
ini_set('session.gc_maxlifetime',60*60*24*30);
//ブラウザが閉じても削除されないようにクッキー自体の有効期限を延ばす
ini_set('session.cookie_lifetime',60*60*24*30);
//セッションスタート
session_start();
//現在のセッションIDを新しく生成したものと置き換える
session_regenerate_id();



//-------------------
//定数定義
//-------------------
//最大文字数
define('MAX_NAME', 25);
define('MAX_EVENT_NAME', 50);
define('MAX_EMAIL', 255);
define('MAX_PASS', 128);
define('MAX_DESCRIPTION', 500);

define('APL_NAME','KENYU(剣友)');
define('APL_SUBNAME',' 〜もっと自由に、もっと気軽に剣道を〜');
define('APL_SUBJECT','イベント'); //サイトコンセプトが変わっても一発で変えられるようにするためのもの

//デバッグ用
define('DEBUG_MODE', true); //デバッグモード（ログインとか毎回入力がめんどくさいのであらかじめ設定）

//エラーメッセージを定数に設定
define('ERR_REQUIRED','入力必須です');
define('ERR_EMAIL_FORMAT', 'Emailの形式で入力してください');
define('ERR_PASSWORD_MISMATCH','パスワード（再入力）が合っていません');
define('ERR_HALF_ALPHANUMERIC','半角英数字のみご利用いただけます');
define('ERR_MIN_LENGTH','6文字以上で入力してください');
define('ERR_MAX_LENGTH','%d文字以内で入力してください');
define('ERR_SYSTEM','エラーが発生しました。しばらく経ってからやり直してください。');
define('ERR_EMAIL_DUPLICATE', 'そのEmailは既に登録されています');
define('ERR_LOGIN', 'メールアドレスまたはパスワードが違います');
define('ERR_TEL_FORMAT', '電話番号の形式が違います');
define('ERR_ZIP_FORMAT', '郵便番号の形式が違います');
define('ERR_AGE', '年齢入力が不正です');
define('ERR_OLD_PASSWORD', '古いパスワードが違います');
define('ERR_SAME_PASSWORD', '古いパスワードと同じです');
define('ERR_AUTH_CODE', '入力された認証コードが違います');
define('ERR_AUTH_EXPIRED', '期限が切れております。再度認証コードを取得してください');
define('ERR_CATEGORY', 'カテゴリの入力が正しくありません');
define('ERR_GENDER', '性別を選択してください');
define('ERR_DATE', '日付の形式が違います');
define('ERR_TIME', '時間の形式が違います');
define('ERR_TIME_RANGE', '終了時間は開始時間より後の時刻を設定してください');
define('ERR_EDIT_ACCESS', '不正なユーザーが編集しようとしています。マイページへ遷移します');

define('SUCCESS_PASSWORD_CHANGE', 'パスワードを変更しました');
define('SUCCESS_MAIL_SEND', 'メールを送信しました。メールに書かれたパスワードでログインしてください');
define('SUCCESS_EVENT_REGISTER', APL_SUBJECT . 'を登録しました');
define('SUCCESS_BOARD_MOVE', '掲示板に移動しました。自分の思いをどんどん投稿しよう');
define('SUCCESS_SIGNUP', 'ユーザ登録しました。推し情報を共有しましょう！');
define('SUCCESS_EVENT_DELETE', APL_SUBJECT . 'を削除しました');
define('SUCCESS_WITHDRAW', '退会処理完了しました。でもいつでも戻ってきてくださいっ！！');
define('SUCCESS_USER_UPDATE', 'ユーザ登録情報を変更しました！');
define('SUCCESS_USER_LOGIN', 'ログインしました！');


//メール送信用
//メールの「from」
define('ML_FROM', 'noreply@yk-lab.jp');
define('SMTP_HOST', 'sv16822.xserver.jp');
define('SMTP_PORT', 587);
define('SMTP_USER', 'noreply@yk-lab.jp');
define('SMTP_PASS', 'u2d9T[.QlSG8');
define('SMTP_SECURE', PHPMailer::ENCRYPTION_STARTTLS);

//-------------------
//変数定義
//-------------------

//エラ〜メッセージ格納用
$err_msg = array();


//-------------------
//バリデーションチェック
//-------------------

//Eメール重複登録チェック
function validEmailDup($email) {
  try {
    //db接続
    $dbh = dbConnect();
    //SQL文作成
    $sql = 'SELECT count(*) FROM users WHERE email = :email AND delete_flg = 0';
    //dataセット
    $data = array(':email' => $email);
    //SQL実行
    $stmt = queryPost($dbh, $sql, $data);
    
    //結果を取り出し
    $result =  $stmt->fetch(PDO::FETCH_ASSOC);

    //重複判定
    if ($result['count(*)'] > 0) {
      global $err_msg;
      $err_msg['common'] = ERR_EMAIL_DUPLICATE;
    }
  } catch (Exception $e) {
    error_log('エラーが発生しました' . $e->getMessage());
    global $err_msg;
    $err_msg['common'] = ERR_SYSTEM;
  }
}

//未入力チェック
function validEmpty($str, $key) {
  if($str === '') {
    global $err_msg;
    $err_msg[$key] = ERR_REQUIRED;
    //debug('エラーチェック：' . print_r($err_msg,true));
  }
}

//英数字チェック(Eメールチェック用)
function validEmail($str, $key) {
  if (!preg_match("/^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/", $str)) {
    global $err_msg;
    $err_msg[$key] = ERR_EMAIL_FORMAT;
  }
}

//パスワード再入力の一致チェック
function validMatch($str, $str_re, $key) {

    global $err_msg;

    if ($key === 'pass_new') {  //パスワード変更時
      if($str === $str_re) {
        $err_msg[$key] = ERR_SAME_PASSWORD;
      }
    } else {                    //ユーザ登録時
      if($str !== $str_re) {
        $err_msg[$key] = ERR_PASSWORD_MISMATCH;
      }
    }
}

//パスワード一致チェック(パスワード変更時の古いパスワードチェック用)
function validOldPassMatch($str, $str_db, $key) {
  if (!password_verify($str, $str_db)) {
    global $err_msg;
    $err_msg[$key] = ERR_OLD_PASSWORD;
  }
}

//半角英数字チェック
function validHalf($str, $key) {
  if (!preg_match("/^[a-zA-Z0-9]+$/",$str)) {
    global $err_msg;
    $err_msg[$key] = ERR_HALF_ALPHANUMERIC;
  }
}

//最小文字数判定
function validMin($str, $key, $min = 6) {
  if (strlen($str) <= $min) {
    global $err_msg;
    if (strlen($str) < 6) {
      $err_msg[$key] = ERR_MIN_LENGTH;
    }
  }
}

//最大文字数
function validMax($str, $key, $max = 256) {
  global $err_msg;


  if (strlen($str) > $max) {
    $err_msg[$key] = sprintf(ERR_MAX_LENGTH, $max);
  }
}

//電話番号形式判定
function validTel($str, $key) {
  if (!preg_match('/0\d{1,4}\d{1,4}\d{4}/', $str)) {
    global $err_msg;
    $err_msg[$key] = ERR_TEL_FORMAT;
  }
}

//郵便番号判定
function validZip($str, $key) {
  if (!preg_match('/^\d{7}$/', $str)) {
    global $err_msg;
    $err_msg[$key] = ERR_ZIP_FORMAT;
  }
}

//半角数字判定
function validNum($str, $key) {
  if (!preg_match('/^[0-9]+$/', $str)) {
    global $err_msg;
    $err_msg[$key] = ERR_AGE;
  }
}

//年齢チェック
function validAge($str, $key, $min = 0, $max = 150) {
  if ($str < $min || $str > $max) {
    global $err_msg;
    $err_msg[$key] = ERR_AGE;
  }
}

//selectboxチェック
function validSelect($str, $key) {
  if(!preg_match('/^[0-9]+$/', $str)) {
    global $err_msg;
    $err_msg['common'] = ERR_CATEGORY;
  }
}

//性別チェック
function validGender($str, $key) {
  global $err_msg;

  if (!in_array($str, ['0', '1'], true)) {
      $err_msg[$key] = ERR_GENDER;
  }
}

//日付チェック
function validDate($str, $key) {
  if (!preg_match('/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/', $str)) {
    global $err_msg;
    $err_msg[$key] = ERR_DATE;
  }
}

//時間チェック
function validTime($str, $key) {
  if (!preg_match('/^([0-1][0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$/', $str)) {
    global $err_msg;
    $err_msg[$key] = ERR_TIME;
  }
}

//時間範囲チェック
function validTimeRange($start_time, $end_time, $key) {
  if ($start_time > $end_time) {
    global $err_msg;
    $err_msg[$key] = ERR_TIME_RANGE;
  }
}

//対象チェック
function validTarget($str, $key) {

  $targets = getTarget();
 
  foreach ($str as $val) {
    $jdg = false;
    foreach ($targets as $target) {
      //targetsテーブル内の値を登録しているか(true: 判定OK)
      if ((int)$target['id'] === (int)$val) {
        $jdg = true;
        break;
      }
    }
    if (!$jdg) {
      global $err_msg;
      $err_msg[$key] = ERR_CATEGORY;
      exit;
    }
  }
}

//-------------------
//DB接続関連
//-------------------
function dbConnect() {
  //dbへの接続準備
  $dsn = 'mysql:dbname=kenyu;host=localhost;charset=utf8';
  $user = 'root';
  $password = 'root';
  $options = array(
    // SQL実行失敗時にはエラーコードのみ設定
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    // デフォルトフェッチモードを連想配列形式に設定
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    // バッファードクエリを使う(一度に結果セットをすべて取得し、サーバー負荷を軽減)
    // SELECTで得た結果に対してもrowCountメソッドを使えるようにする
    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
  );
  //PDOオブジェクト生成
  $dbh = new PDO($dsn, $user, $password, $options);
  return $dbh;
}

function queryPost($dbh, $sql, $data) {
  //クエリ作成
  $stmt = $dbh->prepare($sql);
  
  //プレースホルダに値をセットし、SQL実行
  if (!$stmt->execute($data)) {
    debug('クエリに失敗しました');
    debug('失敗したクエリ：' . print_r($stmt,true));
    $err_msg['common'] = ERR_SYSTEM;
    return 0;
  } else {
    debug('クエリ成功');
    return $stmt;
  }
}

//-------------------
//ユーザ情報取得(単品)
//-------------------
function getUserInfoOne($u_id) {
  debug('getUserInfo：ユーザ情報を取得します');

  try {
    //db接続
    $dbh = dbConnect();
    //SQL作成
    $sql = 'SELECT * FROM users WHERE id = :u_id AND delete_flg = 0';
    //Dataセット
    $data = array(':u_id' => $u_id);
    //SQL実行
    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      debug('ユーザ情報の取得に成功しました');
      return $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
      debug('ユーザ情報の取得に失敗しました');
      return false;
    }
  } catch (Exception $e) {
    error_log('エラーが発生しました' . $e->getMessage());
    global $err_msg;
    $err_msg['common'] = ERR_SYSTEM;
  }
}
//-------------------
//ユーザ情報取得(すべて)
//-------------------
function getUserInfo() {
  try {
    //db接続
    $dbh = dbConnect();
    //sql作成
    $sql = 'SELECT id, name, email, pic, password, delete_flg FROM users';
    //dataセット
    $data = array();
    //sql実行
    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      debug('ユーザ情報(すべて)の取り出しOK');
      return $stmt->fetchALL();
    } else {
      debug('ユーザ情報(すべて)の取り出しNG');
      return false;
    }
  } catch (Exception $e) {
    error_log('エラーが発生しました' . $e->getMessage());
    global $err_msg;
    $err_msg['common'] = ERR_SYSTEM;
  }
}

//-------------------
//サニタイズ
//-------------------
function sanitize($str) {

  //配列の場合は再帰的にサニタイズ
  if (is_array($str)) {
    return $str;
  }
  return (htmlspecialchars($str, ENT_QUOTES));
}
//-------------------
//入力フォーム補助
//-------------------
function getFormData($str, $flg = false) {
  global $dbFormData;
  
  //POSTかGetかの識別
  if ($flg === true) {
    $data = $_GET;
  } else {
    $data = $_POST;
  }
  
  if (!empty($dbFormData[$str])) {
    if (!empty($err_msg[$str])) {
      if (isset($data[$str])) { //郵便番号など、0が入る可能性があるのでここはissetで判定
        return sanitize($data[$str]);
      } else {
        return sanitize($dbFormData[$str]);
      }
    } else {
      if (isset($data[$str]) && $data[$str] !== $dbFormData[$str]) {
        return sanitize($data[$str]);
      } else {  //そもそも変更していないときはここにくる
        return sanitize($dbFormData[$str]);
      }
    }
  } else {
    if (isset($data[$str])) {
      return sanitize($data[$str]);
    }
  }
}

//---------------------------
//入力フォーム補助(イベント対象用)
//---------------------------
function getFormDataTarget($str, $id, $dbTargetData) {

  //POST送信後はPOST値を優先
  if (isset($_POST[$str])) {
    return in_array($id, $_POST[$str]);
  }

  debug('dbTargetDataの値：' . print_r($dbTargetData,true));
  debug('idの値：' . print_r($id,true));
  debug('strの値：' . print_r($str,true));
  
  //初回表示時はDB値を使用
  return in_array($id, $dbTargetData);

}

//-------------------
//入力フォーム補助（時間用）
//-------------------
function getFormDataTime($str, $part) {
  global $dbFormData;
  
  //debug('$_POSTの値：' . print_r($_POST, true));
  //debug('dbFormDataの値：' . print_r($dbFormData[$part], true));

  //POST値があればPOSTを優先
  if (isset($_POST[$str])) {
    return sanitize($_POST[$str]);
  }

  //DB値がなければ空文字
  if (empty($dbFormData[$part])) {
    return '';
  }

  //DBのTIME型（例：19:15:00）を分解
  if (strpos($str, 'hour') !== false) {
    return substr($dbFormData[$part], 0, 2);
  }

  if (strpos($str, 'minute') !== false) {
    return substr($dbFormData[$part], 3, 2);
  }

  return '';
}

//-------------------
//エラー表示
//-------------------
function getErrInfo($str) {

  global $err_msg;
  if (!empty($err_msg[$str])) {
    return $err_msg[$str];
  } else {
    return '';
  }
}

//-------------------
//メール送信
//-------------------
function sendMail($from, $to, $subject, $comments) {

  if (!empty($from) && !empty($to) && !empty($subject) && !empty($comments)) {

    try {

      $mail = new PHPMailer(true);
      $mail->isSMTP();

      $mail->Host = SMTP_HOST;
      $mail->SMTPAuth = true;
      $mail->Username = SMTP_USER;
      $mail->Password = SMTP_PASS;
      $mail->SMTPSecure = SMTP_SECURE;
      $mail->Port = SMTP_PORT;

      $mail->CharSet = 'UTF-8';

      $mail->setFrom($from, APL_NAME);
      $mail->addAddress($to);

      $mail->Subject = $subject;
      $mail->Body = $comments;

      $mail->send();

      debug('メール送信OK');

    } catch (Exception $e) {

      debug('メール送信NG');
      debug($mail->ErrorInfo);

    }

  }

}

/* function sendMail($from, $to, $subject, $comments) {
  if (!empty($from) && !empty($to) && !empty($subject) && !empty($comments)) {
    //文字化けしないように設定（お決まりパターン）
    mb_language("Japanese"); //現在使っている言語を設定する
    mb_internal_encoding("UTF-8"); //内部の日本語をどうエンコーディング（機械が分かる言葉へ変換）するかを設定

    //メール送信
    $result = mb_send_mail($to, $subject, $comments, "From: ".$from);

    if ($result) {
      debug('メール送信OK');
    } else {
      debug('メール送信NG');
    }
  }
}
 */
//-------------------
//ポップアップメッセージ処理
//-------------------
function getSessionMessage($str) {
  debug('セッションに入っているメッセージを取り出します');
  if (!empty($_SESSION[$str])) {
  //セッションに入っているメッセージを取り出す
  $data = $_SESSION[$str];
  
  //セッションに入っているメッセージの削除
    if ($str === SUCCESS_WITHDRAW) {
      debug('退会時');
      session_destroy();
      $_SESSION = array();
    } else {
      debug('通常時');
      $_SESSION[$str] = '';
    }

  return $data;
  }
}

//-------------------
//一時パスワード生成
//-------------------
function makeRandomKey($length = 8) {
  
  $tmp = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJLKMNOPQRSTUVWXYZ0123456789';
  $str = '';

  for ($i=0; $i<$length; $i++) {
    $str.=$tmp[mt_rand(0,61)];
  }

  return $str;
}

//-------------------
//カテゴリ取得
//-------------------
function getCategory() {
  try {
    //dbh接続
    $dbh = dbConnect();
    //sql作成
    $sql = 'SELECT id, name FROM categories';
    //data設定
    $data = array();
    //sql実行
    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      debug('カテゴリデータ取り出しOk');
      return $stmt->fetchAll();
    } else {
      debug('カテゴリデータ取り出しNG');
      return false;
    }

  } catch (Exception $e) {
    error_log('エラーが発生しました:'. $e->getMessage());
    global $err_msg;
    $err_msg['common'] = ERR_SYSTEM;
  }
}

//-------------------
//都道府県データ取得
//-------------------
function getPrefecture() {
  try {
    //dbh接続
    $dbh = dbConnect();
    //sql作成
    $sql = 'SELECT id, name FROM prefectures';
    //data設定
    $data = array();
    //sql実行
    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      debug('都道府県データ取り出しOk');
      return $stmt->fetchAll();
    } else {
      debug('都道府県データ取り出しNG');
      return false;
    }

  } catch (Exception $e) {
    error_log('エラーが発生しました:'. $e->getMessage());
    global $err_msg;
    $err_msg['common'] = ERR_SYSTEM;
  }
}

//-------------------
//市町村データ取得
//-------------------
function getCity() {
  try {
    //dbh接続
    $dbh = dbConnect();
    //sql作成
    $sql = 'SELECT id, name FROM cities';
    //data設定
    $data = array();
    //sql実行
    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      debug('市町村データ取り出しOk');
      return $stmt->fetchAll();
    } else {
      debug('市町村データ取り出しNG');
      return false;
    }

  } catch (Exception $e) {
    error_log('エラーが発生しました:'. $e->getMessage());
    global $err_msg;
    $err_msg['common'] = ERR_SYSTEM;
  }
}

//-------------------
//対象取得
//-------------------
function getTarget() {
  try {
    //dbh接続
    $dbh = dbConnect();
    //sql作成
    $sql = 'SELECT id, name FROM targets';
    //data設定
    $data = array();
    //sql実行
    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      debug('対象データ取り出しOk');
      return $stmt->fetchAll();
    } else {
      debug('対象データ取り出しNG');
      return false;
    }

  } catch (Exception $e) {
    error_log('エラーが発生しました:'. $e->getMessage());
    global $err_msg;
    $err_msg['common'] = ERR_SYSTEM;
  }
}

//-------------------
//当該イベントの対象取得
//-------------------
function getEventTarget($id) {
  try {
    //dbh接続
    $dbh = dbConnect();
    //sql作成
    $sql = 'SELECT target_id from event_targets where event_id = :id';
    //data設定
    $data = array(':id' => $id);
    //sql実行
    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      debug('対象データ取り出しOk');
      return $stmt->fetchAll();
    } else {
      debug('対象データ取り出しNG');
      return false;
    }
  } catch (Exception $e) {
    error_log('エラーが発生しました:'. $e->getMessage());
    global $err_msg;
    $err_msg['common'] = ERR_SYSTEM;
  }
}

//---------------------------------
//当該イベントの掲示板のメッセージ数を取得
//---------------------------------
function getMessageCount($event_id) {
  debug('掲示板のメッセージ数を取得します');

  try {
    //db接続
    $dbh = dbConnect();
    //sql作成
    $sql ='SELECT count(*) FROM messages WHERE board_id = (SELECT id FROM boards WHERE event_id = :event_id) AND delete_flg = 0';
    //data設定
    $data = array(':event_id' => $event_id);
    //sql実行
    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      debug('メッセージ数の取得に成功しました');
      return $stmt->fetchColumn();
    } else {
      debug('メッセージ数の取得に失敗しました');
      return false;
    }
  } catch (Exception $e) {
    error_log('エラーが発生しました:'. $e->getMessage());
    global $err_msg;
    $err_msg['common'] = ERR_SYSTEM;
  }
}

//---------------------------------
//当該イベントのお気に入り数を取得
//---------------------------------
function getLikeCount($event_id) {
  debug('お気に入り数を取得します');

  try {
    //db接続
    $dbh = dbConnect();
    //sql作成
    $sql ='SELECT count(*) FROM favorites WHERE event_id = :event_id';
    //data設定
    $data = array(':event_id' => $event_id);
    //sql実行
    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      debug('お気に入り数の取得に成功しました');
      return $stmt->fetchColumn();
    } else {
      debug('お気に入り数の取得に失敗しました');
      return false;
    }
  } catch (Exception $e) {
    error_log('エラーが発生しました:'. $e->getMessage());
    global $err_msg;
    $err_msg['common'] = ERR_SYSTEM;
  }
}
//-------------------
//画像アップロード
//-------------------
function uploadImg($file, $key) {
  debug('画像アップロード処理開始');
  debug('ファイル情報:' . print_r($file,true));

  try {
    //バリデーションチェック
    switch($file['error']) {
      case UPLOAD_ERR_OK:
        break;
      case UPLOAD_ERR_NO_FILE:
        throw new RuntimeException('ファイルが選択されていません');
      case UPLOAD_ERR_INI_SIZE:
      case UPLOAD_ERR_FORM_SIZE:
        throw new RuntimeException('ファイルサイズが大きすぎます');
      default:
        throw new RuntimeException('その他エラーが発生しました');
    }

    //MIMEタイプチェック
    $type = @exif_imagetype($file['tmp_name']);

    //画像ファイルであるかのチェック
    if(!in_array($type, [IMAGETYPE_GIF, IMAGETYPE_PNG, IMAGETYPE_JPEG], true)) {
      throw new RuntimeException('画像ファイルが不正です');
    }

    //ファイル名をハッシュ化し、保存
    $path = 'uploads/'.sha1_file($file['tmp_name']).image_type_to_extension($type);

    //ファイルを移動する
    if (!move_uploaded_file($file['tmp_name'], $path)) {
      throw new RuntimeException('ファイル保存時にエラーが発生しました');
    }

    //保存したファイルのパーミッションを変更
    chmod($path, 0644);

    debug('ファイルは正常にアップロードされました');
    debug('ファイルパス：' . $path);

    return $path;

  } catch(Exception $e) {
    error_log('エラーが発生しました' . $e->getMessage());
    global $err_msg;
    $err_msg[$key] = $e->getMessage();
  }
}

//イベント対象情報に変更があるか判定
function isTargetChanged($dbTargetData, $target) {

  //配列の中身を整数に変換して比較する
  $target = array_map('intval', $target);
  $dbTargetData = array_map('intval', $dbTargetData);

  sort($dbTargetData);
  sort($target);

  return $dbTargetData !== $target;
}

//-------------------
//推し情報の取得(単品)
//-------------------
function getEventOneInfo($e_id) {
  debug('DBに登録されたイベント情報(単品)を取得');

  try {
    //db接続
    $dbh = dbConnect();
    //sql作成
    $sql = 'SELECT * FROM events WHERE id = :e_id';
    //dataセット
    $data = array(':e_id' => $e_id);
    //sql実行
    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      debug(APL_SUBJECT . '情報取り出しOK');
      return $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
      debug(APL_SUBJECT . '情報取り出しNG');
      return false;
    }
  } catch(Exception $e) {
    error_log('エラーが発生しました' . $e->getMessage());
    global $err_msg;

    $err_msg['common'] = ERR_SYSTEM;
  }
}
//-------------------
//自分が登録した情報の取得
//-------------------
function getMyEventList($u_id) {
  debug('自分が登録した情報一覧を取得します');

  try {
    //db接続
    $dbh = dbConnect();
    //sql作成
    $sql = 'SELECT id, name, description, event_date, start_time, end_time, pic1, update_date FROM events WHERE user_id = :u_id';
    //dataセット
    $data = array(':u_id' => $u_id);
    //sql実行
    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      debug('自分が登録した情報一覧の取得OK');
      return $stmt->fetchALL();
    } else {
      debug('自分が登録した情報一覧の取得NG');
      return false;
    }
  } catch (Exception $e) {
    error_log('エラーが発生しました' . $e->getMessage());
    global $err_msg;
    $err_msg['common'] = ERR_SYSTEM;
  }
}

//-------------------
//イベント情報の取得(複数)
//-------------------
function getEventList($list_span = 20, $display_min = 1, $category, $prefecture, $sort) {
  debug('DBに登録された . APL_SUBJECT . 情報を取得');

  try {
    //-----------
    //全レコード取得
    //-----------
    //db接続
    $dbh = dbConnect();
    //sql作成
    $sql = 'SELECT e.id, e.name, e.category_id, e.prefecture_id, e.description, e.event_date, e.start_time, e.end_time, e.pic1, e.pic2, e.pic3, c.name as category_name, p.name as prefecture_name FROM events e LEFT JOIN categories c ON e.category_id = c.id LEFT JOIN prefectures p ON e.prefecture_id = p.id WHERE delete_flg = 0 ';

    //検索リクエストがあるか(カテゴリ)
    if ($category != 0) {
      $sql .= ' && category_id = :category_id';
      $data = array(':category_id' => $category);
    } else {
      $data = array();
    }

    //検索リクエストがあるか(都道府県)
    if ($prefecture != 0) {
      $sql .= ' && prefecture_id = :prefecture_id';
      $data = array(':prefecture_id' => $prefecture);
    } else {
      $data = array();
    }

    //検索リクエストがあるか(ソート)
    switch ($sort) {
      case 1://登録日付が新しい順
        $sql .= ' ORDER BY create_date DESC';
        break;
      case 2://最近編集された順
        $sql .= ' ORDER BY update_date DESC';
        break;
      default:
        break;
    }

    //sql実行
    $stmt = queryPost($dbh, $sql, $data);

    if (!$stmt) {
      debug('すべての推し情報を取り出しNG');
      return false;
    }

    //全レコード数を取得
    $rst['total_record'] = $stmt->rowCount();
    //トータルのページ数
    $rst['total_page'] = ceil($rst['total_record'] / $list_span);

    //----------------------------
    //ページネーションで必要な数だけ取得
    //----------------------------
    $sql .= ' LIMIT '.$list_span.' OFFSET '.$display_min;

    $stmt = queryPost($dbh, $sql, $data);

    if (!$stmt) {
      debug('ページネーションの推し情報を取り出しNG');
      return false;
    }

    //全レコードを取得
    $rst['data'] = $stmt->fetchALL();
    //sql実行
    $stmt = queryPost($dbh, $sql, $data);

    //debug('イベント情報の全レコードとレコード数：' . print_r($rst,true));

    return $rst;

  } catch (Exception $e) {
    error_log('エラーが発生しました' . $e->getMessage());
    global $err_msg;
    $err_msg['common'] = ERR_SYSTEM;
  }
}

//-------------------
//推し情報の取得(単品)
//-------------------
function getEventOne($e_id) {
  debug(APL_SUBJECT . '情報の取得(単品)を取得します');
  try {
    //db接続
    $dbh = dbConnect();
    //sql作成
    $sql = 'SELECT
              p.id,
              p.name,
              p.category_id,
              p.prefecture_id,
              p.description,
              p.user_id,
              p.event_date,
              p.start_time,
              p.end_time,
              p.pic1,
              p.pic2,
              p.pic3,
              c.name AS category,
              pf.name AS prefecture
          FROM events AS p
          INNER JOIN categories AS c
              ON p.category_id = c.id
          INNER JOIN prefectures AS pf
              ON p.prefecture_id = pf.id
          WHERE p.id = :e_id';

    //dataセット
    $data = array(':e_id' => $e_id);
    //sql実行
    $stmt = queryPost($dbh, $sql, $data);

    if($stmt) {
      debug('推し情報の取得(単品) OK');
      return $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
      debug('推し情報の取得(単品) NG');
      $err_msg['common'] = ERR_SYSTEM;
      return false;
    }

  } catch (Exception $e) {
    error_log('エラーが発生しました' . $e->getMessage());
    global $err_msg;
    $err_msg['common'] = ERR_SYSTEM;
  }
}


//-------------------
//ページネーション
//-------------------
/*
$now:現在のページ
$p_max:総ページ数(総レコード数 / 1ページに表示する項目数)
$icon_num:ページネーションのアイコンの表示数
*/
function pagenation($now, $p_max, $icon_num, $link='') {

  //----------------------------------------
  //ページネーションのアイコン内に表示する数字の算出
  //----------------------------------------
  // 現在のページが、総ページ数と同じ　かつ　総ページ数が表示項目数以上なら、左にリンク４個出す
  if ($now == $p_max && $p_max >= $icon_num) {
    $min_page = $now - 4;
    $max_page = $now;
  // 現在のページが、総ページ数の１ページ前なら、左にリンク３個、右に１個出す
  } else if ($now == $p_max - 1 && $p_max >= $icon_num) {
    $min_page = $now - 3;
    $max_page = $now + 1;
  // 現ページが2の場合は左にリンク１個、右にリンク３個だす。
  } else if ($now == 2 && $p_max >= $icon_num) {
    $min_page = $now - 1;
    $max_page = $now + 3;
  // 現ページが1の場合は左に何も出さない。右に５個出す。
  } else if ($now == 1 && $p_max >= $icon_num) {
    $min_page = $now;
    $max_page = $now + 4;
  // 総ページ数が表示項目数より少ない場合は、総ページ数をループのMax、ループのMinを１に設定
  } else if ($p_max < $icon_num) {
    $min_page = 1;
    $max_page = $p_max;
  } else {
  // それ以外は左に２個出す。
    $min_page = $now - 2;
    $max_page = $now + 2;
  }
  
  //--------------------------------------------
  //ページネーションのアイコンの数字の出力
  //--------------------------------------------
  echo '<div class="pagination">';
    echo '<ul class="pagination-list">';
      //ページネーションのアイコンの左端に"<"をつけるかの判定
      if ($now != 1) {
        echo '<li class="list-item"><a href="?p=1'.$link.'">&lt</a></li>';
      }
      for ($i=$min_page; $i<=$max_page; $i++ ) {
        echo '<li class="list-item ';
        //今どこのページにいるかわかるように、今いるアイコンを黒にする
        if ($i == $now) {
          echo 'active';
        }
        echo ' "><a href="?p='.$i.$link.'">'.$i.'</a></li>';
      }
      //ページネーションのアイコンの左端に">"をつけるかの判定
      if ($now != $p_max) {
        //echo '<li class="list-item"><a href="?p='.$max_page.'">&gt</a></li>';
        echo '<li class="list-item"><a href="?p='.$max_page.$link.'">&gt</a></li>';
      }
    echo '</ul>';
  echo '</div>';

}

/*
・Get送信のURL生成用
・例：http://localhost:8888/output/2.webservice_output/index.php?category_id=1&sort=2
　　上記の「category_id=1&sort=2」部分を生成する
・取り出すGETの例は以下。
[25-Jun-2022 06:52:57 Asia/Tokyo] デバッグ Getの値：Array
(
    [category_id] => 1
    [sort] => 2
)
*/
function appendGetParam($arr_del_key = array()) {
  debug('GET送信部分のURLを算出します');

  if (!empty($_GET)) {
    $str = '&';

    foreach ($_GET as $key => $val) {
      //引数とおなじ配列要素名じゃなかったら文字列を連結する
      if (!in_array($key, $arr_del_key, true)) {
        $str .= $key.'='.$val.'&';
      }
    }
    //最後に&がついてしまっているので削除
    $str = mb_substr($str, 0 , -1);

    return $str;
  }
}

//---------
//画像表示用
//---------
function showImg($img) {
  if (!empty($img)) {
    return $img;
  } else {
    return 'img/sample-img.png';
  }
}

//------------
//掲示板情報取得
//------------
function getBordInfo($e_id) {
  debug('掲示板情報取得');

  try {
    //db接続
    $dbh = dbConnect();
    //sql作成
    $sql = 'SELECT id, event_id FROM boards where event_id = :e_id';
    //dataセット
    $data = array(':e_id' => $e_id);
    //sql実行
    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      debug('掲示板情報取得OK');
      return $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
      debug('掲示板情報取得NG');
      return false;
    }
  } catch (Exception $e) {
    error_log('エラーが発生しました' . $e->getMessage());
    global $err_msg;
    $err_msg['common'] = ERR_SYSTEM;
  }
}
//-------------------------
//じぶんが登録した掲示板情報取得
//-------------------------
function getMyBordInfo($u_id) {
  debug('じぶんが登録した掲示板情報を取得します');

  try {
    //db接続
    $dbh = dbConnect();
    //sql作成
    $sql = 'SELECT id, user_id FROM boards WHERE user_id = :u_id';
    //dataセット
    $data = array(':u_id' => $u_id);
    //sql実行
    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      debug('じぶんが登録した掲示板情報取得OK');
      return $stmt->fetchALL();
    } else {
      debug('じぶんが登録した掲示板情報取得NG');
      return false;
    }

  } catch (Exception $e) {
    error_log('エラーが発生しました' . $e->getMessage());
  }
}

//------------
//メッセージ取得
//------------
function getMessageInfo($b_id) {
  debug('当該掲示板に書かれたメッセージを取得します');
  debug('取得するボードID：' . $b_id);

  try {
    //db接続
    $dbh = dbConnect();
    //sql作成
    $sql ='SELECT from_user, to_user, content, create_date FROM messages WHERE board_id = :b_id';
    //dataセット
    $data = array(':b_id' => $b_id);
    //sql実行
    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      debug('当該掲示板に書かれたメッセージ取得OK');
      return $stmt->fetchALL();
    } else {
      debug('当該掲示板に書かれたメッセージ取得NG');
      return false;
    }

  } catch (Exception $e) {
    error_log('エラーが発生しました' . $e->getMessage());
    global $err_msg;
    $err_msg['common'] = ERR_SYSTEM;
  }
}

//----------------------------------
//自分がアップした推し情報の最新掲示板情報を取得
//----------------------------------
function getMybordMessage($u_id) {
  debug('自分がアップした掲示板の最新情報を取得します');

   try {
    //db接続
    $dbh = dbConnect();
    //sql実行
    $sql = 'SELECT b.id, b.user_id, b.event_id,m.from_user, m.to_user, m.content, m.update_date
            FROM boards as b RIGHT JOIN messages as m ON b.id = m.board_id
            WHERE b.user_id = :u_id ORDER BY m.update_date DESC';
    //dataセット
    $data = array(':u_id' => $u_id);
    //sql実行
    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      debug('自分がアップした掲示板の最新情報の取得OK');
      return $stmt->fetchALL();
    } else {
      debug('自分がアップした掲示板の最新情報の取得NG');
      return false;
    }
  } catch (Exception $e) {
    error_log('エラーが発生しました' . $e->getMessage());
    global $err_msg;
    $err_msg['common'] = ERR_SYSTEM;
  }
 }
//-------------------
//お気に入り機能(取得:すべて)
//-------------------
function getLikeInfo($u_id) {
  debug('自分のお気に入り情報をアップデート順取得します');

  try {
    //db接続
    $dbh = dbConnect();
    //sql実行
    $sql = 'SELECT l.event_id, l.user_id, l.create_date, p.name, p.pic1 FROM favorites as l LEFT JOIN events as p ON l.event_id = p.id WHERE l.user_id = :u_id ORDER BY l.create_date DESC';
    //dataセット
    $data = array(':u_id' => $u_id);
    //sql実行
    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      debug('自分のお気に入り情報取り出しOK');
      return $stmt->fetchALL();
    } else {
      debug('自分のお気に入り情報取り出しNG');
      return false;
    }

  } catch (Exception $e) {
    error_log('エラーが発生しました' . $e->getMessage());
    global $err_msg;
    $err_msg['common'] = ERR_SYSTEM;
  }
}
//-------------------
//お気に入り機能(取得:単品)
//-------------------
function isLike($e_id, $u_id, $flg = 0) {
  debug('お気に入り情報を取得します');
  debug('e_id:' . $e_id);
  debug('u_id:' . $u_id);
  try {
    //db接続
    $dbh = dbConnect();
    //sql作成
    $sql = 'SELECT event_id, user_id FROM favorites WHERE event_id = :e_id && user_id = :u_id';
    //dataセット
    $data = array(':e_id' => $e_id, 'u_id' => $u_id);
    //sql実行
    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      debug('お気に入り情報の取得OK');
      return $stmt->rowCount();
    } else {
      debug('お気に入り情報取得NG');
      return false;
    }
  } catch (Exception $e) {
    error_log('エラーが発生しました' . $e->getMessage());
    global $err_msg;
    $err_msg['common'] = ERR_SYSTEM;
  }
}
//-------------------
//お気に入り機能（削除）
//-------------------
function likeDelete($e_id, $u_id) {
  debug('お気に入りデータを削除します');
  debug('お気に入り情報ID:' . $e_id);
  debug('ユーザ情報ID:' . $u_id);

  try {
    //db接続
    $dbh = dbConnect();
    //sql実行
    $sql = 'DELETE FROM favorites WHERE event_id = :e_id && user_id = :u_id';
    //dataセット
    $data = array(':e_id' => $e_id, ':u_id' => $u_id);
    //sql実行
    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      debug('お気に入りデータ削除OK');
    } else {
      debug('お気に入りデータ削除NG');
    }
  } catch(Exception $e) {
    error_log('エラーが発生しました' . $e->getMessage());
    global $err_msg;
    $err_msg['common'] = ERR_SYSTEM;
  }
}
//-------------------
//お気に入り機能（登録）
//-------------------
function likeRegister($e_id, $u_id) {
  debug('お気に入りを登録します');
  debug('お気に入り情報ID:' . $e_id);
  debug('ユーザ情報ID:' . $u_id);
  try {
    //db接続
    $dbh = dbConnect();
    //sql作成
    $sql = 'INSERT INTO favorites (event_id, user_id, create_date) VALUES (:e_id, :u_id, :create_date)';
    //dataセット
    $data = array(':e_id' => $e_id, ':u_id' => $u_id, ':create_date' => date('Y-m-d H:i:s'));
    //sql実行
    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      debug('お気に入り情報登録OK');
    } else {
      debug('お気に入り情報登録NG');
    }
  } catch (Exception $e) {
    error_log('エラーが発生しました' . $e->getMessage());
    global $err_msg;
    $err_msg['common'] = ERR_SYSTEM;
  }

}

//-------------------------------
//一度削除してからの再登録の際の復活処置
//-------------------------------
function againSignUpCalc($u_id) {
  debug('削除したアカウントを再登録した際のデータ復活処置をおこないます');
  debug('対象ユーザID：' . $u_id);

  //再登録したユーザが登録していた推し情報を取得
  $eventInfo = getMyEventList($u_id);
  //再登録したユーザが登録していたメッセージを取得
  $messageInfo = getMybordMessage($u_id);
  //再登録したユーザが登録していたお気に入りを取得
  $likeInfo = getLikeInfo($u_id);
  //再登録したユーザが登録していた掲示板を取得
  $bordInfo = getMyBordInfo($u_id);

  try {
    //db接続
    $dbh = dbConnect();
    //sql実行
    $sql1 = 'UPDATE events SET delete_flg = 0 WHERE user_id = :u_id';
    $sql2 = 'UPDATE messages SET delete_flg = 0 WHERE from_user = :u_id';
    $sql3 = 'UPDATE messages SET delete_flg = 0 WHERE to_user = :u_id';
    //$sql4 = 'UPDATE favorites SET delete_flg = 0 WHERE user_id = :u_id';
    $sql5 = 'UPDATE boards SET delete_flg = 0 WHERE user_id = :u_id';
    //dataセット
    $data = array(':u_id' => $u_id);
    //sql実行
    $stmt1 = queryPost($dbh, $sql1, $data);
    $stmt2 = queryPost($dbh, $sql2, $data);
    $stmt3 = queryPost($dbh, $sql3, $data);
    //$stmt4 = queryPost($dbh, $sql4, $data);
    $stmt5 = queryPost($dbh, $sql5, $data);

    if ($stmt1 && $stmt2 && $stmt3 && $stmt4 && $stmt5) {
      debug('削除したアカウントを再登録した際のデータ復活処置OK');
    } else {
      debug('削除したアカウントを再登録した際のデータ復活処置NG');
    }
  } catch (Exception $e) {
    error_log('エラーが発生しました' . $e->getMessage());
    global $err_msg;
    $err_msg['common'] = ERR_SYSTEM;
  }
}
//-------------------------------
//日付を日本語表記に変換する
//-------------------------------
function dateFormat($date) {
  debug('日付を日本語表記に変換します');

    $week = ['日', '月', '火', '水', '木', '金', '土'];
    $timestamp = strtotime($date);

    return date('Y年n月j日', $timestamp). '('. $week[date('w', $timestamp)]. ')';
}
//-------------------------------
//時間帯を日本語表記に変換する
//-------------------------------
function timeFormat($start, $end) {
  debug('時間帯を日本語表記に変換します');

  if (empty($start)) {
    return '時間未定';
  }

  $time = substr($start, 0, 5);

  if (!empty($end)) {
    $time .= '〜' . substr($end, 0 ,5); 
  }

  return $time;


}

