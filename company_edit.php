<?php

include_once('./lib/config.php');
include_once('./lib/function.php');
include_once('./lib/db_access.php');

// ログインチェック
if (!check_session_login()) { exit; }

// DB接続
$db = new db_access();

$err_flag = false;
$result_flag = false;
$err_msg  = '';
$result_msg = '';
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];
    // データ更新・取得用
    $where = "where id = '{$id}'";
} else {
    // IDが取得できなければ一覧へ
    header('Location: ./company_list.php');
    exit;
}
if (isset($_POST['sub_up_company'])) {
    // 会社情報登録
    if ($_POST['name'] != '') {
        $params = array();
        $params['code'] = $_POST['code'];
        $params['name'] = $_POST['name'];
        $params['kana'] = $_POST['kana'];
        $params['tel'] = $_POST['tel'];
        $params['address'] = $_POST['address'];
// 封印        $params['tanto_name'] = $_POST['tanto_name'];
// 封印        $params['mail_address'] = $_POST['mail_address'];
        $params['sales_name'] = $_POST['sales_name'];
        $params['end_user_name'] = $_POST['end_user_name'];
        $params['end_user_kana'] = $_POST['end_user_kana'];
        if ($_POST['end_c_year'] && $_POST['end_c_month'] && $_POST['end_c_day']) {
            $params['end_contract_day'] = $_POST['end_c_year'] . '-' . $_POST['end_c_month'] . '-' . $_POST['end_c_day'];
        } else {
            $params['end_contract_day'] = '0000-00-00';
        }
        $params['comment'] = $_POST['comment'];
        session_start();
        $params['update_user_name'] = $_SESSION['login_name'];
        $params['update_date'] = 'NOW()';

        // DBを更新する
        if ($db->update(TABLE_COMPANY, $params, $where)) {
            $result_msg = '<font color="blue">会社情報を登録しました</font><br>' . "\n";
        } else {
            $result_msg = '<font color="red">会社情報の登録に失敗しました</font><br>' . "\n";
        }
    } else {
        $err_flag = true;
        $err_msg  = '登録する会社情報を入力してください<br>' . "\n";
    }
} elseif (($_POST['mode']=='mail_add') & ($_POST['mail_type']!='')) {
    // 通常連絡先追加
    $mail_name = $_POST['mail_name_new'];
    $mail_address = $_POST['mail_address_new'];
    $type = $_POST['mail_type'];

    if ($mail_address == '') {
        $err_flag = true;
        $err_msg  = '登録する連絡先メールアドレスを入力してください<br>' . "\n";
    } else {
        $params['company_id'] = $id;
        $params['type'] = $type;
        $params['name'] = $mail_name;
        $params['address'] = $mail_address;
        $params['add_user_name'] = $_SESSION['login_name'];
        $params['update_user_name'] = $_SESSION['login_name'];
        $params['add_date'] = 'NOW()';
        $params['update_date'] = 'NOW()';
        if ($db->insert(TABLE_MAIL, $params)) {
            $result_msg = '<font color="blue">' . $array_mail_type[$type] . '連絡先を追加しました</font><br>' . "\n";
        } else {
            $result_msg = '<font color="blue">' . $array_mail_type[$type] . '連絡先の追加に失敗しました</font><br>' . "\n";
        }
    }
} elseif ($_POST['mode']=='mail_delete') {
    // 通常連絡先削除
    $mail_id = $_POST['mail_id'];
    $type = $_POST['mail_type'];

    $params = array();
    $params['del_flag'] = 1;
    $params['update_user_name'] = $_SESSION['login_name'];
    $params['update_date'] = 'NOW()';

    if ($db->update(TABLE_MAIL, $params, "WHERE id = '{$mail_id}'")) {
        $result_msg = '<font color="blue">' . $array_mail_type[$type] . '連絡先を削除しました</font><br>' . "\n";
    } else {
        $result_msg = '<font color="red">' . $array_mail_type[$type] . '連絡先の削除に失敗しました</font><br>' . "\n";
    }
} elseif ($_POST['mode']=='mail_update') {
    // 通常連絡先更新
    $mail_id = $_POST['mail_id'];
    $type = $_POST['mail_type'];

    $params = array();
    $params['name'] = $_POST['mail_name_' . $mail_id];
    $params['address'] = $_POST['mail_address_' . $mail_id];
    $params['update_user_name'] = $_SESSION['login_name'];
    $params['update_date'] = 'NOW()';

    if ($db->update(TABLE_MAIL, $params, "WHERE id = '{$mail_id}'")) {
        $result_msg = '<font color="blue">' . $array_mail_type[$type] . '連絡先を更新しました</font><br>' . "\n";
    } else {
        $result_msg = '<font color="red">' . $array_mail_type[$type] . '連絡先の更新に失敗しました</font><br>' . "\n";
    }
} elseif (($_POST['mode']=='phone_add') & ($_POST['phone_type']!='')) {
    // 連絡先電話番号追加
    $phone_name = $_POST['phone_name_new'];
    $phone = $_POST['phone_new'];
    $type = $_POST['phone_type'];

    if ($phone == '') {
        $err_flag = true;
        $err_msg  = '登録する連絡先電話番号を入力してください<br>' . "\n";
    } else {
        $params['company_id'] = $id;
        $params['type'] = $type;
        $params['name'] = $phone_name;
        $params['phone'] = $phone;
        $params['add_user_name'] = $_SESSION['login_name'];
        $params['update_user_name'] = $_SESSION['login_name'];
        $params['add_date'] = 'NOW()';
        $params['update_date'] = 'NOW()';
        if ($db->insert(TABLE_PHONE, $params)) {
            $result_msg = '<font color="blue">' . $array_mail_type[$type] . '連絡先電話番号を追加しました</font><br>' . "\n";
        } else {
            $result_msg = '<font color="blue">' . $array_mail_type[$type] . '連絡先電話番号の追加に失敗しました</font><br>' . "\n";
        }
    }
} elseif ($_POST['mode']=='phone_delete') {
    // 連絡先電話番号削除
    $phone_id = $_POST['phone_id'];
    $type = $_POST['phone_type'];

    $params = array();
    $params['del_flag'] = 1;
    $params['update_user_name'] = $_SESSION['login_name'];
    $params['update_date'] = 'NOW()';

    if ($db->update(TABLE_PHONE, $params, "WHERE id = '{$phone_id}'")) {
        $result_msg = '<font color="blue">' . $array_mail_type[$type] . '連絡先電話番号を削除しました</font><br>' . "\n";
    } else {
        $result_msg = '<font color="red">' . $array_mail_type[$type] . '連絡先電話番号の削除に失敗しました</font><br>' . "\n";
    }
} elseif ($_POST['mode']=='phone_update') {
    // 連絡先電話番号更新
    $phone_id = $_POST['phone_id'];
    $type = $_POST['phone_type'];

    $params = array();
    $params['type'] = $type;
    $params['name'] = $_POST['phone_name_' . $phone_id];
    $params['phone'] = $_POST['phone_' . $phone_id];
    $params['update_user_name'] = $_SESSION['login_name'];
    $params['update_date'] = 'NOW()';

    if ($db->update(TABLE_PHONE, $params, "WHERE id = '{$phone_id}'")) {
        $result_msg = '<font color="blue">' . $array_mail_type[$type] . '連絡先電話番号を更新しました</font><br>' . "\n";
    } else {
        $result_msg = '<font color="red">' . $array_mail_type[$type] . '連絡先電話番号の更新に失敗しました</font><br>' . "\n";
    }
} elseif ($_POST['mode']=='rack_add') {
    // ラック割当追加
    $rack_name = $_POST['txt_rack_name_new'];
    if ($rack_name == '') {
        $err_flag = true;
        $err_msg  = '追加するラック名を入力してください<br>' . "\n";
    } else {
        // ラックが存在するかどうか
        $rack_where = "WHERE (name = '{$rack_name}') AND (del_flag != 1) ";
        $r_data = $db->select(TABLE_RACK, '*', $rack_where);
        if ($r_data) {
            // ラックが存在する
            if (($r_data[0]['company_id']=='') || ($r_data[0]['company_id']==0)) {
                // まだ会社に割り当てられていない
                $params = array();
                $params['company_id'] = $id;
                $params['update_user_name'] = $_SESSION['login_name'];
                $params['update_date'] = 'NOW()';
                // ラックデータに会社情報を追加する
                if ($db->update(TABLE_RACK, $params, $rack_where)) {
                    $result_msg = '<font color="blue">ラックを追加しました</font><br>' . "\n";
                    $rack_name = '';
                } else {
                    $result_msg = '<font color="red">ラックの追加に失敗しました</font><br>' . "\n";
                }
            } else {
                // 既に会社に割り当てられている
                $result_msg = '<font color="red">既に割り当てられたラックです</font><br>' . "\n";
            }
        } else {
            $err_flag = true;
            $err_msg  = 'ラックが存在しません　[' . $rack_name . ']<br>' . "\n";
        }
    }
} elseif ($_POST['mode']=='rack_delete') {
    $rack_id = $_POST['rack_id'];
    $params['company_id'] = '';
    // ルータポートの会社情報を削除する
    if ($db->update(TABLE_RACK, $params, "WHERE id = '{$rack_id}'")) {
        $result_msg = '<font color="blue">ラックを解除しました</font><br>' . "\n";
    } else {
        $result_msg = '<font color="red">ラックの解除に失敗しました</font><br>' . "\n";
    }
} elseif ($_POST['mode']=='router_add') {
    // ルータポート追加
    $router = $_POST['txt_router_new'];
    $port = $_POST['txt_port_new'];
    if (($router == '') || ($port == '')) {
        $err_flag = true;
        $err_msg  = '追加するルータポート名を入力してください<br>' . "\n";
    } else {
        // ルータポートが存在するかどうか
        $router_where = "WHERE (router = '{$router}') AND (port = '{$port}') AND (del_flag != 1) ";
        $ro_data = $db->select(TABLE_ROUTER, '*', $router_where);
        if ($ro_data) {
            // ルータポートが存在する
            if ($ro_data[0]['company_id']=='' || $ro_data[0]['company_id']==0) {
                // まだ会社に割り当てられていない
                $params = array();
                $params['company_id'] = $id;
                $params['update_user_name'] = $_SESSION['login_name'];
                $params['update_date'] = 'NOW()';
                // ルータデータに会社情報を追加する
                if ($db->update(TABLE_ROUTER, $params, $router_where)) {
                    $result_msg = '<font color="blue">ルータポートを追加しました</font><br>' . "\n";
                    $rack_name = '';
                } else {
                    $result_msg = '<font color="red">ルータポートの追加に失敗しました</font><br>' . "\n";
                }
            } else {
                // 既に会社に割り当てられている
                $result_msg = '<font color="red">既に割り当てられたルータポートです</font><br>' . "\n";
            }
        } else {
            $err_flag = true;
            $err_msg  = 'ルータポートが存在しません　[' . $router . $port . ']<br>' . "\n";
        }
    }
} elseif ($_POST['mode']=='router_delete') {
    // ルータポート割当解除
    $router_id = $_POST['router_id'];
    $params = array();
    $params['router_id'] = '';
    $params['update_user_name'] = $_SESSION['login_name'];
    $params['update_date'] = 'NOW()';
    // IPアドレスデータのルータ情報を削除する
    if ($db->update(TABLE_ADDRESS, $params, "WHERE router_id = '{$router_id}'")) {
        unset($params['router_id']);
        $params['company_id'] = '';
        // ルータポートの会社情報を削除する
        if ($db->update(TABLE_ROUTER, $params, "WHERE id = '{$router_id}'")) {
            $result_msg = '<font color="blue">ルータポートを解除しました</font><br>' . "\n";
        } else {
            $result_msg = '<font color="red">ルータポートの解除に失敗しました</font><br>' . "\n";
        }
    } else {
        $result_msg = '<font color="red">IPアドレスの解除に失敗しました</font><br>' . "\n";
    }
} elseif ($_POST['mode']=='dns_add') {
    // DNS追加
    $dns = $_POST['dns_new'];
    if ($dns == '') {
        $err_flag = true;
        $err_msg  = '追加するDNS名を入力してください<br>' . "\n";
    } else {
        $params = array();
        $params['domain'] = $dns;
        $params['use_kind'] = $_POST['sel_kind_new'];
        $params['primary'] = $_POST['primary_new'];
        $params['primary_fqdn'] = $_POST['primary_fqdn_new'];
        $params['secondary'] = $_POST['secondary_new'];
        $params['comment'] = $_POST['comment_new'];
        $params['company_id'] = $id;
        $params['add_user_name'] = $_SESSION['login_name'];
        $params['update_user_name'] = $_SESSION['login_name'];
        $params['add_date'] = 'NOW()';
        $params['update_date'] = 'NOW()';
        // DNSを追加する
        if ($db->insert(TABLE_DNS, $params)) {
            $result_msg = '<font color="blue">DNSを追加しました</font><br>' . "\n";
        } else {
            $result_msg = '<font color="red">DNSの追加に失敗しました</font><br>' . "\n";
        }
    }
} elseif ($_POST['mode']=='dns_delete') {
    // DNS削除
    $dns_id = $_POST['dns_id'];
    $params = array();
    $params['del_flag'] = 1;
    $params['update_user_name'] = $_SESSION['login_name'];
    $params['update_date'] = 'NOW()';
    // IPアドレスデータのルータ情報を削除する
    if ($db->update(TABLE_DNS, $params, "WHERE id = '{$dns_id}'")) {
        $result_msg = '<font color="blue">DNSを削除しました</font><br>' . "\n";
    } else {
        $result_msg = '<font color="red">DNSの削除に失敗しました</font><br>' . "\n";
    }
} elseif ($_POST['mode']=='rack_update') {
    // ラック更新
    $rack_id = $_POST['rack_id'];
    $params = array();
    $params['comment'] = $_POST['txt_rack_comment_' . $rack_id];
    $params['update_date'] = 'NOW()';

    // ラック情報を更新する
    if ($db->update(TABLE_RACK, $params, "WHERE id = {$rack_id}")) {
        $result_msg = '<font color="blue">ラックを更新しました</font><br>' . "\n";
    } else {
        $result_msg = '<font color="red">ラックの更新に失敗しました</font><br>' . "\n";
    }

} elseif ($_POST['mode']=='router_update') {
    // ルータポート更新
    $router_id = $_POST['router_id'];
    $params = array();
    $params['service'] = $_POST['txt_service_' . $router_id];
    $params['panel'] = $_POST['txt_panel_' . $router_id];
    $params['comment'] = $_POST['txt_comment_' . $router_id];
    $params['update_date'] = 'NOW()';

    // ルータポート情報を更新する
    if ($db->update(TABLE_ROUTER, $params, "WHERE id = {$router_id}")) {
        $result_msg = '<font color="blue">ルータポートを更新しました</font><br>' . "\n";
    } else {
        $result_msg = '<font color="red">ルータポートの更新に失敗しました</font><br>' . "\n";
    }
} elseif ($_POST['mode']=='ip_add') {
    // IPアドレス割当追加
    $router_id = $_POST['router_id'];
    $ip_mask = $_POST['add_mask_' . $router_id];
    $ip_loc = get_router_loc($router_id);
    if ($ip_loc) {
        // IPアドレスのサイズ
        $size = (1 << (32 - $ip_mask)) - 1;
        $ip_where = "WHERE (ip_end - ip_start = {$size}) AND (router_id = 0) AND (del_flag != 1) "
                    . "AND (ip_loc = {$ip_loc}) AND (lock_flag != 1) ORDER BY ip_start ASC";
        $ip_data = $db->select(TABLE_ADDRESS, '*', $ip_where);
        if ($ip_data) {
                $params = array();
                $params['router_id'] = $router_id;
                $params['update_user_name'] = $_SESSION['login_name'];
                $params['update_date'] = 'NOW()';
                // ルータポートデータに会社情報を追加する
                $ip_id = $ip_data[0]['id'];
                if ($db->update(TABLE_ADDRESS, $params, "WHERE id = '{$ip_id}'")) {
                    $result_msg = '<font color="blue">IPアドレスを追加しました(' . $ip_data[0]['address'] . ')</font><br>' . "\n";
                    $ip_mask = '';
                } else {
                    $result_msg = '<font color="red">IPアドレスの追加に失敗しました</font><br>' . "\n";
                }
        } else {
            $err_flag = true;
            $err_msg  = '空きIPアドレスが存在しません。[' . $ip_mask . ']<br>' . "\n";
        }
    } else {
        $result_msg = '<font color="red">ルータの割当先取得に失敗しました</font><br>' . "\n";
    }
} elseif ($_POST['mode']=='ip_add_man') {
    // IPアドレス割当追加（手動）
    $router_id = $_POST['router_id'];
    $ip_address = $_POST['txt_ip_new_' . $router_id];
    $ip_loc = get_router_loc($router_id);
    if ($ip_loc) {
        $ip_where = "WHERE (address = '{$ip_address}') AND (del_flag != 1)";
        $ip_data = $db->select(TABLE_ADDRESS, '*', $ip_where);
        if ($ip_data) {
            // IPアドレスが存在した
            if ($ip_data[0]['router_id'] == 0) {
                if ($ip_data[0]['lock_flag'] != 1) {
                        $params = array();
                        $params['router_id'] = $router_id;
                        $params['update_user_name'] = $_SESSION['login_name'];
                        $params['update_date'] = 'NOW()';
                        // ルータポートデータに会社情報を追加する
                        $ip_id = $ip_data[0]['id'];
                        if ($db->update(TABLE_ADDRESS, $params, "WHERE id = '{$ip_id}'")) {
                            $result_msg = '<font color="blue">IPアドレスを追加しました</font><br>' . "\n";
                            $ip_mask = '';
                        } else {
                            $result_msg = '<font color="red">IPアドレスの追加に失敗しました</font><br>' . "\n";
                        }
                } else {
                    $result_msg = '<font color="red">指定されたIPアドレスは割当禁止に設定されています</font><br>' . "\n";
                }
            } else {
                $result_msg = '<font color="red">指定されたIPアドレスは既にルータに割当てられています</font><br>' . "\n";
            }
        } else {
            $result_msg = '<font color="red">指定されたIPアドレスが存在しません</font><br>' . "\n";
        }
    } else {
        $result_msg = '<font color="red">ルータの割当先取得に失敗しました</font><br>' . "\n";
    }

} elseif ($_POST['mode']=='ip_delete') {
    // IPアドレス割当追加
    $ip_id = $_POST['ip_id'];
    $params = array();
    $params['router_id'] = '';
    $params['update_user_name'] = $_SESSION['login_name'];
    $params['update_date'] = 'NOW()';
    // IPアドレスデータのルータ情報を削除する
    if ($db->update(TABLE_ADDRESS, $params, "WHERE id = '{$ip_id}'")) {
        $result_msg = '<font color="blue">IPアドレスを解除しました</font><br>' . "\n";
    } else {
        $result_msg = '<font color="red">IPアドレスの解除に失敗しました</font><br>' . "\n";
    }
}

// 更新後の情報を取得したい為ここでデータ取得
$data = $db->select(TABLE_COMPANY, "*", $where);
$company_info = $data[0];

// 通常連絡先一覧データ取得
$day_where = "WHERE del_flag != 1 AND company_id = {$id} AND type=0";
$day_data = $db->select(TABLE_MAIL, "*", $day_where);

// 緊急連絡先一覧データ取得
$eme_where = "WHERE del_flag != 1 AND company_id = {$id} AND type=1";
$eme_data = $db->select(TABLE_MAIL, "*", $eme_where);

// 通常連絡先電話番号一覧データ取得
$day_where = "WHERE del_flag != 1 AND company_id = {$id} AND type=0";
$day_p_data = $db->select(TABLE_PHONE, "*", $day_where);

// 緊急連絡先電話番号一覧データ取得
$eme_where = "WHERE del_flag != 1 AND company_id = {$id} AND type=1";
$eme_p_data = $db->select(TABLE_PHONE, "*", $eme_where);

// ラック一覧データ取得
$rack_where = "WHERE del_flag != 1 AND company_id = {$id} ORDER BY name";
$rack_data = $db->select(TABLE_RACK, "*", $rack_where);

// ルータ一覧データ取得
$router_where = "WHERE del_flag != 1 AND company_id = {$id} ORDER BY router";
$router_data = $db->select(TABLE_ROUTER, "*", $router_where);

if ($router_data) {
    foreach ($router_data as $index => $val) {
        // IPアドレス取得
        $address_where = "WHERE del_flag != 1 AND router_id = {$val['id']} ";
        $address_data = $db->select(TABLE_ADDRESS, "id, address, router_id, ip_loc", $address_where);
        if ($address_data) {
            $router_data[$index]['address_data'] = $address_data;
        }
    }
}

// DNS一覧データ取得
$dns_where = "WHERE del_flag != 1 AND company_id = {$id} ";
$dns_data = $db->select(TABLE_DNS, "*", $dns_where);

// 契約終了日選択
$end_c_year  = substr($company_info['end_contract_day'], 0, 4);
$end_c_month = substr($company_info['end_contract_day'], 5, 2);
$end_c_day   = substr($company_info['end_contract_day'], 8, 2);
$sel_end_c_year = SelOfInt('end_c_year', $end_c_year, 2009, 2014, true);
$sel_end_c_month = SelOfInt('end_c_month', $end_c_month, 1, 12, true);
$sel_end_c_day = SelOfInt('end_c_day', $end_c_day, 1, 31, true);

?>

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=euc-jp" >
<meta http-equiv="Content-Style-Type" content="text/css" >
<link rel="stylesheet" type="text/css" href="./css/style.css">
<title>IP管理：一覧</title>
</head>
<script LANGUAGE=javascript>
<!--
    function add_mail_submit(type){
        var up_form = document.mail_form;
        up_form.mode.value='mail_add';
        up_form.mail_type.value=type;
        if (type == 1) {
            up_form.mail_name_new.value = up_form.eme_name_new.value;
            up_form.mail_address_new.value = up_form.eme_address_new.value;
        } else {
            up_form.mail_name_new.value = up_form.day_name_new.value;
            up_form.mail_address_new.value = up_form.day_address_new.value;
        }
        up_form.submit();
        return false;
    }
    function add_phone_submit(type){
        var up_form = document.phone_form;
        up_form.mode.value='phone_add';
        up_form.phone_type.value=type;
        if (type == 1) {
            up_form.phone_name_new.value = up_form.e_p_name_new.value;
            up_form.phone_new.value = up_form.e_p_new.value;
        } else {
            up_form.phone_name_new.value = up_form.d_p_name_new.value;
            up_form.phone_new.value = up_form.d_p_new.value;
        }
        up_form.submit();
        return false;
    }
    function add_rack_submit(){
        var up_form = document.rack_form;
        up_form.mode.value='rack_add';
        up_form.submit();
        return false;
    }
    function add_router_submit(){
        var up_form = document.router_form;
        up_form.mode.value='router_add';
        up_form.submit();
        return false;
    }
    function add_dns_submit(){
        var dns_form = document.dns_form;
        dns_form.mode.value='dns_add';
        dns_form.submit();
        return false;
    }
    function add_ip_submit(id){
        var up_form = document.router_form;
        if (confirm('本当にIP自動割り当てしますか？')) {
            up_form.mode.value='ip_add';
            up_form.router_id.value=id;
            up_form.submit();
            return true;
        } else {
            return false;
        }
    }
    function add_ip_manual(id){
        var up_form = document.router_form;
        up_form.mode.value='ip_add_man';
        up_form.router_id.value=id;
        up_form.submit();
        return false;
    }
    function del_mail_submit(type, id){
        var up_form = document.mail_form;
        if (confirm('本当に削除しますか？')) {
            up_form.mode.value='mail_delete';
            up_form.mail_type.value=type;
            up_form.mail_id.value=id;
            up_form.submit();
            return true;
        } else {
            return false;
        }
    }
    function del_phone_submit(type, id){
        var up_form = document.phone_form;
        if (confirm('本当に削除しますか？')) {
            up_form.mode.value='phone_delete';
            up_form.phone_type.value=type;
            up_form.phone_id.value=id;
            up_form.submit();
            return true;
        } else {
            return false;
        }
    }
    function del_rack_submit(id){
        var up_form = document.rack_form;
        if (confirm('本当に解除しますか？')) {
            up_form.mode.value='rack_delete';
            up_form.rack_id.value=id;
            up_form.submit();
            return true;
        } else {
            return false;
        }
    }
    function del_router_submit(id){
        var up_form = document.router_form;
        if (confirm('本当に解除しますか？')) {
            up_form.mode.value='router_delete';
            up_form.router_id.value=id;
            up_form.submit();
            return true;
        } else {
            return false;
        }
    }
    function del_dns_submit(id){
        var up_form = document.dns_form;
        if (confirm('本当に解除しますか？')) {
            up_form.mode.value='dns_delete';
            up_form.dns_id.value=id;
            up_form.submit();
            return true;
        } else {
            return false;
        }
    }
    function del_ip_submit(id){
        var up_form = document.router_form;
        if (confirm('本当に解除しますか？')) {
            up_form.mode.value='ip_delete';
            up_form.ip_id.value=id;
            up_form.submit();
            return true;
        } else {
            return false;
        }
    }
    function up_mail_submit(type, id){
        var up_form = document.mail_form;
        up_form.mode.value='mail_update';
        up_form.mail_type.value=type;
        up_form.mail_id.value=id;
        up_form.submit();
    }
    function up_phone_submit(type, id){
        var up_form = document.phone_form;
        up_form.mode.value='phone_update';
        up_form.phone_type.value=type;
        up_form.phone_id.value=id;
        up_form.submit();
    }
    function up_rack_submit(id){
        var up_form = document.rack_form;
        up_form.mode.value='rack_update';
        up_form.rack_id.value=id;
        up_form.submit();
    }
    function up_router_submit(id){
        var up_form = document.router_form;
        up_form.mode.value='router_update';
        up_form.router_id.value=id;
        up_form.submit();
    }
//-->
</script>
<body>

<h2>会社情報詳細</h2>
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
<?php
    echo $result_msg;
?>
<font color="red"><?php if ($err_flag) { echo $err_msg; } ?></font>
<table width="600" class="input">
<form name="company_form" action="./company_edit.php?id=<?php echo $id; ?>" method="POST">
  <caption style="text-align:left">会社情報</caption>
  <tr>
    <th width="120">
        会社コード：
    </th>
    <td>
        <input type="text" name="code" size="30" value="<?php echo $company_info['code']; ?>">
    </td>
  </tr>
  <tr>
    <th>
        会社名：
    </th>
    <td>
        <input type="text" name="name" size="30" value="<?php echo $company_info['name']; ?>">
    </td>
  </tr>
  <tr>
    <th>
        会社フリガナ：
    </th>
    <td>
        <input type="text" name="kana" size="75" value="<?php echo $company_info['kana']; ?>">
    </td>
  </tr>
  <tr>
    <th>
        エンドユーザー名：
    </th>
    <td>
        <input type="text" name="end_user_name" size="30" value="<?php echo $company_info['end_user_name']; ?>">
    </td>
  </tr>
  <tr>
    <th>
        エンドユーザーカナ：
    </th>
    <td>
        <input type="text" name="end_user_kana" size="30" value="<?php echo $company_info['end_user_kana']; ?>">
    </td>
  </tr>
  <tr>
    <th>
        会社住所：
    </th>
    <td>
        <input type="text" name="address" size="80" value="<?php echo $company_info['address']; ?>">
    </td>
  </tr>
  <tr>
    <th>
        会社電話番号：
    </th>
    <td>
        <input type="text" name="tel" size="20" value="<?php echo $company_info['tel']; ?>">
    </td>
  </tr>
  <tr>
    <th>
        ビットアイル担当者：
    </th>
    <td>
        <input type="text" name="sales_name" size="30" value="<?php echo $company_info['sales_name']; ?>">
    </td>
  </tr>
  <tr>
    <th>
        契約終了日：
    </th>
    <td>
        <?php echo $sel_end_c_year; ?>年<?php echo $sel_end_c_month; ?>月<?php echo $sel_end_c_day; ?>日
    </td>
  </tr>
  <tr>
    <th>
        備考：
    </th>
    <td>
        <textarea name="comment" cols="72" rows="5" style="font-size:9pt;"><?php echo $company_info['comment']; ?></textarea>
    </td>
  </tr>
  <tr>
    <td colspan="2" align="center">
<?php if (check_auth('update')) { ?>
        <input type="submit" name="sub_up_company" value="更新">
<?php } ?>
    </td>
  </tr>
</form>
</table>
<!-- メイン終了 -->

<!-- ラック一覧 -->
<form name="rack_form" action="" method="POST">
<table width="700" class="list">
<caption style="text-align:left">割当ラック一覧</caption>
  <tr>
    <th>ロケーション</th>
    <th>フロア</th>
    <th>ラック名</th>
    <th>サイズ</th>
    <th>備考</th>
    <th>　</th>
  </tr>
<?php 
if ($rack_data) {
    foreach ($rack_data as $row) { 
?>
  <tr>
    <td><?php echo $array_dc_location[$row['dc_loc']]; ?></td>
    <td><?php echo $row['floor']; ?>F</td>
    <td><a href="./rack_edit.php?id=<?php echo ($row['id']); ?>"><?php echo $row['name']; ?></a></td>
    <td><?php echo $array_rack_size[$row['size']]; ?></td>
    <td><input type="text" name="txt_rack_comment_<?php echo $row['id']; ?>" value="<?php echo $row['comment']; ?>" size="30"></td>
    <td align="center">
<?php if (check_auth('update')) { ?>
        <input type="button" name="sub_rack_update" value="ラック備考更新" onclick="up_rack_submit( <?php echo $row['id']; ?> );">
        <input type="button" name="sub_rack_delete" value="割当解除" onclick="del_rack_submit( <?php echo $row['id']; ?> );">
<?php } ?>
    </td>
  </tr>
<?php
    }
}
?>
<?php if (check_auth('update')) { ?>
  <tr>
    <td></td>
    <td></td>
    <td><input type="text" size="20" name="txt_rack_name_new" value="<?php echo $rack_name; ?>"></td>
    <td></td>
    <td></td>
    <td align="center"><input type="button" name="sub_rack_add" value="割当追加" onclick="add_rack_submit();"></td>
  </tr>
<?php } ?>
  <input type="hidden" name="mode" value="">
  <input type="hidden" name="rack_id" value="">
</form>
</table>
<!-- ラック一覧終了 -->

<!-- ルータ一覧 -->
<form name="router_form" action="" method="POST">
<table width="800" class="list">
<caption style="text-align:left">割当IP一覧</caption>
  <tr>
    <th><br></th>
    <th>ルータ</th>
    <th>ポート</th>
    <th>割当先</th>
    <th>サービス品目</th>
    <th>パッチパネル</th>
    <th>備考</th>
    <th>　</th>
  </tr>
<?php 
if ($router_data) {
    foreach ($router_data as $row) {
        $count = count($row['address_data']) + 2;
?>
  <tr>
    <td style="text-align:center;" id="<?php echo $row['router']; ?>-<?php echo substr($row['port'], -2); ?>">
      <a href="https://xxx.xxx.xxx.xxx/eden/user/<?php echo $row['router']; ?>#<?php echo substr($row['port'], -2); ?>" target="_blank">Eden</a><br>
      <a href="http://xxx.xxx.xxx.xxx/cgi-bin/cvsweb.cgi/router-config/<?php echo $row['router']; ?>" target="_blank">cfg</a>
    </td>
    <td><a href="./router_list.php?router=<?php echo urlencode($row['router']); ?>"><?php echo $row['router']; ?></a></td>
    <td><a href="./router_list.php?router=<?php echo urlencode($row['router']); ?>"><?php echo $row['port']; ?></a></td>
    <td><?php echo $array_ip_location[$row['ip_loc']]; ?></td>
    <td><input type="text" size="15" name="txt_service_<?php echo $row['id']; ?>" value="<?php echo $row['service']; ?>"></td>
    <td><input type="text" size="22" name="txt_panel_<?php echo $row['id']; ?>" value="<?php echo $row['panel']; ?>"></td>
    <td><input type="text" size="24" name="txt_comment_<?php echo $row['id']; ?>" value="<?php echo $row['comment']; ?>"></td>
    <td align="center">
<?php if (check_auth('update')) { ?>
        <input type="button" name="sub_router_update" value="ポート更新" onclick="up_router_submit( <?php echo $row['id']; ?> );">
        <input type="button" name="sub_router_delete" value="ポート解除" onclick="del_router_submit( <?php echo $row['id']; ?> );">
<?php } ?>
    </td>
  </tr>
  <tr>
    <td colspan="3" rowspan="<?php echo $count ?>">
<?php
$address_data = $row['address_data'];
if ($address_data) {
    foreach ($address_data as $ip_row) { 
?>
    <td><?php echo $array_ip_location[$ip_row['ip_loc']]; ?></td>
    <td colspan="3"><a href="./ip_edit.php?id=<?php echo urlencode($ip_row['id']); ?>"><?php echo $ip_row['address']; ?></a></td>
    <td align="center">
<?php if (check_auth('update')) { ?>
        <input type="button" name="sub_ip_delete" value="割当解除" onclick="del_ip_submit( <?php echo $ip_row['id']; ?> );">
<?php } ?>
    </td>
  </tr>
  <tr>
<?php
    }
}
?>
    <td></td>
    <td colspan="3">
        サイズ
        <select name="add_mask_<?php echo $row['id']; ?>">
        <option value="24">24</option>
        <option value="25" selected>25</option>
        <option value="26">26</option>
        <option value="27">27</option>
        <option value="28">28</option>
        <option value="29">29</option>
        <option value="30">30</option>
        </select>
    </td>
    <td align="center">
<?php if (check_auth('update')) { ?>
        <input type="button" name="sub_ip_add" value="空きIP自動割当" onclick="add_ip_submit(<?php echo $row['id']; ?>);">
<?php } ?>
    </td>
  </tr>
  <tr>
    <td></td>
    <td colspan="3"><input type="text" size="20" name="txt_ip_new_<?php echo $row['id']; ?>" value=""></td>
    <td align="center">
<?php if (check_auth('update')) { ?>
        <input type="button" name="man_ip_add" value="指定IP割当" onclick="add_ip_manual(<?php echo $row['id']; ?>);">
<?php } ?>
    </td>
  </tr>
<?php
    }
}
?>
<?php if (check_auth('update')) { ?>
  <tr>
    <td></td>
    <td><input type="text" name="txt_router_new" size="20" value="<?php echo $router; ?>"></td>
    <td><input type="text" name="txt_port_new" size="8" value="<?php echo $port; ?>"></td>
    <td></td>
    <td colspan="3"></td>
    <td align="center"><input type="button" name="sub_router_add" value="ポート追加" onclick="add_router_submit();"></td>
  </tr>
<?php } ?>
  <input type="hidden" name="mode" value="">
  <input type="hidden" name="router_id" value="">
  <input type="hidden" name="ip_id" value="">
</form>
</table>
<!-- ルータ一覧終了 -->

<!-- DNS一覧 -->
<form name="dns_form" action="" method="POST">
<table width="800" class="input">
<caption style="text-align:left">DNS一覧</caption>
  <tr>
    <th>ドメイン名</th>
    <th>利用</th>
    <th>プライマリ</th>
    <th>プライマリFQDN</th>
    <th>セカンダリ</th>
    <th>備考</th>
    <th>　</th>
  </tr>
<?php 
if ($dns_data) {
    foreach ($dns_data as $row) {
?>
  <tr>
    <td>
        <?php echo $row['domain'] ?>
    </td>
    <td>
        <?php echo $array_use_kind[$row['use_kind']] ?>
    </td>
    <td>
        <?php echo $row['primary'] ?>
    </td>
    <td>
        <?php echo $row['primary_fqdn'] ?>
    </td>
    <td>
        <?php echo $row['secondary'] ?>
    </td>
    <td>
        <?php echo $row['comment'] ?>
    </td>
    <td align="center">
<?php if (check_auth('update')) { ?>
        <input type="button" name="sub_dns_delete" value="削除" onclick="del_dns_submit( <?php echo $row['id']; ?> );">
<?php } ?>
    </td>
  </tr>
<?php
    }
}
?>
<?php if (check_auth('update')) { ?>
  <tr>
    <td>
        <input type="text" name="dns_new" size="20" value="">
    </td>
    <td>
        <select name="sel_kind_new">
<?php 
        foreach ($array_use_kind as $key => $kind) {
            $selected = ($row['use_kind'] == $key)? "selected": "";
            echo "        <option value=\"{$key}\" {$selected}>{$kind}</option>\n";
        }
?>
        </select>
    </td>
    <td>
        <input type="text" name="primary_new" size="20" value="">
    </td>
    <td>
        <input type="text" name="primary_fqdn_new" size="20" value="">
    </td>
    <td>
        <input type="text" name="secondary_new" size="20" value="">
    </td>
    <td>
        <input type="text" name="comment_new" size="30" value="">
    </td>
    <td align="center"><input type="button" name="sub_dns_add" value="追加" onclick="add_dns_submit();"></td>
  </tr>
<?php } ?>
  <input type="hidden" name="mode" value="">
  <input type="hidden" name="dns_id" value="">
</form>
</table>
<!-- DNS一覧終了 -->

<!-- 連絡先一覧 -->
<form name="mail_form" action="" method="POST">
<table width="600" class="input">
<caption style="text-align:left">連絡先一覧</caption>
  <tr>
    <th colspan="2">
        通常連絡先メールアドレス
    </th>
  </tr>
<?php 
if ($day_data) {
    foreach ($day_data as $row) {
?>
  <tr>
    <td>
        <input type="text" name="mail_name_<?php echo $row['id']; ?>" size="10" value="<?php echo $row['name'] ?>">
        <input type="text" name="mail_address_<?php echo $row['id']; ?>" size="30" value="<?php echo $row['address'] ?>">
    </td>
    <td align="center">
<?php if (check_auth('update')) { ?>
        <input type="button" name="sub_day_update" value="更新" onclick="up_mail_submit( 0, <?php echo $row['id']; ?> );">
        <input type="button" name="sub_day_delete" value="削除" onclick="del_mail_submit( 0, <?php echo $row['id']; ?> );">
<?php } ?>
    </td>
  </tr>
<?php
    }
}
?>
<?php if (check_auth('update')) { ?>
  <tr>
    <td>
        名前<input type="text" name="day_name_new" size="10" value="">
        メール<input type="text" name="day_address_new" size="30" value="">
    </td>
    <td align="center"><input type="button" name="sub_day_add" value="追加" onclick="add_mail_submit(0);"></td>
  </tr>
<?php } ?>
  <tr>
    <th colspan="2">
        緊急連絡先メールアドレス
    </th>
  </tr>
<?php 
if ($eme_data) {
    foreach ($eme_data as $row) {
?>
  <tr>
    <td>
        <input type="text" name="mail_name_<?php echo $row['id']; ?>" size="10" value="<?php echo $row['name'] ?>">
        <input type="text" name="mail_address_<?php echo $row['id']; ?>" size="30" value="<?php echo $row['address'] ?>">
    </td>
    <td align="center">
<?php if (check_auth('update')) { ?>
        <input type="button" name="sub_eme_update" value="更新" onclick="up_mail_submit( 1, <?php echo $row['id']; ?> );">
        <input type="button" name="sub_eme_delete" value="削除" onclick="del_mail_submit( 1, <?php echo $row['id']; ?> );">
<?php } ?>
    </td>
  </tr>
<?php
    }
}
?>
<?php if (check_auth('update')) { ?>
  <tr>
    <td>
        名前<input type="text" name="eme_name_new" size="10" value="">
        メール<input type="text" name="eme_address_new" size="30" value="">
    </td>
    <td align="center"><input type="button" name="sub_eme_add" value="追加" onclick="add_mail_submit(1);"></td>
  </tr>
<?php } ?>
  <input type="hidden" name="mode" value="">
  <input type="hidden" name="mail_id" value="">
  <input type="hidden" name="mail_type" value="">
  <input type="hidden" name="mail_name_new" value="">
  <input type="hidden" name="mail_address_new" value="">
</form>
</table>
<!-- 連絡先一覧終了 -->

<!-- 連絡先電話番号一覧 -->
<form name="phone_form" action="" method="POST">
<table width="600" class="input">
  <tr>
    <th colspan="2">
        通常連絡先電話番号
    </th>
  </tr>
<?php 
if ($day_p_data) {
    foreach ($day_p_data as $row) {
?>
  <tr>
    <td>
        <input type="text" name="phone_name_<?php echo $row['id']; ?>" size="10" value="<?php echo $row['name'] ?>">
        <input type="text" name="phone_<?php echo $row['id']; ?>" size="30" value="<?php echo $row['phone'] ?>">
    </td>
    <td align="center">
<?php if (check_auth('update')) { ?>
        <input type="button" name="sub_day_update" value="更新" onclick="up_phone_submit( 0, <?php echo $row['id']; ?> );">
        <input type="button" name="sub_day_delete" value="削除" onclick="del_phone_submit( 0, <?php echo $row['id']; ?> );">
<?php } ?>
    </td>
  </tr>
<?php
    }
}
?>
<?php if (check_auth('update')) { ?>
  <tr>
    <td>
        名前<input type="text" name="d_p_name_new" size="10" value="">
        電話番号<input type="text" name="d_p_new" size="30" value="">
    </td>
    <td align="center"><input type="button" name="sub_d_p_add" value="追加" onclick="add_phone_submit(0);"></td>
  </tr>
<?php } ?>
  <tr>
    <th colspan="2">
        緊急連絡先電話番号
    </th>
  </tr>
<?php 
if ($eme_p_data) {
    foreach ($eme_p_data as $row) {
?>
  <tr>
    <td>
        <input type="text" name="phone_name_<?php echo $row['id']; ?>" size="10" value="<?php echo $row['name'] ?>">
        <input type="text" name="phone_<?php echo $row['id']; ?>" size="30" value="<?php echo $row['phone'] ?>">
    </td>
    <td align="center">
<?php if (check_auth('update')) { ?>
        <input type="button" name="sub_eme_update" value="更新" onclick="up_phone_submit( 1, <?php echo $row['id']; ?> );">
        <input type="button" name="sub_eme_delete" value="削除" onclick="del_phone_submit( 1, <?php echo $row['id']; ?> );">
<?php } ?>
    </td>
  </tr>
<?php
    }
}
?>
<?php if (check_auth('update')) { ?>
  <tr>
    <td>
        名前<input type="text" name="e_p_name_new" size="10" value="<?php echo $company_info['e_p_name_new'] ?>">
        電話番号<input type="text" name="e_p_new" size="30" value="<?php echo $company_info['e_p_new'] ?>">
    </td>
    <td align="center"><input type="button" name="sub_e_p_add" value="追加" onclick="add_phone_submit(1);"></td>
  </tr>
<?php } ?>
  <input type="hidden" name="mode" value="">
  <input type="hidden" name="phone_id" value="">
  <input type="hidden" name="phone_type" value="">
  <input type="hidden" name="phone_name_new" value="">
  <input type="hidden" name="phone_new" value="">
</form>
</table>
<!-- 連絡先電話番号一覧終了 -->

</td>
</tr>
</table>
<!-- 全体終了 -->

<br>
<hr>
<?php echo FOOTER_STR; ?>

</body>
</html>
