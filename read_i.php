<?php
/*
    p2 - �X���b�h�\���X�N���v�g
    �t���[��������ʁA�E������
*/

require_once './conf/conf.inc.php';
require_once './iphone/conf.inc.php';
$_conf['ktai']          = true;
require_once P2_LIB_DIR . '/thread.class.php';
require_once P2_LIB_DIR . '/threadread.class.php';
require_once P2_LIB_DIR . '/filectl.class.php';
require_once P2_LIB_DIR . '/NgAbornCtl.php';
require_once P2_LIB_DIR . '/ShowThread.php';
require_once P2_LIB_DIR . '/P2Validate.php';
$_login->authorize(); // ���[�U�F��

//================================================================
// �ϐ�
//================================================================

// {{{ �g�їp ���ꃊ�N�G�X�g

if ($_conf['ktai'] && isset($_GET['ktool_name']) && isset($_GET['ktool_value'])) {

    switch ($_GET['ktool_name']) {
        case 'goto':
            if (strpos($_GET['ktool_value'], '-') !== false) {
                $_REQUEST['ls'] = $_GET['ls'] = $_GET['ktool_value'];
            } else {
                $ktv = intval($_GET['ktool_value']);
                $_REQUEST['ls'] = $_GET['ls'] = $ktv . '-' . ($ktv + $_conf['k_rnum_range']);
            }
            break;
            
        case 'res_quote':
            $_GET['resnum'] = (int)$_GET['ktool_value'];
            $_GET['inyou'] = 1;
            require './post_form.php';
            exit;
            
        case 'copy_quote':
            $_GET['inyou'] = 1;
            
        case 'copy':
            $GLOBALS['_read_copy_resnum'] = (int)$_GET['ktool_value'];
            require './read_copy_k.php';
            exit;
            
        /* 2006/03/06 aki �m�[�}��p2�ł͖��Ή�
        case 'aas_rotate':
            $_GET['rotate'] = 1;
            
        case 'aas':
            $_GET['resnum'] = (int)$_GET['ktool_value'];
            require './aas.php';
            exit;
        */
    }
}

// }}}

// �X���̎w��
list($host, $bbs, $key, $ls) = _detectThread();

// {{{ ���X�t�B���^

$GLOBALS['res_filter'] = array(
    'field'  => null,
    'match'  => null,
    'method' => null
);

if (isset($_POST['field']))  { $GLOBALS['res_filter']['field']  = $_POST['field']; }
if (isset($_GET['field']))   { $GLOBALS['res_filter']['field']  = $_GET['field']; }
if (isset($_POST['match']))  { $GLOBALS['res_filter']['match']  = $_POST['match']; }
if (isset($_GET['match']))   { $GLOBALS['res_filter']['match']  = $_GET['match']; }
if (isset($_POST['method'])) { $GLOBALS['res_filter']['method'] = $_POST['method']; }
if (isset($_GET['method']))  { $GLOBALS['res_filter']['method'] = $_GET['method']; }

// $GLOBALS['word'] �Ȃǂ̌�����̎�舵�����ǂ������Ȃ̂́A�ł���ΐ����������Ƃ���...
$GLOBALS['word'] = geti($_POST['word'], geti($_GET['word']));
$GLOBALS['word_fm'] = null;

if (strlen($GLOBALS['word'])) {
    // �f�t�H���g�I�v�V����
    if (empty($GLOBALS['res_filter']['field']))  { $GLOBALS['res_filter']['field']  = "hole"; }
    if (empty($GLOBALS['res_filter']['match']))  { $GLOBALS['res_filter']['match']  = "on"; }
    if (empty($GLOBALS['res_filter']['method'])) { $GLOBALS['res_filter']['method'] = "or"; }

    if (!($GLOBALS['res_filter']['method'] == 'regex' && preg_match('/^\.+$/', $GLOBALS['word']))) {
        require_once P2_LIB_DIR . '/strctl.class.php';
        $GLOBALS['word_fm'] = StrCtl::wordForMatch($word, $GLOBALS['res_filter']['method']);
        if ($GLOBALS['res_filter']['method'] != 'just') {
            if (P2_MBREGEX_AVAILABLE == 1) {
                $GLOBALS['words_fm'] = mb_split('\s+', $GLOBALS['word_fm']);
                $GLOBALS['word_fm'] = mb_ereg_replace('\s+', '|', $GLOBALS['word_fm']);
            } else {
                $GLOBALS['words_fm'] = preg_split('/\s+/', $GLOBALS['word_fm']);
                $GLOBALS['word_fm'] = preg_replace('/\s+/', '|', $GLOBALS['word_fm']);
            }
            $GLOBALS['word_fm'] = '(?:' . $GLOBALS['word_fm'] . ')';
        }
    }
    
    if (UA::isK()) {
        $filter_page = isset($_REQUEST['filter_page']) ? max(1, intval($_REQUEST['filter_page'])) : 1;
        $filter_range = array();
        $filter_range['start'] = ($filter_page - 1) * $_conf['k_rnum_range'] + 1;
        $filter_range['to'] = $filter_range['start'] + $_conf['k_rnum_range'] - 1;
    }
}

// }}}
// {{{ �t�B���^�l�ۑ�

$cachefile = $_conf['pref_dir'] . '/p2_res_filter.txt';

// �t�B���^�w�肪�Ȃ���ΑO��ۑ���ǂݍ��ށi�t�H�[���̃f�t�H���g�l�ŗ��p�j
if (!isset($GLOBALS['word'])) {

    if (file_exists($cachefile) and $res_filter_cont = file_get_contents($cachefile)) {
        $GLOBALS['res_filter'] = unserialize($res_filter_cont);
    }
    
// �t�B���^�w�肪�����
} else {

    // �{�^����������Ă����Ȃ�A�t�@�C���ɐݒ��ۑ�
    if (isset($_REQUEST['submit_filter'])) { // !isset($_REQUEST['idpopup'])
        if (empty($GLOBALS['popup_filter'])) {
            FileCtl::make_datafile($cachefile, $_conf['p2_perm']);
            if (false === file_put_contents($cachefile, serialize($GLOBALS['res_filter']), LOCK_EX)) {
                die("Error: cannot write file.");
            }
        }
    }
}

// }}}


//==================================================================
// ���C��
//==================================================================

if (!isset($aThread)) {
    $aThread =& new ThreadRead();
}

// ls�̃Z�b�g
if (!empty($ls)) {
    $aThread->ls = strip_tags(mb_convert_kana($ls, 'a'));
}

// {{{ idx�̓ǂݍ���

// host�𕪉�����idx�t�@�C���̃p�X�����߂�
if (!isset($aThread->keyidx)) {
    $aThread->setThreadPathInfo($host, $bbs, $key);
}

// �f�B���N�g����������΍��
// FileCtl::mkdirFor($aThread->keyidx);

$aThread->itaj = P2Util::getItaName($host, $bbs);
if (!$aThread->itaj) { $aThread->itaj = $aThread->bbs; }

// idx�t�@�C��������Γǂݍ���
if (file_exists($aThread->keyidx)) {
    $lines = file($aThread->keyidx);
    $idx_data = explode('<>', rtrim($lines[0]));
} else {
    $idx_data = array_fill(0, 12, null);
}

$aThread->getThreadInfoFromIdx();

// }}}

// preview >>1
if (!empty($_GET['onlyone'])) {
    $params = array('res_filter' => $res_filter);
    _printPreview1Html($aThread, $params);
    return;
}

// DAT�̃_�E�����[�h
if (empty($_GET['offline'])) {
    $aThread->downloadDat();
}

// DAT��ǂݍ���
$aThread->readDat();

// �I�t���C���w��ł����O���Ȃ���΁A���߂ċ����ǂݍ���
if (empty($aThread->datlines) && !empty($_GET['offline'])) {
    $aThread->downloadDat();
    $aThread->readDat();
}



$aThread->setTitleFromLocal(); // �^�C�g�����擾���Đݒ�

// {{{ �\�����X�Ԃ͈̔͂�ݒ肷��

if ($_conf['ktai']) {
    $before_respointer = $_conf['before_respointer_k'];
} else {
    $before_respointer = $_conf['before_respointer'];
}

// �擾�ς݂Ȃ�
if ($aThread->isKitoku()) {
    
    //�u�V�����X�̕\���v�̎��͓��ʂɂ�����ƑO�̃��X����\��
    if (!empty($_GET['nt'])) {
        if (substr($aThread->ls, -1) == "-") {
            $n = $aThread->ls - $before_respointer;
            if ($n < 1) { $n = 1; }
            $aThread->ls = "$n-";
        }
        
    } elseif (!$aThread->ls) {
        $from_num = $aThread->readnum +1 - $_conf['respointer'] - $before_respointer;
        if ($from_num < 1) {
            $from_num = 1;
        } elseif ($from_num > $aThread->rescount) {
            $from_num = $aThread->rescount - $_conf['respointer'] - $before_respointer;
        }
        $aThread->ls = "$from_num-";
    }
    
    if ($_conf['ktai'] && (!strstr($aThread->ls, "n"))) {
        $aThread->ls = $aThread->ls . "n";
    }
    
// ���擾�Ȃ�
} else {
    if (!$aThread->ls) {
        $aThread->ls = $_conf['get_new_res_l'];
    }
}

// �t�B���^�����O�̎��́Aall�Œ�Ƃ���
if (isset($GLOBALS['word'])) {
    $aThread->ls = 'all';
}

$aThread->lsToPoint();

// }}}

//===============================================================
// HTML�v�����g
//===============================================================
$ptitle_ht = hs($aThread->itaj) . ' / ' . hs($aThread->ttitle_hc);

if ($_conf['ktai']) {

    $GLOBALS['_filter_hits'] = NULL;
    if (isset($GLOBALS['word']) && strlen($GLOBALS['word'])) {
        $GLOBALS['_filter_hits'] = 0;
    }
    
    // �w�b�_�v�����g
    require_once P2_IPHONE_LIB_DIR . '/read_header_k.inc.php';
    
    if ($aThread->rescount) {
        require_once P2_IPHONE_LIB_DIR . '/ShowThreadK.php';
        $aShowThread =& new ShowThreadK($aThread);
        $aShowThread->datToHtml();
    }
    
    // �t�b�^�v�����g
    
    if ($GLOBALS['_filter_hits'] !== NULL) {
        $params = array(
            'prev_st'      => $prev_st,
            'next_st'      => $next_st,
            'filter_range' => $filter_range,
            'filter_page'  => $filter_page,
            'res_filter'   => $res_filter
        );
        $ar = getResetReadNaviFooterK($aThread, $params);
        extract($ar); // $read_navi_previous_btm, $read_navi_next_btm, $read_footer_navi_new_btm
    }
    
    require_once P2_IPHONE_LIB_DIR . '/read_footer_k.inc.php';
    
} else {

    // �w�b�_ �\��
    require_once P2_IPHONE_LIB_DIR . '/read_header.inc.php';
    ob_flush(); flush();
    
    //===========================================================
    // ���[�J��Dat��ϊ�����HTML�\��
    //===========================================================
    // ���X������A�����w�肪�����
    if (isset($GLOBALS['word']) && $aThread->rescount) {
    
        $all = $aThread->rescount;
        
        $GLOBALS['_filter_hits'] = 0;
        ?>
<script type="text/javascript">
<!--
document.writeln('<p><b id="filerstart"><?php eh($all); ?>���X�� <span id="searching"><?php eh($GLOBALS['_filter_hits']); ?></span>���X���q�b�g</b></p>');
var searching = document.getElementById('searching');

function filterCount(n){
    if (searching) {
        searching.innerHTML = n;
    }
}
-->
</script>
<?php
    }
    
    $debug && $profiler->enterSection('datToHtml');
    
    if ($aThread->rescount) {

        require_once P2_IPHONE_LIB_DIR . '/ShowThreadPc.php';
        $aShowThread =& new ShowThreadPc($aThread);
        
        $res1 = $aShowThread->quoteOne(); // >>1�|�b�v�A�b�v�p
        echo $res1['q'];

        $aShowThread->datToHtml();
    } else {
        $res1 = array();
    }
    
    $debug && $profiler->leaveSection("datToHtml");
    
    // �t�B���^���ʂ�\��
    if ($word && $aThread->rescount) {
        ?>
<script type="text/javascript">
<!--
var filerstart = document.getElementById('filerstart');
if (filerstart) {
    filerstart.style.backgroundColor = 'yellow';
    filerstart.style.fontWeight = 'bold';
}
-->
</script>
<?php
        if ($GLOBALS['_filter_hits'] > 5) {
            ?><p><b class="filtering"><?php eh($all); ?>���X�� <?php eh($GLOBALS['_filter_hits']); ?>���X���q�b�g</b></p><?php
        }
    }
    
    // �t�b�^HTML �\��
    require_once P2_IPHONE_LIB_DIR . '/read_footer.inc.php';

}

//=================================
// �㏈��
//=================================

// {{{ idx�̒l��ݒ�A�L�^

if ($aThread->rescount) {
    // �����̎��́A���ǐ����X�V���Ȃ�
    if (isset($GLOBALS['word']) and strlen($GLOBALS['word']) > 0) {
        $aThread->readnum = $idx_data[5];
    } else {
        $aThread->readnum = min($aThread->rescount, max(0, $idx_data[5], $aThread->resrange_readnum)); 
    }
    $newline = $aThread->readnum + 1; // $newline�͔p�~�\�肾���A����݊��p�ɔO�̂���

    // key.idx�ɋL�^
    P2Util::recKeyIdx($aThread->keyidx, array(
        $aThread->ttitle, $aThread->key, $idx_data[2], $aThread->rescount, '',
        $aThread->readnum, $idx_data[6], $idx_data[7], $idx_data[8], $newline,
        $idx_data[10], $idx_data[11], $aThread->datochiok
    ));
}

// }}}

// �������L�^
if ($aThread->rescount) {
    _recRecent(array(
        $aThread->ttitle, $aThread->key, $idx_data[2], '', '', $aThread->readnum, $idx_data[6],
        $idx_data[7], $idx_data[8], $newline, $aThread->host, $aThread->bbs
    ));
}

// NG���ځ[����L�^
NgAbornCtl::saveNgAborns();


exit;



//===============================================================================
// �֐� �i���̃t�@�C�����ł̂ݗ��p�j
//===============================================================================
/**
 * �X���b�h���w�肷��
 *
 * @return  array|false
 */
function _detectThread()
{
    global $_conf;
    
    $ls = null;
    
    // �X��URL�̒��ڎw��
    if (($url = geti($_GET['nama_url'])) || ($url = geti($_GET['url']))) { 
            
        $url = trim($url);
        
        // 2ch or pink - http://choco.2ch.net/test/read.cgi/event/1027770702/
        if (preg_match('{http://([^/]+\\.(2ch\\.net|bbspink\\.com))/test/read\\.cgi/([^/]+)/([0-9]+)/?([^/]+)?}', $url, $matches)) {
            $host = $matches[1];
            $bbs  = $matches[3];
            $key  = $matches[4];
            $ls   = geti($matches[5]);
        
        // c-docomo c-au c-other http://c-au.2ch.net/test/--3!mail=sage/operate/1159594301/519-n
        } elseif (preg_match('{http://((c-docomo|c-au|c-other)\\.2ch\\.net)/test/([^/]+)/([^/]+)/([0-9]+)/?([^/]+)?}', $url, $m)) {
            require_once P2_LIB_DIR . '/BbsMap.class.php';
            if ($mapped_host = BbsMap::get2chHostByBbs($m[4])) {
                $host = $mapped_host;
                $bbs  = $m[4];
                $key  = $m[5];
                $ls   = geti($m[6]);
            }
        
        // 2ch or pink �ߋ����Ohtml - http://pc.2ch.net/mac/kako/1015/10153/1015358199.html
        } elseif (preg_match("/(http:\/\/([^\/]+\.(2ch\.net|bbspink\.com))(\/[^\/]+)?\/([^\/]+)\/kako\/\d+(\/\d+)?\/(\d+)).html/", $url, $matches) ){ //2ch pink �ߋ����Ohtml
            $host = $matches[2];
            $bbs  = $matches[5];
            $key  = $matches[7];
            $kakolog_uri = $matches[1];
            $_GET['kakolog'] = $kakolog_uri;
            
        // �܂����������JBBS - http://kanto.machibbs.com/bbs/read.pl?BBS=kana&KEY=1034515019
        } elseif (preg_match("/http:\/\/([^\/]+\.machibbs\.com|[^\/]+\.machi\.to)\/bbs\/read\.(pl|cgi)\?BBS=([^&]+)&KEY=([0-9]+)(&START=([0-9]+))?(&END=([0-9]+))?[^\"]*/", $url, $matches) ){
            $host = $matches[1];
            $bbs  = $matches[3];
            $key  = $matches[4];
            $ls   = geti($matches[6]) ."-". geti($matches[8]);
            
        } elseif (preg_match("{http://((jbbs\.livedoor\.jp|jbbs\.livedoor.com|jbbs\.shitaraba\.com)(/[^/]+)?)/bbs/read\.(pl|cgi)\?BBS=([^&]+)&KEY=([0-9]+)(&START=([0-9]+))?(&END=([0-9]+))?[^\"]*}", $url, $matches)) {
            $host = $matches[1];
            $bbs  = $matches[5];
            $key  = $matches[6];
            $ls   = geti($matches[8]) ."-". geti($matches[10]);
            
        // �������JBBS http://jbbs.livedoor.com/bbs/read.cgi/computer/2999/1081177036/-100 
        } elseif (preg_match("{http://(jbbs\.livedoor\.jp|jbbs\.livedoor.com|jbbs\.shitaraba\.com)/bbs/read\.cgi/(\w+)/(\d+)/(\d+)/((\d+)?-(\d+)?)?[^\"]*}", $url, $matches) ){
            $host = $matches[1] . "/" . $matches[2];
            $bbs  = $matches[3];
            $key  = $matches[4];
            $ls   = geti($matches[5]);
            
        // �O���� read.cgi �`�� http://ex14.vip2ch.com/test/read.cgi/operate/1161701941/ 
        } elseif (preg_match('{http://([^/]+)/test/read\\.cgi/(\\w+)/(\\d+)/?([^/]+)?}', $url, $matches)) {
            $host = $matches[1];
            $bbs  = $matches[2];
            $key  = $matches[3];
            $ls   = geti($matches[4]);
        }
    
    } else {
        !empty($_GET['host'])   and $host = $_GET['host'];  // "pc.2ch.net"
        !empty($_POST['host'])  and $host = $_POST['host'];
        isset($_GET['bbs'])     and $bbs  = $_GET['bbs'];   // "php"
        isset($_POST['bbs'])    and $bbs  = $_POST['bbs'];
        isset($_GET['key'])     and $key  = $_GET['key'];   // "1022999539"
        isset($_POST['key'])    and $key  = $_POST['key'];
        !empty($_GET['ls'])     and $ls   = $_GET['ls'];    // "all"
        !empty($_POST['ls'])    and $ls   = $_POST['ls'];
    }
    
    if (empty($host) || !isset($bbs) || !isset($key)) {
        $err = $_conf['read_php'] . ' �X���b�h�̎w�肪�ςł��B';
        $msg = null;
        if ($url) {
            if (preg_match('/^http/', $url)) {
                $msg = sprintf('<a href="%1$s">%1$s</a>', hs($url));
            } else {
                $msg = hs($url);
            }
        }
        p2die($err, $msg);
    }
    
    if (P2Validate::host($host) || P2Validate::bbs($bbs) || P2Validate::key($key)) {
        p2die('�s���Ȉ����ł�');
    }

    return array($host, $bbs, $key, $ls);
}

/**
 * �ŋߓǂ񂾃X���ɋL�^����
 *
 * @param   array  $data_ar
 * @return  boolean
 */
function _recRecent($data_ar)
{
    global $_conf;
    
    $data_line = implode('<>', $data_ar);
    $host = $data_ar[10];
    $key  = $data_ar[1];
    
    // ����headline�͍ŋߓǂ񂾃X���ɋL�^���Ȃ��悤�ɂ��Ă݂�
    if ($host == 'headline.2ch.net') {
        return true;
    }
    
    if (false === FileCtl::make_datafile($_conf['rct_file'], $_conf['rct_perm'])) {
        return false;
    }
    
    $lines = file($_conf['rct_file']);
    $newlines = array();

    // �ŏ��ɏd���v�f���폜���Ă���
    if (is_array($lines)) {
        foreach ($lines as $line) {
            $line = rtrim($line);
            $lar = explode('<>', $line);
            if ($lar[1] == $key) { continue; } // key�ŏd�����
            if (!$lar[1]) { continue; } // key�̂Ȃ����͕̂s���f�[�^
            $newlines[] = $line;
        }
    }
    
    // �V�K�f�[�^�ǉ�
    array_unshift($newlines, $data_line);

    while (sizeof($newlines) > $_conf['rct_rec_num']) {
        array_pop($newlines);
    }
    
    // ��������
    if ($newlines) {
        $cont = '';
        foreach ($newlines as $l) {
            $cont .= $l . "\n";
        }

        if (false === FileCtl::filePutRename($_conf['rct_file'], $cont)) {
            $errmsg = sprintf('p2 error: %s(), FileCtl::filePutRename() failed.', __FUNCTION__);
            trigger_error($errmsg, E_USER_WARNING);
            return false;
        }
    }
    
    return true;
}

/**
 * preview >>1
 *
 * @return  void  HTML�o��
 */
function _printPreview1Html(&$aThread, $params)
{
    global $_conf, $STYLE, $_login;
    global $_filter_hits;
    
    // $res_filter
    extract($params);
    
    $aThread->ls = '1';
    
    // �K���������m�ł͂Ȃ����֋X�I��
    if (!isset($aThread->rescount) and !empty($_GET['rc'])) {
        $aThread->rescount = intval($_GET['rc']);
        $aThread->lsToPoint();
    }
    
    $body = $aThread->previewOne();
    $ptitle_ht = hs($aThread->itaj) . ' / ' . hs($aThread->ttitle_hc);

  
        $read_header_inc_php = P2_IPHONE_LIB_DIR . '/read_header_k.inc.php';
        $read_footer_inc_php = P2_IPHONE_LIB_DIR . '/read_footer_k.inc.php';
    
    require_once $read_header_inc_php;

    echo $body;

    require_once $read_footer_inc_php;
}
