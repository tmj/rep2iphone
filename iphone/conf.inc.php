<?php
/*
    rep2 - iPhone�p��{�ݒ�t�@�C��

	>>11���̂��Q�l�ɍ쐬�B11���h��B
*/
define('P2_IPHONE_LIB_DIR', './iphone');

$_conf['ktai']          = true;
$_conf['subject_php']   = "subject_i.php";
$_conf['read_php']      = "read_i.php";
$_conf['post_php']      = 'post_i.php';
$_conf['read_new_k_php']        = 'read_new_i.php';
$_conf['post_php']              = 'post_i.php';
$_conf['meta_charset_ht'] .= '<link rel="apple-touch-icon" href="p2iphone.png" /><meta name="viewport" content="width=320; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;"/>';


$_conf['menuKIni'] = array(
    'recent_shinchaku'  => array(
        'subject_i.php?spmode=recent&sb_view=shinchaku',
        '�ŋߓǂ񂾃X���̐V��'
    ),
    'recent'            => array(
        'subject_i.php?spmode=recent&norefresh=1',
        '�ŋߓǂ񂾃X���̑S��'
    ),
    'fav_shinchaku'     => array(
        'subject_i.php?spmode=fav&sb_view=shinchaku',
        '���C�ɃX���̐V��'
    ),
    'fav'               => array(
        'subject_i.php?spmode=fav&norefresh=1',
        '���C�ɃX���̑S��'
    ),
    'favita'            => array(
        'menu_i.php?view=favita',
        '���C�ɔ�'
    ),
    'cate'              => array(
        'menu_i.php?view=cate',
        '���X�g'
    ),
    'res_hist'          => array(
        'subject_i.php?spmode=res_hist',
        '��������'
    ),
    'palace'            => array(
        'subject_i.php?spmode=palace&norefresh=1',
        '�X���̓a��'
    ),
    'setting'           => array(
        'setting.php?dummy=1',
        '���O�C���Ǘ�'
    ),
    'editpref'          => array(
        'editpref_i.php?dummy=1',
        '�ݒ�Ǘ�'
    )
);

?>