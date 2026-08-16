<?php
//共通関数
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「eventDetail.php:情報詳細');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//認証
require('auth.php');

//Get情報の取得
debug('Getの値：' . print_r($_GET,true));
$e_id = (!empty($_GET['e_id']))? $_GET['e_id'] : '';
$p = (!empty($_GET['p']))? $_GET['p'] : '';
$category_id = (!empty($_GET['category_id']))? $_GET['category_id'] : '';
$sort = (!empty($_GET['sort']))? $_GET['sort'] : '';

//選択された情報を取得
$event_data = getEventOne($e_id);
debug('取得した情報：' . print_r($event_data,true));

//当該イベント登録者情報の取得
$user_data = getUserInfoOne($event_data['user_id']);

//当該イベントの掲示板のメッセージ数を取得
$message_count = getMessageCount($e_id);

//当該イベントのお気に入り数を取得
$like_count = getLikeCount($e_id);

//都道府県データを取得
$prefecture = getPrefecture();

//当該イベントのターゲット情報を取得
$target = getEventTarget($e_id);

//対象データを取得
$target_data = getTarget();

//不正なアクセスでないか判定
if (empty($event_data)) {
  debug('不正なURLです。情報一覧に戻ります');
  $err_msg['common'] = ERR_SYSTEM;

  header('Location:index.php');
  exit();
}

//情報一覧画面に戻る際のURL(Getデータ)
$str = appendGetParam(array('e_id'));
//先頭の&を削除
$str = mb_substr($str, 1);
debug('生成したGetパラメータ部分のURL：' . $str);

if (!empty($_POST)) {
  debug('ポスト送信あり');

  //当該情報の掲示板がすでにあるか
  $bord_data = getBordInfo($e_id);
  debug('掲示板情報：' . print_r($bord_data,true));

  if (empty($bord_data)) {
    debug('掲示板がないので新規作成します');
    try {
      //db接続
      $dbh = dbConnect();
      //sql作成
      $sql = 'INSERT INTO boards (user_id, event_id, create_date, update_date) VALUES (:u_id, :e_id, :create_date, :update_date)';
      //dataセット
      $data = array(':u_id' => $event_data['user_id'], ':e_id' => $e_id, ':create_date' => date('Y-m-d H:i:s'), ':update_date' => date('Y-m-d H:i:s'));
      $stmt = queryPost($dbh, $sql, $data);
  
      if($stmt) {
        debug('掲示板新規作成OK');
      } else {
        debug('掲示板新規作成NG');
        $err_msg['common'] = ERR_SYSTEM;
      }

    } catch(Exception $e) {
      error_log('エラーが発生しました' . $e->getMessage());
      $err_msg['common'] = ERR_SYSTEM;
    }
  } else {
    debug('掲示板はすでにあります');
  }
  //メッセージを格納
  $_SESSION['msg-success'] = SUCCESS_BOARD_MOVE;

  //掲示板へ移動
  header('Location:msg.php?b_id='.$bord_data['id'].'&e_id='.$e_id);
  exit();
}

?>

<?php
$siteTitle = APL_NAME.'情報詳細ページ';
require('head.php');
?>

  <body class="page-eventDetail page-1colum">

    <!-- メニュー -->
    <?php require('header.php'); ?>

    <!-- メインコンテンツ -->
    <div id="contents" class="site-width">

      <!-- Main -->
      <section id="main" >

        <!-- カテゴリと名前 -->
        <div class="title">
          <span class="badge"><?php echo sanitize($event_data['category']); ?></span>
          <?php echo sanitize($event_data['name']); ?>
          <!-- お気に入り -->
          <i class="fa fa-heart icn-like js-click-like <?php if(isLike($event_data['id'], $_SESSION['user_id'])) {echo ' active';}  ?>" data-eventid = <?php echo $event_data['id']; ?> aria-hidden="true"><span class="js-like-count"><?php echo $like_count; ?></span></i>
          
        </div>
        <!-- 写真 -->
        <div class="event-img-container">
          <div class="event-description">
            <?php echo $event_data['prefecture']; ?>
            <?php echo $event_data['event_date']; ?>
            <?php echo $event_data['start_time']; ?>
            <?php echo $event_data['end_time']; ?>
            <?php echo $prefecture[$event_data['prefecture_id']]['name']; ?>

            <!-- 対象 -->
            <?php
              foreach($target_data as $val) {
                $active = false;

                foreach($target as $t) {
                  if ($val['id'] === $t['target_id']) {
                    $active = true;
                  }
                }
            ?>
            <span class="icon_target <?php echo $active? 'active' : ''; ?>"><?php  echo $val['name'] ?></span>
            <?php } ?>
            
          </div>
          <div class="img-main">
            <img src="<?php echo showImg(sanitize($event_data['pic1'])); ?>" alt="" id="js-show-main">
          </div>
          <div class="img-sub">
            <img src="<?php echo showImg(sanitize($event_data['pic1'])); ?>" alt="<?php echo sanitize($event_data['name']).' main'; ?>" class="js-show-sub">
            <img src="<?php echo showImg(sanitize($event_data['pic2'])); ?>" alt="<?php echo sanitize($event_data['name']).'sub1'; ?>" class="js-show-sub">
            <img src="<?php echo showImg(sanitize($event_data['pic3'])); ?>" alt="<?php echo sanitize($event_data['name']).'sub2'; ?>" class="js-show-sub">
          </div>
        </div>

        <!-- 説明 -->
        <div class="event-detail">
          <p>
            <?php
              echo sanitize($event_data['description']);
            ?>
          </p>
        </div>

        <div class="event-buy">
          <div class="item-left">
            <a href="index.php?<?php echo $str; ?>">&lt; 情報一覧に戻る</a>
          </div>
          <form action="" method="post">
            <div class="item-right">
              <input type="submit" name="submit" class="btn btn-primary" value="掲示板でコメントを見る(<?php echo $message_count; ?>件)" style="margin-top: 0px;">
            </div>
          </form>
        </div>
      </section>
    </div>

    <!-- footer -->
    <?php require('footer.php'); ?>
