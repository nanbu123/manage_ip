<?php header("Content-Type: text/html; charset=utf-8"); ?>
<?php

// 指定したテーブル内の構成を表示
// $table_name：テーブル名
// $link：DB接続
function show_columns($table_name, $link)
{
    $sql = 'DESCRIBE ' . $table_name;
    $result = mysql_query($sql, $link);

    $array = array('Field', 'Type', 'Null', 'Key', 'Default', 'Extra');

    echo '<table><tr>';
    for ($i=0; $i<count($array);$i++){
        echo '<th>' . $array[$i] . '</th>';
    }
    echo '</tr>' . "\n";
    while ($row=mysql_fetch_assoc($result)){
        echo '<tr>';
        for ($i=0; $i<count($array);$i++){
            echo '<td>' . $row[$array[$i]] . '</td>';
       }
        echo '</tr>' . "\n";
    }
    echo '</table>';

}

// ログインチェック
function check_login_pass($name, $pass, &$err_msg)
{
    if ($name == "") {
        // ユーザ名が空
        $err_msg = '<font color="red">ユーザー名を入力してください</font>';
        return false;
    } else {

        // ログインまでは画面にエラーをだす これ以降はエラーログに出力
        $link = mysql_connect(SERVER_NAME, DB_USER, DB_PASS)
            or die('Could not connect: ' . mysql_error());
        mysql_query("SET NAMES 'utf8'"); // ここに追加
        mysql_select_db(DB_NAME) or die('Could not select database');
        $pass_md5 = md5($pass);
        $where = " WHERE name='{$name}' AND password='{$pass_md5}'";
        $query = "SELECT * FROM " . TABLE_USER . $where;
        $result = mysql_query($query) or die('Query failed: ' . mysql_error());
        
        // ユーザー名・パスワードが正しいか
        if ($result) {
            $row = mysql_fetch_array($result);
            // セッションに記録
            if ($row) {
                session_start();
                $_SESSION['login'] = '1';
                $_SESSION['level'] = $row['level'];
                $_SESSION['login_name'] = $row['name'];
                return true;
            } else {
                $err_msg = '<font color="red">ユーザー名またはパスワードが違います</font>';
                return false;
            }
        } else {
            $err_msg = '<font color="red">ユーザー名またはパスワードが違います</font>';
            return false;
        }
    }

}

// ログインされているかチェック
function check_session_login()
{
    session_start();
    if ($_SESSION['login'] == '1') {
        return true;
    } else {
        header('Location: ./login.php');
        return false;
    }
}

// IPアドレスとして正しいかチェック
function check_ip_address($ip_address, &$err_msg)
{
    $pat = '/^([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\/{0,1}([0-9]{2}){0,1}$/';
    if (preg_match($pat, $ip_address, &$matches)) {
        foreach ($matches as $index => $val){
            if ($index==0) {
                // 処理なし
            } elseif ($index==5) {
                // マスクビットは24~30
                if (($val < 8) || ($val > 30)) {
                    $err_flag_sub = true;
                    $err_msg_ip   .= '/' . '<strong>' . $val . '</strong>';
                    $err_msg_sub  .= 'マスクビットは24～30を指定してください<br>';
                } else {
                    $err_msg_ip .= '/' . $val;
                }
            } else {
                // アドレス(1番目から4番目の数字)は0~255
                if ($index > 1) {
                    $err_msg_ip .= '.';
                }
                if (($val < 0) || ($val > 255)) {
                    $err_flag_sub = true;
                    $err_msg_ip   .= '<strong>' . $val . '</strong>';
                    $err_msg_sub  .= 'アドレスは0～255を指定してください<br>';
                } else {
                    $err_msg_ip .= $val;
                }
            }
        }
        if ($err_flag_sub) {
            $err_msg .= $err_msg_ip . '<br>' . "\n";
            $err_msg .= $err_msg_sub;
            return false;
        }
    } else {
        $err_msg  = 'IPアドレスが正しくありません<br>' . "\n";
        return false;
    }

    return true;
}

// IPアドレスからスタート・エンド・マスクを返す
// $ip_info['start'],$ip_info['end'],$ip_info['mask']
// アドレスが正しいかはチェックしないので注意
function get_ip_info($ip_address)
{
    // 最後の数字とマスクを取り出す
    $pat = '/^([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)([0-9]{1,3})\/{0,1}([0-9]{2}){0,1}$/';
    if (preg_match($pat, $ip_address, &$matches)) {
        $ip_info['start'] = $matches[1] . $matches[2];
        if (count($matches) == 3) {
            // maskがないときは開始と終了は同じ
            $ip_info['end']  = $ip_info['start'];
        } else {
            $mask = (1 << (32 - $matches[3])) - 1;
            $ip_info['end']  = long2ip(ip_to_long($ip_info['start']) + $mask);
//            $ip_info['end']  = $matches[1] . ($matches[2] + $mask);
        }
    }
    return $ip_info;
}

// IPアドレスをDBに登録する
function insert_ip_address($ip_address, $ip_loc)
{
    $db = new db_access();

    $params = array();
    $params['address'] = $ip_address;
    $ip_info = get_ip_info($ip_address);
    $params['ip_start'] = ip_to_long($ip_info['start']);
    $params['ip_end']   = ip_to_long($ip_info['end']);
    $params['ip_loc']   = $ip_loc;
    session_start();
    $params['add_user_name'] = $_SESSION['login_name'];
    $params['update_user_name'] = $_SESSION['login_name'];
    $params['add_date'] = 'NOW()';
    $params['update_date'] = 'NOW()';

//    echo "IPアドレス：".$ip_info['start'].ip_to_long($ip_info['start']);

    return $db->insert(TABLE_ADDRESS, $params);

}

// IPアドレスが既に存在しているかどうか
function exists_ip_address($ip_address)
{
    if ($ip_address) {
        $db = new db_access();

        $where = "WHERE (del_flag != 1)";
        $ip_info = get_ip_info($ip_address);
        $search_s = ip_to_long($ip_info['start']);
        $search_e = ip_to_long($ip_info['end']);
        if ($ip_info['start'] != '') {
            $where .= " AND (";
            $where .= " (ip_start <= {$search_s} AND ip_end   >= {$search_s} )";
            $where .= " OR (ip_start <= {$search_e} AND ip_end   >= {$search_e} )";
            $where .= " OR (ip_start >= {$search_s} AND ip_start <= {$search_e} )";
            $where .= " OR (ip_end   >= {$search_s} AND ip_end   <= {$search_e} )";
            $where .= " )";
        }
        return $db->select(TABLE_ADDRESS, "id", $where);

    } else {
        return false;
    }
}

// ラックをDBに登録する
function insert_rack($rack, $size)
{
    $db = new db_access();

    $params = array();
    $params['name'] = $rack;
    // ローケーションとフロアを判定
    $pat = "/^([0-9]{0,1})([0-9]{1})[A-Z].*/";
    if (preg_match($pat, $rack, $matches)) {
        if ($matches[1] == "") {
            $params['dc_loc'] = 1;
        } else {
            $params['dc_loc'] = $matches[1];
        }
        $params['floor'] = $matches[2];
    }
    $params['size'] = $size;
    session_start();
    $params['add_user_name'] = $_SESSION['login_name'];
    $params['update_user_name'] = $_SESSION['login_name'];
    $params['add_date'] = 'NOW()';
    $params['update_date'] = 'NOW()';

    return $db->insert(TABLE_RACK, $params);

}

// ラックが既に存在しているかどうか
function exists_rack($rack)
{
    if ($rack) {
        $db = new db_access();

        $where = "WHERE (del_flag != 1)";
        $where .= " AND ( name = '{$rack}' )";

        return $db->select(TABLE_RACK, "id", $where);

    } else {
        return false;
    }
}

// パッチパネルをDBに登録する
function insert_patch($patch, $rack_id)
{
    $db = new db_access();

    $params = array();
    $params['panel_number'] = $patch;
    $params['rack_id'] = $rack_id;
    session_start();
    $params['add_user_name'] = $_SESSION['login_name'];
    $params['update_user_name'] = $_SESSION['login_name'];
    $params['add_date'] = 'NOW()';
    $params['update_date'] = 'NOW()';

    return $db->insert(TABLE_PATCH, $params);

}

// パッチパネルが既に存在しているかどうか
function exists_patch($patch)
{
    if ($patch) {
        $db = new db_access();

        $where = "WHERE (del_flag != 1)";
        $where .= " AND ( panel_number = '{$patch}' )";

        return $db->select(TABLE_PATCH, "id", $where);

    } else {
        return false;
    }
}

// ブレーカーをDBに登録する
function insert_breaker($dc_loc, $floor, $pdu, $bunden, $mccb)
{
    $db = new db_access();


    if ($dc_loc == '1') {
        
    } else {
    }
    $params = array();
    $params['dc_loc']  = $dc_loc;
    $params['floor']   = $floor;
    $params['pdu_no']  = $pdu;
    $params['bunden']  = $bunden;
    $params['mccb_no'] = $mccb;
    session_start();
    $params['add_user_name'] = $_SESSION['login_name'];
    $params['update_user_name'] = $_SESSION['login_name'];
    $params['add_date'] = 'NOW()';
    $params['update_date'] = 'NOW()';

    return $db->insert(TABLE_BREAKER, $params);

}

// ブレーカーが既に存在しているかどうか
function exists_breaker($pdu, $bunden, $mccb)
{
    if ($pdu) {
        $db = new db_access();

        $where = "WHERE (del_flag != 1)";
        $where .= " AND ( pdu_no = '{$pdu}' ) AND ( bunden = '{$bunden}' ) AND ( mccb_no = '{$mccb}' )";

        return $db->select(TABLE_BREAKER, "id", $where);

    } else {
        return false;
    }
}

// ルータポートをDBに登録する
function insert_router($router, $port, $ip_loc)
{

    $db = new db_access();

    $params = array();
    $params['router'] = $router;
    $params['port'] = $port;
    $params['ip_loc'] = $ip_loc;
    session_start();
    $params['add_user_name'] = $_SESSION['login_name'];
    $params['update_user_name'] = $_SESSION['login_name'];
    $params['add_date'] = 'NOW()';
    $params['update_date'] = 'NOW()';

    return $db->insert(TABLE_ROUTER, $params);


}


// ルータポートが既に存在しているかどうか
function exists_router($router, $port)
{
    if ($router && $port) {
        $db = new db_access();

        $where = "WHERE (del_flag != 1)";
        $where .= " AND ( router = '{$router}' )";
        $where .= " AND ( port = '{$port}' )";

        return $db->select(TABLE_ROUTER, "id", $where);

    } else {
        return false;
    }
}

// ルータポートの割当先を取得
function get_router_loc($router_id)
{
    if ($router_id) {
        $db = new db_access();

        $where = "WHERE (del_flag != 1)";
        $where .= " AND ( id = '{$router_id}' )";
        $r_data = $db->select(TABLE_ROUTER, "ip_loc", $where);
        if ($r_data) {
            return $r_data[0]['ip_loc'];
        } else {
            return false;
        }
    } else {
        return false;
    }
}

// ユーザーが既に存在しているかどうか
function exists_user($login_name)
{
    if ($login_name) {
        $db = new db_access();

        $where = "WHERE (del_flag != 1)";
        $where .= " AND ( name = '{$login_name}' )";

        return $db->select(TABLE_USER, "id", $where);

    } else {
        return false;
    }
}

// エラーを出力
function output_error_log($message)
{
    $log = '[' . date('Y/m/d H:i:s') . '] [error] [' . __FILE__ . '] '. $message . "\n";
    if (!file_exists(ERROR_FILE)) {
        $handle = fopen(ERROR_FILE, 'a');
        fwrite($handle, "");
        fclose($handle);
    }
    error_log($log, 3, ERROR_FILE);
}

// ページング
// 最初の3ページ 現在のページの前後 最後の3ページ のみ表示
// [1] [2] [3] ...[9][10][11]... [27] [28] [29]
function get_paging($total, $cur_page, $link='ip_list.php', $get_param='')
{
    if ($total == 0) {
        $max_page = 1;
    } else if ($total % PAGE_ROW == 0) {
        // 割り切れるときは+1しない
        $max_page = floor($total / PAGE_ROW);
    } else {
        $max_page = floor($total / PAGE_ROW) + 1;
    }

    if ($get_param != '') {
        $get_param = '&' . $get_param;
    }
    $paging = '';
    $period_flag = false;   // trueのとき「...」を書いた 「...」を連続でかかない為のフラグ 
    for ($i=1; $i<=$max_page; $i++){
        // 最初の3ページ 最後の3ページ 現在のページの前後 のみ表示
        if (($i <= 3) || ($i > $max_page-3) || (($i <= $cur_page + 1) && ($i >= $cur_page - 1)) ) {
            if ($i == $cur_page) {
                $paging .= '[<font color="#9999cc">' . $i . '</font>] ';
            } else {
                $paging .= '[' . '<a href="./' . $link . '?page=' . $i . $get_param . '">' . $i . '</a>' . '] ';
            }
            $period_flag = false;
        } else {
            if (!$period_flag) {
                $paging .= '...';
                $period_flag = true;
            }
        }
    }
    if ($cur_page > 1) {
        $paging = '<a href="./' . $link . '?page=' . ($cur_page-1) . $get_param . '">＜前へ' . '</a>　' . $paging;
    }
    if ($cur_page < $max_page) {
        $paging = $paging . '　<a href="./' . $link . '?page=' . ($cur_page+1) . $get_param . '">次へ＞' . '</a>';
    }
    return $paging;

}

// '192.168.1.0' => '3232235776'
function ip_to_long($ip_address)
{
    if (ip2long($ip_address)) {
        return sprintf("%u", ip2long($ip_address));
    } else {
        return '';
    }
}

// パネル名(34A101-01)からラックIDを取得
function get_rack_id($panel_number)
{
    if ($panel_number != '') {
        $db = new db_access();

        $pat = '/^(.*)\-/';
        if (preg_match($pat, $panel_number, &$matches)) {
            $rack = $matches[1];

            $where = "WHERE (del_flag != 1)";
            $where .= " AND (";
            $where .= " name like '{$rack}'";
            $where .= " )";

            $rack_data = $db->select(TABLE_RACK, "id", $where);
        
            if ($rack_data) {
                return $rack_data[0]['id'];
            } else {
                return false;
            }
        }

    } else {
        return false;
    }
}

// 配列から選択BOXを作成
// $sel_name: 選択BOXの名前
// $sel_key: 選択するID
// $array: 選択肢配列
// $null: null選択肢追加
// $add: onChangeやdisabled等追加したい文字列
function SelOfArray($sel_name, $sel_key, $array, $null=false, $add="")
{
    $sel = "<select name=\"{$sel_name}\" {$event}>\n";
    if ($null) {
        $sel .= "<option value=\"\"></option>\n";
    }
    if ($array) {
        foreach ($array as $key => $val) {
            $selected = ($key == $sel_key)? "selected": "";
            $sel .= "<option value=\"{$key}\" {$selected}>{$val}</option>\n";
        }
        $sel .= "</select>\n";
    }
    return $sel;
}

// 配列から選択BOXを作成
// $sel_name: 選択BOXの名前
// $sel_key: 選択する値
// $start: 開始値
// $end:   終了値
// $null: null選択肢追加
// $add: onChangeやdisabled等追加したい文字列
function SelOfInt($sel_name, $sel_key, $start, $end, $null=false, $add="")
{

    $array = array();
    foreach (range($start, $end) as $int) {
        $array[$int] = $int;
    }

    $sel = "<select name=\"{$sel_name}\" {$event}>\n";
    if ($null) {
        $sel .= "<option value=\"\"></option>\n";
    }
    if ($array) {
        foreach ($array as $key => $val) {
            $selected = ($key == $sel_key)? "selected": "";
            $sel .= "<option value=\"{$key}\" {$selected}>{$val}</option>\n";
        }
        $sel .= "</select>\n";
    }
    return $sel;
}


// 機能名またはモードとログインユーザーの権限を比較し、
// 使用可能かを返す
// $mode: 機能名 or モード(update,delete,add)
// true: 使用可能 false: 使用不可
function check_auth($mode)
{
    $res = false;
    if ($mode) {
        switch ($mode) {
            case ('delete'):
                if ($_SESSION['level'] < 4) {
                    $res = true;
                }
                break;
            case ('update'):
                if ($_SESSION['level'] < 9) {
                    $res = true;
                }
                break;
            case ('add'):
                // 会社登録のみ5でも可能（個別に処理する）
                if ($_SESSION['level'] < 5) {
                    $res = true;
                }
                break;
            case ('special_admin') :
                if ($_SESSION['level'] == 1) {
                    $res = true;
                }
                break;
            case ('user_list') :
                if ($_SESSION['level'] < 9) {
                    $res = true;
                }
                break;
        }
    }
    return $res;
}

// 配列の名前からIDを返す
function get_array_id($array, $name)
{
    if ($array) {
        foreach ($array as $key => $value) {
            if ( $value === $name) {
                return $key;
            }
        }
    }
    return 0;
}

// ファイルをSJISからEUCに変換する
function convertSJIS2EUC($org_path) {
    $tmp_path = tempnam(dirname($org_path), 'temp');
    
    $org_fp = fopen($org_path, 'r');
    $tmp_fp = fopen($tmp_path, 'w');
    while (!feof($org_fp)) {
        fwrite($rTemp, mb_convert_encoding(fgets($org_fp, 4096), 'euc', 'sjis'));
    }
    fclose($org_fp);
    fclose($tmp_fp);
    
    copy($tmp_path, $org_path);
    unlink($tmp_path);
}

?>