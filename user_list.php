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
    // ユーザー名登録
    $mode = 'add';
    if ($_POST['txt_name_new'] != '') {

        if (exists_user($_POST['txt_name_new'])) {
            $result_msg  = '<font color="red">既に存在するユーザー名です</font><br>' . "\n";
        } else {
            $params = array();
            $params['name'] = $_POST['txt_name_new'];
            $params['level'] = $_POST['sel_level_new'];
            $params['comment'] = $_POST['txt_comment_new'];
            session_start();
            $params['add_user_name'] = $_SESSION['login_name'];
            $params['update_user_name'] = $_SESSION['login_name'];
            $params['add_date'] = 'NOW()';
            $params['update_date'] = 'NOW()';

            // DBに登録する
            if ($db->insert(TABLE_USER, $params)) {
                $result_msg = '<font color="blue">ユーザ情報を登録しました</font><br>' . "\n";
                $params = array();  // 登録したので初期化する
            } else {
                $result_msg = '<font color="red">ユーザ情報の登録に失敗しました</font><br>' . "\n";
            }
        }
    } else {
        $result_msg  = '<font color="red">登録するユーザ名を入力してください</font><br>' . "\n";
    }
} elseif ($_POST['mode']=='delete'){
    // ユーザ削除
    $mode = 'delete';
    $id = $_POST['id'];
    if ($id == ''){
        $err_flag = true;
    } elseif (!is_numeric($id)){
        $err_flag = true;
    } else {
        $where = "WHERE id = '" . $id . "'";
        if (!$db->delete(TABLE_USER, $where)) {
            $err_flag = true;
        }
    }

    if ($err_flag) {
        $result_msg = '<font color="red">ユーザ情報の削除に失敗しました</font><br>' . "\n";
    } else {
        $result_msg = '<font color="blue">ユーザ情報を削除しました</font><br>' . "\n";
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
        $params['level'] = $_POST['sel_level_'.$id];
        $params['comment'] = $_POST['txt_comment_'.$id];
        $params['update_user_name'] = $_SESSION['login_name'];
        $params['update_date'] = 'NOW()';
        $where = "WHERE id = '" . $id . "'";

        if (!$db->update(TABLE_USER, $params, $where)) {
            $err_flag = true;
        } else {
            // 更新したら初期化
            $params = array();
        }
    }

    if ($err_flag) {
        $result_msg = '<font color="red">ユーザ情報の更新に失敗しました</font><br>' . "\n";
    } else {
        $result_msg = '<font color="blue">ユーザ情報を更新しました</font><br>' . "\n";
    }
} elseif ($_POST['mode']=='pass') {
    // パスワード変更
    header('Location: ./user_password.php?id=' . $_POST['id']);
    exit;
}

// ページ番号
if ($_GET['page']){
    $cur_page = $_GET['page'];
}
if (!($cur_page >= 1)){
    $cur_page = 1;
}

$where = "WHERE (" . TABLE_USER . ".del_flag != 1) ORDER BY " . TABLE_USER . ".id ASC ";

// ページング
$total = $db->sel_count(TABLE_USER, 'id', $where);
$paging = get_paging($total, $cur_page, 'user_list.php', $get_param);

// limit追加する前にダウンロード用に取る
$csv_where = $where;
// limit追加
$where .= " LIMIT " . PAGE_ROW * ($cur_page-1) . "," . PAGE_ROW;

// データを取得
$data = $db->select(TABLE_USER, "*", $where);

// 追加権限配列
$sel_level_new = SelOfArray("sel_level_new", $params['level'], $array_user_level, false);

?>

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=euc-jp" >
<meta http-equiv="Content-Style-Type" content="text/css" >
<link rel="stylesheet" type="text/css" href="./css/style.css">
<title>IP管理：ユーザ一覧</title>
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
    function pass_submit(id){
        var up_form = document.update_form;
        up_form.mode.value='pass';
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

<h2>ユーザ一覧</h2>
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
<br>

<!-- 一覧 ここから -->
<table class="list" width="700">
<form name="update_form" action="" method="POST">
  <caption style="text-align:right"><?php echo $paging; ?></caption>
  <tr>
    <th width="100">ユーザ名</th>
    <th>権限レベル</th>
    <th>備考</th>
    <th>最終更新者</th>
    <th>　</th>
  </tr>
<?php 
if ($data) {
    foreach ($data as $row) { 
?>
  <tr>
    <td><?php echo $row['name']; ?></td>
    <td>
<?php
    // 権限レベルが自分のより低ければ編集できる
    if ($_SESSION['level'] == 1) {
        echo "        <select name=\"sel_level_{$row['id']}\">";
        foreach ($array_user_level as $key => $level) {
            $selected = ($row['level'] == $key)? "selected": "";
            echo "        <option value=\"{$key}\" {$selected}>{$level}</option>\n";
        }
        echo "        </select>";
    } else {
        echo $array_user_level[$row['level']];
    }
?>
    </td>
    <td><input type="text" size="30" name="txt_comment_<?php echo $row['id']; ?>" value="<?php echo $row['comment']; ?>"></td>
    <td><?php echo $row['update_user_name']; ?></td>
    <td align="center">
<?php if (check_auth('special_admin')) { ?>
        <input type="button" name="sub_update" value="更新" onclick="update_submit( <?php echo $row['id']; ?> );">
<?php } ?>
<?php if (check_auth('special_admin')) { ?>
        <input type="button" name="sub_pass" value="パスワード変更" onclick="pass_submit( <?php echo $row['id']; ?> );">
<?php } ?>
<?php if (check_auth('special_admin') && ($row['level']!=1)) { ?>
        <input type="button" name="sub_delete" value="削除" onclick="del_submit( <?php echo $row['id']; ?> );">
<?php } ?>
    </td>
  </tr>
<?php
    }
}
?>
<?php if (check_auth('special_admin')) { ?>
  <tr>
    <td><input type="text" size="20" name="txt_name_new" value="<?php echo $params['name']; ?>"></td>
    <td><?php echo $sel_level_new ?></td>
    <td><input type="text" size="30" name="txt_comment_new" value="<?php echo $params['comment']; ?>"></td>
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
