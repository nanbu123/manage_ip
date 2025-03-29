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
if (isset($_POST['sub_add_rack'])) {
    // ラック登録
    if ($_POST['add_rack'] != '') {
        $add_rack_size = $_POST['add_rack_size'];
        $add_rack = $_POST['add_rack'];
        if (exists_rack($add_rack)) {
            $result_msg  = '<font color="red">既に存在するラックです</font><br>' . "\n";
        } else {
            // DBに登録する
            if (insert_rack($add_rack, $add_rack_size)) {
                $result_msg = '<font color="blue">ラックを登録しました</font><br>' . "\n";
                $add_rack = '';   // 登録したので初期化する
            } else {
                $result_msg = '<font color="red">ラックの登録に失敗しました</font><br>' . "\n";
            }
        }
    } else {
        $err_flag = true;
        $err_msg  = '登録するラック名を入力してください<br>' . "\n";
    }
} elseif (isset($_POST['sub_add_group'])) {
    // まとめ登録
    // すべて入力されているか
    if (($_POST['add_g_fix'] != '') && 
            ($_POST['add_g_key_s'] != '') && ($_POST['add_g_key_e'] != '') &&
            ($_POST['add_g_patch_s'] != '') && ($_POST['add_g_patch_e'] != '')) {
        $fix   = $_POST['add_g_fix'];
        $key_start = $_POST['add_g_key_s'];
        $key_end   = $_POST['add_g_key_e'];
        $patch_start = $_POST['add_g_patch_s'];
        $patch_end   = $_POST['add_g_patch_e'];
        $group_rack_size = $_POST['group_rack_size'];
        // 数字で入力されているか（固定文字以外）
        if (is_numeric($key_start) && is_numeric($key_end) &&
            is_numeric($patch_start) && is_numeric($patch_end)) {
            // 変数の大きいほうの桁数をとる（0で揃えるため）
            $key_len = (strlen($key_start) > strlen($key_end))? strlen($key_start): strlen($key_end);
            $patch_len = (strlen($patch_start) > strlen($patch_end))? strlen($patch_start): strlen($patch_end);
            // ラック
            for ($i=$key_start; $i<=$key_end; $i++) {
                // ラック名取得
                $add_rack = $fix . sprintf("%0{$key_len}d", $i);
                if (!exists_rack($add_rack)) {
                    // ラックをDB登録
                    if (insert_rack($add_rack, $group_rack_size)) {
                        // 登録成功
                        $result_flag = true;
                        $added_rack_panel[] = "ラック：" . $add_rack;

                        for ($j=$patch_start; $j<=$patch_end; $j++) {
                            // パネル名取得
                            $add_panel = $add_rack . "-" . sprintf("%0{$patch_len}d", $j);
                            if (!exists_patch($add_panel)) {
                                $rack_id = get_rack_id($add_panel);
                                if ($rack_id > 0) {
                                    // DBに登録する
                                    if (insert_patch($add_panel, $rack_id)) {
                                        $added_rack_panel[] = "パッチパネル：" . $add_panel;
                                    } else {
                                        $error_rack_panel[] = "パッチパネル：" . $add_panel;
                                    }
                                } else {
                                    // ラックが存在しない！？
                                    $error_rack_panel[] = "パッチパネル：" . $add_panel;
                                }
                            } else {
                                // 既に存在
                                $error_rack_panel[] = "パッチパネル：" . $add_panel;
                            }
                        }
                    } else {
                        $error_rack_panel[] = "ラック：" . $add_rack;
                    }
                } else {
                    $error_rack_panel[] = "ラック：" . $add_rack;
                }
            }
            if ($result_flag) {
                if (count($error_rack_panel)>0) {
                    $result_msg = '<font color="red">ラック/パッチパネルの一部登録に失敗しました</font><br>' . "\n";
                } else {
                    $result_msg = '<font color="blue">ラック/パッチパネルを登録しました</font><br>' . "\n";
                }
            } else {
                $result_msg = '<font color="red">ラック/パッチパネルの登録に失敗しました</font><br>' . "\n";
            }
            // 登録成功したラック/パッチパネル
            if (count($added_rack_panel)>0) {
                $result_msg .= '登録成功したラック/パッチパネル<br><font color="blue">';
                foreach ($added_rack_panel as $value) {
                    $result_msg .= $value . "<br>";
                }
                $result_msg .= '</font>';
            }
            // 登録失敗したラック/パッチパネル
            if (count($error_rack_panel)>0) {
                $result_msg .= '登録失敗したラック/パッチパネル<br><font color="red">';
                foreach ($error_rack_panel as $value) {
                    $result_msg .= $value . "<br>";
                }
                $result_msg .= '</font>';
            }
        } else {
            $result_msg = '<font color="red">開始・終了は数字で入力してください</font><br>' . "\n";
        }
    } else {
        $err_flag = true;
        $err_msg  = 'まとめ登録するラック名を入力してください<br>' . "\n";
    }
} elseif (isset($_POST['sub_add_only'])) {
    // まとめ登録
    // すべて入力されているか
    if (($_POST['add_o_fix'] != '') && 
            ($_POST['add_o_key_s'] != '') && ($_POST['add_o_key_e'] != '')) {
        $o_fix   = $_POST['add_o_fix'];
        $o_key_start = $_POST['add_o_key_s'];
        $o_key_end   = $_POST['add_o_key_e'];
        $only_rack_size = $_POST['only_rack_size'];
        // 数字で入力されているか（固定文字以外）
        if (is_numeric($o_key_start) && is_numeric($o_key_end)) {
            // 変数の大きいほうの桁数をとる（0で揃えるため）
            $key_len = (strlen($o_key_start) > strlen($o_key_end))? strlen($o_key_start): strlen($o_key_end);
            // ラック
            for ($i=$o_key_start; $i<=$o_key_end; $i++) {
                // ラック名取得
                $add_rack = $o_fix . sprintf("%0{$key_len}d", $i);
                if (!exists_rack($add_rack)) {
                    // ラックをDB登録
                    if (insert_rack($add_rack, $only_rack_size)) {
                        // 登録成功
                        $result_flag = true;
                        $added_rack_panel[] = "ラック：" . $add_rack;
                    } else {
                        $error_rack_panel[] = "ラック：" . $add_rack;
                    }
                } else {
                    $error_rack_panel[] = "ラック：" . $add_rack;
                }
            }
            if ($result_flag) {
                if (count($error_rack_panel)>0) {
                    $result_msg = '<font color="red">ラックの一部登録に失敗しました</font><br>' . "\n";
                } else {
                    $result_msg = '<font color="blue">ラックを登録しました</font><br>' . "\n";
                }
            } else {
                $result_msg = '<font color="red">ラックの登録に失敗しました</font><br>' . "\n";
            }
            // 登録成功したラック
            if (count($added_rack_panel)>0) {
                $result_msg .= '登録成功したラック<br><font color="blue">';
                foreach ($added_rack_panel as $value) {
                    $result_msg .= $value . "<br>";
                }
                $result_msg .= '</font>';
            }
            // 登録失敗したラック/パッチパネル
            if (count($error_rack_panel)>0) {
                $result_msg .= '登録失敗したラック<br><font color="red">';
                foreach ($error_rack_panel as $value) {
                    $result_msg .= $value . "<br>";
                }
                $result_msg .= '</font>';
            }
        } else {
            $result_msg = '<font color="red">開始・終了は数字で入力してください</font><br>' . "\n";
        }
    } else {
        $err_flag = true;
        $err_msg  = 'まとめ登録するラック名を入力してください<br>' . "\n";
    }
}

// rack_size配列
$sel_add_rack_size = SelOfArray("add_rack_size", $add_rack_size, $array_rack_size, false);
$sel_only_rack_size = SelOfArray("only_rack_size", $only_rack_size, $array_rack_size, false);
$sel_group_rack_size = SelOfArray("group_rack_size", $group_rack_size, $array_rack_size, false);

?>

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" >
<meta http-equiv="Content-Style-Type" content="text/css" >
<link rel="stylesheet" type="text/css" href="./css/style.css">
<title>IP管理：ラック登録</title>
</head>
<body>

<h2>ラック登録</h2>
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
   <td colspan="9">ラックを登録</td>
  </tr>
  <tr>
    <th>
        ラック名：
    </th>
    <td>
        サイズ
        <br><?php echo $sel_add_rack_size; ?>
    </td>
    <td colspan="6">
        例)34A101<br>
        <input type="text" name="add_rack" size="30" value="<?php echo $add_rack ?>" style="ime-mode:disabled">
    </th>
    <td align="center">
        <input type="submit" name="sub_add_rack" value="登録">
    </td>
  </tr>
  <tr>
   <td colspan="9">ラックのみをまとめて登録</td>
  </tr>
  <tr>
    <th>
        まとめ登録：
    </td>
    <td>
        サイズ
        <br><br><?php echo $sel_only_rack_size; ?>
    </td>
    <td width="100">
        固定<br>例)34A<br>
        <input type="text" name="add_o_fix" size="10" value="<?php echo $o_fix ?>" style="ime-mode:disabled">
    </td>
    <td width="100">
        変数 開始<br>例)101<br>
        <input type="text" name="add_o_key_s" size="10" value="<?php echo $o_key_start ?>" style="ime-mode:disabled">
    </td>
    <td width="100">
        変数 終了<br>例)124<br>
        <input type="text" name="add_o_key_e" size="10" value="<?php echo $o_key_end ?>" style="ime-mode:disabled">
    </td>
    <td colspan="3"></td>
    <td align="center">
        <input type="submit" name="sub_add_only" value="まとめ登録">
    </td>
  </tr>
  <tr>
   <td colspan="9">ラックとパッチをまとめて登録</td>
  </tr>
  <tr>
    <th>
        まとめ登録：
    </td>
    <td>
        サイズ
        <br><br><?php echo $sel_group_rack_size; ?>
    </td>
    <td width="100">
        固定<br>例)34A<br>
        <input type="text" name="add_g_fix" size="10" value="<?php echo $fix ?>" style="ime-mode:disabled">
    </td>
    <td width="100">
        変数 開始<br>例)101<br>
        <input type="text" name="add_g_key_s" size="10" value="<?php echo $key_start ?>" style="ime-mode:disabled">
    </td>
    <td width="100">
        変数 終了<br>例)124<br>
        <input type="text" name="add_g_key_e" size="10" value="<?php echo $key_end ?>" style="ime-mode:disabled">
    </td>
    <td align="center" width="40">
        -
    </td>
    <td width="100">
        パネル 開始<br>例)01<br>
        <input type="text" name="add_g_patch_s" size="10" value="<?php echo $patch_start ?>" style="ime-mode:disabled">
    </td>
    <td width="100">
        パネル 終了<br>例)04<br>
        <input type="text" name="add_g_patch_e" size="10" value="<?php echo $patch_end ?>" style="ime-mode:disabled">
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
