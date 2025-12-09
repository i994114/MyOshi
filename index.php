<?php
//共通関数読みだし
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　index.php:推し情報一覧');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();


//現在のページを取得
$now_page = (!empty($_GET['p']))? $_GET['p'] : 1;
//検索条件(カテゴリ)を取得
$seach_cate = (!empty($_GET['category_id']))? $_GET['category_id'] : 0;
//検索条件(ソート)を取得
$seach_sort = (!empty($_GET['sort']))? $_GET['sort'] : 0;

//GETパラメータ取得(GetのURL生成用。「index.php?category_id=1&sort=2」の？以降の文字列作成)
//「p=◯」の文字列を複数出力しないように、引数にpを設定
$str = appendGetParam(array('p'));

debug('Getの値：' . print_r($_GET,true));
debug('一覧表示時点のセッションの値:' . print_r($_SESSION,true));
//不正なアクセス判定
if (!is_int((int)$now_page)) {
  debug('不正なページへのリンク指定がありました');
  header('Locaiton:index.php');
  exit();
}

//1ページに表示する数
$list_span = 20;

//表示するアイコンの数
$page_num = 5;

//表示するレコード
//先頭(例:1ページ目なら0,2ページ目なら20,3ページ目なら40)
$display_min = ($now_page -1 ) * $list_span;
//最後(例:1ページ目なら20,2ページ目なら40,3ページ目なら60)
$display_max = $display_min - 1 + $list_span;

//推し情報を取得
$product = getProductList($list_span, $display_min, $seach_cate, $seach_sort);
debug('すべての推し情報：' . print_r($product, true));


//カテゴリデータの取得
$category = getCategory();

//登録情報編集用：フォームに表示するデータの選択
$dbFormData = getFormData('category_id');

?>

<?php
  $siteTitle = '商品一覧';
  require('head.php');
?>

  <body class="page-home page-2colum">

    <!-- メニュー -->
    <?php require('header.php'); ?>

    <!-- メッセージ表示 -->
    <p id="js-show-msg" style="display: none;" class="msg-slide">
      <?php echo getSessionMessage('msg-success'); ?>
    </p>

    <!-- メインコンテンツ -->
    <div id="contents" class="site-width">

      <!-- サイドバー -->
      <section id="sidebar">
        <form alt="" method="Get">
          <h1 class="title">カテゴリー</h1>
          <div class="selectbox">
            <span class="icn_select"></span>
            <select name="category_id">
              <option value="0"　<?php if(getFormData('category_id',true) == 0) {echo 'selected';} ?>>選択してください</option>
              <?php foreach($category as $key => $val) {?>
                <option value="<?php echo sanitize($val['id']); ?>" <?php if(getFormData('category_id',true) == $val['id']) echo 'selected'; ?>><?php echo sanitize($val['name']); ?></option>
              <?php }?>
            </select>
          </div>
          <h1 class="title">表示順</h1>
          <div class="selectbox">
            <span class="icn_select"></span>
            <select name="sort">
              <option value="0" <?php if(getFormData('sort',true) == 0) echo 'selected'; ?>>選択してください</option>
              <option value="1" <?php if(getFormData('sort',true) == 1) echo 'selected'; ?>>登録日付が新しい順</option>
              <option value="2" <?php if(getFormData('sort',true) == 2) echo 'selected'; ?>>最近編集された順</option>
            </select>
          </div>
          <input type="submit" value="検索">
        </form>

      </section>

      <!-- Main -->
      <section id="main" >
        <div class="search-title">
          <div class="search-left">
            <span class="total-num"><?php echo sanitize($product['total_record']);?>コの推しがみつかったよ！</span>
          </div>
          <div class="search-right">
            <span class="wf-nicomoji"><?php echo sanitize($display_min+1); ?>-</span><span class="wf-nicomoji"><?php echo sanitize($display_max); ?>件</span> / <span class="wf-nicomoji"><?php echo sanitize($product['total_record']);?>件中</span>
          </div>
        </div>
        <div class="panel-list">
          <?php foreach ($product['data'] as $key => $val) {?>
            <a href="<?php echo 'productDetail.php?'.'p_id='.$val['id'].appendGetParam(); ?>" class="panel">
            <div class="panel-head">
              <img src="<?php echo showImg(sanitize($val['pic1'])); ?>" alt="<?php echo sanitize($val['name'])?>">
            </div>
            <div class="panel-body">
              <p class="panel-title"><?php echo sanitize($val['name']); ?></p>
            </div>
          </a>
          <?php }?>
        </div>

        <!-- ページネーション -->
        <?php pagenation($now_page, $product['total_page'], $page_num, $str); ?>
        
      </section>

    </div>

    <!-- footer -->
    <?php  require('footer.php'); ?>