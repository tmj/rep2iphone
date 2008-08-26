<?php
/*
    rep2 - iPhone用基本設定ファイル

	>>11氏のを参考に作成。11氏敬礼。
*/
define('P2_IPHONE_LIB_DIR', './iphone');

$_conf['ktai']          = true;
$_conf['subject_php']   = "subject_i.php";
$_conf['read_php']      = "read_i.php";
$_conf['post_php']      = 'post_i.php';
$_conf['read_new_k_php']        = 'read_new_i.php';
$_conf['post_php']              = 'post_i.php';
$_conf['meta_charset_ht'] .= '<link rel="apple-touch-icon" href="p2iphone.png" /><meta name="viewport" content="width=320; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;"/>';
?>