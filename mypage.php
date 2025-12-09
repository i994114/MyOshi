<?php
//共通処理
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　mypage.php:マイページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//認証処理
require('auth.php');

//自分のお気に入り情報取得
$likeInfo = getLikeInfo($_SESSION['user_id']);
debug('取得したお気に入り情報:' . print_r($likeInfo,true));

//自分が登録した情報取得
$productInfo = getMyProductList($_SESSION['user_id']);
debug('自分が登録した情報取得:' . print_r($productInfo,true));

//自分の商品の連絡掲示板の新着情報を取得
$bordInfo = getMybordMessage($_SESSION['user_id']);
debug('自分の商品の連絡掲示板の新着情報:' . print_r($bordInfo,true));
?>


<?php
  $siteTitle = 'マイページ';
  require('head.php');
?>

  <body class="page-mypage page-2colum page-logined">
    <style>
      #main {
        border: none !important;
      }
    </style>

    <!-- メニュー -->
    <?php require('header.php'); ?>

    <!-- メッセージ表示 -->
    <p id="js-show-msg" style="display: none;" class="msg-slide">
      <?php echo getSessionMessage('msg-success'); ?>
    </p>

    <!-- メインコンテンツ -->
    <div id="contents" class="site-width">
      
      <h1 class="page-title">MYPAGE</h1>

      <!-- Main -->
      <section id="main" >
         <section class="list panel-list">
           <h2 class="title">
            登録した推し情報一覧
           </h2>
           <?php 
            if (!empty($productInfo)) {
              foreach ($productInfo as $key => $val) {
           ?>
              <a href="registProduct.php?p_id=<?php echo sanitize($val['id']); ?>" class="panel">
                <div class="panel-head">
                  <img src="<?php echo  showImg(sanitize($val['pic1'])); ?>" alt="<?php  echo sanitize($val['name']); ?>">
                </div>
                <div class="panel-body">
                  <p class="panel-title"><?php echo sanitize($val['name']); ?></p>
                </div>
              </a>
           <?php
              }
            } else {
              echo '登録した推し情報はまだありません';
            }
           ?>
         </section>
         
         <style>
           .list{
             margin-bottom: 30px;
           }
        </style>
         
        <section class="list list-table">
          <h2 class="title">
            掲示板一覧
          </h2>
          <table class="table">
          <?php if (!empty($bordInfo)) { ?>
            <thead>
              <tr>
                <th>最新送信日時</th>
                <th>送信者</th>
                <th>メッセージ</th>
              </tr>
            </thead>
            <tbody>
              <?php

                  foreach ($bordInfo as $key => $val) {
                    $person = getUserInfoOne($val['from_user']);
              ?>
              <tr>
                  <td><?php echo sanitize(date('Y.m.d H:i:s',strtotime($val['update_date'])));  ?></td>
                  <td><?php echo (!empty($person['name']))? $person['name'] : '名無し'; ?></td>
                  <td><a href="msg.php?m_id=<?php echo sanitize($val['id']);?>&p_id=<?php echo sanitize(($val['product_id']));  ?>"><?php  echo sanitize(mb_substr($val['comment'],0,40)).'・・・'; ?></a></td>
              </tr>
              <?php
                  }
                } else {
                  echo 'メッセージはまだありません';
                }
              ?>
            </tbody>
          </table>
        </section>
        
        <section class="list panel-list">
          <h2 class="title">
            お気に入り一覧
          </h2>
          <?php
          if (!empty($likeInfo)) {
            foreach ($likeInfo as $key => $val) {
          ?>
            <a href="productDetail.php?p_id=<?php echo sanitize($val['product_id']); ?>" class="panel">
              <div class="panel-head">
                <img src="<?php echo showImg(sanitize($val['pic1'])); ?>" alt="<?php echo sanitize($val['name']); ?>">
              </div>
              <div class="panel-body">
                <p class="panel-title"><?php echo sanitize($val['name']); ?></p>
              </div>
            </a>
          <?php
            }
          } else {
            echo 'お気に入り登録はまだありません';
          }
          ?>
        </section>
      </section>
      
      <!-- サイドバー -->
      <?php
        require('sidebar.php');
      ?>
    </div>

    <!-- footer -->
    <?php require('footer.php'); ?>
