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
    $where = "where " . TABLE_ADDRESS . ".id = '{$id}'";
} else {
    // IDが取得できなければ一覧へ
    header('Location: ./ip_list.php');
    exit;
}
if (isset($_POST['sub_up_address'])) {
    // IPアドレス情報登録
    $params = array();
    $params['comment'] = $_POST['comment'];
    session_start();
    $params['update_user_name'] = $_SESSION['login_name'];
    $params['update_date'] = 'NOW()';

    // DBを更新する
    if ($db->update(TABLE_ADDRESS, $params, $where)) {
        $result_msg = '<font color="blue">IPアドレス情報を更新しました</font><br>' . "\n";
    } else {
        $result_msg = '<font color="red">IPアドレス情報の更新に失敗しました</font><br>' . "\n";
    }
} else if (isset($_POST['sub_up_lock'])) {
    // IP割り当て禁止フラグ変更
    $params = array();
    if ($_POST['lock_flag'] == 1) {
        $params['lock_flag'] = 0;
        $lock_msg = "可能";
    } else {
        $params['lock_flag'] = 1;
        $lock_msg = "禁止";
    }
    session_start();
    $params['update_user_name'] = $_SESSION['login_name'];
    $params['update_date'] = 'NOW()';
    // DBを更新する
    if ($db->update(TABLE_ADDRESS, $params, $where)) {
        $result_msg = '<font color="blue">IP割当を' . $lock_msg . 'に変更しました</font><br>' . "\n";
    } else {
        $result_msg = '<font color="red">IP割当禁止の変更に失敗しました</font><br>' . "\n";
    }
}

// 更新後の情報を取得したい為ここでデータ取得
// FROM ip_address LEFT JOIN router ON ip_address.router_id = router.id 
// LEFT JOIN company ON router.company_id = company.id
$from_str = TABLE_ADDRESS . " LEFT JOIN " . TABLE_ROUTER;
$from_str .= " ON " . TABLE_ADDRESS . ".router_id = " . TABLE_ROUTER . ".id ";
$from_str .= "LEFT JOIN " . TABLE_COMPANY . " ON " . TABLE_ROUTER . ".company_id = " . TABLE_COMPANY . ".id";
// SELECT ip_address.id as ip_id, ip_address.address, ip_address.comment, ip_address.lock_flag, router.router, 
// router.port, router.service, router.panel, company.name as company_name
$select_str = TABLE_ADDRESS . ".id as ip_id," . TABLE_ADDRESS . ".ip_loc," . TABLE_ADDRESS . ".address," 
            . TABLE_ADDRESS . ".comment,". TABLE_ADDRESS . ".lock_flag," . TABLE_ROUTER . ".router, " 
            . TABLE_ROUTER . ".port, " . TABLE_ROUTER . ".service, " . TABLE_ROUTER . " .panel, "
            . TABLE_COMPANY . ".name as company_name, ". TABLE_COMPANY . ".end_user_name";

$data = $db->select($from_str, $select_str, $where);
$address_info = $data[0];
if ($address_info['lock_flag'] == 1) {
    $lock_value = "割当禁止";
    $lock_button = "割当可能にする";
    $lock_color = "#CCCCCC";
} else {
    $lock_value = "割当可能";
    $lock_button = "割当禁止にする";
    $lock_color = "#FFFFFF";
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

<h2>IPアドレス情報詳細</h2>
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
<form name="ip_edit_form" action="./ip_edit.php?id=<?php echo $id; ?>" method="POST">
  <caption style="text-align:left">IPアドレス情報</caption>
  <tr>
    <th width="120">
        IPアドレス：
    </th>
    <td><?php echo $address_info['address']; ?></td>
  </tr>
  <tr>
    <th>
        割振先：
    </th>
    <td><?php echo $array_ip_location[$address_info['ip_loc']]; ?></td>
  </tr>
  <tr>
    <th>
        会社名：
    </th>
    <td><?php echo $address_info['company_name']; ?></td>
  </tr>
  <tr>
    <th>
        エンドユーザ名：
    </th>
    <td><?php echo $address_info['end_user_name']; ?></td>
  </tr>
  <tr>
    <th>
        収容ルータ：
    </th>
    <td><?php echo $address_info['router']; ?></td>
  </tr>
  <tr>
    <th>
        ポート：
    </th>
    <td><?php echo $address_info['port']; ?></td>
  </tr>
  <tr>
    <th>
        パッチパネル：
    </th>
    <td><?php echo $address_info['panel']; ?></td>
  </tr>
  <tr>
    <th>
        サービス：
    </th>
    <td><?php echo $address_info['service']; ?></td>
  </tr>
<?php if ($address_info['company_name'] == "") { ?>
  <tr>
    <th>
        割当禁止：
    </th>
    <td bgcolor="<?php echo $lock_color; ?>">
        <?php echo $lock_value; ?>
        <input type="submit" name="sub_up_lock" value="<?php echo $lock_button; ?>">
        <input type="hidden" name="lock_flag" value="<?php echo $address_info['lock_flag']; ?>">
    </td>
  </tr>
<?php } ?>
  <tr>
    <th>
        備考：
    </th>
    <td>
        <textarea name="comment" cols="50" rows="4"><?php echo htmlspecialchars($address_info['comment']); ?></textarea>
    </td>
  </tr>
  <tr>
    <td colspan="2" align="center">
<?php if (check_auth('update')) { ?>
        <input type="submit" name="sub_up_address" value="更新">
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
