<?php 
//共通関数呼び出し
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「ajaxLike.php:お気に入り登録、解除');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//認証判定
require('auth.php');

if (!empty($_POST)) {
    debug('ajax_ok');
    debug('ポストの値：' . print_r($_POST,true));

    //お気に入り連打防止
    $now = microtime(true);

    if (isset($_SESSION['last_like_time']) && ($now - $_SESSION['last_like_time']) < 1) {
        http_response_code(429);
        exit();        
    }
    //お気に入り登録/解除した時間を記録
    $_SESSION['last_like_time'] = $now;

    //当該イベントのお気に入り数を確認(あれば現在お気に入り登録しているという意味)
    $likeCount = isLike($_POST['eventId'], $_SESSION['user_id']);

    if ($likeCount !== 0) {
        debug('お気に入りのDBデータを削除します');
        likeDelete($_POST['eventId'], $_SESSION['user_id']);
    } else {
        debug('お気に入りのDBデータを追加します');
        likeRegister($_POST['eventId'], $_SESSION['user_id']);
    }
}

?>