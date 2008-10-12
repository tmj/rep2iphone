<?php
/*
    p2 -  �X���b�h�T�u�W�F�N�g�\���X�N���v�g
    �t���[��������ʁA�E�㕔��

    subject_new.php �ƌZ��Ȃ̂ŁA�ꏏ�ɖʓ|���݂邱��
*/

require_once './conf/conf.inc.php';
require_once './iphone/conf.inc.php';

require_once P2_LIB_DIR . '/threadlist.class.php';
require_once P2_LIB_DIR . '/thread.class.php';
require_once P2_LIB_DIR . '/filectl.class.php';
require_once P2_LIB_DIR . '/P2Validate.php';

require_once P2_LIB_DIR . '/subject.funcs.php';

$GLOBALS['debug'] && $GLOBALS['profiler']->enterSection('HEAD');

$_login->authorize(); // ���[�U�F��

//============================================================
// �ϐ��ݒ�
//============================================================
$newtime = date('gis');
$nowtime = time();

$abornoff_st = '���ځ[�����';
$deletelog_st = '���O���폜';

$sb_disp_from = (int)geti($_GET['from'], geti($_POST['from'], 1));

// {{{ �z�X�g�A�A���[�h�ݒ�

$host   = geti($_GET['host'],   geti($_POST['host']));
$bbs    = geti($_GET['bbs'],    geti($_POST['bbs']));
$spmode = geti($_GET['spmode'], geti($_POST['spmode']));

if (!$host || !strlen($bbs) and !$spmode) {
    p2die('�K�v�Ȉ������w�肳��Ă��܂���');
}

if (
    $host && P2Validate::host($host) 
    || strlen($bbs) && P2Validate::bbs($bbs) 
    || $spmode && P2Validate::spmode($spmode)
) {
    p2die('�s���Ȉ����ł�');
}

// }}}
// {{{ p2_setting, sb_keys �ݒ�

if ($spmode) {
    $p2_setting_txt = $_conf['pref_dir'] . '/p2_setting_' . $spmode . '.txt';
    $sb_keys_b_txt = null;
    $sb_keys_txt = null;
    
} else {
    $idx_host_dir = P2Util::idxDirOfHost($host);
    $idx_bbs_dir_s = $idx_host_dir . '/' . $bbs . '/';
    
    $p2_setting_txt = $idx_bbs_dir_s . 'p2_setting.txt';
    $sb_keys_b_txt =  $idx_bbs_dir_s . 'p2_sb_keys_b.txt';
    $sb_keys_txt =    $idx_bbs_dir_s . 'p2_sb_keys.txt';

    // �X�V���Ȃ��ꍇ�́A2�O�̂ƂP�O�̂��ׂāA�V�K�X���𒲂ׂ�
    if (!empty($_REQUEST['norefresh']) || !empty($_REQUEST['word'])) {
        if ($prepre_sb_cont = @file_get_contents($sb_keys_b_txt)) {
            $prepre_sb_keys = unserialize($prepre_sb_cont);
        }
    } else {
        if ($pre_sb_cont = @file_get_contents($sb_keys_txt)) {
            $pre_sb_keys = unserialize($pre_sb_cont);
        }
    }
}

// }}}
// {{{ p2_setting �ǂݍ��݁A�Z�b�g

$p2_setting = $pre_setting = sbLoadP2SettingTxt($p2_setting_txt);

$p2_setting = sbSetP2SettingWithQuery($p2_setting);

if (isset($_GET['sb_view']))  { $sb_view = $_GET['sb_view']; }
if (isset($_POST['sb_view'])) { $sb_view = $_POST['sb_view']; }
if (empty($sb_view)) { $sb_view = "normal"; }

// }}}
// {{{ �\�[�g�̎w��

if (!empty($_POST['sort'])) {
    $GLOBALS['now_sort'] = $_POST['sort'];
} elseif (!empty($_GET['sort'])) {
    $GLOBALS['now_sort'] = $_GET['sort'];
}

if (empty($GLOBALS['now_sort'])) {
    if (!empty($p2_setting['sort'])) {
        $GLOBALS['now_sort'] = $p2_setting['sort'];
    } else {
        if (empty($spmode)) {
            $GLOBALS['now_sort'] = $_conf['sb_sort_ita'] ? $_conf['sb_sort_ita'] : 'ikioi'; // ����
        } else {
            $GLOBALS['now_sort'] = 'midoku'; // �V��
        }
    }
}

// }}}
// {{{ �\���X���b�h���ݒ�

$threads_num_max = 2000;

if (empty($spmode) || $spmode == 'news') {
    $threads_num = $p2_setting['viewnum'];
} elseif ($spmode == 'recent') {
    $threads_num = $_conf['rct_rec_num'];
} elseif ($spmode == 'res_hist') {
    $threads_num = $_conf['res_hist_rec_num'];
} else {
    $threads_num = 2000;
}

if ($p2_setting['viewnum'] == 'all' or $sb_view == 'shinchaku' or $sb_view == 'edit' or isset($_GET['word']) or $_conf['ktai']) {
    $threads_num = $threads_num_max;
}

// }}}

// �N�G���[����t�B���^���[�h���Z�b�g����
_setFilterWord();
//============================================================
// ����ȑO����
//============================================================
// {{{ �폜

if (!empty($_GET['dele']) or (isset($_POST['submit']) and $_POST['submit'] == $deletelog_st)) {
    if ($host && $bbs) {
        require_once P2_LIB_DIR . '/dele.inc.php';
        if ($_POST['checkedkeys']) {
            $dele_keys = $_POST['checkedkeys'];
        } else {
            $dele_keys = array($_GET['key']);
        }
        deleteLogs($host, $bbs, $dele_keys);
    }
    
// }}}

// ���C�ɓ���X���b�h
} elseif (isset($_GET['setfav']) && $_GET['key'] && $host && $bbs) {
    require_once P2_LIB_DIR . '/setfav.inc.php';
    setFav($host, $bbs, $_GET['key'], $_GET['setfav']);

// �a������
} elseif (isset($_GET['setpal']) && $_GET['key'] && $host && $bbs) {
    require_once P2_LIB_DIR . '/setpalace.inc.php';
    setPal($host, $bbs, $_GET['key'], $_GET['setpal']);

// ���ځ[��X���b�h����
} elseif ((isset($_POST['submit']) and $_POST['submit'] == $abornoff_st) && $host && $bbs && !empty($_POST['checkedkeys'])) {
    require_once P2_LIB_DIR . '/settaborn_off.inc.php';
    settaborn_off($host, $bbs, $_POST['checkedkeys']);

// �X���b�h���ځ[��
} elseif (isset($_GET['taborn']) && !is_null($_GET['key']) && $host && $bbs) {
    require_once P2_LIB_DIR . '/settaborn.inc.php';
    settaborn($host, $bbs, $_GET['key'], $_GET['taborn']);
}

//============================================================
// ���C��
//============================================================

$ta_keys = array();

$aThreadList =& new ThreadList();

// �ƃ��[�h�̃Z�b�g ===================================
if ($spmode) {
    if ($spmode == 'taborn' or $spmode == 'soko') {
        $aThreadList->setIta($host, $bbs, P2Util::getItaName($host, $bbs));
    }
    $aThreadList->setSpMode($spmode);
} else {
    // if(!$p2_setting['itaj']){$p2_setting['itaj'] = P2Util::getItaName($host, $bbs);}
    $aThreadList->setIta($host, $bbs, $p2_setting['itaj']);
    
    //�X���b�h���ځ[�񃊃X�g�Ǎ�
    $ta_keys = P2Util::getThreadAbornKeys($aThreadList->host, $aThreadList->bbs);
    $ta_num = sizeOf($ta_keys);

}

// �\�[�X���X�g�Ǎ�
$lines = $aThreadList->readList();

// ���C�ɃX�����X�g �Ǎ�
//$fav_keys = P2Util::getFavListData();


$GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('HEAD');

//============================================================
// ���ꂼ��̍s���
//============================================================
$GLOBALS['debug'] && $GLOBALS['profiler']->enterSection('FORLOOP');

$online_num = 0;
$shinchaku_num = 0;

$linesize = sizeof($lines);

for ($x = 0; $x < $linesize; $x++) {

    $l = rtrim($lines[$x]);
    
    $aThread =& new Thread();
    
    if ($aThreadList->spmode != 'taborn' and $aThreadList->spmode != 'soko') {
        $aThread->torder = $x + 1;
    }

    // ���f�[�^�ǂݍ���
    // spmode
    if ($aThreadList->spmode) {
        switch ($aThreadList->spmode) {
        case "recent":  // ����
            $aThread->getThreadInfoFromExtIdxLine($l);
            $aThread->itaj = P2Util::getItaName($aThread->host, $aThread->bbs);
            if (!$aThread->itaj) {$aThread->itaj = $aThread->bbs;}
            break;
        case "res_hist":    // �������ݗ���
            $aThread->getThreadInfoFromExtIdxLine($l);
            $aThread->itaj = P2Util::getItaName($aThread->host, $aThread->bbs);
            if (!$aThread->itaj) {$aThread->itaj= $aThread->bbs;}
            break;
        case "fav":     // ���C��
            $aThread->getThreadInfoFromExtIdxLine($l);
            $aThread->itaj = P2Util::getItaName($aThread->host, $aThread->bbs);
            if (!$aThread->itaj) {$aThread->itaj = $aThread->bbs;}
            break;
        case "taborn":  // �X���b�h���ځ[��
            $la = explode('<>', $l);
            $aThread->key = $la[1];
            $aThread->host = $aThreadList->host;
            $aThread->bbs = $aThreadList->bbs;
            break;
        case "soko":    // dat�q��
            $la = explode('<>', $l);
            $aThread->key = $la[1];
            $aThread->host = $aThreadList->host;
            $aThread->bbs = $aThreadList->bbs;
            break;
        case "palace":  // �X���̓a��
            $aThread->getThreadInfoFromExtIdxLine($l);
            $aThread->itaj = P2Util::getItaName($aThread->host, $aThread->bbs);
            if (!$aThread->itaj) {$aThread->itaj = $aThread->bbs;}
            break;
        case "news":    // �j���[�X�̐���
            $aThread->isonline = true;
            $aThread->key = $l['key'];
            $aThread->setTtitle($l['ttitle']);
            $aThread->rescount = $l['rescount'];
            $aThread->host = $l['host'];
            $aThread->bbs = $l['bbs'];

            $aThread->itaj = P2Util::getItaName($aThread->host, $aThread->bbs);
            if (!$aThread->itaj) {$aThread->itaj = $aThread->bbs;}
            break;
        }
        
    // subject (not spmode �܂蕁�ʂ̔�)
    } else {
        $aThread->getThreadInfoFromSubjectTxtLine($l);

        $aThread->host = $aThreadList->host;
        $aThread->bbs = $aThreadList->bbs;
    }

    // host��bbs��key���s���Ȃ�X�L�b�v
    if (!($aThread->host && $aThread->bbs && $aThread->key)) {
        unset($aThread);
        continue;
    } 
    
    
    // �����ň�U�X���b�h���X�g�ɂ܂Ƃ߂āA�L���b�V���������悤���Ǝv�������A����������(750K��2M)�������������̂ł�߂Ă������B
    
    
    // {{{ �V�������ǂ���(for subject)
    
    if (!$aThreadList->spmode) {
        if (!empty($_REQUEST['norefresh']) || !empty($_REQUEST['word'])) {
            if (empty($prepre_sb_keys[$aThread->key])) {
                $aThread->new = true;
            }
        } else {
            if (empty($pre_sb_keys[$aThread->key])) {
                $aThread->new = true;
            }
            $subject_keys[$aThread->key] = true;
        }
    }
    
    // }}}
    // {{{ ���[�h�t�B���^(for subject)
    
    $GLOBALS['debug'] && $GLOBALS['profiler']->enterSection('word_filter_for_sb');
    if (!$aThreadList->spmode || $aThreadList->spmode == "news" and (strlen($GLOBALS['word_fm']) > 0)) {
        
        $aThread->setThreadPathInfo($aThread->host, $aThread->bbs, $aThread->key);

        // �}�b�`���Ȃ���΃X�L�b�v
        if (!_matchSbFilter($aThread)) {
            unset($aThread);
            $GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('word_filter_for_sb');
            continue;
    
        // �}�b�`������
        } else {
            $GLOBALS['sb_mikke_num'] = isset($GLOBALS['sb_mikke_num']) ? $GLOBALS['sb_mikke_num'] + 1 : 1;
            if ($_conf['ktai']) {
                if (is_string($_conf['k_filter_marker'])) {
                    $aThread->ttitle_ht = StrCtl::filterMarking($GLOBALS['word_fm'], $aThread->ttitle_hs, $_conf['k_filter_marker']);
                } else {
                    $aThread->ttitle_ht = $aThread->ttitle_hs;
                }
            } else {
                $aThread->ttitle_ht = StrCtl::filterMarking($GLOBALS['word_fm'], $aThread->ttitle_hs);
            }
        }
    } elseif (!$aThreadList->spmode && !empty($GLOBALS['wakati_words'])) {
        // �ގ��X������
        if (!_setSbSimilarity($aThread) || $aThread->similarity < $_conf['expack.min_similarity']) {
            unset($aThread);
            $GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('word_filter_for_sb');
            continue;
        }
        if ($_conf['ktai']) {
            if (is_string($_conf['k_filter_marker'])) {
                $aThread->ttitle_ht = StrCtl::filterMarking($GLOBALS['wakati_hl_regex'], $aThread->ttitle_ht, $_conf['k_filter_marker']);
            }
        } else {
            $aThread->ttitle_ht = StrCtl::filterMarking($GLOBALS['wakati_hl_regex'], $aThread->ttitle_ht);
        }
    }
    $GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('word_filter_for_sb');
    
    // }}}
    // {{{ �X���b�h���ځ[��`�F�b�N
    
    $GLOBALS['debug'] && $GLOBALS['profiler']->enterSection('taborn_check_continue');
    if ($aThreadList->spmode != "taborn" and isset($ta_keys[$aThread->key]) && $ta_keys[$aThread->key]) { 
        unset($ta_keys[$aThread->key]);
        $GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('taborn_check_continue');
        continue; // ���ځ[��X���̓X�L�b�v
    }
    $GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('taborn_check_continue');
    
    $aThread->setThreadPathInfo($aThread->host, $aThread->bbs, $aThread->key);
    // �����X���b�h�f�[�^��idx����擾
    $aThread->getThreadInfoFromIdx();

    // }}}
    // {{{ favlist�`�F�b�N
    /*
    $GLOBALS['debug'] && $GLOBALS['profiler']->enterSection('favlist_check');
    // if ($x <= $threads_num) {
        if ($aThreadList->spmode != 'taborn' and isset($fav_keys[$aThread->key]) && $fav_keys[$aThread->key] == $aThread->bbs) {
            $aThread->fav = 1;
            unset($fav_keys[$aThread->key]);
        }
    // }
    $GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('favlist_check');
    */
    // }}}
    
    // �� spmode(�a������Anews������)�Ȃ� ====================================
    if ($aThreadList->spmode && $aThreadList->spmode != "news" && $sb_view != "edit") { 
        
        // {{{ subject.txt ����DL�Ȃ痎�Ƃ��ăf�[�^��z��Ɋi�[����
        
        if (empty($subject_txts["$aThread->host/$aThread->bbs"])) {

            require_once P2_LIB_DIR . '/SubjectTxt.php';
            $aSubjectTxt =& new SubjectTxt($aThread->host, $aThread->bbs);
            
            $GLOBALS['debug'] && $GLOBALS['profiler']->enterSection('subthre_read');
            if ($aThreadList->spmode == 'soko' or $aThreadList->spmode == 'taborn') {

                if (is_array($aSubjectTxt->subject_lines)) {
                    $it = 1;
                    foreach ($aSubjectTxt->subject_lines as $asbl) {
                        if (preg_match("/^([0-9]+)\.(dat|cgi)(,|<>)(.+) ?(\(|�i)([0-9]+)(\)|�j)/", $asbl, $matches)) {
                            $akey = $matches[1];
                            $subject_txts["$aThread->host/$aThread->bbs"][$akey]['ttitle'] = rtrim($matches[4]);
                            $subject_txts["$aThread->host/$aThread->bbs"][$akey]['rescount'] = $matches[6];
                            $subject_txts["$aThread->host/$aThread->bbs"][$akey]['torder'] = $it;
                        }
                        $it++;
                    }
                }
                
            } else {
                $subject_txts["$aThread->host/$aThread->bbs"] = $aSubjectTxt->subject_lines;

            }
            $GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('subthre_read');
        }
        
        // }}}
        // {{{ �X�������擾����
        
        $GLOBALS['debug'] && $GLOBALS['profiler']->enterSection('subthre_check');
        
        if ($aThreadList->spmode == "soko" or $aThreadList->spmode == "taborn") {
        
            // �I�����C����ɑ��݂���Ȃ�
            if (!empty($subject_txts[$aThread->host . "/" . $aThread->bbs][$aThread->key])) {
            
                // �q�ɂ̓I�����C�����܂܂Ȃ�
                if ($aThreadList->spmode == "soko") {
                    unset($aThread);
                    continue;
                    
                } elseif ($aThreadList->spmode == "taborn") {
                    // $aThread->getThreadInfoFromSubjectTxtLine($l); // subject.txt ����X�����擾
                    $aThread->isonline = true;
                    $ttitle = $subject_txts["$aThread->host/$aThread->bbs"][$aThread->key]['ttitle'];
                    $aThread->setTtitle($ttitle);
                    $aThread->rescount = $subject_txts["$aThread->host/$aThread->bbs"][$aThread->key]['rescount'];
                    if ($aThread->readnum) {
                        $aThread->unum = $aThread->rescount - $aThread->readnum;
                        // machi bbs ��sage��subject�̍X�V���s���Ȃ������Ȃ̂Œ������Ă���
                        if ($aThread->unum < 0) { $aThread->unum = 0; }
                    }
                    $aThread->torder = $subject_txts["$aThread->host/$aThread->bbs"][$aThread->key]['torder'];
                }

            }
            
        } else {
        
            if (!empty($subject_txts[$aThread->host . '/' . $aThread->bbs])) {
                $it = 1;
                foreach ($subject_txts[$aThread->host . '/' . $aThread->bbs] as $l) {
                    if (preg_match('/^' . preg_quote($aThread->key, '/') . '/', $l)) {
                        // subject.txt ����X�����擾
                        $aThread->getThreadInfoFromSubjectTxtLine($l);
                        break;
                    }
                    $it++;
                }
            }
        
        }
        $GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('subthre_check');
        
        // }}}
        
        if ($aThreadList->spmode == "taborn") {
            if (!$aThread->torder) { $aThread->torder = '-'; }
        }

        
        // �V���̂�(for spmode)
        
        if ($sb_view == 'shinchaku' and !isset($_REQUEST['word'])) {
            if ($aThread->unum < 1) {
                unset($aThread);
                continue;
            }
        }

        // {{{ ���[�h�t�B���^(for spmode)
        
        if (strlen($GLOBALS['word_fm']) > 0) {

            // �}�b�`���Ȃ���΃X�L�b�v
            if (!_matchSbFilter($aThread)) {
                unset($aThread);
                continue;
        
            // �}�b�`������
            } else {
                $GLOBALS['sb_mikke_num'] = isset($GLOBALS['sb_mikke_num']) ? $GLOBALS['sb_mikke_num'] + 1 : 1;
                if ($_conf['ktai']) {
                    if (is_string($_conf['k_filter_marker'])) {
                        $aThread->ttitle_ht = StrCtl::filterMarking($GLOBALS['word_fm'], $aThread->ttitle_hs, $_conf['k_filter_marker']);
                    } else {
                        $aThread->ttitle_ht = $aThread->ttitle_hs;
                    }
                } else {
                    $aThread->ttitle_ht = StrCtl::filterMarking($GLOBALS['word_fm'], $aThread->ttitle_hs);
                }
            }
        }
        
        // }}}
    }
    
    $GLOBALS['debug'] && $GLOBALS['profiler']->enterSection('FORLOOP_HIP');
    
    // subjexct����rescount�����Ȃ������ꍇ�́Agotnum�𗘗p����B
    if ((!$aThread->rescount) and $aThread->gotnum) {
        $aThread->rescount = $aThread->gotnum;
    }
    if (!$aThread->ttitle_ht) { $aThread->ttitle_ht = $aThread->ttitle_hd; }
    
    // �V������
    if ($aThread->unum > 0) {
        $shinchaku_num += $aThread->unum; // �V����set
    } elseif ($aThread->fav) { // ���C�ɃX��
        ;
    } elseif ($aThread->new) { // �V�K�X��
        ;
    // �����X��
    } elseif ($_conf['viewall_kitoku'] && $aThread->isKitoku()) {
        ;
        
    } else {
        // �g�сA�j���[�X�`�F�b�N�ȊO��
        if (!$_conf['ktai'] and $spmode != "news") {
            // �w�萔���z���Ă�����J�b�g
            if ($x >= $threads_num) {
                unset($aThread);
                $GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('FORLOOP_HIP');
                continue;
            }
        }
    }
    
    // {{{ �V���\�[�g�̕֋X�� �i���擾�X���b�h�́junum ���Z�b�g����
    
    if (!isset($aThread->unum)) {
        if ($aThreadList->spmode == "recent" or $aThreadList->spmode == "res_hist" or $aThreadList->spmode == "taborn") {
            $aThread->unum = -0.1;
        } else {
            $aThread->unum = $_conf['sort_zero_adjust'];
        }
    }
    
    // }}}
    
    // �����̃Z�b�g
    $aThread->setDayRes($nowtime);
    
    // ������set
    if ($aThread->isonline) { 
        $online_num++;
    }
    
    // ���X�g�ɒǉ�
    $aThreadList->addThread($aThread);

    unset($aThread);
    
    $GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('FORLOOP_HIP');
}

$GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('FORLOOP');

$GLOBALS['debug'] && $GLOBALS['profiler']->enterSection('FOOT');

// ����dat�������Ă���X���͎����I�ɂ��ځ[�����������
if ($resultStr = _autoTAbornOff($aThreadList, $ta_keys)) {
    P2Util::pushInfoHtml(
        sprintf(
            '<div class="info">�@p2 info: DAT���������X���b�h���ځ[��������������܂��� - %s</div>',
            hs($resultStr)
        )
    );
}

// �\�[�g
_sortThreads($aThreadList);

//===============================================================
// �v�����g
//===============================================================
// �g��
if ($_conf['ktai']) {
    
    // {{{ �q�ɂ�torder�t�^
    
    if ($aThreadList->spmode == "soko") {
        if ($aThreadList->threads) {
            $soko_torder = 1;
            $newthreads = array();
            foreach ($aThreadList->threads as $at) {
                $at->torder = $soko_torder++;
                $newthreads[] = $at;
                unset($at);
            }
            $aThreadList->threads =& $newthreads;
            unset($newthreads);
        }
    }
    
    // }}}
    // {{{ �\��������
    
    $aThreadList->num = sizeof($aThreadList->threads); // �Ȃ�ƂȂ��O�̂���
    $sb_disp_all_num = $aThreadList->num;
    
    $disp_navi = P2Util::getListNaviRange($sb_disp_from , $_conf['k_sb_disp_range'], $sb_disp_all_num);

    $newthreads = array();
    for ($i = $disp_navi['from']; $i <= $disp_navi['end']; $i++) {
        if ($aThreadList->threads[$i-1]) {
            $newthreads[] =& $aThreadList->threads[$i-1];
        }
    }
    $aThreadList->threads =& $newthreads;
    unset($newthreads);
    $aThreadList->num = sizeof($aThreadList->threads);
    
    // }}}
    
    // �w�b�_HTML�v�����g
    require_once P2_IPHONE_LIB_DIR . '/sb_header_k.inc.php';

    // ���C��HTML�v�����g
    echo '<ul><li class="group">�X���ꗗ</li>';
    require_once P2_IPHONE_LIB_DIR . '/sb_print_k.inc.php'; // �X���b�h�T�u�W�F�N�g���C������HTML�\���֐�
    sb_print_k($aThreadList);
    echo '</ul>';
    // �t�b�^HTML�v�����g
    require_once P2_IPHONE_LIB_DIR . '/sb_footer_k.inc.php';

} 

//==============================================================
// �㏈��
//==============================================================

// p2_setting�isb�ݒ�j �L�^
_saveSbSetting($p2_setting_txt, $p2_setting, $pre_setting);

// $subject_keys ���V���A���C�Y���ĕۑ�����
_saveSubjectKeys($subject_keys, $sb_keys_txt, $sb_keys_b_txt);

$debug && $profiler->leaveSection('FOOT');


exit;


//==================================================================
// �֐��i���̃t�@�C�����ł̂ݗ��p�j
//==================================================================
/**
 * ����dat�������Ă���X���͎����I�ɂ��ځ[�����������
 * $ta_keys �͂��ځ[�񃊃X�g�ɓ����Ă�������ǁA���ځ[�񂳂ꂸ�Ɏc�����X������
 */
function _autoTAbornOff(&$aThreadList, $ta_keys)
{
    global $ta_num;
    
    $result = '';
    
    // �ςɏ��Ȃ��ꍇ�́A�����������Ȃ�
    if ($aThreadList->num <= 1) {
        return $result;
    }
    
    $GLOBALS['debug'] && $GLOBALS['profiler']->enterSection('abornoff');
    
    if (!$aThreadList->spmode and !$GLOBALS['word'] and !$GLOBALS['wakati_word'] and $aThreadList->threads and $ta_keys) {
        require_once P2_LIB_DIR . '/settaborn_off.inc.php';
        $ta_vkeys = array_keys($ta_keys);
        settaborn_off($aThreadList->host, $aThreadList->bbs, $ta_vkeys);
        foreach ($ta_vkeys as $k) {
            $ta_num--;
            if ($k) {
                $result .= "key:$k ";
            }
        }
    }
    
    $GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('abornoff');
    
    return $result;
}

/**
 * �X���ꗗ�i$aThreadList->threads�j���\�[�g����
 *
 * @return  void
 */
function _sortThreads(&$aThreadList)
{
    global $_conf;
    
    $GLOBALS['debug'] && $GLOBALS['profiler']->enterSection('sort');
    
    if ($aThreadList->threads) {
        if (!empty($GLOBALS['wakati_words'])) {
            $GLOBALS['now_sort'] = 'title';
            usort($aThreadList->threads, "cmp_similarity");
        } elseif ($GLOBALS['now_sort'] == "midoku") {
            if ($aThreadList->spmode == "soko") {
                usort($aThreadList->threads, "cmp_key");
            } else {
                usort($aThreadList->threads, "cmp_midoku");
            }
        } elseif ($GLOBALS['now_sort'] == "res") {
            usort($aThreadList->threads, "cmp_res");
        } elseif ($GLOBALS['now_sort'] == "title") {
            usort($aThreadList->threads, "cmp_title");
        } elseif ($GLOBALS['now_sort'] == "ita") {
            usort($aThreadList->threads, "cmp_ita");
        } elseif ($GLOBALS['now_sort'] == "ikioi" || $GLOBALS['now_sort'] == "spd") {
            if ($_conf['cmp_dayres_midoku']) {
                usort($aThreadList->threads, "cmp_dayres_midoku");
            } else {
                usort($aThreadList->threads, "cmp_dayres");
            }
        } elseif ($GLOBALS['now_sort'] == "bd") {
            usort($aThreadList->threads, "cmp_key");
        } elseif ($GLOBALS['now_sort'] == "fav") {
            usort($aThreadList->threads, "cmp_fav");
        } if ($GLOBALS['now_sort'] == "no") {
            if ($aThreadList->spmode == "soko") {
                usort($aThreadList->threads, "cmp_key");
            } else {
                usort($aThreadList->threads, "cmp_no");
            }
        }
    }

    // �j���[�X�`�F�b�N
    if ($aThreadList->spmode == "news") {
        for ($i = 0; $i < $threads_num ; $i++) {
            if ($aThreadList->threads) {
                $newthreads[] = array_shift($aThreadList->threads);
            }
        }
        $aThreadList->threads = $newthreads;
        $aThreadList->num = sizeof($aThreadList->threads);
    }
    
    $GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('sort');
}

/**
 * p2_setting �L�^����
 *
 * @return  boolean
 */
function _saveSbSetting($p2_setting_txt, $p2_setting, $pre_setting)
{
    global $_conf;

    if (
        $pre_setting['viewnum'] != $p2_setting['viewnum']
        or $pre_setting['sort'] != $GLOBALS['now_sort']
        or $pre_setting_itaj != $p2_setting_itaj
    ) {
        if (!empty($_POST['sort'])) {
            $p2_setting['sort'] = $_POST['sort'];
        } elseif (!empty($_GET['sort'])) {
            $p2_setting['sort'] = $_GET['sort'];
        }
        if ($p2_setting) {
            if (false === FileCtl::make_datafile($p2_setting_txt, $_conf['p2_perm'])) {
                return false;
            }
            $p2_setting_cont = serialize($p2_setting);
            if (false === file_put_contents($p2_setting_txt, $p2_setting_cont, LOCK_EX)) {
                return false;
            }
        }
    }
    return true;
}

/**
 * $subject_keys ���V���A���C�Y���ĕۑ�����
 *
 * @return  boolean
 */
function _saveSubjectKeys(&$subject_keys, $sb_keys_txt, $sb_keys_b_txt)
{
    global $_conf;
    
    //if (file_exists($sb_keys_b_txt)) { unlink($sb_keys_b_txt); }
    if ($subject_keys) {
        if (file_exists($sb_keys_txt)) {
            FileCtl::make_datafile($sb_keys_b_txt, $_conf['p2_perm']);
            copy($sb_keys_txt, $sb_keys_b_txt);
        } else {
            FileCtl::make_datafile($sb_keys_txt, $_conf['p2_perm']);
        }
        if ($sb_keys_cont = serialize($subject_keys)) {
            if (false === file_put_contents($sb_keys_txt, $sb_keys_cont, LOCK_EX)) {
                return false;
            }
        }
    }
    
    return true;
}


/**
 * �N�G���[����t�B���^���[�h���Z�b�g����
 *
 * @return  void
 */
function _setFilterWord()
{
    global $_conf, $sb_filter;
    
    $sb_filter = array();
    $sb_filter['method'] = null;
    
    $GLOBALS['word'] = null;
    $GLOBALS['word_fm'] = null;
    $GLOBALS['wakati_word'] = null;

    // �u�X�V�v�ł͂Ȃ��āA�����w�肪�����
    if (empty($_REQUEST['submit_refresh']) or !empty($_REQUEST['submit_kensaku'])) {

        if (isset($_GET['word'])) {
            $GLOBALS['word'] = $_GET['word'];
        } elseif (isset($_POST['word'])) {
            $GLOBALS['word'] = $_POST['word'];
        }
    
        if (isset($_GET['method'])) {
            $sb_filter['method'] = $_GET['method'];
        } elseif (isset($_POST['method'])) {
            $sb_filter['method'] = $_POST['method'];
        }

        if (isset($sb_filter['method']) and $sb_filter['method'] == 'similar') {
            $GLOBALS['wakati_word'] = $GLOBALS['word'];
            $GLOBALS['wakati_words'] = _wakati($GLOBALS['word']);
        
            if (!$GLOBALS['wakati_words']) {
                unset($GLOBALS['wakati_word'], $GLOBALS['wakati_words']);
            } else {
                require_once P2_LIB_DIR . '/strctl.class.php';
                $wakati_words2 = array_filter($GLOBALS['wakati_words'], '_wakatiFilter');
            
                if (!$wakati_words2) {
                    $GLOBALS['wakati_hl_regex'] = $GLOBALS['wakati_word'];
                } else {
                    rsort($wakati_words2, SORT_STRING);
                    $GLOBALS['wakati_hl_regex'] = implode(' ', $wakati_words2);
                    $GLOBALS['wakati_hl_regex'] = mb_convert_encoding($GLOBALS['wakati_hl_regex'], 'SJIS-win', 'UTF-8');
                }
            
                $GLOBALS['wakati_hl_regex'] = StrCtl::wordForMatch($GLOBALS['wakati_hl_regex'], 'or');
                $GLOBALS['wakati_hl_regex'] = str_replace(' ', '|', $GLOBALS['wakati_hl_regex']);
                $GLOBALS['wakati_length'] = mb_strlen($GLOBALS['wakati_word'], 'SJIS-win');
                $GLOBALS['wakati_score'] = _getSbScore($GLOBALS['wakati_words'], $GLOBALS['wakati_length']);
            
                if (!isset($_conf['expack.min_similarity'])) {
                    $_conf['expack.min_similarity'] = 0.05;
                } elseif ($_conf['expack.min_similarity'] > 1) {
                    $_conf['expack.min_similarity'] /= 100;
                }
                if (count($GLOBALS['wakati_words']) == 1) {
                    $_conf['expack.min_similarity'] /= 100;
                }
                $_conf['expack.min_similarity'] = (float) $_conf['expack.min_similarity'];
            }
            $GLOBALS['word'] = '';
        
        } elseif (preg_match('/^\.+$/', $GLOBALS['word'])) {
            $GLOBALS['word'] = '';
        }
    
        if (strlen($GLOBALS['word']) > 0)  {
        
            // �f�t�H���g�I�v�V����
            if (!$sb_filter['method']) { $sb_filter['method'] = "or"; } // $sb_filter �� global @see sb_print.icn.php
        
            require_once P2_LIB_DIR . '/strctl.class.php';
            $GLOBALS['word_fm'] = StrCtl::wordForMatch($GLOBALS['word'], $sb_filter['method']);
            if ($sb_filter['method'] != 'just') {
                if (P2_MBREGEX_AVAILABLE == 1) {
                    $GLOBALS['words_fm'] = mb_split('\s+', $GLOBALS['word_fm']);
                    $GLOBALS['word_fm'] = mb_ereg_replace('\s+', '|', $GLOBALS['word_fm']);
                } else {
                    $GLOBALS['words_fm'] = preg_split('/\s+/', $GLOBALS['word_fm']);
                    $GLOBALS['word_fm'] = preg_replace('/\s+/', '|', $GLOBALS['word_fm']);
                }
            }
        }
    }
}

/**
 * �X���^�C�i�Ɩ{���j�Ń}�b�`������true��Ԃ�
 *
 * @return  boolean
 */
function _matchSbFilter(&$aThread)
{
    // �S��������dat������΁A���e��ǂݍ���
    if (!empty($_REQUEST['find_cont']) && file_exists($aThread->keydat)) {
        $dat_cont = file_get_contents($aThread->keydat);
    }
    
    if ($GLOBALS['sb_filter']['method'] == "and") {
        reset($GLOBALS['words_fm']);
        foreach ($GLOBALS['words_fm'] as $word_fm_ao) {
            // �S��������dat������΁A���e������
            if (!empty($_REQUEST['find_cont']) && file_exists($aThread->keydat)) {
                // be.2ch.net ��EUC
                if (P2Util::isHostBe2chNet($aThread->host)) {
                   $target_cont = mb_convert_encoding($word_fm_ao, 'eucJP-win', 'SJIS-win');
                }
                if (!StrCtl::filterMatch($target_cont, $dat_cont)) {
                   return false;
                }
            
            // �X���^�C������
            } elseif (!StrCtl::filterMatch($word_fm_ao, $aThread->ttitle)) {
                return false;
            }
        }
        
    } else {
        // �S��������dat������΁A���e������
        if (!empty($_REQUEST['find_cont']) && file_exists($aThread->keydat)) {
            $target_cont = $GLOBALS['word_fm'];
            // be.2ch.net ��EUC
            if (P2Util::isHostBe2chNet($aThread->host)) {
                $target_cont = mb_convert_encoding($target_cont, 'eucJP-win', 'SJIS-win');
            }
            if (!StrCtl::filterMatch($target_cont, $dat_cont)) {
                return false;
            }
            
        // �X���^�C��������
        } elseif (!StrCtl::filterMatch($GLOBALS['word_fm'], $aThread->ttitle)) {
            return false;
        }
    }

    return true;
}

/**
 * �X���b�h�^�C�g���̃X�R�A���v�Z���ĕԂ�
 *
 * @return  float
 */
function _getSbScore($words, $length)
{
    static $bracket_regex = null;
    
    if (!$bracket_regex) {
        $bracket_regex = mb_convert_encoding('/[\\[\\]{}()�i�j�u�v�y�z]/u', 'UTF-8', 'SJIS-win');
    }
    $score = 0.0;
    if ($length) {
        foreach ($words as $word) {
            $chars = mb_strlen($word, 'UTF-8');
            if ($chars == 1 && preg_match($bracket_regex, $word)) {
                $score += 0.1 / $length;
            } elseif ($word == 'part') {
                $score += 1.0 / $length;
            } else {
                $revision = strlen($word) / mb_strwidth($word, 'UTF-8');
                //$score += pow($chars * $revision, 2) / $length;
                $score += $chars * $chars * $revision / $length;
                //$score += $chars * $chars / $length;
            }
        }
        if ($length > $GLOBALS['wakati_length']) {
            $score *= $GLOBALS['wakati_length'] / $length;
        } else {
            $score *= $length / $GLOBALS['wakati_length'];
        }
    }
    return $score;
}

/**
 * �X���b�h�^�C�g���̗ގ������v�Z���ĕԂ�
 * $aThread->similarity ���Z�b�g�����
 *
 * @return  boolean
 */
function _setSbSimilarity(&$aThread)
{
    $common_words = array_intersect(wakati($aThread->ttitle_hc), $GLOBALS['wakati_words']);
    if (!$common_words) {
        $aThread->similarity = 0.0;
        return false;
    }
    $score = getSbScore($common_words, mb_strlen($aThread->ttitle_hc, 'SJIS-win'));
    $aThread->similarity = $score / $GLOBALS['wakati_score'];
    // debug (title ����)
    //$aThread->ttitle_hd = mb_convert_encoding(htmlspecialchars(implode(' ', $common_words)), 'SJIS-win', 'UTF-8');
    return true;
}

/**
 * �������K���ȕ����������p���K�\���p�^�[�����擾����
 * �i�֐��Ŏ擾����͔̂�����I�����j
 *
 * @return  string
 */
function _getWakatiRegex()
{
    $patterns = array(
        //'[��-�]+[��-��]*',
        //'[��-�]+',
        '[���O�l�ܘZ������\]+',
        '[��-�]+',
        '[��-��][��-��[�`�J�K]*',
        '[�@-��][�@-���[�`�J�K]*',
        //'[a-z][a-z_\\-]*',
        //'[0-9][0-9.]*',
        '[0-9a-z][0-9a-z_\\-]*',
    );
    
    $cache_ = mb_convert_encoding('/(' . implode('|', $patterns) . ')/u', 'UTF-8', 'SJIS-win');
    
    return $cache_;
}

/**
 * ��������������K���ɐ��K�����������������āA���ʂ�z��ŕԂ�
 *
 * @param   string
 * @return  array
 */
function _wakati($str)
{
    $str = mb_convert_encoding($str, 'UTF-8', 'SJIS-win');
    $str = mb_convert_kana($str, 'KVas', 'UTF-8');
    $str = mb_strtolower($str, 'UTF-8');

    $array = preg_split(getWakatiRegex(), $str, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

    $array = array_filter(array_map('trim', $array), 'strlen');

    return $array;
}

/**
 * �����������̍\���v�f�Ƃ��ėL���Ȃ�true��Ԃ��Bfor array_filter()
 *
 * @param   string
 * @return  boolean
 */
function _wakatiFilter($str)
{
    $kanjiRegex = mb_convert_encoding('/[��-�]/u', 'UTF-8', 'SJIS-win');
    if (preg_match($kanjiRegex, $str) or preg_match(_getWakatiRegex(), $str) && mb_strlen($str, 'UTF-8') > 1) {
        return true;
    }
    return false;
}

//============================================================
// �\�[�g�֐�
//============================================================

/**
 * �V���\�[�g
 *
 * @return  integer
 */
function cmp_midoku($a, $b)
{
    if ($a->new == $b->new) {
        if (($a->unum == $b->unum) or ($a->unum < 0) && ($b->unum < 0)) {
            return ($a->torder > $b->torder) ? 1 : -1;
        } else {
            return ($a->unum < $b->unum) ? 1 : -1;
        }
    } else {
        return ($a->new < $b->new) ? 1 : -1;
    }
}

/**
 * ���X�� �\�[�g
 *
 * @return  integer
 */
function cmp_res($a, $b)
{ 
    if ($a->rescount == $b->rescount) {
        return ($a->torder > $b->torder) ? 1 : -1;
    } else {
        return ($a->rescount < $b->rescount) ? 1 : -1;
    }
}

/**
 * �^�C�g�� �\�[�g
 *
 * @return  integer
 */
function cmp_title($a, $b)
{ 
    if ($a->ttitle == $b->ttitle) {
        return ($a->torder > $b->torder) ? 1 : -1;
    } else {
        return strcmp($a->ttitle, $b->ttitle);
    }
}

/**
 * �� �\�[�g
 *
 * @return  integer
 */
function cmp_ita($a, $b)
{
    if ($a->host != $b->host) {
        return strcmp($a->host, $b->host);
    } else {
        if ($a->itaj != $b->itaj) {
            return strcmp($a->itaj, $b->itaj);
        } else {
            return ($a->torder > $b->torder) ? 1 : -1;
        }
    }
}

/**
 * ���C�� �\�[�g
 *
 * @return  integer
 */
function cmp_fav($a, $b)
{ 
    if ($a->fav == $b->fav) {
        return ($a->torder > $b->torder) ? 1 : -1;
    } else {
        return strcmp($b->fav, $a->fav);
    }
}

/**
 * �����\�[�g�i�V�����X�D��j
 *
 * @return  integer
 */
function cmp_dayres_midoku($a, $b)
{
    if ($a->new == $b->new) {
        if (($a->unum == $b->unum) or ($a->unum >= 1) && ($b->unum >= 1)) {
            return ($a->dayres < $b->dayres) ? 1 : -1;
        } else {
            return ($a->unum < $b->unum) ? 1 : -1;
        }
    } else {
        return ($a->new < $b->new) ? 1 : -1;
    }
}

/**
 * �����\�[�g
 *
 * @return  integer
 */
function cmp_dayres($a, $b)
{
    if ($a->new == $b->new) {
        return ($a->dayres < $b->dayres) ? 1 : -1;
    } else {
        return ($a->new < $b->new) ? 1 : -1;
    }
}

/**
 * key �\�[�g
 *
 * @return  integer
 */
function cmp_key($a, $b)
{
    return ($a->key < $b->key) ? 1 : -1;
}

/**
 * No. �\�[�g
 *
 * @return  integer
 */
function cmp_no($a, $b)
{ 
    return ($a->torder > $b->torder) ? 1 : -1;
} 

/**
 * �ގ����\�[�g
 *
 * @return  integer
 */
function cmp_similarity($a, $b)
{
    if ($a->similarity == $b->similarity) {
        return ($a->key < $b->key) ? 1 : -1;
    } else {
        return ($a->similarity < $b->similarity) ? 1 : -1;
    }
}

/*
 * Local Variables:
 * mode: php
 * coding: cp932
 * tab-width: 4
 * c-basic-offset: 4
 * indent-tabs-mode: nil
 * End:
 */
// vim: set syn=php fenc=cp932 ai et ts=4 sw=4 sts=4 fdm=marker:
