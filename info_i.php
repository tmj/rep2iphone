<?php
/*
    p2 - �X���b�h���E�B���h�E
*/

/*
iphone ����X�������擾����Ƃ���subject_i����Ăяo�����B
�i�ގ��X���Ɠ����ɕ\�������Ă邽�߁j
�ꕔ�t�@�C�����d�ɓǂݍ��܂Ȃ��悤�ɂ����B
*/
if($_GET['b']){
    require_once './conf/conf.inc.php';
    require_once P2_LIB_DIR . '/thread.class.php';
    require_once P2_LIB_DIR . '/filectl.class.php';
}
require_once P2_LIB_DIR . '/dele.inc.php'; // �폜�����p�̊֐��S
$_conf['k_at_a'] = '&b=15';
$_login->authorize(); // ���[�U�F��

//================================================================
// �ϐ��ݒ�
//================================================================
isset($_GET['host'])    and $host = $_GET['host'];  // "pc.2ch.net"
isset($_GET['bbs'])     and $bbs  = $_GET['bbs'];   // "php"
isset($_GET['key'])     and $key  = $_GET['key'];   // "1022999539"
isset($_GET['ttitle_en'])   and $ttitle_en = $_GET['ttitle_en'];

// popup 0(false), 1(true), 2(true, �N���[�Y�^�C�}�[�t)
!empty($_GET['popup']) and $popup_ht = "&amp;popup=1";

// �ȉ��ǂꂩ����Ȃ��Ă��_���o��
if (empty($host) || !isset($bbs) || !isset($key)) {
    p2die('����������������܂���B');
}

$title_msg = '';

//================================================================
// ���ʂȑO����
//================================================================
$info_msg = '';

// {{{ �폜

if (!empty($_GET['dele'])) {
    $r = deleteLogs($host, $bbs, array($key));
    if (empty($r)) {
        $title_msg  = "�~ ���O�폜���s";
        $info_msg   = "�~ ���O�폜���s";
    } elseif ($r == 1) {
        $title_msg  = "�� ���O�폜����";
        $info_msg   = "�� ���O�폜����";
    } elseif ($r == 2) {
        $title_msg  = "- ���O�͂���܂���ł���";
        $info_msg   = "- ���O�͂���܂���ł���";
    }
}

// }}}
// {{{ �����폜

if (!empty($_GET['offrec'])) {
    $r1 = offRecent($host, $bbs, $key);
    $r2 = offResHist($host, $bbs, $key);
    if (($r1 === false) or ($r2 === false)) {
        $title_msg  = "�~ �����������s";
        $info_msg   = "�~ �����������s";
    } elseif ($r1 == 1 || $r2 == 1) {
        $title_msg  = "�� ������������";
        $info_msg   = "�� ������������";
    } elseif ($r1 === 0 && $r2 === 0) {
        $title_msg  = "- �����ɂ͂���܂���ł���";
        $info_msg   = "- �����ɂ͂���܂���ł���";
    }

// }}}

// ���C�ɓ���X���b�h
} elseif (isset($_GET['setfav'])) {
    require_once P2_LIB_DIR . '/setfav.inc.php';
    setFav($host, $bbs, $key, $_GET['setfav']);

// �a������
} elseif (isset($_GET['setpal'])) {
    require_once P2_LIB_DIR . '/setpalace.inc.php';
    setPal($host, $bbs, $key, $_GET['setpal']);

// �X���b�h���ځ[��
} elseif (isset($_GET['taborn'])) {
    require_once P2_LIB_DIR . '/settaborn.inc.php';
    settaborn($host, $bbs, $key, $_GET['taborn']);
}

//=================================================================
// ���C��
//=================================================================

$aThread =& new Thread();

// host�𕪉�����idx�t�@�C���̃p�X�����߂�
$aThread->setThreadPathInfo($host, $bbs, $key);
$key_line = $aThread->getThreadInfoFromIdx();
$aThread->getDatBytesFromLocalDat(); // $aThread->length ��set

if (!$aThread->itaj = P2Util::getItaName($aThread->host, $aThread->bbs)) {
    $aThread->itaj = $aThread->bbs;
}
$hc['itaj'] = $aThread->itaj;

if (!$aThread->ttitle) {
    if (isset($ttitle_en)) {
        $aThread->setTtitle(base64_decode($ttitle_en));
    } else {
        $aThread->setTitleFromLocal();
    }
}
if (!$ttitle_en) {
    if ($aThread->ttitle) {
        $ttitle_en = base64_encode($aThread->ttitle);
        //$ttitle_urlen = rawurlencode($ttitle_en);
    }
}

if (!is_null($aThread->ttitle_hc)) {
    $hc['ttitle_name'] = $aThread->ttitle_hc;
} else {
    $hc['ttitle_name'] = "�X���b�h�^�C�g�����擾";
}


// {{{ favlist �`�F�b�N

/*
// ���C�ɃX�����X�g �Ǎ�
if ($favlines = @file($_conf['favlist_file'])) {
    foreach ($favlines as $l) {
        $favarray = explode('<>', rtrim($l));
        if ($aThread->key == $favarray[1] && $aThread->bbs == $favarray[11]) {
            $aThread->fav = "1";
            if ($favarray[0]) {
                $aThread->setTtitle($favarray[0]);
            }
            break;
        }
    }
}
*/

// ���C�ɃX��
$fav_atag = _getFavAtag($aThread, $favmark_accesskey, $ttitle_en);

// }}}
// {{{ palace �`�F�b�N

// �a������X�����X�g �Ǎ�
$isPalace = false;
$palace_idx = $_conf['pref_dir'] . '/p2_palace.idx';
if ($pallines = @file($palace_idx)) {
    foreach ($pallines as $l) {
        $palarray = explode('<>', rtrim($l));
        if ($aThread->key == $palarray[1]) {
            $isPalace = true;
            if ($palarray[0]) {
                $aThread->setTtitle($palarray[0]);
            }
            break;
        }
    }
}

$paldo = $isPalace ? 0 : 1;

$pal_a_ht = "info_i.php?host={$aThread->host}&amp;bbs={$aThread->bbs}&amp;key={$aThread->key}&amp;setpal={$paldo}{$popup_ht}{$ttitle_en_ht}{$_conf['k_at_a']}";

if ($isPalace) {
    $pal_ht = "<a href=\"{$pal_a_ht}\" title=\"DAT���������X���p�̂��C�ɓ���\">��</a>";
} else {
    $pal_ht = "<a href=\"{$pal_a_ht}\" title=\"DAT���������X���p�̂��C�ɓ���\">+</a>";
}

// }}}
// {{{ �X���b�h���ځ[��`�F�b�N

// �X���b�h���ځ[�񃊃X�g�Ǎ�
$ta_keys = P2Util::getThreadAbornKeys($aThread->host, $aThread->bbs);
$isTaborn = empty($ta_keys[$aThread->key]) ? false : true;


$taborndo_title_attrs = array();
if (UA::isPC() and !$isTaborn) {
    $taborndo_title_attrs = array('title' => '�X���b�h�ꗗ�Ŕ�\���ɂ��܂�');
}
$atag = P2View::tagA(
    P2Util::buildQueryUri('info_i.php',
        array(
            'host' => $aThread->host,
            'bbs'  => $aThread->bbs,
            'key'  => $aThread->key,
            'taborn' => $isTaborn ? 0 : 1,
            'popup' => (int)(bool)geti($_GET['popup']),
            'ttitle_en' => $ttitle_en,
            UA::getQueryKey() => UA::getQueryValue(),
            'b' => '15'
        )
    ),
    sprintf(
        '%s%s',
        hs(UA::isK() ? $taborn_accesskey . '.' : ''),
        hs($isTaborn ? '���ځ[���������' : '���ځ[�񂷂�')
    ),
    array_merge($taborndo_title_attrs, array('accesskey' => $taborn_accesskey))
);

$taborn_ht = sprintf(
    '%s [%s]', 
    hs($isTaborn ? '���ځ[��' : '�ʏ�'),
    $atag
);


// }}}

// ���O����Ȃ��t���O�Z�b�g
if (file_exists($aThread->keydat) or file_exists($aThread->keyidx)) {
    $existLog = true;
}

//=================================================================
// HTML�v�����g
//=================================================================
$motothre_url = $aThread->getMotoThread();
$motothre_org_url = $aThread->getMotoThread(true);

if ($title_msg) {
    $hc['title'] = $title_msg;
} else {
    $hc['title'] = "info - {$hc['ttitle_name']}";
}

$hs = array_map('htmlspecialchars', $hc);


$hs = array_map('htmlspecialchars', $hc);

//�������d�����Ȃ��悤��
if($_GET['b']){
P2Util::headerNoCache();
P2View::printDoctypeTag();
echo $_conf['doctype'];
echo <<<EOHEADER
<html>
<head>
    {$_conf['meta_charset_ht']}
    <meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
    <meta http-equiv="Content-Style-Type" content="text/css">
    <meta http-equiv="Content-Script-Type" content="text/javascript">
    <link rel="stylesheet" type="text/css" href="./iui/iui.css"> 
    <title>{$hs['title']}</title>\n
EOHEADER;

}

if (isset($_GET['popup']) and $_GET['popup'] == 2) {
    echo <<<EOSCRIPT
    <script type="text/javascript" src="js/closetimer.js"></script>
EOSCRIPT;
    $body_onload = <<<EOP
 onLoad="startTimer(document.getElementById('timerbutton'))"
EOP;
} else {
    $body_onload = '';
}
if($_GET['b']){

    //html �v�����g�w�b�h iPhone�p
echo <<<EOP
    </head>
    <body{$body_onload}>
    <div class="toolbar">
    <h1 id="pageTitle">�X�����</h1>
    <a id="backButton" class="button" href="./iphone.php">TOP</a>
    </div>
EOP;

}

echo '<ul><li class="group">�X�����</li></ul><div id="usage" class="panel">';

P2Util::printInfoHtml();

echo <<<EOP
<h2>{$hs['ttitle_name']}</b></h2>
<fieldset>
EOP;
// �g�тȂ�`���ŏ�񃁃b�Z�[�W�\��
if (UA::isK()) {
    if (strlen($info_msg)) {
        printf('<p>%s</p>', hs($info_msg));
    }
}

if (checkRecent($aThread->host, $aThread->bbs, $aThread->key) or checkResHist($aThread->host, $aThread->bbs, $aThread->key)) {
    $offrec_ht = " / [<a href=\"info_i.php?host={$aThread->host}&amp;bbs={$aThread->bbs}&amp;key={$aThread->key}&amp;offrec=true{$popup_ht}{$ttitle_en_ht}{$_conf['k_at_a']}\" title=\"���̃X�����u�ŋߓǂ񂾃X���v�Ɓu�������ݗ����v����O���܂�\">��������O��</a>]";
}


//printInfoTrHtml("���X��", "<a href=\"{$motothre_url}\"{$target_read_at}>{$motothre_url}</a>");
//printInfoTrHtml("�z�X�g", $aThread->host);

$dele_pre_ht = '';
$up_pre_ht = '';

$offrecent_ht = '';
if (checkRecent($aThread->host, $aThread->bbs, $aThread->key) or checkResHist($aThread->host, $aThread->bbs, $aThread->key)) {
    $atag = _getOffRecentAtag($aThread, $offrecent_accesskey, $ttitle_en);
    $offrecent_ht = " / [$atag]";
}

_printInfoTrHtml(
    '���X��',
    P2View::tagA(
        $motothre_url,
        hs($motothre_url),
        UA::isPC() ? array('target' => 'read') : array()
    )
);

if (UA::isPC()) {
    _printInfoTrHtml("�z�X�g", $aThread->host);
}

// ��
$ita_uri = P2Util::buildQueryUri(
    $_conf['subject_php'],
    array(
        'host' => $aThread->host,
        'bbs'  => $aThread->bbs,
        UA::getQueryKey() => UA::getQueryValue(),
        'b' => '15'
    )
);
$attrs =  array($_conf['accesskey'] => $_conf['k_accesskey']['up']);
UA::isPC() and $attrs['target'] = 'subject';
$ita_atag = P2View::tagA(
    $ita_uri,
    "{$up_pre_ht}{$hs['itaj']}",
    $attrs
);
_printInfoTrHtml('��', $ita_atag);

if ($existLog) {
    $atag = P2View::tagA(
        P2Util::buildQueryUri('info_i.php',
            array(
                'host' => $aThread->host,
                'bbs'  => $aThread->bbs,
                'key'  => $aThread->key,
                'dele' => '1',
                'popup' => (int)(bool)geti($_GET['popup']),
                'ttitle_en' => $ttitle_en,
                UA::getQueryKey() => UA::getQueryValue(),
                'b' => '15'
            )
        ),
        "{$dele_pre_ht}�폜����",
        array($_conf['accesskey'] => $_conf['k_accesskey']['dele'])
    );
    _printInfoTrHtml("���O", "���� [$atag]{$offrecent_ht}");

} else {
    _printInfoTrHtml("���O", "���擾{$offrecent_ht}");
}

if ($aThread->gotnum) {
    _printInfoTrHtml("�������X��", $aThread->gotnum);

} elseif (!$aThread->gotnum and $existLog) {
    _printInfoTrHtml("�������X��", "0");

} else {
    _printInfoTrHtml("�������X��", "-");
}

_printInfoTrHtml("���C�ɃX��", $fav_atag);
_printInfoTrHtml("�a������", $pal_ht);
_printInfoTrHtml("�\��", $taborn_ht);


/*
// �֘A�L�[���[�h
if (!$_conf['ktai'] and P2Util::isHost2chs($aThread->host)) {
    echo <<<EOP
<iframe src="http://p2.2ch.io/getf.cgi?{$motothre_url}" border="0" frameborder="0" height="30" width="520"></iframe>
EOP;
}
*/

// {{{ ����{�^��

if (!empty($_GET['popup'])) {
    echo '<div align="center">';
    if ($_GET['popup'] == 1) {
        echo '<form action=""><input type="button" value="�E�B���h�E�����" onClick="window.close();"></form>';
    } elseif ($_GET['popup'] == 2) {
        echo <<<EOP
    <form action=""><input id="timerbutton" type="button" value="Close Timer" onClick="stopTimer(document.getElementById('timerbutton'))"></form>
EOP;
    }
    echo '</div>' . "\n";
}

// }}}

echo '</filedset></div>';
if($_GET['b']){
    echo '</body></html>';
}

//exit;


//=================================================================
// �֐� �i���̃t�@�C�����ł̂ݗ��p�j
//=================================================================
/**
 * �X�����HTML��\������
 *
 * @return  void
 */
function _printInfoTrHtml($s, $c_ht)
{
    global $_conf;
    
    // iPhone
    echo "<div class=\"row\">\n<label>{$s}</label><span>{$c_ht}</span></div>\n";
}

/**
 * �X���^�C��URL�̃R�s�y�p�̃t�H�[��HTML���擾����
 *
 * @return  string
 */
function _getCopypaFormHtml($url, $ttitle_name_hd)
{
    global $_conf;
    
    $url_hs = htmlspecialchars($url, ENT_QUOTES);
    
    $me_url = $me_url = P2Util::getMyUrl();
    // $_SERVER['REQUEST_URI']
    
        $htm = <<<EOP
<form action="{$me_url}">
 <textarea name="copy" rows="5" cols="50">{$ttitle_name_hd}&#10;{$url_hs}</textarea>
</form>
EOP;
    
// <input type="text" name="url" value="{$url_hs}">
// <textarea name="msg_txt">{$msg_txt}</textarea><br>

    return $htm;
}

/**
 * @return  string  HTML
 */
function _getFavAtag($aThread, $favmark_accesskey, $ttitle_en)
{
    global $_conf;
    
    return P2View::tagA(
        P2Util::buildQueryUri('info_i.php',
            array(
                'host' => $aThread->host,
                'bbs'  => $aThread->bbs,
                'key'  => $aThread->key,
                'setfav' => $aThread->fav ? 0 : 1,
                'popup' => (int)(bool)geti($_GET['popup']),
                'ttitle_en' => $ttitle_en,
                UA::getQueryKey() => UA::getQueryValue(),
                'b' => '15'
            )
        ),
        sprintf(
            '%s<span class="fav">%s</span>',
            hs(UA::isK() ? $favmark_accesskey . '.' : ''),
            hs($aThread->fav ? '��' : '+')
        ),
        array('accesskey' => $favmark_accesskey)
    );
}

/**
 * @return  string  HTML
 */
function _getTtitleNameAtag($aThread, $ttitle_name)
{
    global $_conf;
    
    $attrs = array('class' => 'thre_title');
    if (UA::isPC()) {
        $attrs['target'] = 'read';
    }
    
    return P2View::tagA(
        P2Util::buildQueryUri($_conf['read_php'],
            array(
                'host' => $aThread->host,
                'bbs'  => $aThread->bbs,
                'key'  => $aThread->key,
                UA::getQueryKey() => UA::getQueryValue(),
                'b' => '15'
            )
        ),
        hs($ttitle_name) . ' ',
        $attrs
    );
}

/**
 * @return  string  HTML
 */
function _getOffRecentAtag($aThread, $offrecent_accesskey, $ttitle_en)
{
    global $_conf;
    
    return P2View::tagA(
        P2Util::buildQueryUri('info_i.php',
            array(
                'host' => $aThread->host,
                'bbs'  => $aThread->bbs,
                'key'  => $aThread->key,
                'offrecent' => '1',
                'popup' => (int)(bool)geti($_GET['popup']),
                'ttitle_en' => $ttitle_en,
                UA::getQueryKey() => UA::getQueryValue(),
                'b' => '15'
            )
        ),
        sprintf('%s��������O��', hs(UA::isK() ? $offrecent_accesskey . '.' : '')),
        array(
            'title' => '���̃X�����u�ŋߓǂ񂾃X���v�Ɓu�������ݗ����v����O���܂�',
            'accesskey' => $offrecent_accesskey
        )
    );
}
