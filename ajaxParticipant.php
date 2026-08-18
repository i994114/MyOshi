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

    //ボタン連打防止
    $now = microtime(true);

    if (isset($_SESSION['last_participant_time']) && ($now - $_SESSION['last_participant_time']) < 1) {
        http_response_code(429);
        exit();        
    }
    //参加/参加取り消しした時間を記録
    $_SESSION['last_participant_time'] = $now;

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