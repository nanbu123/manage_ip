<?php header("Content-Type: text/html; charset=utf-8"); ?>
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
    $where = "where " . TABLE_RACK . ".id = '{$id}'";
} else {
    // IDが取得できなければ一覧へ
    header('Location: ./rack_list.php');
    exit;
}
if (isset($_POST['sub_up_rack'])) {
    // ラック情報登録
    $params = array();
    //$params['size'] = $_POST['rack_size'];
    $params['comment'] = $_POST['comment'];
    session_start();
    $params['update_user_name'] = $_SESSION['login_name'];
    $params['update_date'] = 'NOW()';
    
    // DBを更新する
    if ($db->update(TABLE_RACK, $params, $where)) {
        $result_msg = '<font color="blue">ラック情報を更新しました</font><br>' . "\n";
    } else {
        $result_msg = '<font color="red">ラック情報の更新に失敗しました</font><br>' . "\n";
    }
} elseif ($_POST['mode']=='breaker_onoff') {
    // ブレーカーONOFF切替
    $breaker_id = $_POST['breaker_id'];
    $breaker_status = $_POST['breaker_status'];
    $params = array();
    if ($breaker_status == '1') {
        $params['status'] = 0;
    } else {
        $params['status'] = 1;
    }
    $params['update_date'] = 'NOW()';

    // ブレーカー情報を更新する
    if ($db->update(TABLE_BREAKER, $params, "WHERE id = {$breaker_id}")) {
        $result_msg = '<font color="blue">ブレーカーを更新しました</font><br>' . "\n";
    } else {
        $result_msg = '<font color="red">ブレーカーの更新に失敗しました</font><br>' . "\n";
    }
} elseif ($_POST['mode']=='breaker_update') {
    // ブレーカー更新
    $breaker_id = $_POST['breaker_id'];
    $params = array();
    $params['comment'] = $_POST['txt_breaker_comment_' . $breaker_id];
    $params['update_date'] = 'NOW()';

    // ブレーカー情報を更新する
    if ($db->update(TABLE_BREAKER, $params, "WHERE id = {$breaker_id}")) {
        $result_msg = '<font color="blue">ブレーカーを更新しました</font><br>' . "\n";
    } else {
        $result_msg = '<font color="red">ブレーカーの更新に失敗しました</font><br>' . "\n";
    }
} elseif ($_POST['mode']=='breaker_add') {
    // ブレーカー割当追加
    $breaker_pdu = $_POST['txt_breaker_pdu_new'];
    $breaker_bunden = $_POST['breaker_bunden_new'];
    $breaker_mccb = $_POST['txt_breaker_mccb_new'];
    if (($breaker_pdu == '') || ($breaker_bunden == '') || ($breaker_mccb == '')) {
        $err_flag = true;
        $err_msg  = '追加するブレーカー名を入力してください<br>' . "\n";
    } else {
        // ブレーカーが存在するかどうか
        $breaker_where = "WHERE (pdu_no = '{$breaker_pdu}') AND (bunden = '{$breaker_bunden}') "
                       . "AND (mccb_no = '{$breaker_mccb}') AND (del_flag != 1) ";
        $b_data = $db->select(TABLE_BREAKER, '*', $breaker_where);
        if ($b_data) {
            // ブレーカーが存在する
            if (($b_data[0]['rack_id']=='') || ($b_data[0]['rack_id']==0)) {
                // まだ会社に割り当てられていない
                $params = array();
                $params['rack_id'] = $id;
                $params['update_user_name'] = $_SESSION['login_name'];
                $params['update_date'] = 'NOW()';
                // ブレーカーデータに会社情報を追加する
                if ($db->update(TABLE_BREAKER, $params, $breaker_where)) {
                    $result_msg = '<font color="blue">ブレーカーを追加しました</font><br>' . "\n";
                    $breaker_name = '';
                } else {
                    $result_msg = '<font color="red">ブレーカーの追加に失敗しました</font><br>' . "\n";
                }
            } else {
                // 既に会社に割り当てられている
                $result_msg = '<font color="red">既に割り当てられたブレーカーです</font><br>' . "\n";
            }
        } else {
            $err_flag = true;
            $err_msg  = 'ブレーカーが存在しません　[' . $breaker_name . ']<br>' . "\n";
        }
    }
} elseif ($_POST['mode']=='breaker_delete') {
    $breaker_id = $_POST['breaker_id'];
    $params['rack_id'] = '';
    // ルータポートの会社情報を削除する
    if ($db->update(TABLE_BREAKER, $params, "WHERE id = '{$breaker_id}'")) {
        $result_msg = '<font color="blue">ブレーカーを解除しました</font><br>' . "\n";
    } else {
        $result_msg = '<font color="red">ブレーカーの解除に失敗しました</font><br>' . "\n";
    }
} elseif ($_POST['mode']=='patch_update') {
    // パッチパネル更新
    $patch_id = $_POST['patch_id'];
    $params = array();
    $params['type'] = $_POST['sel_patch_type_' . $patch_id];
    $params['length'] = $_POST['txt_patch_length_' . $patch_id];
    $params['comment'] = $_POST['txt_patch_comment_' . $patch_id];
    $params['update_date'] = 'NOW()';

    // パッチパネル情報を更新する
    if ($db->update(TABLE_PATCH, $params, "WHERE id = {$patch_id}")) {
        $result_msg = '<font color="blue">パッチパネルを更新しました</font><br>' . "\n";
    } else {
        $result_msg = '<font color="red">パッチパネルの更新に失敗しました</font><br>' . "\n";
    }
}

// 更新後の情報を取得したい為ここでデータ取得
// FROM rack LEFT JOIN company ON rack.company_id = company.id 
$from_str = TABLE_RACK . " LEFT JOIN " . TABLE_COMPANY;
$from_str .= " ON " . TABLE_RACK . ".company_id = " . TABLE_COMPANY . ".id ";
// SELECT rack.*, company.name as company_name
$select_str = TABLE_RACK . ".*," . TABLE_COMPANY . ".name as company_name, " . TABLE_COMPANY . ".end_user_name";

$data = $db->select($from_str, $select_str, $where);
$rack_info = $data[0];

// rack_size配列
//$sel_rack_size = SelOfArray("rack_size", $rack_info['size'], $array_rack_size, true);

// ブレーカー一覧データ取得
$breaker_where = "WHERE del_flag != 1 AND rack_id = {$id} ";
$breaker_data = $db->select(TABLE_BREAKER, "*", $breaker_where);

// 分電盤
foreach(range(1, $max_breaker_bunden) as $val) {
    $ar_bunden[$val] = "(" . $val . ")";
}
$sel_add_bunden = SelOfArray("breaker_bunden_new", $breaker_bunden_new, $ar_bunden, false);

// パッチパネル一覧データ取得
$patch_where = "WHERE del_flag != 1 AND rack_id = {$id} ";
$patch_data = $db->select(TABLE_PATCH, "*", $patch_where);

?>

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" >
<meta http-equiv="Content-Style-Type" content="text/css" >
<link rel="stylesheet" type="text/css" href="./css/style.css">
<title>IP管理：一覧</title>
</head>
<script LANGUAGE=javascript>
<!--
    function add_breaker_submit(){
        var up_form = document.breaker_form;
        up_form.mode.value='breaker_add';
        up_form.submit();
        return false;
    }
    function del_breaker_submit(id){
        var up_form = document.breaker_form;
        if (confirm('本当に解除しますか？')) {
            up_form.mode.value='breaker_delete';
            up_form.breaker_id.value=id;
            up_form.submit();
            return true;
        } else {
            return false;
        }
    }
    function up_breaker_submit(id){
        var up_form = document.breaker_form;
        up_form.mode.value='breaker_update';
        up_form.breaker_id.value=id;
        up_form.submit();
    }
    function onoff_breaker_submit(id, status){
        var up_form = document.breaker_form;
        up_form.mode.value='breaker_onoff';
        up_form.breaker_id.value=id;
        up_form.breaker_status.value=status;
        up_form.submit();
    }
    function up_patch_submit(id){
        var up_form = document.patch_form;
        up_form.mode.value='patch_update';
        up_form.patch_id.value=id;
        up_form.submit();
    }
//-->
</script>
<body>

<h2>ラック情報詳細</h2>
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
<form name="rack_edit_form" action="./rack_edit.php?id=<?php echo $id; ?>" method="POST">
  <caption style="text-align:left">ラック情報</caption>
  <tr>
    <th width="120">
        割振先：
    </th>
    <td><?php echo $array_dc_location[$rack_info['dc_loc']]; ?></td>
  </tr>
  <tr>
    <th>
        フロア：
    </th>
    <td><?php echo $rack_info['floor']; ?>F</td>
  </tr>
  <tr>
    <th>
        ラック名：
    </th>
    <td><?php echo $rack_info['name']; ?></td>
  </tr>
  <tr>
    <th>
        サイズ：
    </th>
    <td>
        <?php echo $array_rack_size[$rack_info['size']]; ?>
    </td>
  </tr>
  <tr>
    <th>
        会社名：
    </th>
    <td><a href="./company_edit.php?id=<?php echo $rack_info['company_id']; ?>"><?php echo $rack_info['company_name']; ?></a></td>
  </tr>
  <tr>
    <th>
        エンドユーザ名：
    </th>
    <td><?php echo $rack_info['end_user_name']; ?></td>
  </tr>
  <tr>
    <th>
        備考：
    </th>
    <td>
        <textarea name="comment" cols="50" rows="4"><?php echo htmlspecialchars($rack_info['comment']); ?></textarea>
    </td>
  </tr>
  <tr>
    <td colspan="2" align="center">
<?php if (check_auth('update')) { ?>
        <input type="submit" name="sub_up_rack" value="更新">
<?php } ?>
    </td>
  </tr>
</form>
</table>
<!-- メイン終了 -->

<!-- パッチパネル一覧 -->
<form name="patch_form" action="" method="POST">
<table width="700" class="list">
<caption style="text-align:left">割当パッチパネル一覧</caption>
  <tr>
    <th>パッチパネル名</th>
    <th>サービス形態</th>
    <th>距離</th>
    <th>備考</th>
    <th>　</th>
  </tr>
<?php 
if ($patch_data) {
    foreach ($patch_data as $row) { 
?>
  <tr>
    <td><?php echo $row['panel_number']; ?></td>
    <td>
        <select name="sel_patch_type_<?php echo $row['id'] ?>">
<?php 
        foreach ($array_patch_type as $key => $type) {
            $selected = ($row['type'] == $key)? "selected": "";
            echo "        <option value=\"{$key}\" {$selected}>{$type}</option>\n";
        }
?>
        </select>
    </td>
    <td><input type="text" name="txt_patch_length_<?php echo $row['id']; ?>" size="20" value="<?php echo $row['length']; ?>"></td>
    <td><input type="text" name="txt_patch_comment_<?php echo $row['id']; ?>" size="40" value="<?php echo $row['comment']; ?>"></td>
    <td align="center">
<?php if (check_auth('update')) { ?>
        <input type="button" name="sub_patch_update" value="パッチパネル更新" onclick="up_patch_submit( <?php echo $row['id']; ?> );">
<?php } ?>
        </td>
  </tr>
<?php
    }
}
?>
  <input type="hidden" name="mode" value="">
  <input type="hidden" name="patch_id" value="">
</form>
</table>
<!-- パッチパネル一覧終了 -->

<!-- ブレーカー一覧 -->
<form name="breaker_form" action="" method="POST">
<table width="800" class="list">
<caption style="text-align:left">割当ブレーカー一覧</caption>
  <tr>
    <th>PDU番号</th>
    <th>分電盤番号</th>
    <th width="80">MCCB番号</th>
    <th>電源</th>
    <th>備考</th>
    <th>　</th>
  </tr>
<?php 
if ($breaker_data) {
    foreach ($breaker_data as $row) { 
        if ($row['status'] == '1') {
            $bgcl = "#FFFFFF";
        } else {
            $bgcl = "#CCCCCC";
        }
?>
  <tr bgcolor="<?php echo $bgcl; ?>">
    <td><?php echo $row['pdu_no']; ?></td>
    <td>分電盤(<?php echo $row['bunden']; ?>)</td>
    <td><a href="./breaker_edit.php?id=<?php echo ($row['id']); ?>"><?php echo $row['mccb_no']; ?></a></td>
    <td><?php echo ($row['status'] == '1' ? "ON" : "OFF"); ?></td>
    <td><input type="text" name="txt_breaker_comment_<?php echo $row['id']; ?>" value="<?php echo $row['comment']; ?>"></td>
    <td align="center">
<?php if (check_auth('update')) { ?>
        <input type="button" name="sub_breaker_onoff" value="ON/OFF" onclick="onoff_breaker_submit( <?php echo $row['id'] . ", " . $row['status']; ?> );">
        <input type="button" name="sub_breaker_update" value="備考更新" onclick="up_breaker_submit( <?php echo $row['id']; ?> );">
        <input type="button" name="sub_breaker_delete" value="割当解除" onclick="del_breaker_submit( <?php echo $row['id']; ?> );">
<?php } ?>
    </td>
  </tr>
<?php
    }
}
?>
  <tr>
    <td><input type="text" size="20" name="txt_breaker_pdu_new" value="<?php echo $breaker_pdu; ?>"></td>
    <td><?php echo $sel_add_bunden; ?></td>
    <td><input type="text" size="10" name="txt_breaker_mccb_new" value="<?php echo $breaker_mccb; ?>"></td>
    <td></td>
    <td></td>
    <td align="center">
<?php if (check_auth('update')) { ?>
        <input type="button" name="sub_breaker_add" value="割当追加" onclick="add_breaker_submit();">
<?php } ?>
    </td>
  </tr>
  <input type="hidden" name="mode" value="">
  <input type="hidden" name="breaker_id" value="">
  <input type="hidden" name="breaker_status" value="">
</form>
</table>
<!-- ブレーカー一覧終了 -->

</td>
</tr>
</table>
<!-- 全体終了 -->

<br>
<hr>
<?php echo FOOTER_STR; ?>

</body>
</html>
