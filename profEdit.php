<?php
//共通処理
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　profEdit.php：プロフィール編集');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//認証処理
require('auth.php');

//DBからユーザデータを取得
$dbFormData = getUserInfoOne($_SESSION['user_id']);
debug('取得したユーザ情報' . print_r($dbFormData,true));

//POSTされたか
if (!empty($_POST)) {
  debug('ポスト送信があります');

  //-------------------
  //ポストされた情報の取得
  //-------------------
  $username = $_POST['name'];
  $tel = $_POST['tel'];
  $zip = (!empty($_POST['zip']))? $_POST['zip'] : 0 ;
  $addr = $_POST['addr'];
  $age = $_POST['age'];
  $email = $_POST['email'];

  //-------------------
  //画像情報の取得
  //-------------------
  debug('$_FILESの値：' . print_r($_FILES,true));
  $pic = (!empty($_FILES['pic']['name']))? uploadImg($_FILES['pic'],'pic') : 'img/00016.jpg';
  $pic = (empty($_FILES['pic']['name']) && !empty($dbFormData['pic']))? $dbFormData['pic'] : $pic;

  //-------------------------------------------------
  //バリデーションチェック
  //(DBの登録データとPOSTのデータに差分がある場合のみおこなう)
  //-------------------------------------------------

  //名前
  if ($username !== $dbFormData['name']) {
    //最大文字数チェック
    validMax($username, 'name');
  }

  //電話番号
  if ($tel !== "" && $tel !== $dbFormData['tel']) {
    //電話番号の形式かチェック
    validTel($tel, 'tel');
  }

  //郵便番号
  if ($zip !== (int)$dbFormData['zip']) {
    //郵便番号チェック
    validZip($zip, 'zip');
  }

  //住所
  if ($addr !== $dbFormData['addr']) {
    validMax($addr, 'addr');
  }

  //年齢
  if ($age !== $dbFormData['age']) {
    //数字チェック
    validNum($age, 'age');
    //不正入力チェック
    validAge($age, 'age');
  }

  //Eメール
  if($email !== $dbFormData['email']) {
    //最大文字数チェック
    validMax($email, 'email');
    //最小文字数チェック
    validMin($email, 'email');
    //Eメール形式か
    validEmail($email, 'email');
    //空欄チェック
    validEmpty($email, 'email');
  }

  if (empty($err_msg)) {
    debug('バリデーションOK');

    try {
      //dbセット
      $dbh = dbConnect();
      //sql作成
      $sql = 'UPDATE users SET name = :name, tel = :tel, zip = :zip, addr = :addr, age = :age, email = :email, pic = :pic
              WHERE id = :u_id';
      //dataセット
      $data = array(':name' => $username, ':tel' => $tel, ':zip' => $zip, ':addr' => $addr, ':age' => $age, ':email' => $email, ':pic' => $pic, ':u_id' => $_SESSION['user_id']);
      //sql実行
      $stmt = queryPost($dbh, $sql, $data);

      if ($stmt) {
        debug('プロフィール編集成功');

        //メッセージ出力
        $_SESSION['msg-success'] = SUC07;

        //マイページへ遷移
        header("Location:mypage.php");
      } else {
        debug('プロフィール編集失敗');
        $err_msg['common'] = MSG07;
      }
    } catch (Exception $e) {
      error_log('エラーが発生しました' . $e->getMessage());
      $err_msg['common'] = MSG07;
    }
  } else {
    debug('バリデーションNG');
  }
}

?>



<?php
  $siteTitle = 'プロフィール編集';
  require('head.php')
?>

  <body class="page-profEdit page-2colum page-logined">

    <!-- メニュー -->
    <header>
      <div class="site-width">
        <h1><a href="index.php"><?php echo APL_NAME.APL_SUBNAME; ?></a></h1>
        <nav id="top-nav">
          <ul>
            <li><a href="mypage.php">マイページ</a></li>
            <li><a href="">ログアウト</a></li>
          </ul>
        </nav>
      </div>
    </header>

    <!-- メインコンテンツ -->
    <div id="contents" class="site-width">
      <h1 class="page-title">プロフィール編集</h1>
      <!-- Main -->
      <section id="main" >
        <div class="form-container">
          <form action="" class="form" method="post" enctype="multipart/form-data">
            <div class="area-msg">
              <?php
                if (!empty($err_msg['common'])) {
                  echo $err_msg['common'];
                }
              ?>
            </div>
          
            <!-- 名前 -->
            <label class="<?php if(!empty($err_msg['name'])) echo 'err'; ?>">
              名前
              <input type="text" name="name" value="<?php echo getFormData('name'); ?>">
            </label>
            <div class="area-msg">
                <?php if(!empty($err_msg['name'])) echo $err_msg['name']; ?>
            </div>
            
            <!-- TEL -->
            <label class="<?php if(!empty($err_msg['tel'])) echo 'err'; ?>">
                TEL
                <input type="text" name="tel" value="<?php echo getFormData('tel'); ?>">
            </label>
            <div class="area-msg">
                <?php if(!empty($err_msg['tel'])) echo $err_msg['tel']; ?>
            </div>
            
            <!-- 郵便番号 -->
            <label class="<?php if(!empty($err_msg['zip'])) echo 'err'; ?>">
                郵便番号
                <input type="text" name="zip" value="<?php echo getFormData('zip'); ?>">
            </label>
            <div class="area-msg">
                <?php if(!empty($err_msg['zip'])) echo $err_msg['zip']; ?>
            </div>
            
            <!-- 住所 -->
            <label class="<?php if(!empty($err_msg['addr'])) echo 'err'; ?>">
                住所
                <input type="text" name="addr" value="<?php echo getFormData('addr'); ?>">
            </label>
            <div class="area-msg">
                <?php if(!empty($err_msg['addr'])) echo $err_msg['addr']; ?>
            </div>
            
            <!-- 年齢 -->
            <label style="text-align:left;" class="<?php if(!empty($err_msg['age'])) echo 'err'; ?>">
              年齢
                <input type="number" name="age" value="<?php echo getFormData('age'); ?>">
            </label>
            <div class="area-msg">
                <?php if(!empty($err_msg['age'])) echo $err_msg['age']; ?>
            </div>
            
            <!-- email -->
            <label class="<?php if(!empty($err_msg['email'])) echo 'err'; ?>">
                Email
                <input type="text" name="email" value="<?php echo getFormData('email'); ?>">
            </label>
            <div class="area-msg">
                <?php if(!empty($err_msg['email'])) echo $err_msg['email']; ?>
            </div>

            <!-- プロフィール画像 -->
            プロフィール画像
            <label class="area-drop <?php  if(!empty($err_msg['pic'])) echo 'err'; ?>" style="width: 400px; height: 400px; line-height: 400px;">
              <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
              <input type="file" name="pic" class="input-file" style="height: 400px;">
              <img src="<?php  echo getFormData('pic'); ?>" alt="" class="prev-img" style="<?php if(empty($dbFormData['pic'])) echo 'display: none';  ?>">
              ドラッグ＆ドロップ
            </label>
            <div class="area-msg">
                <?php  echo getErrInfo('pic'); ?>
            </div>

            <div class="btn-container">
              <input type="submit" class="btn btn-mid" value="変更する">
            </div>
          </form>
        </div>
      </section>
      
      <!-- サイドバー -->
      <?php require('sidebar.php'); ?>
    </div>

    <!-- footer -->
    <?php require('footer.php'); ?>

  </body>
</html>
