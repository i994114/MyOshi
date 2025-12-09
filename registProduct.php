<?php
//共通関数の呼び出し
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　registProduct.php:情報登録');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
require('auth.php');

//パラメータ改ざんチェック(不正なidが入力されていたらマイページに戻す)
if (!empty($p_id) && empty($dbFormData)) {
  debug('GETパラメータの商品IDが違います。マイページへ遷移します');
  header('Location:mypage.php');
  exit();
}

//登録情報編集用：推し情報IDの取得
$p_id = (!empty($_GET['p_id']))? $_GET['p_id'] : ''; 
debug('Getの値:' . print_r($_GET,true));

//登録情報編集用：フォームに表示するデータの選択
$dbFormData = (!empty($p_id))? getProductOneInfo($p_id) : '';
debug('取得した推し情報一覧' . print_r($dbFormData,true));

//新規登録か編集か(true:新規、false:編集)
$edit_flg = (empty($_GET['p_id']))? true : false;

//DBに登録されたカテゴリ情報を取り出し
$category_info = getCategory();
debug('取得したカテゴリ：' . print_r($category_info,true));

//ポスト送信があるか
if (!empty($_POST)) {
  debug('ポスト送信あり');
  debug('ポストの値：' . print_r($_POST,true));
  debug('ファイルの値：' . print_r($_FILES,true));

  //----------------------
  //ポスト送信された情報を取得
  //----------------------
  $name = $_POST['name'];
  $category_id = $_POST['category_id'];
  $comment = $_POST['comment'];


  $pic1 = (!empty($_FILES['pic1']['name']))? uploadImg($_FILES['pic1'], 'pic1') : '';
  $pic2 = (!empty($_FILES['pic2']['name']))? uploadImg($_FILES['pic2'], 'pic2') : '';
  $pic3 = (!empty($_FILES['pic3']['name']))? uploadImg($_FILES['pic3'], 'pic3') : '';

  //画像がアップロードされていなかったら、DBの画像を取得
  $pic1 = (empty($pic1) && !empty($dbFormData['pic1']))?  $dbFormData['pic1'] : $pic1;
  $pic2 = (empty($pic2) && !empty($dbFormData['pic2']))?  $dbFormData['pic2'] : $pic2;
  $pic3 = (empty($pic3) && !empty($dbFormData['pic3']))?  $dbFormData['pic3'] : $pic3;
  
  //----------------------
  //バリデーション
  //----------------------
  debug('$dbの値' . print_r($dbFormData,true));
  
  if (empty($dbFormData)) {   //新規登録のとき
    validEmpty($name, 'name');
    validMax($name, 'name');

    validEmpty($category_id, 'category_id');
    validMax($comment, 'comment');


  } else {                    //登録情報があるとき
    if ($dbFormData['name'] !== $name) {
      validEmpty($name, 'name');
      validMax($name, 'name');
    }

    if ($dbFormData['comment'] !== $comment) {
      validMax($comment, 'comment', 500);
    }

    if ($dbFormData['category_id'] !== $category_id) {
      validEmpty($category_id, 'category_id');
      validSelect($category_id, 'category_id');
    }

  }

  //----------------------
  //DB登録
  //----------------------
  if (empty($err_msg)) {
    debug('バリデーションOK');


      try {
        //db接続
        $dbh = dbConnect();

        if ($edit_flg === true) {
          //---------
          //新規登録
          //---------
          debug('DBに新規登録します');

          //sql作成
          $sql = 'INSERT INTO product (name, category_id, comment, pic1, pic2, pic3, user_id, create_date, update_date)
                  VALUES(:name, :category_id, :comment, :pic1, :pic2, :pic3, :user_id, :create_date, :update_date)';
          //dataセット
          $data = array(':name' => $name, ':category_id' => $category_id, ':comment' => $comment, ':pic1' => $pic1, ':pic2' => $pic2, ':pic3' => $pic3,
                        ':user_id' => $_SESSION['user_id'], ':create_date' => date('Y-m-d H:i:s'), ':update_date' => date('Y-m-d H:i:s'));
        } else {
          //--------
          //編集
          //--------
          debug('DBの内容を変更します');

          //sql作成
          $sql = 'UPDATE product SET name = :name, comment = :comment, pic1 = :pic1, pic2 = :pic2, pic3 = :pic3 user_id = :u_id, update_date = :date WHERE id = :p_id';
          //dataセット
          $data = array(':name' => $name, ':comment' => $comment, ':pic1' => $pic1, ':pic2' => $pic2, ':pic3' => $pic3, ':u_id' => $_SESSION['user_id'], ':date' => date('Y-m-d H:i:s'), ':p_id' => $p_id);
        }
        //sql実行
        $stmt = queryPost($dbh, $sql, $data);

        if ($stmt) {
          debug('推し情報の登録OK');
          $_SESSION['msg-success'] = SUC03;

          //マイページへ遷移
          header('Location:mypage.php');
          exit();
        } else {
          debug('推し情報の登録NG');
          $err_msg['common'] = MSG07;
        }
      } catch(Exception $e) {
        error_log('エラーが発生しました' . $e->getMessage());
        $err_msg['common'] = MSG07;
      }
  }
}

?>

<?php
  $siteTitle = ($edit_flg === true)? APL_SUBJECT.'情報編集' : APL_SUBJECT.'情報登録';
  require('head.php');
?>

  <body class="page-profEdit page-2colum page-logined">

    <!-- メニュー -->
    <?php  require('header.php'); ?>

    <!-- メインコンテンツ -->
    <div id="contents" class="site-width">
      <h1 class="page-title"><?php ($edit_flg === true)? APL_SUBJECT.'情報を編集する' : APL_SUBJECT.'情報を登録する'; ?></h1>
      <!-- Main -->
      <section id="main" >
        <div class="form-container">
          <form action="" class="form"method="post" enctype="multipart/form-data" style="width: 100%; box-sizing: border-box;">
            <div class="area-msg">
              <?php getErrInfo('common'); ?>
            </div>

            <!-- 推し情報 -->
            <label class="<?php if(!empty($err_msg['name'])) echo 'err';  ?>">
            <?php echo APL_SUBJECT.'情報'; ?><span class="label-require">必須</span>
              <input type="text" name="name" value="<?php  echo getFormData('name'); ?>">
            </label>
            <div class="area-msg">
              <?php echo getErrInfo('name'); ?>
            </div>
            
            <!-- カテゴリ -->
            <label class="<?php if(!empty($err_msg['category_id'])) echo 'err'; ?>">
              カテゴリ<span class="label-require">必須</span>
              <select name="category_id" id="">
                <option value="0" <?php if(getCategory('category_id') == 0) {echo 'selected';}  ?>>選択してください</option>
                  <?php foreach ($category_info as $key => $val) {?>
                    <option value="<?php  echo $val['id']; ?>" <?php if(getFormData('category_id') == $val['id'] ){ echo 'selected'; }   ?>>
                      <?php echo $val['name']; ?>
                    </option>
                  <?php }?>
              </select>

            </label>
            <div class="area-msg">
              <?php echo getErrInfo('category_id'); ?>
            </div>
            
            <!-- 推しポイント -->
            <label class="<?php if(!empty($err_msg['commnet'])) echo 'err'; ?>">
              <?php echo APL_SUBJECT.'ポイント'; ?>
              <textarea name="comment" id="js-count" cols="30" rows="10" style="height:150px;"><?php echo getFormData('comment'); ?></textarea>
            </label>
            <p class="counter-text"><span id="js-count-view">0</span>/255文字</p>
            <div class="area-msg">
              <?php  echo getErrInfo('comment'); ?>
            </div>
            
            <div style="overflow: hidden;">
              <!-- 画像1 -->
              <div class="imgDrop-container">
                画像１
                <label class="area-drop <?php  if(!empty($err_msg['pic1'])) echo 'err' ?>" >
                  <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
                  <input type="file" name="pic1" class="input-file">
                  <img src="<?php echo getFormData('pic1'); ?>" alt="" class="prev-img" style="<?php  if(empty($dbFormData['pic1'])) echo 'display: none;'; ?>">
                  ドラッグ＆ドロップ
                </label>
                  <div class="area-msg">
                    <?php  echo getErrInfo('pic1'); ?>
                  </div>
              </div>

              <!-- 画像2 -->
              <div class="imgDrop-container">
                画像２
                <label class="area-drop <?php if(!empty($err_msg['pic2'])) echo 'err'; ?>" >
                  <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
                  <input type="file" name="pic2" class="input-file">
                  <img src="<?php  echo getFormData('pic2'); ?>" alt="" class="prev-img" style="<?php if(empty($dbFormData['pic2'])) echo 'display: none;';  ?>">
                  ドラッグ＆ドロップ
                </label>
                <div class="area-msg">
                    <?php echo getErrInfo('pic2'); ?>
                </div>
              </div>

              <!-- 画像3 -->
              <div class="imgDrop-container">
                画像３
                <label class="area-drop <?php if(!empty($err_msg['pic3'])) echo 'err'; ?>" >
                  <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
                  <input type="file" name="pic3" class="input-file">
                  <img src="<?php  echo getFormData('pic3'); ?>" alt="" class="prev-img" style="<?php  if(empty($dbFormData['pic3'])) echo 'display: none;'; ?>">
                  ドラッグ＆ドロップ
                </label>
                <div class="area-msg">
                  <?php  echo getErrInfo('pic3'); ?>
                </div>
              </div>
            </div>

            <div class="btn-container">
              <input type="submit" class="btn btn-mid" value="<?php echo ($edit_flg === true)? '紹介する' : '編集する'; ?>">
            </div>
          </form>
        </div>
      </section>

      <!-- サイドバー -->
      <?php  require('sidebar.php'); ?>
    </div>

    <!-- footer -->
    <?php  require('footer.php'); ?>