<?php

include_once('./lib/config.php');
include_once('./lib/function.php');

// ログインチェック
if (!check_session_login()) { exit; }

// ip_loc配列
$sel_ip_loc = SelOfArray("search_ip_loc", $search_ip_loc, $array_ip_location, true);

// dc_loc配列
$sel_rack_dc_loc = SelOfArray("search_rack_loc", $search_rack_loc, $array_dc_location, true);
$sel_bre_dc_loc = SelOfArray("search_breaker_loc", $search_breaker_loc, $array_dc_location, true);
// フロア
foreach(range(1, $max_breaker_floor) as $val) {
    $ar_floor[$val] = $val . "F";
}
$sel_rack_floor = SelOfArray("search_rack_floor", $search_rack_floor, $ar_floor, true);
$sel_bre_floor = SelOfArray("search_breaker_floor", $search_breaker_floor, $ar_floor, true);


?>

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=euc-jp" >
<meta http-equiv="Content-Style-Type" content="text/css" >
<link rel="stylesheet" type="text/css" href="./css/style.css">
<title>IP管理：検索</title>
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

<h2>検索</h2>
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
<table class="list"">
  <!--IPアドレス検索-->
  <form name="ip_form" action="./ip_list.php" method="POST">
  <tr><th colspan="4">IPアドレスで検索</th></tr>
  <tr>
    <td>
        IPロケーション<br>
        <?php echo $sel_ip_loc; ?>
    </td>
    <td>
        例)192.168.1.0/24<br>
        <input type="text" name="ip_address" size="40" value="<?php echo $_SESSION['search_ip']; ?>" style="ime-mode:disabled">
    </td>
    <td>
        <br>
        <input type="submit" name="sub_ip_address" value="検索">
        <input type="submit" name="sub_ip_space" value="空きアドレス検索">
    </td>
    <td>
        <br><input type="submit" name="sub_clear_ip" value="リセット">
    </td>
  </tr>
  </form>
  <!--会社名で検索-->
  <tr><td colspan="4">&nbsp;</td></tr>
  <form name="company_form" action="./company_list.php" method="POST">
  <tr><th colspan="4">会社名で検索</th></tr>
  <tr>
    <td>
        　
    </td>
    <td>
        例)セガ<br>
        <input type="text" name="company" size="40" value="<?php echo $_SESSION['search_cp']; ?>">
    </td>
    <td>
        <br><input type="submit" name="sub_company" value="検索">
    </td>
    <td>
        <br><input type="submit" name="sub_clear_cp" value="リセット">
    </td>
  </tr>
  </form>
  <!--ラック名で検索-->
  <tr><td colspan="4">&nbsp;</td></tr>
  <form name="rack_form" action="./rack_list.php" method="POST">
  <tr><th colspan="4">ラック名で検索</th></tr>
  <tr>
    <td>
        DCロケーション・フロア<br>
        <?php echo $sel_rack_dc_loc; ?><?php echo $sel_rack_floor; ?>
    </td>
    <td>
        例)34A101<br>
        <input type="text" name="search_rack" size="40" value="<?php echo $_SESSION['search_rack']; ?>" style="ime-mode:disabled">
    </td>
    <td>
        <br><input type="submit" name="sub_rack" value="検索">
        <input type="submit" name="sub_ini_rack" value="先頭一致検索">
    </td>
    <td>
        <br><input type="submit" name="sub_clear_rack" value="リセット">
    </td>
  </tr>
  </form>
  <!--ブレーカー名で検索-->
  <tr><td colspan="4">&nbsp;</td></tr>
  <form name="bre_form" action="./breaker_list.php" method="POST">
  <tr><th colspan="4">ブレーカー名で検索</th></tr>
  <tr>
    <td>
        DCロケーション・フロア<br>
        <?php echo $sel_bre_dc_loc; ?><?php echo $sel_bre_floor; ?>
    </td>
    <td>
        例)3-1A<br>
        <input type="text" name="search_breaker" size="40" value="<?php echo $_SESSION['search_breaker']; ?>" style="ime-mode:disabled">
    </td>
    <td>
        <br><input type="submit" name="sub_breaker" value="検索">
    </td>
    <td>
        <br><input type="submit" name="sub_clear_breaker" value="リセット">
    </td>
  </tr>
  </form>
</table>
<!-- メイン終了 -->
</td>
</tr>
</table>
<!-- 全体終了 -->


<hr>
<?php echo FOOTER_STR; ?>

</body>
</html>
