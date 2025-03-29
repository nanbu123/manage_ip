<?php header("Content-Type: text/html; charset=utf-8"); ?>
<?php

    // DBの設定読み込み
    include_once('../lib/config.php');
    include_once('../lib/function.php');

    // DB接続
    $link = mysql_connect(SERVER_NAME, DB_USER, DB_PASS)
        or die('Could not connect: ' . mysql_error());

    mysql_select_db(DB_NAME) or die('Could not select database');

//    // 現在のテーブル削除
//    $sql = 'DROP TABLE ' . TABLE_BREAKER;
//    mysql_query($sql, $link);

    // 新テーブル作成
    $sql = 'CREATE TABLE IF NOT EXISTS ' . TABLE_BREAKER . 
        ' (id int unsigned not null auto_increment primary key,' . 
        ' pdu_no     varchar(40) not null,' .
        ' bunden     varchar(10) not null,' .
        ' mccb_no    varchar(10) not null,' .
        ' dc_loc     tinyint(3) unsigned not null default 0,' .
        ' floor      varchar(10) not null,' .
        ' rack_id    int unsigned not null,' .
        ' status     tinyint(2) unsigned not null,' .
        ' ampere     int unsigned not null,' .
        ' plug_type  varchar(40) not null,' .
        ' plug_count tinyint(2) unsigned not null,' .
        ' comment    text not null,' .
        ' add_user_name    varchar(20) not null,' .
        ' update_user_name varchar(20) not null,' .
        " add_date       datetime default '0000-00-00'," .
        " update_date    datetime default '0000-00-00'," .
        ' del_flag tinyint(1) default 0' .
        ')';

//    echo $sql;    // debug
    if (!mysql_query($sql, $link)){
        echo mysql_error($link);
        exit;
    }

    echo 'table created. ' . TABLE_BREAKER;

    // 生成したテーブルの内容表示
    show_columns(TABLE_BREAKER, $link);

?>
