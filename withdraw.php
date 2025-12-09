<?php
//共通関数読みだし
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　withdraw.php：退会');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
require('auth.php');

//変数クリア
$userInfo = array();

//post送信されていた場合
if (!empty($_POST)) {

  try {
    //dbセット
    $dbh = dbConnect();
    //sql作成
    $sql1 = 'UPDATE users SET delete_flg = 1 WHERE id = :u_id';
    $sql2 = 'UPDATE product SET delete_flg = 1 WHERE user_id = :u_id';
    $sql3 = 'UPDATE `like` SET delete_flg = 1 WHERE user_id = :u_id';
    $sql4 = 'UPDATE bord SET delete_flg = 1 WHERE user_id = :u_id';
    $sql5 = 'UPDATE message SET delete_flg = 1 WHERE from_user = :u_id';
    $sql6 = 'UPDATE message SET delete_flg = 1 WHERE to_user = :u_id';

    //dataセット
    $data = array(':u_id' => $_SESSION['user_id']);
    debug('退会するユーザID：' . print_r($data,true));
    
    //sql実行
    $stmt1 = queryPost($dbh, $sql1, $data);
    $stmt2 = queryPost($dbh, $sql2, $data);
    $stmt3 = queryPost($dbh, $sql3, $data);
    $stmt4 = queryPost($dbh, $sql4, $data);
    $stmt5 = queryPost($dbh, $sql5, $data);
    $stmt6 = queryPost($dbh, $sql6, $data);

    //最悪userテーブルさえ削除できていればよしとする
    if($stmt1) {
      debug('退会処理成功');

      //セッション削除
      //session_destroy();
      //$_SESSION = array();

      $_SESSION['msg-success'] = SUC06;
      debug('退会時点のセッションの値:' . print_r($_SESSION,true));
      
      header("Location:signup.php");
      exit();
    } else {
      debug('退会処理失敗');
    }

  } catch (Exception $e) {
    error_log('エラーが発生しました' . $e->getMessage());
    $err_msg['common'] = MSG07;
  }
} 

?>

<?php
  $siteTitle = '退会';
  require('head.php');
?>

<body class="page-withdraw page-1colum">

  <style>
      .form .btn{
        float: none;
      }
      .form{
        text-align: center;
      }
  </style>

  <!-- メニュー -->
  <header>
    <div class="site-width">
      <h1><a href="index.html"><?php echo APL_NAME.APL_SUBNAME; ?></a></h1>
      <nav id="top-nav">
        <ul>
          <li><a href="mypage.php">マイページ</a></li>
          <li><a href="logout.php">ログアウト</a></li>
        </ul>
      </nav>
    </div>
  </header>

  <!-- メインコンテンツ -->
  <div id="contents" class="site-width">
    <!-- Main -->
    <section id="main" >
      <div class="form-container">
        <form action="" class="form" method="post">
          <h2 class="title">退会</h2>
          <div class="area-msg">
            <?php
              if (!empty($err_msg['common'])) {
                echo $err_msg['common'];
              }
            ?>
          </div>
          <div class="btn-container">
            <p>ほんとにやめちゃいますか・・？</p>
            <input type="submit" name="withdraw" class="btn btn-mid" value="退会する">
          </div>
        </form>
      </div>
      <a href="mypage.php">&lt; マイページに戻る</a>
    </section>
  </div>

  <!-- footer -->
  <?php 
    require('footer.php');
  ?>