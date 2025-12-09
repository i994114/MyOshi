<?php
//共通関数
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　productDetail.php:情報詳細');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//認証
require('auth.php');

//Get情報の取得
debug('Getの値：' . print_r($_GET,true));
$p_id = (!empty($_GET['p_id']))? $_GET['p_id'] : '';
$p = (!empty($_GET['p']))? $_GET['p'] : '';
$category_id = (!empty($_GET['category_id']))? $_GET['category_id'] : '';
$sort = (!empty($_GET['sort']))? $_GET['sort'] : '';

//選択された情報を取得
$product_data = getProductOne($p_id);
debug('取得した情報：' . print_r($product_data,true));

//不正なアクセスでないか判定
if (empty($product_data)) {
  debug('不正なURLです。情報一覧に戻ります');
  $err_msg['common'] = MSG07;

  header('Location:index.php');
  exit();
}

//情報一覧画面に戻る際のURL(Getデータ)
$str = appendGetParam(array('p_id'));
//先頭の&を削除
$str = mb_substr($str, 1);
debug('生成したGetパラメータ部分のURL：' . $str);

if (!empty($_POST)) {
  debug('ポスト送信あり');

  //当該情報の掲示板がすでにあるか
  $bord_data = getBordInfo($p_id);
  debug('掲示板情報：' . print_r($bord_data,true));

  if (empty($bord_data)) {
    debug('掲示板がないので新規作成します');
    try {
      //db接続
      $dbh = dbConnect();
      //sql作成
      $sql = 'INSERT INTO bord (user_id, product_id, user_id, create_date, update_date) VALUES (:u_id, :p_id, :create_date, :update_date)';
      //dataセット
      $data = array(':u_id' => $product_data['user_id'], ':p_id' => $p_id, ':create_date' => date('Y-m-d H:i:s'), ':update_date' => date('Y-m-d H:i:s'));
      $stmt = queryPost($dbh, $sql, $data);
  
      if($stmt) {
        debug('掲示板新規作成OK');
      } else {
        debug('掲示板新規作成NG');
        $err_msg['common'] = MSG07;
      }

    } catch(Exception $e) {
      error_log('エラーが発生しました' . $e->getMessage());
      $err_msg['common'] = MSG07;
    }
  } else {
    debug('掲示板はすでにあります');
  }
  //メッセージを格納
  $_SESSION['msg-success'] = SUC04;

  //掲示板へ移動
  header('Location:msg.php?b_id='.$bord_data['id'].'&p_id='.$p_id);
  exit();
}

?>

<?php
$siteTitle = APL_NAME.'情報詳細ページ';
require('head.php');
?>

  <body class="page-productDetail page-1colum">

    <!-- メニュー -->
    <?php require('header.php'); ?>

    <!-- メインコンテンツ -->
    <div id="contents" class="site-width">

      <!-- Main -->
      <section id="main" >

        <!-- カテゴリと名前 -->
        <div class="title">
          <span class="badge"><?php echo sanitize($product_data['category']); ?></span>
          <?php echo sanitize($product_data['name']); ?>
          <i class="fa fa-heart icn-like js-click-like <?php if(isLike($product_data['id'], $_SESSION['user_id'])) {echo ' active';}  ?>" data-productid = <?php echo $product_data['id']; ?> area-hidden="true"></i>
        </div>

        <!-- 写真 -->
        <div class="product-img-container">
          <div class="img-main">
            <img src="<?php echo sanitize($product_data['pic1']); ?>" alt="" id="js-show-main">
          </div>
          <div class="img-sub">
            <img src="<?php echo showImg(sanitize($product_data['pic1'])); ?>" alt="<?php echo sanitize($product_data['name']).' main'; ?>" class="js-show-sub">
            <img src="<?php echo showImg(sanitize($product_data['pic2'])); ?>" alt="<?php echo sanitize($product_data['name']).'sub1'; ?>" class="js-show-sub">
            <img src="<?php echo showImg(sanitize($product_data['pic3'])); ?>" alt="<?php echo sanitize($product_data['name']).'sub2'; ?>" class="js-show-sub">
          </div>
        </div>

        <!-- 説明 -->
        <div class="product-detail">
          <p>
            <?php
              echo sanitize($product_data['comment']);
            ?>
          </p>
        </div>

        <div class="product-buy">
          <div class="item-left">
            <a href="index.php?<?php echo $str; ?>">&lt; 情報一覧に戻る</a>
          </div>
          <form action="" method="post">
            <div class="item-right">
              <input type="submit" name="submit" class="btn btn-primary" value="掲示板を見る" style="margin-top: 0px;">
            </div>
          </form>
        </div>
      </section>
    </div>

    <!-- footer -->
    <?php require('footer.php'); ?>
