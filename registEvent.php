<?php
//共通関数の呼び出し
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「registEvent.php:' . APL_SUBJECT . '情報登録');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
require('auth.php');

//パラメータ改ざんチェック(不正なidが入力されていたらマイページに戻す)
if (!empty($e_id) && empty($dbFormData)) {
  debug('GETパラメータの' . APL_SUBJECT . 'IDが違います。マイページへ遷移します');
  header('Location:mypage.php');
  exit();
}

//登録情報編集用：イベント情報IDの取得
$e_id = (!empty($_GET['e_id']))? $_GET['e_id'] : ''; 

//登録情報編集用：フォームに表示するデータの選択
$dbFormData = (!empty($e_id))? getEventOneInfo($e_id) : '';

//新規登録か編集か(true:新規、false:編集)
$edit_flg = (empty($_GET['e_id']))? true : false;

//DBに登録されたカテゴリ情報を取り出し
$category_info = getCategory();

//都道府県データの取得
$prefecture_info = getPrefecture();

//市町村データの取得
//$city_info = getCity();

//DBに登録された対象情報を取り出し
$target_info = getTarget();

//ポスト送信があるか
if (!empty($_POST)) {
  debug('ポスト送信あり');
  debug('ポストの値：' . print_r($_POST,true));
  debug('ファイルの値：' . print_r($_FILES,true));

  //まず、削除ボタンが押されたかを確認
  if (!empty($_POST['delete'])) {
    debug('削除ボタンが押されました');

    //----------------------
    //削除処理
    //----------------------
    try {
      //db接続
      $dbh = dbConnect();

      //sql作成
      $sql = 'UPDATE events SET delete_flg = 1 WHERE id = :e_id';
      $data = array(':e_id' => $e_id);

      //sql実行
      $stmt = queryPost($dbh, $sql, $data);

      if ($stmt) {
        debug('削除に成功しました');
        $_SESSION['msg-success'] = SUCCESS_EVENT_DELETE;

        //マイページへ遷移
        header('Location:mypage.php');
        exit();
      } else {
        debug('削除に失敗しました');
        $err_msg['common'] = ERR_SYSTEM;
      }
    } catch(Exception $e) {
      error_log('エラーが発生しました' . $e->getMessage());
      $err_msg['common'] = ERR_SYSTEM;
    }
  }

  //----------------------
  //ポスト送信された情報を取得
  //----------------------
  $name = $_POST['name'];
  $category_id = $_POST['category_id'];
  $prefecture_id = $_POST['prefecture_id'];
  $event_date = $_POST['event_date'];
  $time_undecided = (!empty($_POST['time_undecided']))? 1 : 0;
  $description = $_POST['description'];
  $target = $_POST['target'] ?? [];
  $event_id =(empty($e_id))? '' : $e_id;
  
  if ($time_undecided) {
    $start_time = null;
    $end_time = null;
  } else {
    $start_time = $_POST['start_hour'] . ':' . $_POST['start_minute'] . ':00';
    $end_time = $_POST['end_hour'] . ':' . $_POST['end_minute'] . ':00';
  }

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
    validMax($name, 'name', MAX_EVENT_NAME);

    validEmpty($category_id, 'category_id');
    validSelect($category_id, 'category_id');
    validEmpty($prefecture_id, 'prefecture_id');
    validSelect($prefecture_id, 'prefecture_id');
    validMax($description, 'description', MAX_DESCRIPTION);
    validTarget($target, 'target_id');
    validDate($event_date, 'event_date');
    
    if (!$time_undecided) {
      validEmpty($start_time, 'start_time');
      validEmpty($end_time, 'end_time');
      
      validTime($start_time, 'start_time');
      validTime($end_time, 'end_time');

      //開始時間と終了時間の大小チェック
      //(時間フォーマットが正しい場合のみチェックする)
      if (!empty($start_time) && !empty($end_time)) {
        validTimeRange($start_time, $end_time, 'end_time');
      }
    } 
  } else {                    //登録情報があるとき
    if ($dbFormData['name'] !== $name) {
      validEmpty($name, 'name');
      validMax($name, 'name');
    }

    if ($dbFormData['description'] !== $description) {
      validMax($description, 'description', MAX_DESCRIPTION);
    }

    if ($dbFormData['category_id'] !== $category_id) {
      validEmpty($category_id, 'category_id');
      validSelect($category_id, 'category_id');
    }

    if ($dbFormData['target'] !== $target) {
      validEmpty($target, 'target');
    }

    if (!empty($event_id)) {
      validEmpty($event_id, 'event_id');
    }

    if ($dbFormData['prefecture_id'] !== $prefecture_id) {
      validEmpty($prefecture_id, 'prefecture_id');
      validSelect($prefecture_id, 'prefecture_id');
    }

    if ($dbFormData['event_date'] !== $event_date) {
      validDate($event_date, 'event_date');
    }

    if ($dbFormData['start_time'] !== $start_time) {
      if (!$time_undecided) {
        validTime($start_time, 'start_time');
      }
    } 

    if ($dbFormData['end_time'] !== $end_time) {
      if (!$time_undecided) {
        validTime($end_time, 'end_time');
      }
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
          $sql = 'INSERT INTO events (name, category_id, prefecture_id, event_date, start_time, end_time, description, pic1, pic2, pic3, user_id, create_date, update_date)
                  VALUES(:name, :category_id, :prefecture_id, :event_date, :start_time, :end_time, :description, :pic1, :pic2, :pic3, :user_id, :create_date, :update_date)';
          //dataセット
          $data = array(':name' => $name, ':category_id' => $category_id, ':prefecture_id' => $prefecture_id, ':event_date' => $event_date, ':start_time' => $start_time, ':end_time' => $end_time, ':description' => $description, ':pic1' => $pic1, ':pic2' => $pic2, ':pic3' => $pic3,
                        ':user_id' => $_SESSION['user_id'], ':create_date' => date('Y-m-d H:i:s'), ':update_date' => date('Y-m-d H:i:s'));
            
        } else {
          //--------
          //編集
          //--------
          debug('DBの内容を変更します');

          //sql作成
          $sql = 'UPDATE events SET name = :name, category_id = :category_id, prefecture_id = :prefecture_id, event_date = :event_date, start_time = :start_time, end_time = :end_time, description = :description, pic1 = :pic1, pic2 = :pic2, pic3 = :pic3, user_id = :u_id, update_date = :date WHERE id = :e_id';
          //dataセット
          $data = array(':name' => $name, ':category_id' => $category_id, ':prefecture_id' => $prefecture_id, ':event_date' => $event_date, ':start_time' => $start_time, ':end_time' => $end_time, ':description' => $description, ':pic1' => $pic1, ':pic2' => $pic2, ':pic3' => $pic3, ':u_id' => $_SESSION['user_id'], ':date' => date('Y-m-d H:i:s'), ':e_id' => $e_id);
        }
        //sql実行
        $stmt1 = queryPost($dbh, $sql, $data);

        //------------------
        //イベント対象情報を登録
        //------------------
        if ($edit_flg === true) {
          //新規登録のため、最後に登録したイベントIDを取得
          $event_id = $dbh->lastInsertId();
        } else {
          //編集のため、対象イベントIDはGETパラメータから取得
          $event_id = $e_id;
        }

        $stmt2 = true;
        foreach($target as $val) {
          debug('foreach開始 target=' . $val);
          $sql = 'INSERT INTO event_targets (event_id, target_id) VALUES (:event_id, :target_id)';
          $data = array(':event_id' => $event_id, ':target_id' => $val);

          //sql実行
          $stmt2 = queryPost($dbh, $sql, $data);

          if (!$stmt2) {
            debug('イベント対象情報の登録に失敗しました');
            break;
          }
        }

        if ($stmt1 && $stmt2) {
          debug('イベント情報の登録OK');
          $_SESSION['msg-success'] = SUCCESS_EVENT_REGISTER;

          //マイページへ遷移
          header('Location:mypage.php');
          exit();
        } else {
          debug('イベント情報の登録NG');
          $err_msg['common'] = ERR_SYSTEM;
        }
      } catch(Exception $e) {
        error_log('エラーが発生しました' . $e->getMessage());
        $err_msg['common'] = ERR_SYSTEM;
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

            <!-- イベント情報 -->
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
                <option value="" <?php if(getCategory('category_id') == 0) {echo 'selected';}  ?>>選択してください</option>
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

            <!-- 対象 -->
            <label class="<?php if(!empty($err_msg['target_id'])) echo 'err'; ?>">
              対象
                  <?php foreach ($target_info as $key => $val) {?>
                    <input type="checkbox" name="target[]" value="<?php  echo $val['id']; ?>" <?php if(in_array($val['id'], (array)getFormData('target'))){ echo 'checked'; }   ?>>
                      <?php echo $val['name']; ?>
                  <?php }?>
            </label>
            <div class="area-msg">
              <?php echo getErrInfo('target_id'); ?>
            </div>

            <!-- 都道府県選択 -->
            <label class="<?php if(!empty($err_msg['prefecture_id'])) echo 'err'; ?>">
              都道府県<span class="label-require">必須</span>
              <select name="prefecture_id" id="">
                <option value="" <?php if(getFormData('prefecture_id') == 0) {echo 'selected';}  ?>>選択してください</option>
                  <?php foreach ($prefecture_info as $key => $val) {?>
                    <option value="<?php  echo $val['id']; ?>" <?php if(getFormData('prefecture_id') == $val['id'] ){ echo 'selected'; }   ?>>
                      <?php echo $val['name']; ?>
                    </option>
                  <?php }?>
              </select>

            </label>
            <div class="area-msg">
              <?php echo getErrInfo('prefecture_id'); ?>
            </div>

            <!-- イベント日 -->
            <label class="<?php if(!empty($err_msg['event_date'])) echo 'err'; ?>">
              <?php echo APL_SUBJECT.'日'; ?><span class="label-require">必須</span>
              <input type="date" name="event_date" value="<?php echo getFormData('event_date'); ?>">
            </label>
            <div class="area-msg">
              <?php echo getErrInfo('event_date'); ?>
            </div> 

            <!-- 時間未定選択 -->
            <label>
              <input type="checkbox" name="time_undecided" value="1" class="js-time-undecided">
              時間未定
            </label>

            <!-- 開始時間：hour -->
            <label class="<?php if(!empty($err_msg['start_time'])) echo 'err'; ?>">
              <?php echo APL_SUBJECT.'開始時間'; ?>
              <select name="start_hour" class="js-start-time">
                <?php for ($i = 0; $i <= 23; $i++): ?>
                  <?php $hour = sprintf('%02d', $i); ?>
                  <option value="<?php echo $hour; ?>"
                    <?php selected('start_hour', $hour); ?>
                  >
                  <?php  echo $hour; ?>
                  </option>
                <?php endfor; ?>
              </select>
            </label>
            <div class="area-msg">
              <?php echo getErrInfo('start_time'); ?>
            </div>
            
            <!-- 開始時間:minitutes -->
            <select name="start_minute" class="js-start-time">
              <option value="00" <?php selected('start_minute', '00'); ?>>00</option>
              <option value="15" <?php selected('start_minute', '15'); ?>>15</option>
              <option value="30" <?php selected('start_minute', '30'); ?>>30</option>
              <option value="45" <?php selected('start_minute', '45'); ?>>45</option>
            </select>
            <div class="area-msg">
              <?php echo getErrInfo('start_time'); ?>
            </div>  

            <!-- 終了時間：hour -->
            <label class="<?php if(!empty($err_msg['end_time'])) echo 'err'; ?>">
              <?php echo APL_SUBJECT.'終了時間'; ?>
              <select name="end_hour" class="js-end-time">
                <?php for ($i = 0; $i <= 23; $i++): ?>
                  <?php $hour = sprintf('%02d', $i); ?>
                  <option value="<?php echo $hour; ?>"
                    <?php selected('end_hour', $hour); ?>
                  >
                  <?php  echo $hour; ?>
                  </option>
                <?php endfor; ?>
              </select>
            </label>
            <div class="area-msg">
              <?php echo getErrInfo('end_time'); ?>
            </div>
            <!-- 終了時間:minitutes -->
            <select name="end_minute" class="js-end-time">
              <option value="00" <?php selected('end_minute', '00'); ?>>00</option>
              <option value="15" <?php selected('end_minute', '15'); ?>>15</option>
              <option value="30" <?php selected('end_minute', '30'); ?>>30</option>
              <option value="45" <?php selected('end_minute', '45'); ?>>45</option>
            </select>
            <div class="area-msg">
              <?php echo getErrInfo('end_time'); ?>
            </div>  

            <!-- イベント詳細 -->
            <label class="<?php if(!empty($err_msg['description'])) echo 'err'; ?>">
              <?php echo APL_SUBJECT.'詳細'; ?>
              <textarea name="description" id="js-count" cols="30" rows="10" style="height:150px;"><?php echo getFormData('description'); ?></textarea>
            </label>
            <p class="counter-text"><span id="js-count-view">0</span>/255文字</p>
            <div class="area-msg">
              <?php  echo getErrInfo('description'); ?>
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
              <input type="submit" class="btn btn-mid" value="<?php echo ($edit_flg === true)? 'イベント登録する' : 'イベント編集する'; ?>">
            </div>

            <?php if ($edit_flg === false && !empty($_SESSION['user_id']) && $_SESSION['user_id'] === $dbFormData['user_id'] ) { ?>
              <input type="submit" class="btn btn-mid" name="delete" value="削除する" onclick="return confirm('本当に削除しますか？'); ">
            <?php }?>
          </form>
        </div>
      </section>

      <!-- サイドバー -->
      <?php  require('sidebar.php'); ?>
    </div>

    <!-- footer -->
    <?php  require('footer.php'); ?>