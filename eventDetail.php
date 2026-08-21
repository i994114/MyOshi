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

//----------------------
//イベント情報取得
//----------------------
$event_data = getEventOne($e_id);
debug('取得した情報：' . print_r($event_data, true));

//不正なアクセスでないか判定
if (empty($event_data)) {
  debug('不正なURLです。情報一覧に戻ります');

  header('Location:index.php');
  exit();
}

//----------------------
//掲示板情報取得
//----------------------
$bord_data = getBordInfo($e_id);
//debug('掲示板情報：' . print_r($bord_data, true));

//イベント作成時に掲示板も作成される仕様のため、
//掲示板が存在しない場合はシステム上の異常として扱う
if (empty($bord_data)) {
  debug('当該イベントの掲示板情報が存在しません');

  header('Location:index.php');
  exit();
}

//----------------------
//その他表示用データ取得
//----------------------

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

//情報一覧画面に戻る際のURL(Getデータ)
$str = appendGetParam(array('e_id'));
//先頭の&を削除
$str = mb_substr($str, 1);
//debug('生成したGetパラメータ部分のURL：' . $str);

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

            <div class="event-info-item">
              <span class="event-info-label">開催地</span>
              <span class="event-info-value">
                <?php echo sanitize($event_data['prefecture']); ?>
              </span>
            </div>

            <div class="event-info-item">
              <span class="event-info-label">開催日</span>
              <span class="event-info-value">
                <?php echo sanitize($event_data['event_date']); ?>
              </span>
            </div>

            <div class="event-info-item">
              <span class="event-info-label">開催時間</span>
              <span class="event-info-value">
                <?php echo timeFormat($event_data['start_time'], $event_data['end_time']); ?>
              </span>
            </div>

            <div class="event-targets">
              <span class="event-info-label">対象</span>

              <?php foreach($target_data as $val) {
                $active = false;

                foreach($target as $t) {
                  if ($val['id'] === $t['target_id']) {
                    $active = true;
                  }
                }
              ?>
                <span class="icon_target <?php echo $active ? 'active' : ''; ?>">
                  <?php echo sanitize($val['name']); ?>
                </span>
              <?php } ?>
            </div>

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
              echo nl2br(sanitize($event_data['description']));
            ?>
          </p>
        </div>

        <div class="event-buy">
          <div class="item-left">
            <a href="index.php?<?php echo $str; ?>">&lt; 情報一覧に戻る</a>
          </div>
          <div class="item-right">
            <input type="button" class="btn btn-primary js-click-event-participant" value="<?php  echo isEventParticipants($event_data['id'], $_SESSION['user_id']) ? '参加取消' : '参加する';  ?>" data-eventid = <?php echo $event_data['id']; ?>>
            <a href="msg.php?b_id=<?php echo $bord_data['id']; ?>&e_id=<?php echo $event_data['id']; ?>" class="btn btn-primary">
              掲示板でコメントを見る(<?php echo $message_count; ?>件)
            </a>
          </div>
        </div>
      </section>
    </div>

    <!-- footer -->
    <?php require('footer.php'); ?>
