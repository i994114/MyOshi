<?php
//共通関数呼び出し
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　passEdit.php：パスワード変更');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//認証処理
require('auth.php');

//ポスト送信があるか
if (!empty($_POST)) {
  debug('ポスト送信あり');

  //-------------------
  //ポスト送信のデータを取得
  //-------------------
  $pass_old = $_POST['pass_old'];
  $pass_new = $_POST['pass_new'];
  $pass_new_re = $_POST['pass_new_re'];

  //ユーザ情報取得
  $user_info = getUserInfoOne($_SESSION['user_id']);
  debug('取得したユーザ情報' . print_r($user_info, true));

  //-------------------
  //バリデーションチェック
  //補足：古いパスワードと再入力パスワードは、一致判定すればよいのでチェックしない
  //-------------------
  
  //古いパスワードのチェック
  validOldPassMatch($pass_old, $user_info['password'], 'pass_old');

  //パスワードが古いものと同じかチェック
  validMatch($pass_old, $pass_new, 'pass_new');
  
  //パスワード再入力の一致チェック
  validMatch($pass_new, $pass_new_re, 'pass_new_re');
  
  //最小文字数チェック
  validMin($pass_new, 'pass_new');
  
  //最大文字数
  validMax($pass_new, 'pass_new');
  
  //半角英数字チェック
  validHalf($pass_new, 'pass_new');
 
  //空欄チェック
  validEmpty($pass_old, 'pass_old');
  validEmpty($pass_new, 'pass_new');
  validEmpty($pass_new_re, 'pass_new_re');

  //-------------------
  //DBに新しいパスワードを反映
  //-------------------

  if (empty($err_msg)) {
    debug('バリデーションOK');

    try {
      //db接続
      $dbh = dbConnect();
      //sql実行
      $sql = 'UPDATE users SET password = :password WHERE id = :id AND delete_flg = 0';
      //dataセット
      $data = array(':password' => password_hash($pass_new,PASSWORD_DEFAULT), ':id' => $_SESSION['user_id']);
      //sql実行
      $stmt = queryPost($dbh, $sql, $data);

      if ($stmt) {
        debug('クエリ成功');
        
        //セッションに成功結果をいれておく
        $_SESSION['msg-success'] = SUC01;

        //-------------------
        //変更メール送信処理
        //-------------------
        $username = (!empty($user_info['name']))? $user_info['name'] : '名無し';
        $from = 'webkatu@webkatu.com';
        $to = $user_info['email'];
        $subject = '【押し活共有アプリ】パスワード変更通知';
        $comments = <<<EOF
        {$username}さん
        パスワードが変更されました。

        押し活共有アプリより
        EOF;
        sendMail($from, $to, $subject, $comments);

        //マイページへ遷移
        header('Location:mypage.php');
      } else {
        debug('クエリ失敗');
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
  $siteTitle = 'パスワード変更';
  require('head.php');
?>

  <body class="page-passEdit page-2colum page-logined">

    <!-- メニュー -->
    <?php require('header.php'); ?>

    <!-- メインコンテンツ -->
    <div id="contents" class="site-width">
      <h1 class="page-title">パスワード変更</h1>
      <!-- Main -->
      <section id="main" >
        <div class="form-container">
          <form action="" class="form" method="post">
           <div class="area-msg">
            <?php
              echo getErrInfo('common');
            ?>
           </div>

            <!-- 古いパスワード -->
            <label class="<?php if(!empty($err_msg['pass_old'])) echo 'err'; ?>">
              古いパスワード
              <input type="text" name="pass_old" value="<?php if(!empty($_POST['pass_old'])) echo sanitize($_POST['pass_old']); ?>">
            </label>
            <div class="area-msg">
              <?php echo getErrInfo('pass_old'); ?>
            </div>

            <!-- 新しいパスワード -->
            <label class="<?php if(!empty($err_msg['pass_new'])) echo 'err'; ?>">
              新しいパスワード
              <input type="password" name="pass_new" value="<?php if(!empty($_POST['pass_new'])) echo sanitize($_POST['pass_new']); ?>">
            </label>
            <div class="area-msg">
              <?php echo getErrInfo('pass_new'); ?>
            </div>
           
            <!-- 再入力パスワード -->
            <label class="<?php if(!empty($err_msg['pass_new_re'])) echo 'err'; ?>">
              新しいパスワード（再入力）
              <input type="password" name="pass_new_re" value="<?php if(!empty($_POST['pass_new_re'])) echo sanitize($_POST['pass_new_re']); ?>">
            </label>
            <div class="area-msg">
              <?php echo getErrInfo('pass_new_re'); ?>
            </div>
           
            <div class="btn-container">
              <input type="submit" class="btn btn-mid" value="変更する">
            </div>
          </form>
        </div>
      </section>
      
      <!-- サイドバー -->
      <section id="sidebar">
        <a href="registProduct.html">商品を出品する</a>
        <a href="tranSale.html">販売履歴を見る</a>
        <a href="profEdit.html">プロフィール編集</a>
        <a href="passEdit.html">パスワード変更</a>
        <a href="withdraw.html">退会</a>
      </section>
      
    </div>

    <!-- footer -->
    <?php require('footer.php'); ?>
