<?php
//共通関数読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　login.php:ログイン処理');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//認証判定
require('auth.php');

if (!empty($_POST)) {

  //-------------------
  //ユーザ情報取得
  //-------------------

  $email = $_POST['email'];
  $pass = $_POST['pass'];
  $login_save = (!empty($_POST['pass_save']))? true : false;
  //-------------------
  //バリデーションチェック
  //-------------------

  //Eメール形式チェック
  validEmail($email, 'email');
  
  //文字数チェック
  validMax($email, 'email');
  validMax($pass, 'pass');
  validMin($pass, 'pass'); 

  //空欄チェック
  validEmpty($email, 'email');
  validEmpty($pass, 'pass');

  if (empty($err_msg)) {  
    debug('バリデーションOkです');
  //------------------------------------
  //POSTされたメールアドレスのユーザ情報を取得
  //-----------------------------------
    try {
      //db接続
      $dbh = dbConnect();
      //SQL文生成
      $sql = 'SELECT id, password FROM users WHERE email = :email AND delete_flg = 0';
      //dataセット
      $data = array(':email' => $email);
      //SQL実行
      $stmt = queryPost($dbh, $sql, $data);

      if ($stmt) {
        debug('ログインしようとしているEメールアドレスのユーザ情報取得OK');

        //結果を取得
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        debug('$resultの値：' . print_r($result,true));

        if (!empty($result) && password_verify($pass, $result['password'])) {
          debug('ログイン判定：パスワード一致');

          //ログイン日時を更新
          $_SESSION['login_date'] = time();

          //ログイン期限(デフォルト1時間)
          $seslimit = 60 * 60;

          if ($login_save) {
            //ログイン時間を30日で設定
            $_SESSION['login_limit'] = $seslimit * 24 * 30;
          } else {
            //ログイン期限は1時間で設定
            $_SESSION['login_limit'] = $seslimit;
          }

          //ユーザIDを格納
          $_SESSION['user_id'] = $result['id'];
          
          //ログイン成功メッセージをセッションにいれる
          $_SESSION['msg-success'] = 'ログインしました';

          debug('ログインOKの際の$_SESSION情報：' . print_r($_SESSION,true));

          //マイページへ遷移
          header("Location:mypage.php");
          exit();
        } else {
          debug('ログイン判定：パスワード不一致');
          $err_msg['common'] = MSG09;
        }
      } else {
        debug('ログインしようとしているEメールアドレスのユーザ情報取得NG');
      }
    } catch (Exception $e) {
      error_log('エラーが発生しました' . $e->getMessage());
      $err_msg['common'] = MSG07;
    }
  } else {
    debug('バリデーションNGです');
  }
}
debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>

<?php
  $siteTitle = 'ログイン';
  require('head.php');
?>

  <body class="page-login page-1colum">

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
           <h2 class="title">ログイン</h2>
           <div class="area-msg">
             <?php
              if (!empty($err_msg['common'])) {
                echo $err_msg['common'];
              }
             ?>
           </div>
          
           <!-- メールアドレス -->
           <label class="<?php echo (!empty($err_msg))? 'err' : '';  ?>">
            メールアドレス
             <input type="text" name="email" value="<?php if(!empty($_POST['email'])) echo sanitize($_POST['email']); ?>">
           </label>
           <div class="area-msg">
              <?php
                if (!empty($err_msg['email'])) {
                  echo $err_msg['email'];
                }
              ?>
           </div>
           
           <!-- パスワード -->
           <label>
             パスワード
             <input type="password" name="pass" value="<?php if(!empty($_POST['pass'])) echo sanitize($_POST['pass']); ?>">
           </label>
           <div class="area-msg">
              <?php
                if (!empty($err_msg['pass'])) {
                  echo $err_msg['pass'];
                }
              ?>
           </div>

           <label>
             <input type="checkbox" name="pass_save">次回ログインを省略する
           </label>
           
           <div class="btn-container">
              <input type="submit" class="btn btn-mid" value="ログイン">
            </div>
            パスワードを忘れた方は<a href="passRemindSend.php">コチラ</a>
         </form>
       </div>

      </section>

    </div>

    <!-- footer -->
    <?php require('footer.php'); ?>
