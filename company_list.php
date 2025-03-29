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
if ($_POST['mode']=='add') {
    // 会社名登録
    $mode = 'add';
    if ($_POST['txt_name_new'] != '') {

        $params = array();
        $params['name'] = $_POST['txt_name_new'];
        $params['end_user_name'] = $_POST['txt_end_user_name_new'];
        $params['sales_name'] = $_POST['txt_sales_name_new'];
        session_start();
        $params['add_user_name'] = $_SESSION['login_name'];
        $params['update_user_name'] = $_SESSION['login_name'];
        $params['add_date'] = 'NOW()';
        $params['update_date'] = 'NOW()';

        // DBに登録する
        if ($db->insert(TABLE_COMPANY, $params)) {
            $result_msg = '<font color="blue">会社情報を登録しました</font><br>' . "\n";
            $params = array();  // 登録したので初期化する
            $new_data = $db->select(TABLE_COMPANY, "MAX(id) as add_id", "");
            if ($new_data) {
                $new_id = $new_data[0]['add_id'];
                header('Location:./company_edit.php?id=' . $new_id);
            }
        } else {
            $result_msg = '<font color="red">会社情報の登録に失敗しました</font><br>' . "\n";
        }
    } else {
        $result_msg  = '<font color="red">登録する会社名を入力してください</font><br>' . "\n";
    }

} elseif ($_POST['mode']=='delete'){
    // 会社削除
    $mode = 'delete';
    $id = $_POST['id'];
    if ($id == ''){
        $err_flag = true;
    } elseif (!is_numeric($id)){
        $err_flag = true;
    } else {
        $where = "WHERE id = '" . $id . "'";
        if (!$db->delete(TABLE_COMPANY, $where)) {
            $err_flag = true;
        }
    }

    if ($err_flag) {
        $result_msg = '<font color="red">会社情報の削除に失敗しました</font><br>' . "\n";
    } else {
        $result_msg = '<font color="blue">会社情報を削除しました</font><br>' . "\n";
    }
} elseif ($_POST['mode']=='update'){
    // 会社情報の更新
    $mode = 'update';
    $id = $_POST['id'];
    if ($id == ''){
        $err_flag = true;
    } elseif (!is_numeric($id)){
        $err_flag = true;
    } else {
        $params['tel'] = $_POST['txt_tel_'.$id];
        $params['sales_name'] = $_POST['txt_sales_name_'.$id];
        $params['update_user_name'] = $_SESSION['login_name'];
        $params['update_date'] = 'NOW()';
        $where = "WHERE id = '" . $id . "'";

        if (!$db->update(TABLE_COMPANY, $params, $where)) {
            $err_flag = true;
        } else {
            // 更新したら初期化
            $params = array();
        }
    }

    if ($err_flag) {
        $result_msg = '<font color="red">会社情報の更新に失敗しました</font><br>' . "\n";
    } else {
        $result_msg = '<font color="blue">会社情報を更新しました</font><br>' . "\n";
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

$where = "WHERE (del_flag = '0') ";
// 検索キーを取得
if ($_POST['sub_clear_cp']) {
    $_SESSION['search_cp'] = "";
    $_SESSION['search_sales'] = "";
    header('Location:./company_list.php');
    exit;
} elseif ($_POST['sub_company']) {
    $company = $_POST['company'];
    $_SESSION['search_cp'] = $company;
    $_SESSION['search_sales'] = $_POST['search_sales'];
    $_SESSION['search_end_cp'] = 0;
    header('Location:./company_list.php');
    exit;
} elseif ($_POST['sub_end_c_company']) {
    $company = $_POST['company'];
    $_SESSION['search_cp'] = $company;
    $_SESSION['search_sales'] = $_POST['search_sales'];
    $_SESSION['search_end_cp'] = 1;
    header('Location:./company_list.php');
    exit;
} elseif ($_GET['cp']) {
    $company = urldecode($_GET['cp']);
    $_SESSION['search_cp'] = $company;
    $_SESSION['search_sales'] = "";
} elseif (isset($_SESSION['search_cp']) || isset($_SESSION['search_sales'])) {
    $company = $_SESSION['search_cp'];
    $search_sales = $_SESSION['search_sales'];
    $end_company = $_SESSION['search_end_cp'];
}


// 会社名検索
if ($company != '') {

    $where .= " AND (";
    $where .= " (name like '%" . addslashes($company) . "%') OR ";
    $where .= " (kana like '%" . addslashes($company) . "%') OR ";
    $where .= " (end_user_name like '%" . addslashes($company) . "%') OR ";
    $where .= " (end_user_kana like '%" . addslashes($company) . "%') ";
    $where .= ") ";
}
if ($search_sales != '') {
    $where .= " AND (";
    $where .= " (sales_name like '%" . addslashes($search_sales) . "%') ";
    $where .= ") ";
}
if ($end_company) {
    $where .= " AND (end_contract_day > 0 AND end_contract_day < NOW('Y-m-d'))";
}

$where .= " ORDER BY " . TABLE_COMPANY . ".kana," . TABLE_COMPANY . ".name ASC ";

// ページング
$total = $db->sel_count(TABLE_COMPANY, 'id', $where);
$paging = get_paging($total, $cur_page, 'company_list.php', $get_param);

// limit追加する前にダウンロード用に取る
$csv_where = $where;
// limit追加
$where .= " LIMIT " . PAGE_ROW * ($cur_page-1) . "," . PAGE_ROW;

// データを取得
$data = $db->select(TABLE_COMPANY, "*", $where);


?>

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" >
<meta http-equiv="Content-Style-Type" content="text/css" >
<link rel="stylesheet" type="text/css" href="./css/style.css">
<title>IP管理：会社一覧</title>
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
    function add_submit(id){
        var up_form = document.update_form;
        up_form.mode.value='add';
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

<h2>会社一覧</h2>
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
        会社名
        <br><input type="text" name="company" size="40" value="<?php echo htmlspecialchars($company); ?>">
    </td>
    <td>
        例）國澤
        <br><input type="text" name="search_sales" size="20" value="<?php echo htmlspecialchars($search_sales); ?>">
    </td>
    <td>
        <br><input type="submit" name="sub_company" value="検索">
    </td>
    <td>
        <br><input type="submit" name="sub_end_c_company" value="契約終了検索">
    </td>
    <td>
        <br><input type="submit" name="sub_clear_cp" value="リセット">
    </td>
  </tr>
</form>
</table>
<br>
<form name="dl_form" action="./download.php" method="POST">
<input type="hidden" name="csv_where" value="<?php echo $csv_where; ?>">
<input type="hidden" name="dl_mode" value="company_download">
<?php echo $total ?>件見つかりました　<input type="submit" name="sub_download" value="CSV出力">
</form>

<!-- 一覧 ここから -->
<table class="list" width="700">
<form name="update_form" action="" method="POST">
  <caption style="text-align:right"><?php echo $paging; ?></caption>
  <tr>
    <th>会社名</th>
    <th>エンドユーザー名</th>
    <th>ビットアイル担当者</th>
    <th>最終更新者</th>
    <th>　</th>
  </tr>
<?php 
if ($data) {
    foreach ($data as $row) { 
        if (($row['end_contract_day'] > 0) && ($row['end_contract_day'] < date('Y-m-d'))) {
            $bgcolor = "#CCCCCC";
        } else {
            $bgcolor = "";
        }
?>
  <tr bgcolor="<?php echo $bgcolor; ?>">
    <td><a href="./company_edit.php?id=<?php echo $row['id']; ?>"><?php echo $row['name']; ?></a></td>
    <td><a href="./company_edit.php?id=<?php echo $row['id']; ?>"><?php echo $row['end_user_name']; ?></a></td>
    <td><input type="text" size="20" name="txt_sales_name_<?php echo $row['id']; ?>" value="<?php echo $row['sales_name']; ?>"></td>
    <td><?php echo $row['update_user_name']; ?></td>
    <td>
<?php if (check_auth('update')) { ?>
        <input type="button" name="sub_update" value="更新" onclick="update_submit( <?php echo $row['id']; ?> );">
<?php } ?>
<?php if (check_auth('delete')) { ?>
        <input type="button" name="sub_delete" value="削除" onclick="del_submit( <?php echo $row['id']; ?> );">
<?php } ?>
    </td>
  </tr>
<?php
    }
}
?>
<?php if (check_auth('update')) { ?>
  <tr>
    <td><input type="text" size="20" name="txt_name_new" value="<?php echo $params['name']; ?>"></td>
    <td><input type="text" size="20" name="txt_end_user_name_new" value="<?php echo $params['end_user_name']; ?>"></td>
    <td><input type="text" size="20" name="txt_sales_name_new" value="<?php echo $params['sales_name']; ?>"></td>
    <td>&nbsp</td>
    <td align="center"><input type="button" name="sub_add" value="登録" onclick="add_submit();"></td>
  </tr>
<?php } ?>
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
