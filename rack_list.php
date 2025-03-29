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
// パラメータ取得
if ($_POST['mode']=='delete'){
    // ラック削除
    $mode = 'delete';
    $id = $_POST['id'];
    if ($id == ''){
        $err_flag = true;
    } elseif (!is_numeric($id)){
        $err_flag = true;
    } else {
        $where = "WHERE id = '" . $id . "'";
        if (!$db->delete(TABLE_RACK, $where)) {
            $err_flag = true;
        }
    }

    if ($err_flag) {
        $result_msg = '<font color="red">ラック情報の削除に失敗しました</font><br>' . "\n";
    } else {
        $result_msg = '<font color="blue">ラック情報を削除しました</font><br>' . "\n";
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
//        $params['name'] = $_POST['txt_name_'.$id];
        $params['comment'] = $_POST['txt_comment_'.$id];
        $params['update_user_name'] = $_SESSION['login_name'];
        $params['update_date'] = 'NOW()';
        $where = "WHERE id = '" . $id . "'";

        if (!$db->update(TABLE_RACK, $params, $where)) {
            $err_flag = true;
        } else {
            // 更新したら初期化
            $params = array();
        }
    }

    if ($err_flag) {
        $result_msg = '<font color="red">ラック情報の更新に失敗しました</font><br>' . "\n";
    } else {
        $result_msg = '<font color="blue">ラック情報を更新しました</font><br>' . "\n";
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

$where = "WHERE (" . TABLE_RACK . ".del_flag != 1) ";
// 検索キーを取得
if ($_POST['sub_clear_rack']) {
    $_SESSION['search_rack'] = "";
    $_SESSION['search_rack_loc'] = "";
    $_SESSION['search_rack_floor'] = "";
    header('Location: ./rack_list.php');
    exit;
} elseif ($_POST['sub_ini_rack']) {
    $_SESSION['search_rack'] = $_POST['search_rack'];
    $_SESSION['search_ini_rack'] = 1;
    $_SESSION['search_rack_loc'] = $_POST['search_rack_loc'];
    $_SESSION['search_rack_floor'] = $_POST['search_rack_floor'];
    header('Location: ./rack_list.php');
    exit;
} elseif ($_POST['sub_rack']) {
    $search_rack = $_POST['search_rack'];
    $_SESSION['search_rack'] = $search_rack;
    $_SESSION['search_ini_rack'] = "";
    $_SESSION['search_rack_loc'] = $_POST['search_rack_loc'];
    $_SESSION['search_rack_floor'] = $_POST['search_rack_floor'];
    header('Location: ./rack_list.php');
    exit;
} elseif ($_GET['rack']) {
    $search_rack = urldecode($_GET['rack']);
    $_SESSION['search_rack'] = $search_rack;
} elseif ($_SESSION['search_rack'] || $_SESSION['search_rack_loc'] || $_SESSION['search_rack_floor']) {
    $search_rack = $_SESSION['search_rack'];
    $search_ini_rack = $_SESSION['search_ini_rack'];
    $search_rack_loc = $_SESSION['search_rack_loc'];
    $search_rack_floor = $_SESSION['search_rack_floor'];
}

// ラック名検索
if ($search_rack != '') {
    if ($search_ini_rack) {
        // 先頭一致検索
        $where .= " AND (" . TABLE_RACK . ".name like '" . addslashes($search_rack) . "%') ";
    } else {
        $where .= " AND (" . TABLE_RACK . ".name like '%" . addslashes($search_rack) . "%') ";
    }
}
if ($search_rack_loc != "") {
    $where .= " AND (" . TABLE_RACK . ".dc_loc = '{$search_rack_loc}')";
}
if ($search_rack_floor != "") {
    $where .= " AND (" . TABLE_RACK . ".floor = '{$search_rack_floor}')";
}

$where .= " ORDER BY " . TABLE_RACK . ".name ASC ";

// ページング
// FROM rack LEFT JOIN company ON rack.company_id = company.id
$from_str = TABLE_RACK . " LEFT JOIN " . TABLE_COMPANY 
        . " ON " . TABLE_RACK . ".company_id = " . TABLE_COMPANY . ".id";
$total = $db->sel_count($from_str, TABLE_RACK.'.id', $where);
$paging = get_paging($total, $cur_page, 'rack_list.php', $get_param);

// limit追加する前にダウンロード用に取る
$csv_where = $where;
// limit追加
$where .= " LIMIT " . PAGE_ROW * ($cur_page-1) . "," . PAGE_ROW;

// データを取得
// SELECT rack.*, company.name as company_name 
$select_str = TABLE_RACK . ".*," . TABLE_COMPANY . ".name as company_name,"
            . TABLE_COMPANY . ".end_user_name as company_end_user";
$data = $db->select($from_str, $select_str, $where);

// dc_loc配列
$sel_dc_loc = SelOfArray("search_rack_loc", $search_rack_loc, $array_dc_location, true);
// フロア
foreach(range(1, $max_breaker_floor) as $val) {
    $ar_floor[$val] = $val . "F";
}
$sel_floor = SelOfArray("search_rack_floor", $search_rack_floor, $ar_floor, true);


?>

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" >
<meta http-equiv="Content-Style-Type" content="text/css" >
<link rel="stylesheet" type="text/css" href="./css/style.css">
<title>IP管理：ラック一覧</title>
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
    function output_mail(type){
        var dl_form = document.dl_form;
        dl_form.dl_mode.value='rack_output_mail';
        dl_form.mail_type.value=type;
        dl_form.submit();
        return false;
    }
//-->
</script>
<body>

<h2>ラック一覧</h2>
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
        <?php echo $sel_dc_loc; ?>
    </td>
    <td>
        <?php echo $sel_floor; ?>
    </td>
    <td>
        <input type="text" name="search_rack" size="40" value="<?php echo htmlspecialchars($search_rack); ?>" style="ime-mode:disabled">
    </td>
    <td>
        <input type="submit" name="sub_rack" value="検索">
        <input type="submit" name="sub_ini_rack" value="先頭一致検索">
    </td>
    <td>
        <input type="submit" name="sub_clear_rack" value="リセット">
    </td>
  </tr>
</form>
</table>
<br>
<form name="dl_form" action="./download.php" method="POST">
<input type="hidden" name="csv_where" value="<?php echo $csv_where; ?>">
<input type="hidden" name="dl_mode" value="rack_download">
<input type="hidden" name="dl_search" value="<?php echo htmlspecialchars($search_rack); ?>">
<?php echo $total ?>件見つかりました
　<input type="submit" name="sub_download" value="CSV出力">
  <input type="submit" name="sub_dl_mail_0" value="通常連絡先出力" onclick="output_mail(0);">
  <input type="submit" name="sub_dl_mail_1" value="緊急連絡先出力" onclick="output_mail(1);">
  <input type="hidden" name="mail_type" value="">
</form>

<!-- 一覧 ここから -->
<table class="list" width="700">
<form name="update_form" action="" method="POST">
  <caption style="text-align:right"><?php echo $paging; ?></caption>
  <tr>
    <th>ロケーション</th>
    <th>フロア</th>
    <th width="100">ラック名</th>
    <th>サイズ</th>
    <th>会社名</th>
    <th>エンドユーザー名</th>
    <th>最終更新者</th>
    <th>　</th>
  </tr>
<?php 
if ($data) {
    foreach ($data as $row) { 
?>
  <tr>
    <td><?php echo $array_dc_location[$row['dc_loc']]; ?></td>
    <td><?php echo $row['floor']; ?>F</td>
    <td><a href="./rack_edit.php?id=<?php echo ($row['id']); ?>"><?php echo $row['name']; ?></a></td>
    <td><?php echo $array_rack_size[$row['size']]; ?></td>
    <td><a href="./company_edit.php?id=<?php echo $row['company_id']; ?>"><?php echo $row['company_name']; ?></a></td>
    <td><?php echo $row['company_end_user']; ?></td>
    <td><?php echo $row['update_user_name']; ?></td>
    <td align="center">
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
<table class="none" width="700">
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
