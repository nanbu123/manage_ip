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
if (isset($_POST['sub_add_patch'])) {
    // パッチパネル登録
    if ($_POST['add_patch'] != '') {
        $add_patch = $_POST['add_patch'];
        if (exists_patch($add_patch)) {
            $result_msg  = '<font color="red">既に登録されているパッチパネル名です</font><br>' . "\n";
        } else {
            $rack_id = get_rack_id($add_patch);
            if ($rack_id > 0) {
                // DBに登録する
                if (insert_patch($add_patch, $rack_id)) {
                    $result_msg = '<font color="blue">パッチパネルを登録しました</font><br>' . "\n";
                    $add_patch = '';   // 登録したので初期化する
                } else {
                    $result_msg = '<font color="red">パッチパネルの登録に失敗しました</font><br>' . "\n";
                }
            } else {
                $result_msg = '<font color="red">ラックが登録されていません</font><br>' . "\n";
            }
        }
    } else {
        $err_flag = true;
        $err_msg  = '登録するパッチパネル名を入力してください<br>' . "\n";
    }
} elseif (isset($_POST['sub_add_group'])) {
    // まとめ登録
    // すべて入力されているか
    if ( ($_POST['add_g_rack'] !='') &&($_POST['add_g_fix'] != '') && 
            ($_POST['add_g_patch_s'] != '') && ($_POST['add_g_patch_e'] != '')) {
        $add_rack = $_POST['add_g_rack'];
        $fix    = $_POST['add_g_fix'];
        $patch_start = $_POST['add_g_patch_s'];
        $patch_end   = $_POST['add_g_patch_e'];
        // 数字で入力されているか（固定文字以外）
        if (is_numeric($patch_start) && is_numeric($patch_end)) {
            // 変数の大きいほうの桁数をとる（0で揃えるため）
            $patch_len = (strlen($patch_start) > strlen($patch_end))? strlen($patch_start): strlen($patch_end);

            if (exists_rack($add_rack)) {
                // ラックが存在する
                for ($j=$patch_start; $j<=$patch_end; $j++) {
                    // パネル名取得
                    $add_panel = $add_rack . "-" . $fix . sprintf("%0{$patch_len}d", $j);
                    if (!exists_patch($add_panel)) {
                        $rack_id = get_rack_id($add_panel);
                        if ($rack_id > 0) {
                            // DBに登録する
                            if (insert_patch($add_panel, $rack_id)) {
                                $result_flag = true;
                                $added_panel[] = $add_panel;
                            } else {
                                $error_panel[] = $add_panel;
                            }
                        } else {
                            // ラックが存在しない！？
                            $error_panel[] = $add_panel;
                        }
                    } else {
                        // 既に存在
                        $error_panel[] = $add_panel;
                    }
                }
                if ($result_flag) {
                    if (count($error_panel)>0) {
                        $result_msg = '<font color="red">パッチパネルの一部登録に失敗しました</font><br>' . "\n";
                    } else {
                        $result_msg = '<font color="blue">パッチパネルを登録しました</font><br>' . "\n";
                    }
                } else {
                    $result_msg = '<font color="red">パッチパネルの登録に失敗しました</font><br>' . "\n";
                }
                // 登録成功したパッチパネル
                if (count($added_panel)>0) {
                    $result_msg .= '登録成功したパッチパネル<br><font color="blue">';
                    foreach ($added_panel as $value) {
                        $result_msg .= $value . "<br>";
                    }
                    $result_msg .= '</font>';
                }
                // 登録失敗したパッチパネル
                if (count($error_panel)>0) {
                    $result_msg .= '登録失敗したパッチパネル<br><font color="red">';
                    foreach ($error_panel as $value) {
                        $result_msg .= $value . "<br>";
                    }
                    $result_msg .= '</font>';
                }
            } else {
                $result_msg = '<font color="red">ラックが登録されていません</font><br>' . "\n";
            }
        } else {
            $result_msg = '<font color="red">開始・終了は数字で入力してください</font><br>' . "\n";
        }
    } else {
        $err_flag = true;
        $err_msg  = 'まとめ登録するラック名を入力してください<br>' . "\n";
    }
}

?>

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" >
<meta http-equiv="Content-Style-Type" content="text/css" >
<link rel="stylesheet" type="text/css" href="./css/style.css">
<title>IP管理：パッチパネル登録</title>
</head>
<body>

<h2>パッチパネル登録</h2>
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
   <td colspan="7">パッチパネルを登録</td>
  </tr>
  <tr>
    <th>
        パッチパネル名：
    </th>
    <td colspan="5">
        例)34A101-01<br>
        <input type="text" name="add_patch" size="30" value="<?php echo $add_patch ?>" style="ime-mode:disabled">
    </th>
    <td align="center">
        <input type="submit" name="sub_add_patch" value="登録">
    </td>
  </tr>
  <tr>
    <th>
        まとめ登録：
    </td>
    <td width="100">
        ラック<br>例)34A101<br>
        <input type="text" name="add_g_rack" size="15" value="<?php echo $add_rack ?>" style="ime-mode:disabled">
    </td>
    <td align="center" width="40">
        -
    </td>
    <td width="100">
        パネル 固定<br>例)A<br>
        <input type="text" name="add_g_fix" size="10" value="<?php echo $fix ?>" style="ime-mode:disabled">
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
