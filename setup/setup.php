<?php

// DBの設定読み込み
include_once('../lib/config.php');
include_once('../lib/function.php');

// DB作成
require_once('./db_create.php');
// テーブル作成
require_once('./table_create_company.php');
require_once('./table_create_address.php');
require_once('./table_create_rack.php');
require_once('./table_create_patch.php');
require_once('./table_create_user.php');
require_once('./table_create_router.php');
require_once('./table_create_mail.php');
require_once('./table_create_phone.php');

?>
