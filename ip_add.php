<?php

include_once('./lib/config.php');
include_once('./lib/function.php');
include_once('./lib/db_access.php');

// ログインチェック
if (!check_session_login()) { exit; }

$err_flag = false;
$result_flag = false;
$err_msg  = '';
$result_msg = '';
if (isset($_POST['sub_add_ip'])) {
    // IPアドレス登録
    if ($_POST['add_ip'] != '') {
        $add_ip = $_POST['add_ip'];
        $add_ip_loc = $_POST['add_ip_loc'];
        // IPアドレスとして正しいか
        if (check_ip_address($add_ip, &$err_msg)) {
            if (exists_ip_address($add_ip)) {
                $result_msg  = '<font color="red">既に登録されているIPアドレスです</font><br>' . "\n";
            } else {
                // DBに登録する
                if (insert_ip_address($add_ip, $add_ip_loc)) {
                    $result_msg = '<font color="blue">IPアドレスを登録しました</font><br>' . "\n";
                    $add_ip = '';   // 登録したので初期化する
                } else {
                    $result_msg = '<font color="red">IPアドレスの登録に失敗しました</font><br>' . "\n";
                }
            }
        } else {
            $err_flag = true;
        }
    } else {
        $err_flag = true;
        $err_msg  = '登録するIPアドレスを入力してください<br>' . "\n";
    }
} elseif (isset($_POST['sub_add_group'])) {
    // まとめ登録
    if (($_POST['add_group_ip'] != '') && ($_POST['add_group_mask'] != '')) {
        $add_group_ip   = $_POST['add_group_ip'];
        $add_group_mask = $_POST['add_group_mask'];
        $group_ip_loc = $_POST['group_ip_loc'];
        // IPアドレスとして正しいか
        if (check_ip_address($add_group_ip.'/'.$add_group_mask, &$err_msg)) {
            // 最後の数字を取り出す
            $pat = '/^([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)([0-9]{1,3})$/';
            if (preg_match($pat, $add_group_ip, &$matches)) {
                // 何分割するか
                $interval = (1 << (32 - $add_group_mask));
                $loop = (256 - $matches[2]) / $interval;
                for ($i == 0; $i < $loop; $i++) {
                    $ip_address = $matches[1] . ($matches[2] + ($i * $interval)) . '/' . $add_group_mask;
                    if (exists_ip_address($ip_address)) {
                        $error_address[] = $ip_address;
                    } else {
                        // DBに登録する
                        if (insert_ip_address($ip_address, $group_ip_loc)) {
                            $result_flag = true;
                            $added_address[] = $ip_address;
                        } else {
                            $error_address[] = $ip_address;
                        }
                    }
                }
            }
            if ($result_flag) {
                if (count($error_address)>0) {
                    $result_msg = '<font color="red">IPアドレスの一部登録に失敗しました</font><br>' . "\n";
                } else {
                    $result_msg = '<font color="blue">IPアドレスを登録しました</font><br>' . "\n";
                }
            } else {
                $result_msg = '<font color="red">IPアドレスの登録に失敗しました</font><br>' . "\n";
            }
            // 登録成功したIPアドレス一覧
            if (count($added_address)>0) {
                $result_msg .= '登録成功したIPアドレス<br><font color="blue">';
                foreach ($added_address as $value) {
                    $result_msg .= $value . "<br>";
                }
                $result_msg .= '</font>';
            }
            // 登録失敗したIPアドレス一覧
            if (count($error_address)>0) {
                $result_msg .= '登録失敗したIPアドレス<br><font color="red">';
                foreach ($error_address as $value) {
                    $result_msg .= $value . "<br>";
                }
                $result_msg .= '</font>';
            }
        } else {
            $err_flag = true;
        }
    } else {
        $err_flag = true;
        $err_msg  = 'まとめ登録するIPアドレスを入力してください<br>' . "\n";
    }
}
// ip_loc配列
$sel_add_ip_loc = SelOfArray("add_ip_loc", $add_ip_loc, $array_ip_location, false);
$sel_group_ip_loc = SelOfArray("group_ip_loc", $group_ip_loc, $array_ip_location, false);

?>

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=euc-jp" >
<meta http-equiv="Content-Style-Type" content="text/css" >
<link rel="stylesheet" type="text/css" href="./css/style.css">
<title>IP管理：まとめ登録</title>
</head>
<body>

<h2>IPアドレス登録</h2>
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
<table width="600" class="input">
<form name="ip_add_form" action="" method="POST">
  <tr><td colspan="5">IPアドレスで登録</td></tr>
  <tr>
    <th>
        IPアドレス：
    </th>
    <td>
        IPロケーション
        <br><?php echo $sel_add_ip_loc; ?>
    </td>
    <td colspan="2">
        例)192.168.1.0/24<br>
        <input type="text" name="add_ip" size="30" value="<?php echo $add_ip ?>" style="ime-mode:disabled">
    </td>
    <td align="center">
        <input type="submit" name="sub_add_ip" value="登録">
    </td>
  </tr>
  <tr>
    <th>
        まとめ登録：
    </td>
    <td>
        IPロケーション
        <br><?php echo $sel_group_ip_loc; ?>
    </td>
    <td>
        例)192.161.1.0<br>
        <input type="text" name="add_group_ip" size="30" value="" style="ime-mode:disabled">
    </td>
    <td>
        例)26▽ <br>
        <select name="add_group_mask">
        <option value="24">24</option>
        <option value="25">25</option>
        <option value="26">26</option>
        <option value="27">27</option>
        <option value="28">28</option>
        <option value="29">29</option>
        <option value="30">30</option>
        </select>
    </td>
    <td align="center">
        <input type="submit" name="sub_add_group" value="まとめ登録">
    </td>
  </tr>
</form>
</table>
<!-- メイン終了 -->
</td>
</tr>
</table>
<!-- 全体終了 -->

<br>
<hr>
<?php echo FOOTER_STR; ?>

</body>
</html>
