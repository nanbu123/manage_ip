<?php

include_once('./lib/config.php');
include_once('./lib/function.php');
include_once('./lib/db_access.php');

// ログインチェック
if (!check_session_login()) { exit; }

if ($_POST['sub_dl_mail'] == '') {
    exit;
}

// DB接続
$db = new db_access();

// ヘッダ出力
header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=ip_list.csv");

if ($_POST['dl_mode'] == 'rack_download') {
    $where = $_POST['csv_where'];

// SELECT distinct(mail.address) from rack, mail where rack.del_flag != 1 and rack.company_id = mail.company_id

    $from_str = TABLE_RACK . ", " . TABLE_MAIL;
    $select_str = " distinct(" . TABLE_MAIL . ".address)" ;
    $where_str = $where . "AND " . TABLE_RACK . ".company_id = " . TABLE_MAIL . ".company_id";
    echo  $select_str . $from_str . $where_str
    $mail_data = $DB->select($from_str, $select_str, $where_str);
    if ($mail_data) {
        $output = inplove(",", $mail_data);
    }
} elseif ($_POST['dl_mode'] == 'router_download') {
    $where = $_POST['csv_where'];

// SELECT distinct(mail.address) from router, mail where router.del_flag != 1 and router.company_id = mail.company_id

    $from_str = TABLE_ROUTER . ", " . TABLE_MAIL;
    $select_str = " distinct(" . TABLE_MAIL . ".address)" ;
    $where_str = $where . "AND " . TABLE_ROUTER . ".company_id = " . TABLE_MAIL . ".company_id";
    echo  $select_str . $from_str . $where_str
    $mail_data = $DB->select($from_str, $select_str, $where_str);
    if ($mail_data) {
        $output = inplove(",", $mail_data);
    }
}

if ($output) {
    print (mb_convert_encoding($output,"SJIS","EUC-JP"));
}

exit;
?>
