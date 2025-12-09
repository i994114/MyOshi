<?php

//共通関数呼び出し
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　signup.php：ユーザ登録');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//post送信されていた場合
if(!empty($_POST)){
  
  //変数にユーザー情報を代入
  $email = $_POST['email'];
  $pass = $_POST['pass'];
  $pass_re = $_POST['pass_re'];

//-------------------
//バリデーションチェック
//-------------------

  //Eメール重複チェック
  validEmailDup($email);

  //パスワード再入力の一致チェック
  validMatch($pass, $pass_re, 'pass');

  //最小文字数チェック
  validMin($pass, 'pass');

  //最大文字数
  validMax($email, 'email');
  validMax($pass, 'pass');

  //Eメール形式かチェック
  validEmail($email, 'email');

  //半角英数字チェック
  validHalf($pass, 'pass');

  //空欄チェック
  validEmpty($email, 'email');
  validEmpty($pass, 'pass');
  validEmpty($pass_re, 'pass_re');

  debug('$err_msgの値：' . print_r($err_msg,true));

  if (empty($err_msg)) {
    debug('signup.php バリデーションOK');

    //登録しようとしているユーザ情報が、一度退会したユーザかを判定
    //(一度退会したユーザなら前のデータを復活させる)

    //全ユーザ情報を取得
    $userInfo = getUserInfo();
    debug('取得した全ユーザ情報' . print_r($userInfo,true));

    //一度退会したユーザの判定
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

      //新規登録か、前のユーザ登録データを復活させるか
      if (!$regAgain) {
        debug('ユーザ情報を新規登録します');
        //sql作成
        $sql = 'INSERT INTO users (email, password, delete_flg, login_time, create_date) VALUES (:email, :pass, :del, :login_time, :date)';
        //dataセット
        $data = array(':email' => $email, ':pass' => password_hash($pass,PASSWORD_DEFAULT), ':del' => 0, ':login_time' => date('Y-m-d H:i:s'), ':date' => date('Y-m-d H:i:s'));
      } else {
        debug('削除フラグをクリアし、ユーザ情報を復活します');
        //sql作成
        $sql = 'UPDATE users SET delete_flg = 0 WHERE id = :u_id';
        //data作成
        $data = array(':u_id' => $u_id);
      }

      //sql実行
      $stmt = queryPost($dbh, $sql, $data);

      if($stmt) {
        debug('ユーザ情報を登録しました');

        //一度削除してからの再登録の際の復活処置
        againSignUpCalc($u_id);

        //ログイン有効時間(デフォルトを1時間とする)
        $login_limit = 60*60;
        //最終ログイン日時を現在日時に
        $_SESSION['login_date'] = time();
        $_SESSION['login_limit'] = $login_limit;
        //ユーザIDを格納
        if (!$regAgain) {
          //新規登録時
          $_SESSION['user_id'] = $dbh->lastInsertId();
        } else {
          //削除されたアカウントの復活時
          $_SESSION['user_id'] = $u_id;
        }
        
        $_SESSION['msg-success'] = SUC05;
        
        debug('セッション変数の中身' . print_r($_SESSION,true));

        //マイページへ遷移
        header("Location:mypage.php");
        exit();
      }
    } catch (Exception $e) {
      error_log('エラーが発生しました' . $e->getMessage());
      $err_msg['common'] = MSG07;
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
    <p id="js-show-msg" style="display: none;" class="msg-slide">
      <?php echo getSessionMessage('msg-success'); ?>
    </p>

    <!-- メインコンテンツ -->
    <div id="contents" class="site-width">

      <!-- Main -->
      <section id="main" >

        <div class="form-container">

          <form action="" class="form" method="post">
            <h2 class="title">ユーザー登録</h2>
            
            <div class="area-msg">
              <?php echo (!empty($err_msg['common']))? $err_msg['common'] : ''; ?>
            </div>

            <!-- アドレス -->
            <label class="<?php echo (!empty($err_msg['email']))? 'err' : '';?>">
              Email
              <input type="text" name="email" value="<?php echo (!empty($_POST['email']))? sanitize($_POST['email']) : ''; ?>">
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
              パスワード <span style="font-size:12px">※英数字６文字以上</span>
              <input type="password" name="pass" value="<?php echo (!empty($_POST['pass']))? sanitize($_POST['pass']) : ''; ?>">
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
              <input type="password" name="pass_re" value="<?php echo (!empty($_POST['pass_re']))? sanitize($_POST['pass_re']) : ''; ?>">
            </label>
            <div class="area-msg">
              <?php 
                if (!empty($err_msg['pass_re'])) {
                  echo $err_msg['pass_re'];
                }
              ?>
            </div>

            <div class="btn-container">
              <input type="submit" class="btn btn-mid" value="登録する">
            </div>
          </form>
        </div>

      </section>

    </div>

    <!-- footer -->
    <?php require('footer.php'); ?>