<?php

if(!empty($_SESSION['login_date'])){
    debug('ログイン済みユーザです');

    //現在日時が最終ログイン日時＋有効期限をすぎていた場合
    if(($_SESSION['login_date'] + $_SESSION['login_limit']) < time()) {
        debug('ログイン期限オーバーです');
        
        //セッション削除
        session_destroy();
        $_SESSION = array();

        //ログイン画面へ遷移
        header("Location:login.php");

    } else {
        debug('ログイン有効期限OKです');

        //最終ログイン日時を現在日時に
        $_SESSION['login_date'] = time();

        if (basename($_SERVER['PHP_SELF']) === 'login.php') {
            header("Location:mypage.php");
        }

    }
} else {
    debug('未ログインユーザです');
    if(basename($_SERVER['PHP_SELF']) !== 'login.php') {
        //ログイン画面へ遷移
        header("Location:login.php");
    }
}

?>