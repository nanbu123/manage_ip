<?php header("Content-Type: text/html; charset=utf-8"); ?>
<?php
// ファイル関連
define('ERROR_FILE', './log/error.log');

// DB関連
define('SERVER_NAME', 'mariadb55');
define('DB_USER',     'user');
define('DB_PASS',     'xxxxxx');
define('DB_NAME',     'manage_ip');

// テーブル名
define('TABLE_COMPANY', 'company');
define('TABLE_ADDRESS', 'ip_address');
define('TABLE_RACK',    'rack');
define('TABLE_PATCH',   'patch');
define('TABLE_USER',    'user');
define('TABLE_ROUTER',  'router');
define('TABLE_MAIL',    'mail');
define('TABLE_PHONE',   'telephone');
define('TABLE_BREAKER', 'breaker');
define('TABLE_DNS',     'dns');

// １ページに表示する行数
define('PAGE_ROW', 30);

// フッタ
define('FOOTER_STR',    'IPアドレス & ラック管理システム copyright 2008-2018 yano');

$array_patch_type[0] = "";
$array_patch_type[1] = "インターネット";
$array_patch_type[2] = "電話";
$array_patch_type[3] = "MSP部";
$array_patch_type[4] = "ラック接続";
$array_patch_type[5] = "VLANサービス";

$array_mail_type[0] = "通常";
$array_mail_type[1] = "緊急";

// IPアドレス、ルータポート用
$array_ip_location[1] = "TY6/TY7";
//$array_ip_location[2] = "";
$array_ip_location[3] = "TY8";
$array_ip_location[4] = "TY9/10";
//$array_ip_location[5] = "TY10";
$array_ip_location[8] = "OS99";
$array_ip_location[9] = "PI";

// ラック用、ブレーカー
$array_dc_location[1] = "TY6";
$array_dc_location[2] = "TY7";
$array_dc_location[3] = "TY8";
$array_dc_location[4] = "TY9";
$array_dc_location[5] = "TY10";
$array_dc_location[8] = "OS99";

// ラック用サイズ
$array_rack_size[1] = "1";
$array_rack_size[2] = "1/2";
$array_rack_size[3] = "1/4";
$array_rack_size[4] = "Cage";
$array_rack_size[5] = "Room";

// 権限レベル
$array_user_level[1] = "最高権限";          // 最初に登録されているadminの権限 この権限のままでは削除できない
$array_user_level[2] = "管理者権限";        // ユーザーの登録変更パスワード変更以外は最高権限と同じ
//$array_user_level[4] = "登録のみ権限";      // 削除不可 登録と更新は可能
$array_user_level[5] = "一般権限";          // 削除不可 更新は可能 登録は会社のみ
$array_user_level[9] = "参照のみ権限";      // 登録･更新･削除は不可 ユーザー一覧の閲覧も不可

// ブレーカーの最高値
$max_breaker_floor  = 6;     // フロア
$max_breaker_bunden = 5;     // 分電盤

// プライマリ・セカンダリ利用
$array_use_kind[0] = "プライマリ利用";
$array_use_kind[1] = "セカンダリ利用";
$array_use_kind[2] = "権限委譲";
?>
