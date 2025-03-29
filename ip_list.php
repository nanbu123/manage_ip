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
    $mode = 'delete';
    $id = $_POST['id'];
    if ($id == ''){
        $err_flag = true;
    } elseif (!is_numeric($id)){
        $err_flag = true;
    } else {
        $where = "WHERE id = '" . $id . "'";
        if (!$db->delete('ip_address', $where)) {
            $err_flag = true;
        }
    }

    if ($err_flag) {
        $result_msg = '<font color="red">IPアドレス情報の削除に失敗しました</font><br>' . "\n";
    } else {
        $result_msg = '<font color="blue">IPアドレス情報を削除しました</font><br>' . "\n";
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
        $params['comment'] = $_POST['txt_comment_'.$id];
        $params['update_user_name'] = $_SESSION['login_name'];
        $params['update_date'] = 'NOW()';
        $where = "WHERE id = '" . $id . "'";

        if (!$db->update(TABLE_ADDRESS, $params, $where)) {
            $err_flag = true;
        } else {
            // 更新したら初期化
            $params = array();
        }
    }

    if ($err_flag) {
        $result_msg = '<font color="red">IPアドレス情報の更新に失敗しました</font><br>' . "\n";
    } else {
        $result_msg = '<font color="blue">IPアドレス情報を更新しました</font><br>' . "\n";
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

$where = "WHERE (" . TABLE_ADDRESS . ".del_flag != 1) ";
// 検索キーを取得
if ($_POST['sub_clear_ip']) {
    // 検索リセット
    $_SESSION['search_ip'] = "";
    $_SESSION['search_ip_loc'] = "";
    header('Location:./ip_list.php');
    exit;
} elseif ($_POST['sub_ip_address'] || $_POST['sub_ip_space']) {
    // 検索実行
    $ip_address = $_POST['ip_address'];
    $_SESSION['search_ip'] = $ip_address;
    if ($_POST['sub_ip_space']) {
        $_SESSION['search_ip_space'] = 1;
    } else {
        $_SESSION['search_ip_space'] = 0;
    }
    $_SESSION['search_ip_loc'] = $_POST['search_ip_loc'];
    // セッションに保存して読み直す
    header('Location:./ip_list.php');
    exit;
} elseif ($_GET['ip']) {
    $ip_address = urldecode($_GET['ip']);
    $_SESSION['search_ip'] = $ip_address;
} elseif ($_SESSION['search_ip'] || $_SESSION['search_ip_loc']){
    $ip_address = $_SESSION['search_ip'];
    $search_ip_loc = $_SESSION['search_ip_loc'];
}

// IPアドレス検索
if ($ip_address != '') {
    $where .= " AND ( ";
    $where .= " (" . TABLE_ADDRESS . ".address like '%" . addslashes($ip_address) . "%')";
    $ip_info = get_ip_info($ip_address);
    $search_s = ip_to_long($ip_info['start']);
    $search_e = ip_to_long($ip_info['end']);
    $ip_s = TABLE_ADDRESS . ".ip_start";
    $ip_e = TABLE_ADDRESS . ".ip_end";
    if ($ip_info['start'] != '') {
        $where .= " OR ({$ip_s} <= {$search_s} AND {$ip_e} >= {$search_s} )";
        $where .= " OR ({$ip_s} <= {$search_e} AND {$ip_e} >= {$search_e} )";
        $where .= " OR ({$ip_s} >= {$search_s} AND {$ip_s} <= {$search_e} )";
        $where .= " OR ({$ip_e} >= {$search_s} AND {$ip_e} <= {$search_e} )";
    }
    $where .= " )";
}
if ($search_ip_loc != "") {
    $where .= " AND (" . TABLE_ADDRESS . ".ip_loc = '{$search_ip_loc}')";
}
if ($_SESSION['search_ip_space'] == 1) {
    $where .= " AND (" . TABLE_ADDRESS . ".router_id = '')";
}

$where .= " ORDER BY " . TABLE_ADDRESS . ".ip_start ASC ";
// ページング
// FROM ip_address LEFT JOIN router ON ip_address.router_id = router.id 
// LEFT JOIN company ON router.company_id = company.id
$from_str = TABLE_ADDRESS . " LEFT JOIN " . TABLE_ROUTER;
$from_str .= " ON " . TABLE_ADDRESS . ".router_id = " . TABLE_ROUTER . ".id ";
$from_str .= "LEFT JOIN " . TABLE_COMPANY . " ON " . TABLE_ROUTER . ".company_id = " . TABLE_COMPANY . ".id";

$total = $db->sel_count($from_str, TABLE_ADDRESS . ".id", $where);
$paging = get_paging($total, $cur_page, 'ip_list.php', $get_param);

// limit追加する前にダウンロード用に取る
$csv_where = $where;
// limit追加
$where .=  " LIMIT " . PAGE_ROW * ($cur_page-1) . "," . PAGE_ROW;

// データを取得
// SELECT ip_address.id as ip_id, ip_address.address, ip_address.update_user_name, 
// ip_address.ip_end - ip_address.ip_start + 1 as ip_size router.router, 
// router.port, company.name as company_name, company.id as company_id
$select_str = TABLE_ADDRESS . ".id as ip_id," . TABLE_ADDRESS . ".ip_loc," . TABLE_ADDRESS . ".address," 
            . TABLE_ADDRESS . ".update_user_name," . TABLE_ADDRESS . ".lock_flag,"
            . TABLE_ADDRESS . ".ip_end - " . TABLE_ADDRESS . ".ip_start + 1 as ip_size," 
            . TABLE_ROUTER . ".router, " . TABLE_ROUTER . ".port, " 
            . TABLE_COMPANY . ".name as company_name, ". TABLE_COMPANY . ".id as company_id";
$data = $db->select($from_str, $select_str, $where);

// ip_loc配列
$sel_ip_loc = SelOfArray("search_ip_loc", $search_ip_loc, $array_ip_location, true);

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
    function update_submit(id){
        var up_form = document.update_form;
        up_form.mode.value='update';
        up_form.id.value=id;
        up_form.submit();
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

<h2>IPアドレス一覧</h2>
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
        IPロケーション
        <br><?php echo $sel_ip_loc; ?>
    </td>
    <td>
        例)192.168.1.0/24<br>
        <input type="text" name="ip_address" size="40" value="<?php echo htmlspecialchars($ip_address); ?>" style="ime-mode:disabled">
    </td>
    <td>
        <br><input type="submit" name="sub_ip_address" value="検索">
    </td>
    <td>
        <br><input type="submit" name="sub_ip_space" value="空きアドレス検索">
    </td>
    <td>
        <br><input type="submit" name="sub_clear_ip" value="リセット">
    </td>
  </tr>
</form>
</table>
<br>
<form name="dl_form" action="./download.php" method="POST">
<input type="hidden" name="csv_where" value="<?php echo $csv_where; ?>">
<input type="hidden" name="from_str" value="<?php echo $from_str; ?>">
<input type="hidden" name="select_str" value="<?php echo $select_str; ?>">
<input type="hidden" name="dl_mode" value="ip_download">
<?php echo $total ?>件見つかりました　<input type="submit" name="sub_download" value="CSV出力">
</form>

<!-- 一覧 ここから -->
<table class="list" width="700">
<form name="update_form" action="" method="POST">
  <caption style="text-align:right"><?php echo $paging; ?></caption>
  <tr>
    <th>割振先</th>
    <th width="120">IPアドレス</th>
    <th>サイズ</th>
    <th>会社名</th>
    <th>収容ルータ</th>
    <th>ポート</th>
    <th>最終更新者</th>
    <th>　</th>
  </tr>
<?php 
if ($data) {
    foreach ($data as $row) { 
        // 割当禁止の場合背景色を淡灰色に
        if ($row['lock_flag'] == 1) {
            $bgcl = "#CCCCCC";
        } else {
            $bgcl = "#FFFFFF";
        }
        // IP空間の切れ目で線を入れる
        $pat = '/^([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\/{0,1}([0-9]{2}){0,1}$/';
        if (preg_match($pat, $row['address'], &$matches)) {
            if ($matches[4] == '0') {
?>
  <tr><td colspan="8" height="3" bgcolor="gray"> </td></tr>
<?php 
            }
        }
?>
  <tr bgcolor="<?php echo $bgcl; ?>">
    <td><?php echo $array_ip_location[$row['ip_loc']]; ?></td>
    <td><a href="./ip_edit.php?id=<?php echo $row['ip_id']; ?>"><?php echo $row['address']; ?></a></td>
    <td align="center"><?php echo $row['ip_size']; ?></td>
    <td><a href="./company_edit.php?id=<?php echo $row['company_id']; ?>"><?php echo $row['company_name']; ?></a></td>
    <td><?php echo $row['router']; ?></td>
    <td><?php echo $row['port']; ?></td>
    <td><?php echo $row['update_user_name']; ?></td>
    <td align="center">
        <!--<input type="button" name="sub_update" value="更新" onclick="update_submit( <?php echo $row['ip_id']; ?> );">-->
<?php if (check_auth('delete') && $row['lock_flag'] != 1) { ?>
    <?php if ($row['company_name']=='') { ?>
        <input type="button" name="sub_delete" value="削除" onclick="del_submit( <?php echo $row['ip_id']; ?> );">
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
