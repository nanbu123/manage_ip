<?php header("Content-Type: text/html; charset=utf-8"); ?>
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
if (isset($_POST['sub_add_router'])) {
    // ルータポート登録
    if (($_POST['add_router'] != '') && ($_POST['add_port'] != ''))  {
        $add_router = $_POST['add_router'];
        $add_port   = $_POST['add_port'];
        $add_ip_loc = $_POST['add_ip_loc'];
        if (exists_router($add_router, $add_port)) {
            $result_msg  = '<font color="red">既に登録されているルータポート名です</font><br>' . "\n";
        } else {
            // DBに登録する
            if (insert_router($add_router, $add_port, $add_ip_loc)) {
                $result_msg = '<font color="blue">ルータポートを登録しました</font><br>' . "\n";
                $add_patch = '';   // 登録したので初期化する
            } else {
                $result_msg = '<font color="red">ルータポートの登録に失敗しました</font><br>' . "\n";
            }
        }
    } else {
        $err_flag = true;
        $err_msg  = '登録するルータポート名を入力してください<br>' . "\n";
    }
} elseif (isset($_POST['sub_add_group'])) {
    // まとめ登録
    // すべて入力されているか
    if ( ($_POST['add_g_router'] !='') &&($_POST['add_g_fix'] != '') && 
            ($_POST['add_g_port_s'] != '') && ($_POST['add_g_port_e'] != '')) {
        $add_router = $_POST['add_g_router'];
        $fix    = $_POST['add_g_fix'];
        $port_start = $_POST['add_g_port_s'];
        $port_end   = $_POST['add_g_port_e'];
        $group_ip_loc = $_POST['group_ip_loc'];
        // 数字で入力されているか（固定文字以外）
        if (is_numeric($port_start) && is_numeric($port_end)) {
            // 変数の大きいほうの桁数をとる（0で揃えるため）
            $port_len = (strlen($port_start) > strlen($port_end))? strlen($port_start): strlen($port_end);
            for ($j=$port_start; $j<=$port_end; $j++) {
                // ルータポート名取得
                $add_r_port =$fix . sprintf("%0{$port_len}d", $j);
                if (!exists_router($add_router, $add_r_port)) {
                    // DBに登録する
                    if (insert_router($add_router, $add_r_port, $group_ip_loc)) {
                        $result_flag = true;
                        $added_r_port[] = $add_router . "&nbsp" . $add_r_port ;
                    } else {
                        $error_r_port[] = $add_router . "&nbsp" . $add_r_port;
                    }
                } else {
                    // 既に存在
                    $error_r_port[] = $add_router . "&nbsp" . $add_r_port;
                }
            }
            if ($result_flag) {
                if (count($error_r_port)>0) {
                    $result_msg = '<font color="red">ルータポートの一部登録に失敗しました</font><br>' . "\n";
                } else {
                    $result_msg = '<font color="blue">ルータポートを登録しました</font><br>' . "\n";
                }
            } else {
                $result_msg = '<font color="red">ルータポートの登録に失敗しました</font><br>' . "\n";
            }
            // 登録成功したルータポート
            if (count($added_r_port)>0) {
                $result_msg .= '登録成功したルータポート<br><font color="blue">';
                foreach ($added_r_port as $value) {
                    $result_msg .= $value . "<br>";
                }
                $result_msg .= '</font>';
            }
            // 登録失敗したルータポート
            if (count($error_r_port)>0) {
                $result_msg .= '登録失敗したルータポート<br><font color="red">';
                foreach ($error_r_port as $value) {
                    $result_msg .= $value . "<br>";
                }
                $result_msg .= '</font>';
            }
        } else {
            $result_msg = '<font color="red">開始・終了は数字で入力してください</font><br>' . "\n";
        }
    } else {
        $err_flag = true;
        $err_msg  = 'まとめ登録するルータポート名を入力してください<br>' . "\n";
    }
}

// ip_loc配列
$sel_add_ip_loc = SelOfArray("add_ip_loc", $add_ip_loc, $array_ip_location, false);
$sel_group_ip_loc = SelOfArray("group_ip_loc", $group_ip_loc, $array_ip_location, false);

?>

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" >
<meta http-equiv="Content-Style-Type" content="text/css" >
<link rel="stylesheet" type="text/css" href="./css/style.css">
<title>IP管理：ルータポート登録</title>
</head>
<body>

<h2>ルータポート登録</h2>
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
<form name="add_form" action="" method="POST">
  <tr>
   <td colspan="7">ルータポートを登録</td>
  </tr>
  <tr>
    <th>
        ルータポート名：
    </th>
    <td>
        IPロケーション
        <br><?php echo $sel_add_ip_loc; ?>
    </td>
    <td>
        例)c49-1.fs.tnz34<br>
        <input type="text" name="add_router" size="20" value="<?php echo $add_router ?>" style="ime-mode:disabled">
    </td>
    <td colspan="3">
        例)Gi1/1<br>
        <input type="text" name="add_port" size="10" value="<?php echo $add_port ?>" style="ime-mode:disabled">
    </td>
    <td align="center">
        <input type="submit" name="sub_add_router" value="登録">
    </td>
  </tr>
  <tr>
    <th>
        まとめ登録：
    </td>
    <td>
        IPロケーション
        <br><br><?php echo $sel_group_ip_loc; ?>
    </td>
    <td>
        ルータ<br>例)c49-1.ts.tnz37<br>
        <input type="text" name="add_g_router" size="20" value="<?php echo $add_router; ?>" style="ime-mode:disabled">
    </td>
    <td width="100">
        ポート 固定<br>例)Gi1/<br>
        <input type="text" name="add_g_fix" size="10" value="<?php echo $fix ?>" style="ime-mode:disabled">
    </td>
    <td width="100">
        ポート 開始<br>例)01<br>
        <input type="text" name="add_g_port_s" size="10" value="<?php echo $port_start ?>" style="ime-mode:disabled">
    </td>
    <td width="100">
        ポート 終了<br>例)47<br>
        <input type="text" name="add_g_port_e" size="10" value="<?php echo $port_end ?>" style="ime-mode:disabled">
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
