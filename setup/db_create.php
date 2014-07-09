<?php

    include_once('../lib/config.php');

    $link = mysql_connect(SERVER_NAME, DB_USER, DB_PASS)
        or die('Could not connect: ' . mysql_error());

    $sql = 'Create Database IF NOT EXISTS ' . DB_NAME . " DEFAULT CHARACTER SET UJIS";

    mysql_query($sql, $link) or die('Could not create database');

    mysql_select_db(DB_NAME) or die('Could not select database');

    echo 'Databalse Created. ' . DB_NAME;

?>
