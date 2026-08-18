<?php 
  $user = getUserInfoOne($_SESSION['user_id']);
?>

<header>
  <div class="site-width">
    <h1><a href="index.php"><img src="<?php echo HEADER_ICON; ?>" alt="KENYU（剣友）"></a></h1>
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
            <img src="<?php echo showImg(sanitize($user['pic'])); ?>" class="header-user-icon">
            <span class="header-user-name"><?php echo sanitize($user['name']); ?></span>
            <li><a href="mypage.php">マイページ</a></li>
            <li><a href="logout.php">ログアウト</a></li>

        <?php
          }
        ?>
      </ul>
    </nav>
  </div>
</header>