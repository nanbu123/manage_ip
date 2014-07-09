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
        if (!$db->delete(TABLE_ROUTER, $where)) {
            $err_flag = true;
        }
    }

    if ($err_flag) {
        $result_msg = '<font color="red">ルータポートの削除に失敗しました</font><br>' . "\n";
    } else {
        $result_msg = '<font color="blue">ルータポートを削除しました</font><br>' . "\n";
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
        $params['service'] = $_POST['txt_service_'.$id];
        $params['panel'] = $_POST['txt_panel_'.$id];
        $params['comment'] = $_POST['txt_comment_'.$id];
        $params['update_user_name'] = $_SESSION['login_name'];
        $params['update_date'] = 'NOW()';
        $where = "WHERE id = '" . $id . "'";

        if (!$db->update(TABLE_ROUTER, $params, $where)) {
            $err_flag = true;
        } else {
            // 更新したら初期化
            $params = array();
        }
    }

    if ($err_flag) {
        $result_msg = '<font color="red">ルータポートの更新に失敗しました</font><br>' . "\n";
    } else {
        $result_msg = '<font color="blue">ルータポートを更新しました</font><br>' . "\n";
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

$where = "WHERE (" . TABLE_ROUTER . ".del_flag != 1) ";
// 検索キーを取得
if ($_POST['sub_clear_router']) {
    // 検索リセット
    $_SESSION['search_router'] = "";
    $_SESSION['search_router_loc'] = "";
    header('Location:./router_list.php');
    exit;
} elseif ($_POST['sub_router']) {
    $search_router = $_POST['search_router'];
    $_SESSION['search_router'] = $search_router;
    $_SESSION['search_router_loc'] = $_POST['search_router_loc'];
    // セッションに保存して読み直す
    header('Location:./router_list.php');
    exit;
} elseif ($_GET['router']) {
    $search_router = urldecode($_GET['router']);
    $_SESSION['search_router'] = $search_router;
} elseif ($_SESSION['search_router'] || $_SESSION['search_router_loc']) {
    $search_router = $_SESSION['search_router'];
    $search_router_loc = $_SESSION['search_router_loc'];
}

// ルータポート名検索
if ($search_router != '') {
    $where .= " AND (" . TABLE_ROUTER . ".router like '%" . addslashes($search_router) . "%') ";
}
if ($search_router_loc != "") {
    $where .= " AND (" . TABLE_ROUTER . ".ip_loc = '{$search_router_loc}')";
}

$where .= " ORDER BY " . TABLE_ROUTER . ".router," . TABLE_ROUTER . ".port ASC ";
// ページング
// FROM router LEFT JOIN company ON router.company_id = company.id
$from_str = TABLE_ROUTER . " LEFT JOIN " . TABLE_COMPANY 
        . " ON " . TABLE_ROUTER . ".company_id = " . TABLE_COMPANY . ".id";
$total = $db->sel_count($from_str, TABLE_ROUTER.'.id', $where);
$paging = get_paging($total, $cur_page, 'router_list.php', $get_param);

// limit追加する前にダウンロード用に取る
$csv_where = $where;
// limit追加
$where .= " LIMIT " . PAGE_ROW * ($cur_page-1) . "," . PAGE_ROW;

// データを取得
// SELECT router.*, company.name as company_name 
$select_str = TABLE_ROUTER . ".*," . TABLE_COMPANY . ".name as company_name";
$data = $db->select($from_str, $select_str, $where);

// ip_loc配列
$sel_ip_loc = SelOfArray("search_router_loc", $search_router_loc, $array_ip_location, true);

?>

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=euc-jp" >
<meta http-equiv="Content-Style-Type" content="text/css" >
<link rel="stylesheet" type="text/css" href="./css/style.css">
<title>IP管理：ルータポート一覧</title>
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
        dl_form.dl_mode.value='router_output_mail';
        dl_form.mail_type.value=type;
        dl_form.submit();
        return false;
    }
//-->
</script>
<body>

<h2>ルータポート一覧</h2>
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
        <?php echo $sel_ip_loc; ?>
    </td>
    <td>
        <input type="text" name="search_router" size="40" value="<?php echo htmlspecialchars($search_router); ?>" style="ime-mode:disabled">
    </td>
    <td>
        <input type="submit" name="sub_router" value="検索">
    </td>
    <td>
        <input type="submit" name="sub_clear_router" value="リセット">
    </td>
  </tr>
</form>
</table>
<br>
<form name="dl_form" action="./download.php" method="POST">
    <input type="hidden" name="csv_where" value="<?php echo $csv_where; ?>">
    <input type="hidden" name="dl_mode" value="router_download">
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
    <th>ルータ名</th>
    <th>ポート名</th>
    <th>割振先</th>
    <th>会社名</th>
    <th>サービス</th>
    <th>パッチパネル</th>
    <th>備考</th>
    <th>最終更新者</th>
    <th>　</th>
  </tr>
<?php 
if ($data) {
    foreach ($data as $row) { 
?>
  <tr>
    <td><?php echo $row['router']; ?></td>
    <td><?php echo $row['port']; ?></td>
    <td><?php echo $array_ip_location[$row['ip_loc']]; ?></td>
    <td><a href="./company_edit.php?id=<?php echo $row['company_id']; ?>"><?php echo $row['company_name']; ?></a></td>

    <td><input type="text" size="15" name="txt_service_<?php echo $row['id']; ?>" value="<?php echo $row['service']; ?>"></td>
    <td><input type="text" size="22" name="txt_panel_<?php echo $row['id']; ?>" value="<?php echo $row['panel']; ?>"></td>
    <td><input type="text" size="25" name="txt_comment_<?php echo $row['id']; ?>" value="<?php echo $row['comment']; ?>"></td>
    <td><?php echo $row['update_user_name']; ?></td>
    <td align="center">
<?php if (check_auth('update')) { ?>
        <input type="button" name="sub_update" value="更新" onclick="update_submit( <?php echo $row['id']; ?> );">
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
