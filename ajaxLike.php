<?php 
//共通関数呼び出し
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　productDetail.php:情報詳細');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//認証判定
require('auth.php');

if (!empty($_POST)) {
    debug('ajax_ok');
    debug('ポストの値：' . print_r($_POST,true));

    $likeCount = isLike($_POST['productId'], $_SESSION['user_id']);
    debug($likeCount);

    if ($likeCount !== 0) {
        debug('お気に入りのDBデータを削除します');
        likeDelete($_POST['productId'], $_SESSION['user_id']);
    } else {
        debug('お気に入りのDBデータを追加します');
        likeRegister($_POST['productId'], $_SESSION['user_id']);
    }
}

?>