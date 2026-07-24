<?php 
  $user = getUserInfoOne($_SESSION['user_id']);
?>

<header>
  <div class="site-width">
    <h1><a href="index.php"><?php echo APL_NAME.APL_SUBNAME; ?></a></h1>
    <nav id="top-nav">
      <ul>
        <?php
          if (empty($_SESSION['user_id'])) {
        ?>
            <li><a href="signup.php" class="btn btn-primary">ユーザ登録</a></li>
            <li><a href="login.php">ログイン</a></li>
        <?php
          } else {
        ?>
            <li><a href="mypage.php">マイページ</a></li>
            <li><a href="logout.php">ログアウト</a></li>
            <p><?php echo $user['name']?></p>
        <?php
          }
        ?>
      </ul>
    </nav>
  </div>
</header>