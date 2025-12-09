<?php
//共通関数呼び出し
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　passRemindSend.php：パスワード再発行');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

if (!empty($_POST)) {
  debug('ポスト送信あり');

  //------------------
  //バリデーションチェック
  //------------------

  //ポスト送信された情報を取得
  $email = $_POST['email'];

  //Eメール形式かチェック
  validEmail($email, 'email');
  
  //最大文字数
  validMax($email, 'email');

  //空欄チェック
  validEmpty($email, 'email');

  if (empty($err_msg)) {
    debug('バリデーションOK');

    //----------------------------------
    //入力されたアドレスがユーザであるかの確認
    //----------------------------------
    try {
      //db接続
      $dbh = dbConnect();
      //sql作成
      $sql = 'SELECT count(*) FROM users WHERE email = :email';
      //data作成
      $data = array(':email' => $email);
      //sql実行
      $stmt = queryPost($dbh, $sql, $data);
      debug('実行結果：' . print_r($stmt,true));
      //結果取り出し
      $result = $stmt->fetch(PDO::FETCH_ASSOC);
      //var_dump(array_shift($result));

      if ($stmt && array_shift($result)) {
        debug('該当ユーザあり');
        
        //認証キーを生成
        $rand_key = makeRandomKey();
        debug('生成した一時キー：' . $rand_key);

        //----------------------------------
        //パスワード変更メール処理
        //----------------------------------
        $from = ML_FROM;
        $to = $email;
        $subject = APL_NAME.'パスワードを再発行しました';
        $contents = <<<EOF
        本メールアドレス宛にパスワード再発行のご依頼がありました。
        下記のURLにて認証キーをご入力頂くとパスワードが再発行されます。
        
        パスワード再発行認証キー入力ページ：http://localhost:8888/output/2.webservice_output/passRemindSend.php
        認証キー：{$rand_key}
        ※認証キーの有効期限は30分となります
        
        認証キーを再発行されたい場合は下記ページより再度再発行をお願い致します。
        http://localhost:8888/output/2.webservice_output/passRemindSend.php
        EOF;

        //メール送信処理
        sendMail($from, $to, $subject, $contents);

        //----------------------------------
        //セッション保存
        //----------------------------------
        
        //セッションに一時キー保存
        $_SESSION['tmp_key'] = $rand_key;
        //一時パスワードを受け付けたアドレスを保持
        $_SESSION['tmp_email'] = $email;
        //期限の設定
        $stslimit = 60 * 30;  //一時キーの期限は30分とする
        $_SESSION['tmp_login_limit'] = time() +  $stslimit;
        
        //成功メッセージ登録
        $_SESSION['msg-success'] = SUC02;
        debug('セッション情報：' . print_r($_SESSION,true));

        //パスワード入力画面へ遷移
        header('Location:passRemindRecieve.php');

      } else {
        debug('該当ユーザなし');
      }
    } catch (Exception $e) {
      error_log('エラーが発生しました' . $e->getMessage());
      $err_msg['common'] = MSG07;
    }

  } else {
    debug('バリデーションNG');
  }
}

?>

<?php
  $siteTitle = 'パスワード再発行';
  require('head.php');
?>

  <body class="page-signup page-1colum">

    <!-- メニュー -->
    <?php require('header.php'); ?>

    <!-- メインコンテンツ -->
    <div id="contents" class="site-width">

      <!-- Main -->
      <section id="main" >

        <div class="form-container">

          <form action="" class="form" method="post">
           <p>ご指定のメールアドレス宛にパスワード再発行用のURLと認証キーをお送り致します。</p>

            <div class="area-msg">
              <?php echo getErrInfo('common'); ?>
            </div>

            <label class="<?php if(!empty($err_msg['email'])) echo 'err'; ?>">
              Email
              <input type="text" name="email" value="<?php echo sanitize(getFormData('email')); ?>">
            </label>
           
            <div class="area-msg">
              <?php  echo getErrInfo('email'); ?>
            </div>
           
            <div class="btn-container">
              <input type="submit" class="btn btn-mid" value="送信する">
            </div>
          </form>
        </div>
        <a href="mypage.php">&lt; マイページに戻る</a>
      </section>

    </div>

    <!-- footer -->
    <?php require('footer.php'); ?>