<?php
require('function.php');

$siteTitle = 'KENYU';
require('head.php');
?>

<body class="page-top">

<?php require('header.php'); ?>

<main>

  <!-- =========================
       メインビジュアル
  ========================== -->
  <section class="hero">
    <div class="hero-inner">
      <p class="hero-subtitle">剣道を、もっと気軽に。</p>
      <h1 class="hero-title">KENYU</h1>
      <p class="hero-description">
        「久しぶりに剣道したい」「たまには違う人とも稽古したい」<br>
        そんな人が、気軽に稽古できる場所を探せるサービスです。
      </p>

      <div class="hero-btns">
        <a href="index.php" class="btn btn-primary">イベントを探す</a>
        <a href="signup.php" class="btn btn-secondary">ユーザ登録する</a>
      </div>
    </div>
  </section>


  <!-- =========================
       KENYUとは
  ========================== -->
  <section class="top-section">
    <div class="top-inner">

      <h2 class="top-title">KENYUとは</h2>

      <p class="top-lead">
        「剣道はしたい。でも、道場に入るのはちょっと。。。」<br>
        そんな人が、もっと気軽に剣道できる場所を。
      </p>

      <p class="top-text">
        久しぶりに竹刀を握りたい人。<br>
        いつもとは違う相手と稽古してみたい人。<br>
        ガチな稽古ではなく、たまに楽しく地稽古したい人。<br><br>

        KENYUは、そんな人たちが自分に合った稽古や交流の場を
        見つけるためのサービスです。
      </p>

    </div>
  </section>


  <!-- =========================
       できること
  ========================== -->
  <section class="top-section top-feature">
<div class="feature-list">

  <div class="feature-item">
    <i class="fa fa-search feature-icon" aria-hidden="true"></i>
    <h3>稽古を探す</h3>
    <p>
      地域や対象から、
      自分に合った稽古やイベントを探せます。
    </p>
  </div>

  <div class="feature-item">
    <i class="fa fa-users feature-icon" aria-hidden="true"></i>
    <h3>仲間を募集する</h3>
    <p>
      「この日に少し稽古したい」
      そんな気軽な募集もできます。
    </p>
  </div>

  <div class="feature-item">
    <i class="fa fa-check-circle feature-icon" aria-hidden="true"></i>
    <h3>気軽に参加する</h3>
    <p>
      気になる稽古を見つけたら、
      参加登録して一緒に剣道できます。
    </p>
  </div>

  <div class="feature-item">
    <i class="fa fa-comments feature-icon" aria-hidden="true"></i>
    <h3>事前に話す</h3>
    <p>
      掲示板を使って、
      稽古内容などを事前に確認できます。
    </p>
  </div>

</div>  </section>


  <!-- =========================
       CTA
  ========================== -->
  <section class="top-cta">
    <div class="top-inner">
      <h2>「ちょっと剣道したい」を、もっと気軽に。</h2>

      <p>
        道場に所属しなくても、ガチな稽古じゃなくてもいい。<br>
        自分に合った剣道の楽しみ方を見つけてみませんか。
      </p>
      
      <a href="signup.php" class="btn btn-primary">ユーザ登録する</a>

    </div>
  </section>

</main>

<?php require('footer.php'); ?>