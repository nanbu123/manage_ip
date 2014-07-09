<?php

include_once('./lib/config.php');
include_once('./lib/function.php');
include_once('./lib/db_access.php');

// ログインチェック
if (!check_session_login()) { exit; }

$db = new db_access();
if ($_GET['id']) {
    $user_id = $_GET['id'];
    $where = "WHERE id = {$user_id}";
    if ($user_data = $db->select(TABLE_USER, "*", $where)) {
        $user_name = $user_data[0]['name'];
    }
}


if (isset($_POST['sub_password'])) {
    if ($user_id) {
        // 新しいパスワード
        if ($_POST['new_pass']) {
            $new_pass = $_POST['new_pass'];
        } else {
            $result_msg = '<font color="red">新しいパスワードを入力してください</font><br>' . "\n";
        }
        // 新しいパスワード（確認用）
        if ($_POST['con_pass']) {
            $con_pass = $_POST['con_pass'];
        } else {
            $result_msg = '<font color="red">新しいパスワード（確認用）を入力してください</font><br>' . "\n";
        }
        if ($user_name != "" && $new_pass != "" && $con_pass != "") {
            // 現在のパスワードが一致するか
            if ($new_pass == $con_pass) {
                $params['password'] = md5($new_pass);
                $params['update_user_name'] = $_SESSION['login_name'];
                $params['update_date'] = 'NOW()';
                $where = "WHERE name = '" . $user_name . "'";
                if ($db->update(TABLE_USER, $params, $where)) {
                    $result_msg = '<font color="blue">パスワードを変更しました</font><br>' . "\n";
                } else {
                    $result_msg = '<font color="red">パスワードの変更に失敗しました</font><br>' . "\n";
                }
            } else {
                $result_msg = '<font color="red">新しいパスワード（確認用）が一致しません</font><br>' . "\n";
            }
        }
    } else {
        // 現在のパスワード
        if ($_POST['cur_pass']) {
            $cur_pass = $_POST['cur_pass'];
        } else {
            $result_msg = '<font color="red">現在のパスワードを入力してください</font><br>' . "\n";
        }
        // 新しいパスワード
        if ($_POST['new_pass']) {
            $new_pass = $_POST['new_pass'];
        } else {
            $result_msg = '<font color="red">新しいパスワードを入力してください</font><br>' . "\n";
        }
        // 新しいパスワード（確認用）
        if ($_POST['con_pass']) {
            $con_pass = $_POST['con_pass'];
        } else {
            $result_msg = '<font color="red">新しいパスワード（確認用）を入力してください</font><br>' . "\n";
        }
        if ($cur_pass != "" && $new_pass != "" && $con_pass != "") {

            // 現在のパスワードが一致するか
            $login_name = $_SESSION['login_name'];
            $pass_md5 = md5($cur_pass);
            $where = "WHERE (del_flag != 1) AND ( name = '{$login_name}' ) AND ( password = '{$pass_md5}' )";
            if ($db->select(TABLE_USER, "id", $where)) {
                if ($new_pass == $con_pass) {
                    $params['password'] = md5($new_pass);
                    $params['update_user_name'] = $_SESSION['login_name'];
                    $params['update_date'] = 'NOW()';
                    $where = "WHERE name = '" . $login_name . "'";
                    if ($db->update(TABLE_USER, $params, $where)) {
                        $result_msg = '<font color="blue">パスワードを変更しました</font><br>' . "\n";
                    } else {
                        $result_msg = '<font color="red">パスワードの変更に失敗しました</font><br>' . "\n";
                    }
                } else {
                    $result_msg = '<font color="red">新しいパスワード（確認用）が一致しません</font><br>' . "\n";
                }
            } else {
                $result_msg = '<font color="red">現在のパスワードが一致しません</font><br>' . "\n";
            }
        }
    }
}

?>

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=euc-jp" >
<meta http-equiv="Content-Style-Type" content="text/css" >
<link rel="stylesheet" type="text/css" href="./css/style.css">
<title>IP管理：検索</title>
</head>
</script>
<body>

<h2>パスワード変更</h2>
<!-- 全体 -->
<table class="none">
<tr>
<td width="120" valign="top">
    <!-- メニュー開始 -->
    <?php require_once('./menu.php'); ?>
    <!-- メニュー終了 -->
</td>
<td>
    <!-- メイン開始 -->
<?php
    echo $result_msg;
?>
<font color="red"><?php if ($err_flag) { echo $err_msg; } ?></font>
<table class="input">
<form name="pass_form" action="./user_password.php?id=<?php echo $user_id; ?>" method="POST">
  <tr>
    <?php if ($user_id) { ?>
    <td colspan="2"><?php echo $user_name; ?>のパスワード変更</td>
    <?php } else { ?>
    <td colspan="2">パスワード変更</td>
    <?php } ?>
  </tr>
  <tr>
    <?php if (!$user_id) { ?>
    <th>
        現在のパスワード：
    </th>
    <td><input type="password" name="cur_pass"></td>
    <?php } ?>
  </tr>
  <tr>
    <th>
        新しいパスワード：
    </th>
    <td><input type="password" name="new_pass"></td>
  </tr>
  <tr>
    <th>
        新しいパスワード（確認用）：
    </th>
    <td><input type="password" name="con_pass"></td>
  </tr>
  <tr>
    <td colspan="2" align="center">
        <input type="submit" name="sub_password" value="変更">
    </td>
  </tr>
</form>
</table>
    <!-- メイン終了 -->
</td>
</tr>
</table>
<!-- 全体終了 -->


<hr>
<?php echo FOOTER_STR; ?>

</body>
</html>
