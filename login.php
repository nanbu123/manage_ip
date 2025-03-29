<?php header("Content-Type: text/html; charset=utf-8"); ?>
<?php

include_once('./lib/config.php');
include_once('./lib/function.php');

$message = "";
$login_name = "";
if (isset($_POST['login_sub'])) {
    // ログインチェック
    $login_name = $_POST['login_name'];
    $login_pass = $_POST['login_pass'];
    if (check_login_pass($login_name, $login_pass, &$message)) {
        header('Location: ./index.php');
        exit;
    }

} else {
    // 初回表示
    $message = 'ユーザー名とパスワードを入力してください';
}


?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" >
<meta http-equiv="Content-Style-Type" content="text/css" >
<link rel="stylesheet" type="text/css" href="./css/style.css">
<title>IP管理：ログイン</title>
</head>
<body>
<h2>ログイン</h2>

<table class="none">
  <form name="login_form" action="./login.php" method="post">
  <tr>
    <td colspan="2"><?php echo $message; ?></td>
  </tr>
  <tr>
    <td colspan="2">&nbsp</td>
  </tr>
  <tr>
    <th align="right">ユーザー名：</th>
    <td><input type="text" name="login_name" value="<?php echo $login_name; ?>" style="ime-mode:disabled"></td>
  </tr>
  <tr>
    <th align="right">パスワード：</th>
    <td><input type="password" name="login_pass" value=""></td>
  </tr>
  <tr>
    <td colspan="2">&nbsp</td>
  </tr>
  <tr>
    <td colspan="2" align="center"><input type="submit" name="login_sub" value="ログイン"></td>
  </tr>
  </form>
</table>

<br><hr>
<?php echo FOOTER_STR; ?>

</body>
</html>
