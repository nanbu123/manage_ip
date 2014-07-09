<?php

include_once('./lib/config.php');
include_once('./lib/function.php');

session_start();
if ($_SESSION['login']=='1') {
    echo $_SESSION['level'];
} else {
    header('Location: ./index.php');
}

// パラメータ取得
if ($_POST['mode']=='add'){
    $mode = 'add';
    add_address($error_str);
} elseif ($_GET['mode']=='search'){
    $mode = 'search';
} elseif ($_GET['mode']=='delete'){
    $mode = 'delete';
    del_address($_GET[id]);
} elseif ($_POST['mode']=='update'){
    $mode = 'update';
    $id = $_POST['id'];
    update_address($id );
}
if ($_GET['page']){
    $cur_page = $_GET['page'];
}
if (!($cur_page >= 1)){
    $cur_page = 1;
}

// IPアドレス情報を取得
function get_address($cur_page)
{
    $link = mysql_connect(SERVER_NAME, DB_USER, DB_PASS)
        or die('Could not connect: ' . mysql_error());
    mysql_select_db(DB_NAME) or die('Could not select database');

    $where = '';
    if ($_POST['txt_search']){
        $where = " WHERE ip like %" . $_POST['txt_search'] . "% or kaisha_name like %" . $_POST['txt_search'] . "%";
    }
    $where = 

    $query = 'SELECT * FROM '. TABLE_ADDRESS .' ORDER BY id' . $where .' limit ' . ($cur_page-1)*PAGE_ROW . ', ' . PAGE_ROW ;
    $result = mysql_query($query) or die('Query failed: ' . mysql_error());

    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        echo '    <tr>';
        echo '<td>' . $row['address'] . '</td>' . "\n";
//        echo '<td><input type="text" size="20" name="txt_adr_' . $row['id'] . '" value="' . $row['address'] . '"></td>' . "\n";
        echo '<td><input type="text" size="20" name="txt_kishu_' . $row['id'] . '" value="' . $row['kishu'] . '"></td>' . "\n";
        echo '<td><input type="text" size="5" name="txt_port_' . $row['id'] . '" value="' . $row['port'] . '"></td>' . "\n";
        echo '<td><input type="text" size="40" name="txt_kaisha_' . $row['id'] . '" value="' . $row['kaisha_name'] . '"></td>' . "\n";
        echo '<td><a href="./mi_address.php?mode=delete&id=' . $row['id'] . '">削除</a>　';
        echo '<a href="#" onclick="update_submit(' . $row['id'] . ');">編集</a></td>' . "\n";
        echo("    </tr>\n");
    }
    // 結果セットを開放する
    mysql_free_result($result);

    // 接続を閉じる
    mysql_close($link);
}

// IPアドレスを登録
function add_address(&$error_str)
{
    $error_str = '';
    // nullチェック
    if ($_POST['add_address'] == '') {
        $error_str = '追加するIPアドレス名を入力してください。';
        return false;
    }
    $pat = '/^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})\/{0,1}(\d{1,2})$/';
    if (preg_match($pat, $_POST['add_address'], &$matches)) {
        for ($i=0;$i<count($matches);$i++){
            echo $matches[$i] . "と";
        }
    }
    $link = mysql_connect(SERVER_NAME, DB_USER, DB_PASS)
        or die('Could not connect: ' . mysql_error());
    mysql_select_db(DB_NAME) or die('Could not select database');

    $query = "INSERT INTO mi_address (address, kishu, port, kaisha_name) values ('" .
             $_POST['add_address'] . "','" . $_POST['add_kishu'] . "','" . $_POST['add_port'] . "','" . $_POST['add_kaisha'] . "')";
    $result = mysql_query($query) or die('Query failed: ' . mysql_error());

}
// IPアドレスを削除
function del_address($id)
{
    if ($id == ''){
        return false;
    } 
    if (!is_numeric($id)){
        return false;
    }
    
    $link = mysql_connect(SERVER_NAME, DB_USER, DB_PASS)
        or die('Could not connect: ' . mysql_error());
    mysql_select_db(DB_NAME) or die('Could not select database');

    $query = "DELETE FROM mi_address WHERE id = '" . $id . "'";
    mysql_query($query) or die('Query failed: ' . mysql_error());

}

// IPアドレスを編集
function update_address($id)
{
    if ($id == ''){
        return false;
    } 
    if (!is_numeric($id)){
        return false;
    }
    
    $link = mysql_connect(SERVER_NAME, DB_USER, DB_PASS)
        or die('Could not connect: ' . mysql_error());
    mysql_select_db(DB_NAME) or die('Could not select database');

    $query = "UPDATE " . TABLE_ADDRESS . " set address='" . $_POST['txt_adr_'.$id] .
            "', kishu='" . $_POST['txt_kishu_'.$id] . 
            "', port='" . $_POST['txt_port_'.$id] . 
            "', kaisha_name='" . $_POST['txt_kaisha_'.$id] . 
            "' WHERE id = " . $id;
//    echo $query;
    mysql_query($query) or die('Query failed: ' . mysql_error());

}

// ページング
function set_paging($cur_page)
{
    $link = mysql_connect(SERVER_NAME, DB_USER, DB_PASS)
        or die('Could not connect: ' . mysql_error());
    mysql_select_db(DB_NAME) or die('Could not select database');

    $query = 'SELECT * FROM ' . TABLE_ADDRESS;
    $result = mysql_query($query) or die('Query failed: ' . mysql_error());
    $num_rows = mysql_num_rows($result);

    $max_page = ($num_rows / PAGE_ROW) + 1;
    for ($i=1;$i<=$max_page;$i++){
        if ($cur_page==$i) {
            echo '[' . $i . '] ';
        } else {
            echo '[' . '<a href="./mi_address.php?page=' . $i . '">' . $i . '</a>' . '] ';
        }
    }

    // 結果セットを開放する
    mysql_free_result($result);
    // 接続を閉じる
    mysql_close($link);
}


?>

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=euc-jp" >
<meta http-equiv="Content-Style-Type" content="text/css" >
<title>IP管理：一覧</title>
</head>
<script LANGUAGE=javascript>
<!--
    function update_submit(id){
        var up_form = document.update_address_form;
        up_form.id.value=id;
        up_form.submit();
        return false;
    }
//-->
</script>
<body>

<h1>IPアドレス</h1>

<form name="search_form" action="" method="POST">
    <input type="hidden" name="mode" value="search">
    <input type="text" name="txt_search" value="">
    <input type="submit" name="submit" value="検索">
</form>
<!-- 一覧 ここから -->
<table>
    <form name="update_address_form" action="" method="POST">
    <input type="hidden" name="mode" value="update">
    <input type="hidden" name="id" value="">
    <?php set_paging($cur_page); ?>
    <table>
        <tr><th>IPアドレス</th><th>ルータ</th><th>ポート</th><th>会社名</th><th>　</th></tr>
        <?php get_address($cur_page); ?>
    </table>
    </form>
</table>
<br>

<hr>
新規登録<br>
<br>
<?php
    if ($mode=='add'){
        echo '<font color="red">' . $error_str . '</font>' . "\n";
    }
?>
<form name="add_address_form" action="" method="POST">
<table>
  <tr>
    <td>IPアドレス</td>
    <td><input type="text" name="add_address" size="30" value=""></td>
  </tr>
  <tr>
    <td>ルータ</td>
    <td><input type="text" name="add_kishu" size="30" value=""></td>
  </tr>
  <tr>
    <td>ポート</td>
    <td><input type="text" name="add_port" size="5" value=""></td>
  </tr>
  <tr>
    <td>会社名</td>
    <td><input type="text" name="add_kaisha" size="40" value=""></td>
  </tr>
  <tr>
    <td colspan="2">　</td>
  </tr>
  <tr>
    <input type="hidden" name="mode" value="add">
    <td colspan=2 align="center"><input type="submit" name="submit" value="登録"></td>
  </tr>
</table>
</form>

<hr>
2008 IPアドレス管理システム

</body>
</html>
