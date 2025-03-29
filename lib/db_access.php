<?php header("Content-Type: text/html; charset=utf-8"); ?>
<?php

//呼び出し元でincludeする
//include_once('./lib/config.php');
//include_once('./lib/function.php');

class db_access {
    var $dbh;

    // コンストラクタ
    function db_access()
    {
        $this->dbh = mysql_connect(SERVER_NAME, DB_USER, DB_PASS);
        if (!$this->dbh) {
            die('Could not connect: ' . mysql_error());
        }

        // 文字コードをUTF-8に設定
        mysql_query("SET NAMES 'utf8'", $this->dbh);

        if (!mysql_select_db(DB_NAME, $this->dbh)) {
            die('Could not select database: ' . mysql_error());
        }
    }

    // 指定したテーブルからデータを取得する
    function select($table, $field, $other)
    {
        $query = "SELECT {$field} FROM {$table} {$other}";
//        echo $query;    // debug

        $result = mysql_query($query);
        if ($result) {
            while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
                $data[] = $row;
            }
            return $data;
        } else {
            output_error_log(mysql_error());
            return false;
        }
    }

    // 指定したテーブルに行を挿入する
    // $tableにテーブル名を、$paramsに配列でデータを指定する
    // 例）$params['name'] = '名前';
    function insert($table, $params)
    {
        if (!is_array($params)) {
            // パラメータが指定されてない時
            output_log("TABLE:{$table} 挿入するパラメータがありません");
            return false;
        }
        foreach ($params as $key => $val){
            if ($fields != '') {
                $fields .= ', ';
            }
            if ($values != '') {
                $values .= ', ';
            }
            $fields .= "`" . $key . "`";
            if ($val == 'NOW()') {
                // NOWのときは括らない
                $values .= $val;
            } else {
                $values .= "'" . $val . "'";
            }
        }
        $query = "INSERT INTO {$table} ({$fields}) VALUES ({$values})";
//        echo $query;    // debug

        if (mysql_query($query)) {
            return true;
        } else {
            output_error_log(mysql_error());
            return false;
        }
    }

    // 指定したテーブルの修正をする
    // $tableにテーブル名を、$paramsに配列でデータを指定する
    // 例）$params['name'] = '名前';
    function update($table, $params, $where)
    {
        if (!is_array($params)) {
            // パラメータが指定されてない時
            output_log("TABLE:{$table} 更新するパラメータがありません");
            return false;
        }
        if ( $where == '' ) {
            output_log("TABLE:{$table} 更新時には必ずWHERE句を使用してください");
            return false;
        }
        foreach ($params as $key => $val){
            if ($update_data != '') {
                $update_data .= ', ';
            }
            if ($val == 'NOW()') {
                // NOWのときは括らない
                $update_data .= "`" . $key . "`" .  " = NOW()";
            } else {
                $update_data .= "`" . $key . "`" .  " = '" . $val . "'";
            }
        }
        $query = "UPDATE {$table} SET {$update_data} $where";
//        echo $query;    // debug

        if (mysql_query($query)) {
            return true;
        } else {
            output_error_log(mysql_error());
            return false;
        }
    }

    // 指定したテーブルからデータを取得する
    function delete($table, $other)
    {
        $query = "UPDATE {$table} SET del_flag = 1 {$other}";
//        echo $query;    // debug

        $result = mysql_query($query);
        if ($result) {
            return true;
        } else {
            output_error_log(mysql_error());
            return false;
        }
    }

    // 指定した条件の件数を取得する
    function sel_count($table, $field, $other)
    {
        $query = "SELECT {$field} FROM {$table} {$other}";
//        echo $query;    // debug

        $result = mysql_query($query);
        if ($result) {
            return mysql_num_rows($result);
        } else {
            output_error_log(mysql_error());
            return false;
        }

    }


}   // class db_access

?>