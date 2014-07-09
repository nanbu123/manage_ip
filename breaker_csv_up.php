<?php

include_once('./lib/config.php');
include_once('./lib/function.php');
include_once('./lib/db_access.php');
include_once('./lib/csv_format.php');

// ログインチェック
if (!check_session_login()) { exit; }
// DB接続
$db = new db_access();

$err_flag = false;
$result_flag = false;
$err_msg  = '';
$result_msg = '';
$added_breaker = array();
$error_breaker = array();
if (isset($_POST['sub_csv_upload'])) {
    if ($_FILES['up_file']['tmp_name']) {
        $up_file_path = $_FILES['up_file']['tmp_name'];

        $fp = fopen($up_file_path, "r");
        // 1行目を無視する
        $data = fgetcsv($fp, 4096);
        while ($data = fgetcsv($fp, 4096)) {
            $params = array();

            $params['dc_loc']       = get_array_id($array_dc_location, $data[$cf_bre_up_dc_loc]);
            $params['floor']        = $data[$cf_bre_up_floor];
            $params['pdu_no']       = $data[$cf_bre_up_pdu_no];
            $params['bunden']       = $data[$cf_bre_up_bunden];
            $params['mccb_no']      = $data[$cf_bre_up_mccb_no];
            // ラック名からラックIDを取得
            $rack_info = exists_rack($data[$cf_bre_up_rack_name]);
            if ($rack_info) {
                $params['rack_id']  = $rack_info[0]['id'];
            }
            $params['ampere']       = $data[$cf_bre_up_ampere];
            $params['status']       = $data[$cf_bre_up_status];
            $params['plug_type']    = $data[$cf_bre_up_plug_type];
            $params['plug_count']   = $data[$cf_bre_up_plug_count];
            session_start();
            $params['add_user_name'] = $_SESSION['login_name'];
            $params['update_user_name'] = $_SESSION['login_name'];
            $params['add_date'] = 'NOW()';
            $params['update_date'] = 'NOW()';
            if (!exists_breaker($params['pdu_no'], $params['bunden'], $params['mccb_no'])) {
                if ($db->insert(TABLE_BREAKER, $params)) {
                    $result_flag = true;
                    $added_breaker[] = $params['pdu_no'] . "({$params['bunden']})" . $params['mccb_no'];
                } else {
                    $error_breaker[] = $params['pdu_no'] . "({$params['bunden']})" . $params['mccb_no'];
                }
            } else {
                // 既に存在
                $error_breaker[] = $params['pdu_no'] . "({$params['bunden']})" . $params['mccb_no'];
            }
        }
        fclose($fp);

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

    } else {
        $result_msg = '<font color="red">ファイルを選択してください</font><br>' . "\n";
    }
} else if (isset($_POST['sub_csv_download'])) {
    // ヘッダ出力
    header("Cache-Control: public");
    header("Pragma: public");
    header("Content-Type: application/octet-stream");
    header("Content-Disposition: attachment; filename=breaker_csv_upload.csv");

    $output = "";
    foreach ($cf_bre_up_title as $title) {
        if ($output) {
            $output .= ",";
        }
        $output .= "\"{$title}\"";
    }
    $output .= "\r\n";
    // サンプル追加
    $output .= "T1DC,1,3-1A,1,102,3C101,30,1,\"L5-30,WN1164\",4\r\n";

    $output = mb_convert_encoding($output,"SJIS","EUC-JP");
    echo $output;

    exit;
}

?>

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=euc-jp" >
<meta http-equiv="Content-Style-Type" content="text/css" >
<link rel="stylesheet" type="text/css" href="./css/style.css">
<title>IP管理：ブレーカーCSV登録</title>
</head>
<body>

<h2>ブレーカーCSV登録</h2>
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
<form name="csv_form" action="" method="POST" enctype="multipart/form-data">
  <tr>
   <td colspan="3">ブレーカーをCSVから登録<input type="submit" name="sub_csv_download" value="CSVサンプル"></td>
  </tr>
  <tr>
    <th>
        CSVファイル：
    </th>
    <td>
        CSVファイルを選択してください。
        <br><input type="file" name="up_file" size="60">
    </td>
    <td align="center">
        <input type="submit" name="sub_csv_upload" value="CSV登録">
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
