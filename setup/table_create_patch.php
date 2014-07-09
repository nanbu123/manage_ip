<?php

    // DBの設定読み込み
    include_once('../lib/config.php');
    include_once('../lib/function.php');

    $link = mysql_connect(SERVER_NAME, DB_USER, DB_PASS)
        or die('Could not connect: ' . mysql_error());

    mysql_select_db(DB_NAME) or die('Could not select database');

//    // 現在のテーブル削除
//    $sql = 'DROP TABLE ' . TABLE_PATCH;
//    mysql_query($sql, $link);

    $sql = 'CREATE TABLE IF NOT EXISTS ' . TABLE_PATCH . 
        ' (id int unsigned not null auto_increment primary key,' . 
        ' panel_number varchar(40) not null,' .
        ' rack_id  int unsigned not null,' .
        ' type     tinyint not null,' .
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
    show_columns(TABLE_PATCH, $link);

?>
