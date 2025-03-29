<?php header("Content-Type: text/html; charset=utf-8"); ?>
<?php

// CSVアップロードなどの列を定義する
// cfから始まるprefix必ずつけること

// ブレーカーCSV登録(pre: cf_bre_up_ )
$cf_bre_up_dc_loc       =  0;       // dcロケーション
$cf_bre_up_floor        =  1;       // フロア
$cf_bre_up_pdu_no       =  2;       // PDC番号
$cf_bre_up_bunden       =  3;       // 分電盤番号
$cf_bre_up_mccb_no      =  4;       // MCCB番号
$cf_bre_up_rack_name    =  5;       // ラック名
$cf_bre_up_ampere       =  6;       // 定格電流
$cf_bre_up_status       =  7;       // ON・OFF
$cf_bre_up_plug_type    =  8;       // コンセント形状
$cf_bre_up_plug_count   =  9;       // コンセント口数

$cf_bre_up_title[$cf_bre_up_dc_loc]         = "dcロケーション";
$cf_bre_up_title[$cf_bre_up_floor]          = "フロア";
$cf_bre_up_title[$cf_bre_up_pdu_no]         = "PDC番号";
$cf_bre_up_title[$cf_bre_up_bunden]         = "分電盤番号";
$cf_bre_up_title[$cf_bre_up_mccb_no]        = "MCCB番号";
$cf_bre_up_title[$cf_bre_up_rack_name]      = "ラック名";
$cf_bre_up_title[$cf_bre_up_ampere]         = "定格電流";
$cf_bre_up_title[$cf_bre_up_status]         = "ON(1)・OFF(0)";
$cf_bre_up_title[$cf_bre_up_plug_type]      = "コンセント形状";
$cf_bre_up_title[$cf_bre_up_plug_count]     = "コンセント口数";
?>
