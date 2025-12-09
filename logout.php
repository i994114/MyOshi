<?php 
//共通関数呼びだし
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　logout.php：ログアウト');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//セッション削除
debug('ログアウトします');
session_destroy();
$_SESSION = array();

debug('ログイン画面へ遷移します');

//ログイン画面へ遷移
header("Location:login.php");

?>