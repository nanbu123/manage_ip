<?php

include_once('./lib/config.php');
include_once('./lib/function.php');
include_once('./lib/db_access.php');

// ログインチェック
if (!check_session_login()) { exit; }

// DB接続
$db = new db_access();

$err_flag = false;
$result_flag = false;
$err_msg  = '';
$result_msg = '';
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];
    // データ更新・取得用
    $where = "where " . TABLE_BREAKER . ".id = '{$id}'";
} else {
    // IDが取得できなければ一覧へ
    header('Location: ./breaker_list.php');
    exit;
}
if (isset($_POST['sub_up_breaker'])) {
    // ブレーカー情報登録
    $params = array();
    $params['ampere'] = $_POST['ampere'];
    $params['plug_type'] = $_POST['plug_type'];
    $params['plug_count'] = $_POST['plug_count'];
    $params['comment'] = $_POST['comment'];
    session_start();
    $params['update_user_name'] = $_SESSION['login_name'];
    $params['update_date'] = 'NOW()';

    // DBを更新する
    if ($db->update(TABLE_BREAKER, $params, $where)) {
        $result_msg = '<font color="blue">ブレーカー情報を更新しました</font><br>' . "\n";
    } else {
        $result_msg = '<font color="red">ブレーカー情報の更新に失敗しました</font><br>' . "\n";
    }
} else if (isset($_POST['sub_up_status'])) {
    // ON/OFF変更
    $params = array();
    if ($_POST['status'] == 1) {
        $params['status'] = 0;
        $status_msg = "OFF";
    } else {
        $params['status'] = 1;
        $status_msg = "ON";
    }
    session_start();
    $params['update_user_name'] = $_SESSION['login_name'];
    $params['update_date'] = 'NOW()';
    // DBを更新する
    if ($db->update(TABLE_BREAKER, $params, $where)) {
        $result_msg = '<font color="blue">電源を' . $status_msg . 'に変更しました</font><br>' . "\n";
    } else {
        $result_msg = '<font color="red">電源ON/OFFの変更に失敗しました</font><br>' . "\n";
    }
}

// 更新後の情報を取得したい為ここでデータ取得
// FROM breaker LEFT JOIN rack ON breaker.rack_id = rack.id 
// LEFT JOIN company ON rack.company_id = company.id
$from_str = TABLE_BREAKER . " LEFT JOIN " . TABLE_RACK;
$from_str .= " ON " . TABLE_BREAKER . ".rack_id = " . TABLE_RACK . ".id ";
$from_str .= "LEFT JOIN " . TABLE_COMPANY . " ON " . TABLE_RACK . ".company_id = " . TABLE_COMPANY . ".id";
// SELECT breaker.*, rack.name as rack_name, company.name as company_name
$select_str = TABLE_BREAKER . ".*," . TABLE_RACK . ".name as rack_name, " . TABLE_RACK . ".company_id, "
            . TABLE_COMPANY . ".name as company_name, " . TABLE_COMPANY . ".end_user_name";

$data = $db->select($from_str, $select_str, $where);
$breaker_info = $data[0];

if ($breaker_info['status'] == 1) {
    $status_value = "ON";
    $status_button = "OFFにする";
    $status_color = "#FFFFFF";
} else {
    $status_value = "OFF";
    $status_button = "ONにする";
    $status_color = "#CCCCCC";
}
?>

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=euc-jp" >
<meta http-equiv="Content-Style-Type" content="text/css" >
<link rel="stylesheet" type="text/css" href="./css/style.css">
<title>IP管理：一覧</title>
</head>
<body>

<h2>ブレーカー情報詳細</h2>
<!-- 全体 -->
<table class="none">
<tr>
<td width="120" valign="top">
    <!-- メニュー開始 -->
    <?php require_once('./menu.php'); ?>
    <!-- メニュー終了 -->
</td>
<td valign="top">
    <!-- メイン開始 -->
<?php
    echo $result_msg;
?>
<font color="red"><?php if ($err_flag) { echo $err_msg; } ?></font>
<table width="600" class="input">
<form name="breaker_edit_form" action="./breaker_edit.php?id=<?php echo $id; ?>" method="POST">
  <caption style="text-align:left">ブレーカー情報</caption>
  <tr>
    <th width="120">
        割振先：
    </th>
    <td><?php echo $array_dc_location[$breaker_info['dc_loc']]; ?></td>
  </tr>
  <tr>
    <th>
        フロア：
    </th>
    <td><?php echo $breaker_info['floor']; ?>F</td>
  </tr>
  <tr>
    <th>
        PDU番号：
    </th>
    <td><?php echo $breaker_info['pdu_no']; ?></td>
  </tr>
  <tr>
    <th>
        分電盤番号：
    </th>
    <td>分電盤(<?php echo $breaker_info['bunden']; ?>)</td>
  </tr>
  <tr>
    <th>
        MCCB番号：
    </th>
    <td><?php echo $breaker_info['mccb_no']; ?></td>
  </tr>
  <tr>
    <th>
        定格電流：
    </th>
    <td><input type="text" name="ampere" size="10" value="<?php echo $breaker_info['ampere']; ?>">A</td>
  </tr>
  <tr>
    <th>
        コンセント形状：
    </th>
    <td><input type="text" name="plug_type" value="<?php echo $breaker_info['plug_type']; ?>"></td>
  </tr>
  <tr>
    <th>
        口数：
    </th>
    <td><input type="text" name="plug_count" size="10" value="<?php echo $breaker_info['plug_count']; ?>"></td>
  </tr>
  <tr>
    <th>
        ON/OFF：
    </th>
    <td bgcolor="<?php echo $status_color; ?>">
        <?php echo $status_value; ?>
<?php if (check_auth('update')) { ?>
        <input type="submit" name="sub_up_status" value="<?php echo $status_button; ?>">
        <input type="hidden" name="status" value="<?php echo $breaker_info['status']; ?>">
<?php } ?>
    </td>
  </tr>
  <tr>
    <th>
        ラック名：
    </th>
    <td><a href="./rack_edit.php?id=<?php echo $breaker_info['rack_id']; ?>"><?php echo $breaker_info['rack_name']; ?></a></td>
  </tr>
  <tr>
    <th>
        会社名：
    </th>
    <td><a href="./company_edit.php?id=<?php echo $breaker_info['company_id']; ?>"><?php echo $breaker_info['company_name']; ?></a></td>
  </tr>
  <tr>
    <th>
        エンドユーザ名：
    </th>
    <td><?php echo $breaker_info['end_user_name']; ?></td>
  </tr>
  <tr>
    <th>
        備考：
    </th>
    <td>
        <textarea name="comment" cols="50" rows="4"><?php echo htmlspecialchars($breaker_info['comment']); ?></textarea>
    </td>
  </tr>
  <tr>
    <td colspan="2" align="center">
<?php if (check_auth('update')) { ?>
        <input type="submit" name="sub_up_breaker" value="更新">
<?php } ?>
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
