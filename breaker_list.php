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
        if (!$db->delete(TABLE_BREAKER, $where)) {
            $err_flag = true;
        }
    }

    if ($err_flag) {
        $result_msg = '<font color="red">ブレーカーの削除に失敗しました</font><br>' . "\n";
    } else {
        $result_msg = '<font color="blue">ブレーカーを削除しました</font><br>' . "\n";
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
        $params['ampere'] = $_POST['txt_ampere_'.$id];
        $params['comment'] = $_POST['txt_comment_'.$id];
        $params['update_user_name'] = $_SESSION['login_name'];
        $params['update_date'] = 'NOW()';
        $where = "WHERE id = '" . $id . "'";

        if (!$db->update(TABLE_BREAKER, $params, $where)) {
            $err_flag = true;
        } else {
            // 更新したら初期化
            $params = array();
        }
    }

    if ($err_flag) {
        $result_msg = '<font color="red">ブレーカーの更新に失敗しました</font><br>' . "\n";
    } else {
        $result_msg = '<font color="blue">ブレーカーを更新しました</font><br>' . "\n";
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

$where = "WHERE (" . TABLE_BREAKER . ".del_flag != 1) ";
// 検索キーを取得
if ($_POST['sub_clear_breaker']) {
    // 検索リセット
    $_SESSION['search_breaker'] = "";
    $_SESSION['search_breaker_loc'] = "";
    $_SESSION['search_breaker_floor'] = "";
    $_SESSION['search_breaker_bunden'] = "";
    header('Location:./breaker_list.php');
    exit;
} elseif ($_POST['sub_breaker']) {
    $search_breaker = $_POST['search_breaker'];
    $_SESSION['search_breaker'] = $search_breaker;
    $_SESSION['search_breaker_loc'] = $_POST['search_breaker_loc'];
    $_SESSION['search_breaker_floor'] = $_POST['search_breaker_floor'];
    $_SESSION['search_breaker_bunden'] = $_POST['search_breaker_bunden'];
    // セッションに保存して読み直す
    header('Location:./breaker_list.php');
    exit;
} elseif ($_GET['breaker']) {
    $search_breaker = urldecode($_GET['breaker']);
    $_SESSION['search_breaker'] = $search_breaker;
    $_SESSION['search_breaker_loc'] = "";
    $_SESSION['search_breaker_floor'] = "";
    $_SESSION['search_breaker_bunden'] = "";
} elseif ($_SESSION['search_breaker'] || $_SESSION['search_breaker_loc'] || $_SESSION['search_breaker_floor'] || $_SESSION['search_breaker_bunden']) {
    $search_breaker = $_SESSION['search_breaker'];
    $search_breaker_loc = $_SESSION['search_breaker_loc'];
    $search_breaker_floor = $_SESSION['search_breaker_floor'];
    $search_breaker_bunden = $_SESSION['search_breaker_bunden'];
}

// ブレーカー名検索
if ($search_breaker != '') {
    $where .= " AND (" . TABLE_BREAKER . ".pdu_no like '%" . addslashes($search_breaker) . "%') ";
}
if ($search_breaker_loc != "") {
    $where .= " AND (" . TABLE_BREAKER . ".dc_loc = '{$search_breaker_loc}')";
}
if ($search_breaker_floor != "") {
    $where .= " AND (" . TABLE_BREAKER . ".floor = '{$search_breaker_floor}')";
}
if ($search_breaker_bunden != "") {
    $where .= " AND (" . TABLE_BREAKER . ".bunden = '{$search_breaker_bunden}')";
}

$where .= " ORDER BY " . TABLE_BREAKER . ".pdu_no ASC ";
// ページング
// FROM breaker LEFT JOIN rack ON breaker.rack_id = rack.id LEFT JOIN company ON rack.company_id = company.id
$from_str = TABLE_BREAKER . " LEFT JOIN " . TABLE_RACK 
        . " ON " . TABLE_BREAKER . ".rack_id = " . TABLE_RACK . ".id"
        . " LEFT JOIN " . TABLE_COMPANY . " ON " . TABLE_RACK . ".company_id = " . TABLE_COMPANY . ".id";
$total = $db->sel_count($from_str, TABLE_BREAKER.'.id', $where);
$paging = get_paging($total, $cur_page, 'breaker_list.php', $get_param);

// limit追加する前にダウンロード用に取る
$csv_where = $where;
// limit追加
$where .= " LIMIT " . PAGE_ROW * ($cur_page-1) . "," . PAGE_ROW;

// データを取得
// SELECT breaker.*, rack.name as rack_name, company.name as company_name 
$select_str = TABLE_BREAKER . ".*," . TABLE_RACK . ".name as rack_name, " 
            . TABLE_COMPANY . ".id as company_id, " . TABLE_COMPANY . ".name as company_name";
$data = $db->select($from_str, $select_str, $where);

// dc_loc配列
$sel_dc_loc = SelOfArray("search_breaker_loc", $search_breaker_loc, $array_dc_location, true);
// フロア
foreach(range(1, $max_breaker_floor) as $val) {
    $ar_floor[$val] = $val . "F";
}
$sel_floor = SelOfArray("search_breaker_floor", $search_breaker_floor, $ar_floor, true);
// 分電盤
foreach(range(1, $max_breaker_bunden) as $val) {
    $ar_bunden[$val] = "分電盤(" . $val . ")";
}
$sel_bunden = SelOfArray("search_breaker_bunden", $search_breaker_bunden, $ar_bunden, true);


?>

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=euc-jp" >
<meta http-equiv="Content-Style-Type" content="text/css" >
<link rel="stylesheet" type="text/css" href="./css/style.css">
<title>IP管理：ブレーカー一覧</title>
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
        dl_form.dl_mode.value='breaker_output_mail';
        dl_form.mail_type.value=type;
        dl_form.submit();
        return false;
    }
//-->
</script>
<body>

<h2>ブレーカー一覧</h2>
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
        <input type="text" name="search_breaker" size="30" value="<?php echo htmlspecialchars($search_breaker); ?>" style="ime-mode:disabled">
    </td>
    <td>
        <?php echo $sel_bunden; ?>
    </td>
    <td>
        <input type="submit" name="sub_breaker" value="検索">
    </td>
    <td>
        <input type="submit" name="sub_clear_breaker" value="リセット">
    </td>
  </tr>
</form>
</table>
<br>
<form name="dl_form" action="./download.php" method="POST">
    <input type="hidden" name="csv_where" value="<?php echo $csv_where; ?>">
    <input type="hidden" name="dl_mode" value="breaker_download">
    <?php echo $total ?>件見つかりました　<input type="submit" name="sub_download" value="CSV出力">
    <input type="submit" name="sub_dl_mail_0" value="通常連絡先出力" onclick="output_mail(0);">
    <input type="submit" name="sub_dl_mail_1" value="緊急連絡先出力" onclick="output_mail(1);">
    <input type="hidden" name="mail_type" value="">
</form>

<!-- 一覧 ここから -->
<table class="list" width="850">
<form name="update_form" action="" method="POST">
  <caption style="text-align:right"><?php echo $paging; ?></caption>
  <tr>
    <th>ロケー<br>ション</th>
    <th>フロア</th>
    <th>PDU番号</th>
    <th>分電盤番号</th>
    <th>MCCB<br>番号</th>
    <th>ラック番号</th>
    <th>定格電流</th>
    <th>ON/OFF</th>
    <th>会社名</th>
    <th>最終更新者</th>
    <th>　</th>
  </tr>
<?php 
if ($data) {
    foreach ($data as $row) { 
        if ($row['status'] == '1') {
            $bgcl = "#FFFFFF";
        } else {
            $bgcl = "#CCCCCC";
        }
?>
  <tr bgcolor="<?php echo $bgcl; ?>">
    <td><?php echo $array_dc_location[$row['dc_loc']]; ?></td>
    <td><?php echo $row['floor']; ?>F</td>
    <td><?php echo $row['pdu_no']; ?></td>
    <td>分電盤(<?php echo $row['bunden']; ?>)</td>
    <td><a href="./breaker_edit.php?id=<?php echo $row['id']; ?>"><?php echo $row['mccb_no']; ?></a></td>
    <td><a href="./rack_edit.php?id=<?php echo $row['rack_id']; ?>"><?php echo $row['rack_name']; ?></a></td>
    <td><?php echo $row['ampere']; ?>A</td>
    <td><?php echo ($row['status'] == 1 ? "ON" : "OFF"); ?></td>
    <td><a href="./company_edit.php?id=<?php echo $row['company_id']; ?>"><?php echo $row['company_name']; ?></a></td>
    <td><?php echo $row['update_user_name']; ?></td>
    <td align="center">
<?php if (check_auth('delete')) { ?>
    <?php if ($row['rack_name']=='') { ?>
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
