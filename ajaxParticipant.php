<?php 
//共通関数呼び出し
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「ajaxParticipant.php:参加登録、解除');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//認証判定
require('auth.php');

if (!empty($_POST)) {
    debug('ajax_ok');
    debug('ポストの値：' . print_r($_POST,true));

    $participantCount = isEventParticipants($_POST['eventId'], $_SESSION['user_id']);
    debug($participantCount);

    if ($participantCount !== 0) {
        debug('参加のDBデータを削除します');
        eventParticipantDelete($_POST['eventId'], $_SESSION['user_id']);
    } else {
        debug('参加のDBデータを追加します');
        eventParticipantRegister($_POST['eventId'], $_SESSION['user_id']);
    }
}

?>