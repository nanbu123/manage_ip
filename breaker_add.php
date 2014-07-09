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
if (isset($_POST['sub_add_pdu'])) {
    // ブレーカー登録
    $add_dc_loc = $_POST['add_dc_loc'];
    $add_floor  = $_POST['add_floor'];
    $add_pdu    = $_POST['add_pdu'];
    $add_bunden = $_POST['add_bunden'];
    $add_mccb   = $_POST['add_mccb'];
    if (($add_pdu != '') && ($add_bunden != '') && ($add_mccb != '')) {
        if (exists_breaker($add_pdu, $add_bunden, $add_mccb)) {
            $result_msg  = '<font color="red">既に登録されているブレーカー名です</font><br>' . "\n";
        } else {
            // DBに登録する
            if (insert_breaker($add_dc_loc, $add_floor, $add_pdu, $add_bunden, $add_mccb)) {
                $result_msg = '<font color="blue">ブレーカーを登録しました</font><br>' . "\n";
            } else {
                $result_msg = '<font color="red">ブレーカーの登録に失敗しました</font><br>' . "\n";
            }
        }
    } else {
        $err_flag = true;
        $err_msg  = '登録するブレーカー名を入力してください<br>' . "\n";
    }
} elseif (isset($_POST['sub_add_group'])) {
    // まとめ登録
    $add_dc_loc = $_POST['group_dc_loc'];
    $add_floor  = $_POST['group_floor'];
    $add_pdu    = $_POST['group_pdu'];
    $add_bunden = $_POST['group_bunden'];
    $mccb_start = $_POST['group_mccb_s'];
    $mccb_end   = $_POST['group_mccb_e'];
    // すべて入力されているか
    if ( ($add_pdu != '') && ($add_bunden != '') && ($mccb_start != '') && ($mccb_end != '')) {
        // 数字で入力されているか（固定文字以外）
        if (is_numeric($mccb_start) && is_numeric($mccb_end)) {
            // 変数の大きいほうの桁数をとる（0で揃えるため）
            $patch_len = (strlen($mccb_start) > strlen($mccb_end))? strlen($mccb_start): strlen($mccb_end);
            for ($j=$mccb_start; $j<=$mccb_end; $j++) {
                // ブレーカー名取得
                $add_mccb = sprintf("%0{$patch_len}d", $j);
                $add_pdu_name = $add_pdu . "(" . $add_bunden . ")" . $add_mccb;
                if (!exists_breaker($add_pdu, $add_bunden, $add_mccb)) {
                    // DBに登録する
                    if (insert_breaker($add_dc_loc, $add_floor, $add_pdu, $add_bunden, $add_mccb)) {
                        $result_flag = true;
                        $added_breaker[] = $add_pdu_name;
                    } else {
                        $error_breaker[] = $add_pdu_name;
                    }
                } else {
                    // 既に存在
                    $error_breaker[] = $add_pdu_name;
                }
                if ($result_flag) {
                    if (count($error_breaker)>0) {
                        $result_msg = '<font color="red">ブレーカーの一部登録に失敗しました</font><br>' . "\n";
                    } else {
                        $result_msg = '<font color="blue">ブレーカーを登録しました</font><br>' . "\n";
                    }
                } else {
                    $result_msg = '<font color="red">ブレーカーの登録に失敗しました</font><br>' . "\n";
                }
                // 登録成功したブレーカー
                if (count($added_breaker)>0) {
                    $result_msg .= '登録成功したブレーカー<br><font color="blue">';
                    foreach ($added_breaker as $value) {
                        $result_msg .= $value . "<br>";
                    }
                    $result_msg .= '</font>';
                }
                // 登録失敗したブレーカー
                if (count($error_breaker)>0) {
                    $result_msg .= '登録失敗したブレーカー<br><font color="red">';
                    foreach ($error_breaker as $value) {
                        $result_msg .= $value . "<br>";
                    }
                    $result_msg .= '</font>';
                }
            }
        } else {
            $result_msg = '<font color="red">開始・終了は数字で入力してください</font><br>' . "\n";
        }
    } else {
        $err_flag = true;
        $err_msg  = 'まとめ登録するPDU・分電盤・MCCB番号を入力してください<br>' . "\n";
    }
}

// dc_loc配列
$sel_add_dc_loc = SelOfArray("add_dc_loc", $add_dc_loc, $array_dc_location, false);
$sel_group_dc_loc = SelOfArray("group_dc_loc", $add_dc_loc, $array_dc_location, false);

// フロア
foreach(range(1, $max_breaker_floor) as $val) {
    $ar_floor[$val] = $val . "F";
}
$sel_add_floor = SelOfArray("add_floor", $add_floor, $ar_floor, false);
$sel_group_floor = SelOfArray("group_floor", $add_floor, $ar_floor, false);

// 分電盤
foreach(range(1, $max_breaker_bunden) as $val) {
    $ar_bunden[$val] = "(" . $val . ")";
}
$sel_add_bunden = SelOfArray("add_bunden", $add_bunden, $ar_bunden, false);
$sel_group_bunden = SelOfArray("group_bunden", $add_bunden, $ar_bunden, false);


?>

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=euc-jp" >
<meta http-equiv="Content-Style-Type" content="text/css" >
<link rel="stylesheet" type="text/css" href="./css/style.css">
<title>IP管理：ブレーカー登録</title>
</head>
<body>

<h2>ブレーカー登録</h2>
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
   <td colspan="9">ブレーカーを登録</td>
  </tr>
  <tr>
    <th>
        ブレーカー名：
    </th>
    <td width="100">
        DCロケーション
        <br><?php echo $sel_add_dc_loc; ?>
    </td>
    <td width="50">
        フロア
        <br><?php echo $sel_add_floor; ?>
    </td>
    <td width="100">
        PDU番号
        <br><input type="text" name="add_pdu" size="20" value="<?php echo $add_pdu; ?>" style="ime-mode:disabled">
    </td>
    <td width="50">
        分電盤
        <br><?php echo $sel_add_bunden; ?>
    </td>
    <td colspan="3">
        MCCB番号
        <br><input type="text" name="add_mccb" size="10" value="<?php echo $add_mccb; ?>" style="ime-mode:disabled">
    </td>
    <td align="center">
        <input type="submit" name="sub_add_pdu" value="登録">
    </td>
  </tr>
  <tr>
    <th>
        まとめ登録：
    </th>
    <td>
        DCロケーション
        <br><?php echo $sel_group_dc_loc; ?>
    </td>
    <td>
        フロア
        <br><?php echo $sel_group_floor; ?>
    </td>
    <td>
        PDU番号
        <br><input type="text" name="group_pdu" size="20" value="<?php echo $add_pdu; ?>" style="ime-mode:disabled">
    </td>
    <td>
        分電盤
        <br><?php echo $sel_group_bunden; ?>
    </td>
    <td width="100">
        MCCB番号開始
        <br><input type="text" name="group_mccb_s" size="10" value="<?php echo $mccb_start; ?>" style="ime-mode:disabled">
    </td>
    <td align="center" width="40">
        -
    </td>
    <td width="100">
        MCCB番号終了
        <br><input type="text" name="group_mccb_e" size="10" value="<?php echo $mccb_end; ?>" style="ime-mode:disabled">
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
