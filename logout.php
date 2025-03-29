<?php header("Content-Type: text/html; charset=utf-8"); ?>
<?php

include_once('./lib/config.php');
include_once('./lib/function.php');

session_start();
$_SESSION['login'] = '';
$_SESSION['level'] = '';
$_SESSION['login_name'] = '';

header('Location: ./login.php');
exit;

?>
