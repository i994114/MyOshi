<?php

//共通関数呼び出し
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「signup.php：ユーザ登録');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン中はユーザ登録不可
if (!empty($_SESSION['user_id'])) {
  debug('ログイン中のユーザがアクセスしたため、マイページへ遷移します');
  header("Location:mypage.php");
  exit();
}

//post送信されていた場合
if(!empty($_POST)){
  
  //変数にユーザ情報を代入
  $name = $_POST['name'];
  $email = $_POST['email'];
  $pass = $_POST['pass'];
  $pass_re = $_POST['pass_re'];
  $ip = $_SERVER['REMOTE_ADDR'];

  //-------------------
  //バリデーションチェック
  //-------------------
  
  //一度に大量のユーザ登録をおこなっていないかチェック(いたずら防止)
  validSignupRateLimit($ip, 'common');

  //Eメール重複チェック
  validEmailDup($email);

  //パスワード再入力の一致チェック
  validMatch($pass, $pass_re, 'pass');

  //最小文字数チェック
  validMin($pass, 'pass');

  //最大文字数
  validMax($name, 'name', MAX_NAME);
  validMax($email, 'email', MAX_EMAIL);
  validMax($pass, 'pass', MAX_PASS);

  //Eメール形式かチェック
  validEmail($email, 'email');

  //半角英数字チェック
  validHalf($pass, 'pass');

  //空欄チェック
  validEmpty($name, 'name');
  validEmpty($email, 'email');
  validEmpty($pass, 'pass');
  validEmpty($pass_re, 'pass_re');

  debug('$err_msgの値：' . print_r($err_msg,true));

  if (empty($err_msg)) {
    debug('signup.php バリデーションOK');

    //一度退会したユーザの判定
    //(登録しようとしているユーザ情報が、一度退会したユーザかを判定)
    //(一度退会したユーザなら前のデータを復活させる)

    //全ユーザ情報を取得
    $userInfo = getUserInfo();
    //debug('取得した全ユーザ情報' . print_r($userInfo,true));

    $regAgain = false;
    if (!empty($userInfo)) {
      foreach($userInfo as $key => $val) {
        if ($val['email'] === $email && 
          password_verify($pass, $val['password']) &&
          (int)$val['delete_flg'] === (int)1) {
          $u_id = $val['id'];
          $regAgain = true;
        }
      }
    }

    try {
      //db接続
      $dbh = dbConnect();
      $dbh->beginTransaction();

      //新規登録か、前のユーザ登録データを復活させるか
      if (!$regAgain) {
        debug('ユーザ情報を新規登録します');
        //sql作成
        $sql = 'INSERT INTO users (name, email, password, delete_flg, login_time, create_date) VALUES (:name, :email, :pass, :del, :login_time, :date)';
        //dataセット
        $data = array(':name' => $name, ':email' => $email, ':pass' => password_hash($pass,PASSWORD_DEFAULT), ':del' => 0, ':login_time' => date('Y-m-d H:i:s'), ':date' => date('Y-m-d H:i:s'));
      } else {
        debug('削除フラグをクリアし、ユーザ情報を復活します');
        //sql作成
        $sql = 'UPDATE users SET delete_flg = 0 WHERE id = :u_id';
        //data作成
        $data = array(':u_id' => $u_id);
      }

      //sql実行
      $stmt_user = queryPost($dbh, $sql, $data);
      
      if (!$stmt_user) {
        throw new Exception('ユーザ情報の登録に失敗しました');
      }

      //新規登録の場合は、登録したユーザのIDを取得
      if (!$regAgain) {
        $new_user_id = $dbh->lastInsertId();
      }

      //----------------------------
      //ユーザ登録成功時のIPアドレスを保存
      //----------------------------
      $sql = 'INSERT INTO signup_logs(ip_address, create_date) VALUES (:ip, :create_date)';
      $data = array(':ip' => $ip, ':create_date' => date('Y-m-d H:i:s'));
      $stmt_signup_log = queryPost($dbh, $sql, $data);

      if (!$stmt_signup_log) {
        throw new Exception('ユーザ登録履歴の保存に失敗しました');
      }

      $dbh->commit();

      //--------------
      //ユーザ登録成功後処理
      //--------------

      //ログイン有効時間(デフォルトを1時間とする)
      $login_limit = 60*60;
      //最終ログイン日時を現在日時に
      $_SESSION['login_date'] = time();
      $_SESSION['login_limit'] = $login_limit;

      //ログインユーザIDを格納
      if (!$regAgain) {
        //新規登録時
        $_SESSION['user_id'] = $new_user_id;

      } else {
        //削除されたアカウントの復活時
        $_SESSION['user_id'] = $u_id;

        //過去に登録していたイベント・掲示板を復活
        if (!againSignUpCalc($u_id)) {
          debug('関連データの復活に失敗しました');
          $err_msg['common'] = ERR_SYSTEM;
          return;
        }
      }
      
      $_SESSION['msg-success'] = SUCCESS_SIGNUP;
      
      debug('セッション変数の中身' . print_r($_SESSION,true));

      //マイページへ遷移
      header("Location:mypage.php");
      exit();

    } catch (Exception $e) {
      if ($dbh->inTransaction()) {
        $dbh->rollback();
      }
      error_log('エラーが発生しました' . $e->getMessage());
      $err_msg['common'] = ERR_SYSTEM;
    }
  } else {
    debug('signup.php バリデーションNG');
    debug('$err_msgの値' . print_r($err_msg,true));
  }
  debug('signup.php終了');
}
?>
<?php
  $siteTitle = 'ユーザ登録';
  require('head.php');
?>

  <body class="page-signup page-1colum">

    <!-- メニュー -->
    <?php require('header.php'); ?>

    <!-- メッセージ表示 -->
    <p id="js-show-msg"  class="msg-slide">
      <?php echo getSessionMessage('msg-success'); ?>
    </p>

    <!-- メインコンテンツ -->
    <div id="contents" class="site-width">

      <!-- Main -->
      <section id="main" >

        <div class="form-container">

          <form action="" class="form" method="post">
            <h2 class="title">ユーザ登録</h2>
            
            <div class="area-msg">
              <?php echo (!empty($err_msg['common']))? $err_msg['common'] : ''; ?>
            </div>

            <!-- ユーザ名 -->
            <label class="<?php echo (!empty($err_msg['name']))? 'err' : '';?>">
              ユーザ名
              <input type="text" name="name" value="<?php echo (!empty($_POST['name'])) ? sanitize($_POST['name']) : ''; ?>">
            </label>
            <div class="area-msg">
              <?php 
                if (!empty($err_msg['name'])) {
                  echo $err_msg['name'];
                }
              ?>
            </div>

            <!-- アドレス -->
            <label class="<?php echo (!empty($err_msg['email']))? 'err' : '';?>">
              Email
              <input type="text" name="email" value="<?php echo (!empty($_POST['email'])) ? sanitize($_POST['email']) : ''; ?>">
            </label>
            <div class="area-msg">
              <?php 
                if (!empty($err_msg['email'])) {
                  echo $err_msg['email'];
                }
              ?>
            </div>

            <!-- パスワード -->
            <label class="<?php echo (!empty($err_msg['pass']))? 'err' : '';?>">
              パスワード <span class="form-note">※英数字６文字以上</span>
              <input type="password" name="pass" value="">
            </label>
            <div class="area-msg">
              <?php 
                if (!empty($err_msg['pass'])) {
                  echo $err_msg['pass'];
                }
              ?>
            </div>

            <!-- パスワード(再入力) -->
            <label class="<?php echo (!empty($err_msg['pass_re']))? 'err' : '';?>">
              パスワード（再入力）
              <input type="password" name="pass_re" value="">
            </label>
            <div class="area-msg">
              <?php 
                if (!empty($err_msg['pass_re'])) {
                  echo $err_msg['pass_re'];
                }
              ?>
            </div>

            <div class="btn-container">
              <input type="submit" class="btn btn-mid" value="ユーザ登録する">
            </div>
          </form>
        </div>

      </section>

    </div>

    <!-- footer -->
    <?php require('footer.php'); ?>