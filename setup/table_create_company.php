<?php header("Content-Type: text/html; charset=utf-8"); ?>
<?php

    // DBの設定読み込み
    include_once('../lib/config.php');
    include_once('../lib/function.php');

    $link = mysql_connect(SERVER_NAME, DB_USER, DB_PASS)
        or die('Could not connect: ' . mysql_error());

    mysql_select_db(DB_NAME) or die('Could not select database');

//    // 現在のテーブル削除
//    $sql = 'DROP TABLE ' . TABLE_COMPANY;
//    mysql_query($sql, $link);

    $sql = 'CREATE TABLE IF NOT EXISTS ' . TABLE_COMPANY . 
        ' (id int unsigned not null auto_increment primary key,' . 
        ' code     varchar(40) not null,' .
        ' name     varchar(100) not null,' .
        ' kana     varchar(100) not null,' .
        ' address  varchar(200) not null,' .
        ' tel      varchar(20) not null,' .
        ' tanto_name   varchar(40) not null,' .
        ' mail_address varchar(40) not null,' .
        ' sales_name   varchar(40) not null,' .
        ' end_user_name varchar(100) not null,' .
        ' end_user_kana varchar(100) not null,' .
        ' end_contract_day datetime default '0000-00-00'," .
        ' comment  text not null,' .
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

    // 生成したテーブルの内容表示
    show_columns(TABLE_COMPANY, $link);

?>
