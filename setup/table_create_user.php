<?php header("Content-Type: text/html; charset=utf-8"); ?>
<?php

    // DBの設定読み込み
    include_once('../lib/config.php');
    include_once('../lib/function.php');

    $link = mysql_connect(SERVER_NAME, DB_USER, DB_PASS)
        or die('Could not connect: ' . mysql_error());

    mysql_select_db(DB_NAME) or die('Could not select database');

    $sql = 'DROP TABLE ' . TABLE_USER;
    
    mysql_query($sql, $link);

    $sql = 'CREATE TABLE ' . TABLE_USER . 
        ' (id int unsigned not null auto_increment primary key,' . 
        ' name     varchar(20) not null,' .
        ' password varchar(50) not null,' .
        ' level    tinyint unsigned not null,' .
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
    show_columns(TABLE_USER, $link);
    $admin_pass = md5("admin");
    $sql = "INSERT INTO " . TABLE_USER . 
        // id, name, password, level, comment, add_user_name, update_user_name, add_date, update_date, del_flag
        " values ('', 'admin', '{$admin_pass}', '1', '', 'admin', 'admin', NOW(), NOW(), '')";
    if (mysql_query($sql, $link)){
        echo 'inserted admin data ' . "<br>\n";
    } else {
        echo mysql_error($link);
        exit;
    }
    

?>
