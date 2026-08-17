<?php
//共通処理
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「profEdit.php：プロフィール編集');
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
    //空欄チェック
    validEmpty($username, 'name');
    //最大文字数チェック
    validMax($username, 'name', MAX_NAME);
  }

  //Eメール
  if($email !== $dbFormData['email']) {
    //最大文字数チェック
    validMax($email, 'email', MAX_EMAIL);
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
      $sql = 'UPDATE users SET name = :name, email = :email, pic = :pic
              WHERE id = :u_id';
      //dataセット
      $data = array(':name' => $username, ':email' => $email, ':pic' => $pic, ':u_id' => $_SESSION['user_id']);
      //sql実行
      $stmt = queryPost($dbh, $sql, $data);

      if ($stmt) {
        debug('プロフィール編集成功');

        //メッセージ出力
        $_SESSION['msg-success'] = SUCCESS_USER_UPDATE;

        //マイページへ遷移
        header("Location:mypage.php");
        exit();
      } else {
        debug('プロフィール編集失敗');
        $err_msg['common'] = ERR_SYSTEM;
      }
    } catch (Exception $e) {
      error_log('エラーが発生しました' . $e->getMessage());
      $err_msg['common'] = ERR_SYSTEM;
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
