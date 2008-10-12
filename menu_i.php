<?php
/*
    p2 -  ���j���[ �g�їp
*/

//080825iphone�p���C�u�����ǉ�
require_once './conf/conf.inc.php';
require_once './iphone/conf.inc.php';
require_once P2_LIB_DIR . '/brdctl.class.php';
require_once P2_IPHONE_LIB_DIR . '/showbrdmenuk.class.php';

$_login->authorize(); // ���[�U�F��

//==============================================================
// �ϐ��ݒ�
//==============================================================
$_conf['ktai'] = 1;
$brd_menus = array();
$GLOBALS['menu_show_ita_num'] = 0;

BrdCtl::parseWord(); // set $GLOBALS['word']

//============================================================
// ����ȑO����
//============================================================
// ���C�ɔ̒ǉ��E�폜
if (isset($_GET['setfavita'])) {
    require_once P2_LIB_DIR . '/setfavita.inc.php';
    setFavIta();
}

//================================================================
// ���C��
//================================================================
$aShowBrdMenuK =& new ShowBrdMenuK;

//============================================================
// �w�b�_HTML��\��
//============================================================

$get['view'] = isset($_GET['view']) ? $_GET['view'] : null;

if ($get['view'] == "favita") {
    $ptitle = "���C�ɔ�";
} elseif ($get['view'] == "cate"){
    $ptitle = "���X�g";
} elseif (isset($_GET['cateid'])) {
    $ptitle = "���X�g";
} else {
    $ptitle = "��޷��p2";
}

P2View::printDoctypeTag();
?>
<html lang="ja">
<head>
<style type="text/css" media="screen">@import "./iui/iui.css";</style>
<script type="text/javascript"> 
<!-- 
window.onload = function() { 
setTimeout(scrollTo, 100, 0, 1); 
} 
</script> 
<?php
P2View::printHeadMetasHtml();
?>

<title><?php eh($ptitle); ?></title>
</head><body>
<div class="toolbar"><h1 id="pageTitle"><?php eh($ptitle); ?></h1></div>
<?php
P2Util::printInfoHtml();


// ���C�ɔ�HTML�\������
if ($get['view'] == 'favita') {
    $aShowBrdMenuK->printFavItaHtml();

// ����ȊO�Ȃ�brd�ǂݍ���
} else {
    $brd_menus = BrdCtl::readBrdMenus();
}

// �����t�H�[����HTML�\��
if ($get['view'] != 'favita' && $get['view'] != 'rss' && empty($_GET['cateid'])) {
    echo '<div id="usage" class="panel"><filedset>';
    echo BrdCtl::getMenuKSearchFormHtml();
    echo '</filedset></div>';
}

//===========================================================
// �������ʂ�HTML�\��
//===========================================================
// {{{ �������[�h�������

$modori_url_ht = '';

// {{{ �������[�h�������

if (strlen($GLOBALS['word']) > 0) {

    ?>��ؽČ�������
    <?php
    if ($GLOBALS['ita_mikke']['num']) {
        printf('<br>"%s" %dhit!', hs($GLOBALS['word']), $GLOBALS['ita_mikke']['num']);
        echo $hr;
    }
    
    // �����������ĕ\������
    if ($brd_menus) {
        foreach ($brd_menus as $a_brd_menu) {
            $aShowBrdMenuK->printItaSearch($a_brd_menu->categories);
        }
    }

    if (!$GLOBALS['ita_mikke']['num']) {
        P2Util::pushInfoHtml(sprintf('<p>"%s"���܂ޔ͌�����܂���ł����B</p>', hs($GLOBALS['word'])));
    }
    $atag = P2View::tagA(
        P2Util::buildQueryUri('menu_k.php',
            array(
                'view' => 'cate',
                'nr'   => '1',
                UA::getQueryKey() => UA::getQueryValue()
            )
        ),
        hs('��ؽ�')
    );
    $modori_url_ht = '<div>' . $atag . '</div>';
}

// }}}
// �J�e�S����HTML�\��
if ($get['view'] == 'cate' or isset($_REQUEST['word']) && strlen($GLOBALS['word']) == 0) {
    //echo "��ؽ�{$hr}";
    if ($brd_menus) {
        foreach ($brd_menus as $a_brd_menu) {
            $aShowBrdMenuK->printCate($a_brd_menu->categories);
        }
    }
}

// �J�e�S���̔�HTML�\��
if (isset($_GET['cateid'])) {
    if ($brd_menus) {
        foreach ($brd_menus as $a_brd_menu) {
            $aShowBrdMenuK->printIta($a_brd_menu->categories);
        }
    }
    $modori_url_ht = P2View::tagA(
        P2Util::buildQueryUri('menu_k.php',
            array('view' => 'cate', 'nr' => '1', UA::getQueryKey() => UA::getQueryValue())
        ),
        '��ؽ�'
    ) . '<br>';
}


P2Util::printInfoHtml();


// �t�b�^��HTML�\��
echo geti($GLOBALS['list_navi_ht']);
echo '<p><a id="backButton"class="button" href="iphone.php">TOP</a></p>';
?>
</body></html>
<?php

exit;