<?php
//共通処理よびだし
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　passRemindRecieve.php：パスワード再発行認証');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//post送信があるか
if (!empty($_POST)) {
  debug('ポスト送信あり');

  //ポスト送信された情報の取得
  $token = $_POST['token'];
  
  //一時パスワードと、入力された認証コードの一致を確認
  if ($_SESSION['tmp_key'] !== $token) {
    $err_msg['token'] = MSG15;
  }
  debug('一時パスワード：' . $_SESSION['tmp_key']);
  debug('入力された承認コード' . $token);

  //入力時期が期限前か確認
  if (time() > $_SESSION['tmp_login_limit']) {
    $err_msg['token'] = MSG16;
  }
  debug('現在時刻' . time());
  debug('承認期限：' . $_SESSION['tmp_login_limit']);

  if (empty($err_msg)) {
    debug('認証コードOK');

    //リセット用のパスワード生成
    $pass = makeRandomKey();
    debug('リセット用のパスワード:' . $pass);
     
    //----------------------------------
    //リセット用パスワードをメールで通知する処理
    //----------------------------------

    $from = ML_FROM;
    $to = $_SESSION['tmp_email'];
    $subject = APL_NAME.'ログインパスワードを初期化しました';
    $comments = <<<EOF
    本メールアドレス宛にパスワードの再発行を致しました。
    下記のURLにて再発行パスワードをご入力頂き、ログインください。
    
    ログインページ：http://localhost:8888/output/2.webservice_output/login.php
    再発行パスワード：{$pass}
    ※ログイン後、パスワードのご変更をお願い致します
    EOF;

    //メール送信処理
    sendMail($from, $to, $subject, $comments);
    
    //-----------------------------------------
    //当該ユーザのパスワードをリセット用パスワードに変更
    //-----------------------------------------
    try {
      //db接続
      $dbh = dbConnect();
      //sql作成
      $sql = 'UPDATE users SET password = :pass WHERE email = :email';
      //data作成
      $data = array(':email' => $_SESSION['tmp_email'], ':pass' => password_hash($pass,PASSWORD_DEFAULT) );

      //sql実行
      $stmt = queryPost($dbh, $sql, $data);

      if ($stmt) {
        debug('パスワード初期化OK');
        debug('Eメール:' . $_SESSION['tmp_email']);
        debug('パスワード:' . $pass);

        //セッションクリア
        session_unset();

        //セッションにメッセージ挿入
        $_SESSION['msg-success'] = SUC02;
        debug('セッションの値：' . print_r($_SESSION,true));

        //ログイン画面へ遷移
        header('Location:login.php');
        exit();

      } else {
        debug('パスワード初期化NG');
      }


    } catch (Exception $e) {
      error_log('エラーが発生しました' . $e->getMessage());
      $err_msg['common'] = MSG07;
    }



  } else {
    debug('認証コードNG');
  }
}

?>

<?php
  $siteTitle = 'パスワード再発行認証';
  require('head.php');
?>
  <body class="page-signup page-1colum">
    <!-- メッセージ表示 -->
    <div id="js-show-msg" style="display: none;" class="msg-slide">
      <?php echo getSessionMessage('msg-success'); ?>
    </div>

    <!-- メニュー -->
    <?php require('header.php'); ?>

    <!-- メインコンテンツ -->
    <div id="contents" class="site-width">

      <!-- Main -->
      <section id="main" >

        <div class="form-container">

          <form action="" class="form" method="post">
            <p>ご指定のメールアドレスお送りした【パスワード再発行認証メール】内にある「認証キー」をご入力ください。</p>
            <div class="area-msg">
              <?php echo getErrInfo('common'); ?>
            </div>

            <label class="<?php if(!empty($err_msg['token'])) echo 'err'; ?>">
              認証キー
              <input type="text" name="token" value="<?php echo getFormData('token'); ?>">
            </label>
            <div class="area-msg">
              <?php  echo getErrInfo('token'); ?>
            </div>

            <div class="btn-container">
              <input type="submit" class="btn btn-mid" value="変更画面へ">
            </div>
          </form>
        </div>
        <a href="passRemindSend.php">&lt; パスワード再発行メールを再度送信する</a>
      </section>

    </div>

    <!-- footer -->
    <?php  require('footer.php'); ?>