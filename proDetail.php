<?php
//共通関数
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　proDetail.php:ユーザ情報詳細');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//認証
require('auth.php');

//Get情報の取得
debug('Getの値：' . print_r($_GET,true));
$u_id = (!empty($_GET['u_id']))? $_GET['u_id'] : '';
$p = (!empty($_GET['p']))? $_GET['p'] : '';

//選択された情報を取得
$user_data = getUserInfoOne($u_id);
debug('取得したユーザ情報：' . print_r($user_data,true));

//不正なアクセスでないか判定

if (empty($user_data)) {
  debug('不正なURLです。情報一覧に戻ります');
  $err_msg['common'] = ERR_SYSTEM;

  header('Location:index.php');
  exit();
}

//情報一覧画面に戻る際のURL(Getデータ)
$str = appendGetParam(array('u_id'));
//先頭の&を削除
$str = mb_substr($str, 1);
debug('生成したGetパラメータ部分のURL：' . $str);



?>

