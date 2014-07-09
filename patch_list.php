<?php

include_once('./lib/config.php');
include_once('./lib/function.php');
include_once('./lib/db_access.php');

// ログインチェック
if (!check_session_login()) { exit; }

// DB接続
$db = new db_access();
$err_flag = false;
// パラメータ取得
if ($_POST['mode']=='delete'){
    $mode = 'delete';
    $id = $_POST['id'];
    if ($id == ''){
        $err_flag = true;
    } elseif (!is_numeric($id)){
        $err_flag = true;
    } else {
        $where = "WHERE id = '" . $id . "'";
        if (!$db->delete(TABLE_PATCH, $where)) {
            $err_flag = true;
        }
    }

    if ($err_flag) {
        $result_msg = '<font color="red">パッチパネル情報の削除に失敗しました</font><br>' . "\n";
    } else {
        $result_msg = '<font color="blue">パッチパネル情報を削除しました</font><br>' . "\n";
    }
} elseif ($_POST['mode']=='update'){
    // DBの更新
    $mode = 'update';
    $id = $_POST['id'];
    if ($id == ''){
        $err_flag = true;
    } elseif (!is_numeric($id)){
        $err_flag = true;
    } else {
//        $params['panel_number'] = $_POST['txt_num_'.$id];
        $params['type'] = $_POST['sel_type_'.$id];
        $params['length'] = $_POST['txt_length_'.$id];
        $params['comment'] = $_POST['txt_comment_'.$id];
        $params['update_user_name'] = $_SESSION['login_name'];
        $params['update_date'] = 'NOW()';
        $where = "WHERE id = '" . $id . "'";

        if (!$db->update(TABLE_PATCH, $params, $where)) {
            $err_flag = true;
        } else {
            // 更新したら初期化
            $params = array();
        }
    }

    if ($err_flag) {
        $result_msg = '<font color="red">パッチパネル情報の更新に失敗しました</font><br>' . "\n";
    } else {
        $result_msg = '<font color="blue">パッチパネル情報を更新しました</font><br>' . "\n";
    }
} elseif ($_POST['mode']=='rack_update'){
    // ラックとのリンクの更新
    $mode = 'rack_update';
    $id = $_POST['id'];
    if ($id == ''){
        $err_flag = true;
    } elseif (!is_numeric($id)){
        $err_flag = true;
    } else {
        $where = "WHERE id = '" . $id . "'";
        $patch_data = $db->select(TABLE_PATCH, "*", $where);
        $panel_number = $patch_data[0]['panel_number'];
        $rack_id = get_rack_id($panel_number);
        echo "rack_id:" . $rack_id;
        if ($rack_id) {
            $params['rack_id'] = $rack_id;
            $params['update_user_name'] = $_SESSION['login_name'];
            $params['update_date'] = 'NOW()';
            if (!$db->update(TABLE_PATCH, $params, $where)) {
                $err_flag = true;
            }
            // 初期化
            $params = array();
        }
    }

    if ($err_flag) {
        $result_msg = '<font color="red">パッチパネル情報の更新に失敗しました</font><br>' . "\n";
    } else {
        $result_msg = '<font color="blue">パッチパネル情報を更新しました</font><br>' . "\n";
    }
} else {
    $mode = 'search';
}

// ページ番号
if ($_GET['page']){
    $cur_page = $_GET['page'];
}
if (!($cur_page >= 1)){
    $cur_page = 1;
}

$where = "WHERE (" . TABLE_PATCH . ".del_flag != 1) ";
// 検索キーを取得
if ($_POST['sub_clear_patch']) {
    // 検索リセット
    $_SESSION['search_patch'] = "";
    header('Location:./patch_list.php');
    exit;
} elseif ($_POST['sub_patch']) {
    $patch = $_POST['patch'];
    $_SESSION['search_patch'] = $patch;
    header('Location: ./patch_list.php');
    exit;
} elseif ($_GET['patch']) {
    $patch = urldecode($_GET['patch']);
    $_SESSION['search_patch'] = $patch;
} elseif ($_SESSION['search_patch']) {
    $patch = $_SESSION['search_patch'];
}

// パッチパネル名検索
if ($patch != '') {
    $get_param = 'cp=' . urlencode($patch);

    $where .= " AND (";
    $where .= TABLE_PATCH . ".panel_number like '%" . addslashes($patch) . "%' ";

    $where .= ") ";
}

$where .= " ORDER BY " . TABLE_PATCH . ".panel_number ASC ";

// ページング
// FROM patch LEFT JOIN rack ON patch.rack_id = rack.id 
// LEFT JOIN company ON rack.company_id = company.id
$from_str = TABLE_PATCH . " LEFT JOIN " . TABLE_RACK;
$from_str .= " ON " . TABLE_PATCH . ".rack_id = " . TABLE_RACK . ".id ";
$from_str .= "LEFT JOIN " . TABLE_COMPANY . " ON " . TABLE_RACK . ".company_id = " . TABLE_COMPANY . ".id";

$total = $db->sel_count(TABLE_PATCH, 'id', $where);
$paging = get_paging($total, $cur_page, 'patch_list.php', $get_param);

// limit追加する前にダウンロード用に取る
$csv_where = $where;
// limit追加
$where .= " LIMIT " . PAGE_ROW * ($cur_page-1) . "," . PAGE_ROW;

// データを取得
// SELECT patch.*, rack.name as rack_name, company.name as company_name, company.id as company_id 
$select_str = TABLE_PATCH . ".*," . TABLE_RACK . ".name as rack_name," . TABLE_COMPANY . ".name as company_name,"
            . TABLE_COMPANY . ".id as company_id";
$data = $db->select($from_str, $select_str, $where);


?>

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=euc-jp" >
<meta http-equiv="Content-Style-Type" content="text/css" >
<link rel="stylesheet" type="text/css" href="./css/style.css">
<title>IP管理：パッチパネル一覧</title>
</head>
<script LANGUAGE=javascript>
<!--
    function update_submit(id){
        var up_form = document.update_form;
        up_form.mode.value='update';
        up_form.id.value=id;
        up_form.submit();
        return false;
    }
    function up_rack_submit(id){
        var up_form = document.update_form;
        up_form.mode.value='rack_update';
        up_form.id.value=id;
        up_form.submit();
        return false;
    }
    function del_submit(id){
        var up_form = document.update_form;
        if (confirm('本当に削除しますか？')) {
            up_form.mode.value='delete';
            up_form.id.value=id;
            up_form.submit();
            return true;
        } else {
            return false;
        }
    }
//-->
</script>
<body>

<h2>パッチパネル一覧</h2>
<?php
    echo $result_msg;
?>
<br>
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

<table class="none">
<form name="search_form" action="" method="POST">
  <tr>
    <td>
        <input type="text" name="patch" size="40" value="<?php echo htmlspecialchars($patch); ?>">
    </td>
    <td>
        <input type="submit" name="sub_patch" value="検索">
    </td>
    <td>
        <input type="submit" name="sub_clear_patch" value="リセット">
    </td>
  </tr>
</form>
</table>
<br>
<form name="dl_form" action="./download.php" method="POST">
<input type="hidden" name="csv_where" value="<?php echo $csv_where; ?>">
<input type="hidden" name="dl_mode" value="patch_download">
<?php echo $total ?>件見つかりました　<input type="submit" name="sub_download" value="CSV出力">
</form>

<!-- 一覧 ここから -->
<table class="list" width="850">
<form name="update_form" action="" method="POST">
  <caption style="text-align:right"><?php echo $paging; ?></caption>
  <tr>
    <th>パッチパネル名</th>
    <th>ラック名</th>
    <th>会社名</th>
    <th>使用形態</th>
    <th>距離</th>
    <th>備考</th>
    <th>最終更新者</th>
    <th>　</th>
  </tr>
<?php 
if ($data) {
    foreach ($data as $row) { 
?>
  <tr>
    <td><?php echo $row['panel_number']; ?></td>
    <td><?php echo $row['rack_name']; ?></td>
    <td><a href="./company_edit.php?id=<?php echo $row['company_id']; ?>"><?php echo $row['company_name']; ?></td>
    <td>
        <select name="sel_type_<?php echo $row['id'] ?>">
<?php 
        foreach ($array_patch_type as $key => $type) {
            $selected = ($row['type'] == $key)? "selected": "";
            echo "        <option value=\"{$key}\" {$selected}>{$type}</option>\n";
        }
?>
        </select>
    </td>
    <td><input type="text" size="20" name="txt_length_<?php echo $row['id']; ?>" value="<?php echo $row['length']; ?>"></td>
    <td><input type="text" size="30" name="txt_comment_<?php echo $row['id']; ?>" value="<?php echo $row['comment']; ?>"></td>
    <td><?php echo $row['update_user_name']; ?></td>
    <td align="center">
<?php if (check_auth('update')) { ?>
        <input type="button" name="sub_update" value="更新" onclick="update_submit( <?php echo $row['id']; ?> );">
    <?php if ($row['rack_name']=='') { ?>
        <input type="button" name="sub_rack" value="ラック更新" onclick="up_rack_submit( <?php echo $row['id']; ?> );">
    <?php } ?>
<?php } ?>
<?php if (check_auth('delete')) { ?>
    <?php if ($row['company_name']=='') { ?>
        <input type="button" name="sub_delete" value="削除" onclick="del_submit( <?php echo $row['id']; ?> );">
    <?php } ?>
<?php } ?>
    </td>
  </tr>
<?php
    }
}
?>
  <input type="hidden" name="mode" value="">
  <input type="hidden" name="id" value="">
</form>
</table>
<table class="none" width="850">
  <tr class="none"><td align="right"><font size="+0"><?php echo $paging; ?></font></td></tr>
</table>
<br>

<!-- メイン終了 -->
</td>
</tr>
</table>
<!-- 全体終了 -->

<hr>
<?php echo FOOTER_STR; ?>

</body>
</html>
