<?php header("Content-Type: text/html; charset=utf-8"); ?>
<?php

include_once('./lib/config.php');
include_once('./lib/function.php');
include_once('./lib/db_access.php');

// ログインチェック
if (!check_session_login()) { exit; }

// DB接続
$db = new db_access();


if ($_POST['dl_mode'] === 'ip_download') {
    $where = $_POST['csv_where'];
    $from = $_POST['from_str'];
    $select = $_POST['select_str'];
    $output = '"IPアドレス","収容ルータ","ポート","パッチパネル番号","サービス","備考"' . "\r\n";
    // データを取得
    $data = $db->select($from, $select, $where);
    if ($data) {
        foreach ($data as $row) {
            $output .= '"' . $row['address'] . '",';
            $output .= '"' . $row['router'] . '",';
            $output .= '"' . $row['port'] . '",';
            $output .= '"' . $row['panel'] . '",';
            $output .= '"' . $row['service'] . '",';
            $output .= '"' . $row['comment'] . '"' . "\r\n";
        }
    }
} elseif ($_POST['dl_mode'] === 'company_download') {

    $where = $_POST['csv_where'];
    $output = '"会社名","会社フリガナ","会社住所","会社電話番号","会社担当者名","会社メールアドレス","ビットアイル担当者","エンドユーザー名","エンドユーザーカナ","備考"' . "\r\n";
    // データを取得
    $data = $db->select(TABLE_COMPANY, "*", $where);
    if ($data) {
        foreach ($data as $row) {
            $output .= '"' . $row['name'] . '",';
            $output .= '"' . $row['kana'] . '",';
            $output .= '"' . $row['address'] . '",';
            $output .= '"' . $row['tel'] . '",';
            $output .= '"' . $row['tanto_name'] . '",';
            $output .= '"' . $row['mail_address'] . '",';
            $output .= '"' . $row['sales_name'] . '",';
            $output .= '"' . $row['end_user_name'] . '",';
            $output .= '"' . $row['end_user_kana'] . '",';
            $output .= '"' . $row['comment'] . '"' . "\r\n";
        }
    }
} elseif ($_POST['dl_mode'] === 'rack_output_mail') {
    $search_rack = $_SESSION['search_rack'];
    $mail_type = $_POST['mail_type'];

// SELECT distinct(mail.address) from rack, mail where rack.del_flag != 1 and rack.company_id = mail.company_id
    $from_str = TABLE_RACK . ", " . TABLE_MAIL;
    $select_str = " distinct(" . TABLE_MAIL . ".address)" ;
    $where_str = "WHERE " . TABLE_RACK . ".del_flag != 1 "
                . " AND " . TABLE_RACK . ".company_id = " . TABLE_MAIL . ".company_id "
                . " AND " . TABLE_MAIL . ".type = " . $mail_type;
    if ($search_rack) {
        if ($search_ini_rack) {
            // 先頭一致検索
            $where_str .= " AND (" . TABLE_RACK . ".name like '" . addslashes($search_rack) . "%') ";
        } else {
            $where_str .= " AND (" . TABLE_RACK . ".name like '%" . addslashes($search_rack) . "%') ";
        }
    }

    $mail_data = $db->select($from_str, $select_str, $where_str);
    if ($mail_data) {
        foreach ($mail_data as $value) {
            $mail_address[] = $value['address'];
        };
        $output = implode(";", $mail_address);
    } else {
//debug   echo  $select_str . $from_str . $where_str;
    }
} elseif ($_POST['dl_mode'] === 'router_output_mail') {
    $search_router = $_SESSION['search_router'];
    $search_router_loc = $_SESSION['search_router_loc'];
    $mail_type = $_POST['mail_type'];

// SELECT distinct(mail.address) from router, mail where router.del_flag != 1 and router.company_id = mail.company_id
    $from_str = TABLE_ROUTER . ", " . TABLE_MAIL;
    $select_str = " distinct(" . TABLE_MAIL . ".address)" ;
    $where_str = "WHERE " . TABLE_ROUTER . ".del_flag != 1 "
                . " AND " . TABLE_ROUTER . ".company_id = " . TABLE_MAIL . ".company_id "
                . " AND " . TABLE_MAIL . ".type = " . $mail_type;
    if ($search_router != '') {
        $where_str .= " AND (" . TABLE_ROUTER . ".router like '%" . addslashes($search_router) . "%') ";
    }
    if ($search_router_loc != "") {
        $where_str .= " AND (" . TABLE_ROUTER . ".ip_loc = '{$search_router_loc}')";
    }

    $mail_data = $db->select($from_str, $select_str, $where_str);
    if ($mail_data) {
        foreach ($mail_data as $value) {
            $mail_address[] = $value['address'];
        };
        $output = implode(";", $mail_address);
    } else {
//debug   echo  $select_str . $from_str . $where_str;
    }
} elseif ($_POST['dl_mode'] === 'breaker_output_mail') {
    $search_breaker = $_SESSION['search_breaker'];
    $search_breaker_loc = $_SESSION['search_breaker_loc'];
    $search_breaker_floor = $_SESSION['search_breaker_floor'];
    $search_breaker_bunden = $_SESSION['search_breaker_bunden'];
    $mail_type = $_POST['mail_type'];

// SELECT distinct(mail.address) from breaker, rack, mail where breaker.del_flag != 1 
// and rack.id = breaker.rack_id and rack.company_id = mail.company_id and mail.type = 1
    $from_str = TABLE_BREAKER . ", " . TABLE_RACK . ", " . TABLE_MAIL;
    $select_str = " distinct(" . TABLE_MAIL . ".address)" ;
    $where_str = "WHERE " . TABLE_BREAKER . ".del_flag != 1 "
                . " AND " . TABLE_BREAKER . ".rack_id = " . TABLE_RACK . ".id "
                . " AND " . TABLE_RACK . ".company_id = " . TABLE_MAIL . ".company_id "
                . " AND " . TABLE_MAIL . ".type = " . $mail_type;
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

    $mail_data = $db->select($from_str, $select_str, $where_str);
    if ($mail_data) {
        foreach ($mail_data as $value) {
            $mail_address[] = $value['address'];
        };
        $output = implode(";", $mail_address);
    } else {
echo  $select_str . $from_str . $where_str;
    }
}

header("Cache-Control: public");
header("Pragma: public");

// ヘッダ出力
//    header("Content-Type: text/plain");
header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=mail.txt");
$output = mb_convert_encoding($output,"SJIS","EUC-JP");
echo $output;

exit;
?>
