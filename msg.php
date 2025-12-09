<?php
//共通関数の呼び出し
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　msg.php:掲示板');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//認証判定
require('auth.php');

//Getデータを取得
debug('Getデータの値：' . print_r($_GET,true));
$p_id = (!empty($_GET['p_id']))? $_GET['p_id'] : 0;
$b_id = (!empty($_GET['b_id']))? $_GET['b_id'] : 0;

//登録情報を取得
$product = getProductOne($p_id);

//メッセージ情報を取得
$messageInfo = getMessageInfo($b_id);
debug('取得したメッセージ：' . print_r($messageInfo,true));

//ユーザ情報を取得
$userInfo = getUserInfoOne($_SESSION['user_id']);
debug('取得したユーザID:' . print_r($userInfo,true));

//すべてのユーザ情報を取得
$userInfoAll = getUserInfo();
debug('すべてのユーザ情報:' . print_r($userInfoAll,true));

if(!empty($_POST)) {
  debug('ポスト送信あり');

  //POSTデータの取得
  $message = $_POST['message'];
  
  //バリデーションチェック
  validMax($message,'message');
  validEmpty($message,'message');

  if (empty($err_msg)) {
    //投稿されたメッセージをDBに登録
    try {
      //db接続
      $dbh = dbConnect();
      //sql実行
      $sql = 'INSERT INTO message (bord_id, from_user, to_user, comment, create_date, update_date) VALUES (:b_id, :from_user, :to_user, :comment, :create_date, :update_date)';
      //dataセット
      $data = array( ':b_id' => $b_id, ':from_user' => $_SESSION['user_id'], ':to_user' => $product['user_id'], ':comment' => $message, ':create_date' => date('Y-m-d H:i:s'), 'update_date' => date('Y-m-d H:i:s'));
      //sql実行
      $stmt = queryPost($dbh, $sql, $data);

      if ($stmt) {
        debug('投稿メッセージのDB登録OK');
        
        //ポストをクリア
        $_POST = array();

        //再度同じページにリダイレクト
        header("Location:".$_SERVER['PHP_SELF'].'?b_id='.$_GET['b_id'].'&p_id='.$_GET['p_id']);
        exit();
      } else {
        debug('投稿メッセージのDB登録NG');
      }

    } catch (Exception $e) {
      error_log('エラーが発生しました' . $e->getMessage());
      global $err_msg;
      $err_msg['common'] = MSG07;
    }
  }
}

?>
  <!-- head部分 -->
<?php
$siteTitle = '掲示板';
require('head.php');
?>
  <body class="page-msg page-1colum">
    <!-- メッセージ表示 -->
    <div id="js-show-msg" style="display: none;" class="msg-slide">
      <?php  echo getSessionMessage('msg-success'); ?>
    </div>

    <!-- メニュー -->
    <?php  require('header.php'); ?>

    <!-- メインコンテンツ -->
    <div id="contents" class="site-width">
      <!-- Main -->
      <section id="main" >
        <div class="msg-info">
          <h3><?php echo sanitize($product['name']); ?></h3>
          <img src="<?php echo showImg(sanitize($product['pic1'])); ?>" alt="" height="100px" width="auto" >
          <img src="<?php echo showImg(sanitize($product['pic2'])); ?>" alt="" height="100px" width="auto" >
          <img src="<?php echo showImg(sanitize($product['pic3'])); ?>" alt="" height="100px" width="auto" >
          <div class="msg-comment">
            <p>
              <?php echo sanitize($product['comment']);  ?>
            </p>
          </div>
        </div>
        <div class="area-bord" id="js-scroll-bottom">
          <?php
            //自分の投稿を探して右側に表示
            if (!empty($messageInfo)) {
              foreach($messageInfo as $key => $val) {
                if ((int)$val['from_user'] === (int)$userInfo['id']) {
            ?>
                  <div class="msg-cnt msg-right">
                    <!-- 画像 -->
                    <div class="avatar">
                      <img src="<?php echo sanitize(($userInfo['pic']));  ?>" alt="" class="avatar">
                      <p class="avatar-name"><?php echo sanitize(mb_substr($userInfo['name'],0,6)); ?></p>
                    </div>
                    <!-- メッセージ -->
                    <p class="msg-inrTxt">
                      <span class="triangle"></span>
                      <?php  echo sanitize($val['comment']); ?>
                    </p>
                    <!-- 投稿時刻 -->
                    <span class="msg-right-time">
                      <p><?php echo sanitize($val['create_date']); ?></p>
                    </span>
                  </div>
            <?php 
                } else {
            ?>
            <?php
                //自分以外の投稿を左に表示
                foreach ($userInfoAll as $key => $valAll) {
                  if ($val['from_user'] === $valAll['id']) {
            ?>
                    <div class="msg-cnt msg-left">
                      <!-- 画像 -->
                      <div class="avatar">
                        <img src="<?php echo sanitize($valAll['pic']);  ?>" alt="" class="avatar">
                        <p class="avatar-name"><?php  echo sanitize(mb_substr($valAll['name'],0,6)); ?></p>
                      </div>
                      <!-- メッセージ -->
                      <p class="msg-inrTxt">
                        <span class="triangle"></span>
                        <?php  echo sanitize($val['comment']); ?>
                      </p>
                      <!-- 投稿時刻 -->
                      <span class="msg-left-time">
                        <p><?php echo sanitize($val['create_date']); ?></p>
                      </span>
                    </div>
            <?php
                    }
            ?>
            <?php
                  }
            ?>
            <?php
                }
            ?>
            <?php
              }
            } else {
            ?>
              <p class="msg-inrTxt">
                <span class="triangle"></span>
                まだ投稿がありません。
              </p>
            <?php
            }
            ?>
        </div>

        <form action="" method="post">
            <div class="area-send-msg">
              <div class="area-msg" style="padding-bottom: 0px;">
                <?php echo getErrInfo('message');  ?>
              </div>
              <textarea name="message" id="" cols="30" rows="3"></textarea>
              <input type="submit" value="送信" class="btn btn-send">
            </div>
        </form>
      </section>
      
      <script src="js/vendor/jquery-2.2.2.min.js"></script>
      
      <script>
        $(function(){
          $('#js-scroll-bottom').animate({scrollTop: $('#js-scroll-bottom')[0].scrollHeight}, 'fast');
        });
      </script>

    </div>

    <!-- footer -->
    <?php require('footer.php'); ?>
