<?php
/*
    p2 - ���[�U�ݒ�ҏWUI
*/
/* 2008/7/25 iPhone�p�ɃJ�X�^�}�C�Y*/

require_once './conf/conf.inc.php';
require_once './iphone/conf.inc.php';

require_once P2_LIB_DIR . '/dataphp.class.php';

$_login->authorize(); // ���[�U�F��

if (!empty($_POST['submit_save']) || !empty($_POST['submit_default'])) {
    if (!isset($_POST['csrfid']) or $_POST['csrfid'] != P2Util::getCsrfId()) {
        P2Util::printSimpleHtml("p2 error: �s���ȃ|�X�g�ł�");
        die;
    }
}

//=====================================================================
// �O����
//=====================================================================

// {{{ �ۑ��{�^����������Ă�����A�ݒ��ۑ�

if (!empty($_POST['submit_save'])) {

    // �l�̓K���`�F�b�N�A����
    
    // �g����
    $_POST['conf_edit'] = array_map('trim', $_POST['conf_edit']);
    
    // �I�����ɂȂ����� �� �f�t�H���g����
    notSelToDef();
    
    // ���[����K�p����
    applyRules();

    /**
     * �f�t�H���g�l $conf_user_def �ƕύX�l $_POST['conf_edit'] �̗��������݂��Ă��āA
     * �f�t�H���g�l�ƕύX�l���قȂ�ꍇ�̂ݐݒ�ۑ�����i���̑��̃f�[�^�͕ۑ����ꂸ�A�j�������j
     */
    $conf_save = array();
    foreach ($conf_user_def as $k => $v) {
        if (isset($conf_user_def[$k]) && isset($_POST['conf_edit'][$k])) {
            if ($conf_user_def[$k] != $_POST['conf_edit'][$k]) {
                $conf_save[$k] = $_POST['conf_edit'][$k];
            }
            
        // ���ʂȍ��ځiedit_conf_user.php �ȊO�ł��ݒ肳�ꂤ����͔̂j�������ɂ��̂܂܎c���j
        // �L�[�ɖ����K���iprefix�j������������������ �� �L�[���ύX�̕K�v������̂ŋp��
        } elseif (in_array($k, array('k_use_aas', 'maru_kakiko', 'index_menu_k', 'index_menu_k_from1'))) {
            $conf_save[$k] = $_conf[$k];
        }
    }

    // �V���A���C�Y���ĕۑ�
    FileCtl::make_datafile($_conf['conf_user_file'], $_conf['conf_user_perm']);
    if (file_put_contents($_conf['conf_user_file'], serialize($conf_save), LOCK_EX) === false) {
        P2Util::pushInfoHtml("<p>�~�ݒ���X�V�ۑ��ł��܂���ł���</p>");
        trigger_error("file_put_contents(" . $_conf['conf_user_file'] . ")", E_USER_WARNING);
        
    } else {
        P2Util::pushInfoHtml("<p>���ݒ���X�V�ۑ����܂���</p>");
        // �ύX������΁A�����f�[�^���X�V���Ă���
        $_conf = array_merge($_conf, $conf_user_def);
        $_conf = array_merge($_conf, $conf_save);
    }

// }}}
// {{{ �f�t�H���g�ɖ߂��{�^����������Ă�����

} elseif (!empty($_POST['submit_default'])) {
    if (file_exists($_conf['conf_user_file']) and unlink($_conf['conf_user_file'])) {
        P2Util::pushInfoHtml("<p>���ݒ���f�t�H���g�ɖ߂��܂���</p>");
        // �ύX������΁A�����f�[�^���X�V���Ă���
        $_conf = array_merge($_conf, $conf_user_def);
    }
}

// }}}

//=====================================================================
// �v�����g�ݒ�
//=====================================================================
$ptitle = '���[�U�ݒ�ҏW';

$csrfid = P2Util::getCsrfId();


//=====================================================================
// �v�����g
//=====================================================================
// �w�b�_HTML���v�����g
P2Util::headerNoCache();
P2View::printDoctypeTag();
?>
<html lang="ja">
<head>
    <?php echo $_conf['meta_charset_ht']; ?>
    <meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
    <meta http-equiv="Content-Style-Type" content="text/css">
    <meta http-equiv="Content-Script-Type" content="text/javascript">
    <script type="text/javascript" src="./iui/smooth.pack.js"></script>
<style type="text/css" media="screen">@import "./iui/iui.css";
body{background:url(iui/pinstripes.png)}input,select {float: right;}
</style>
    <title><?php eh($ptitle); ?></title>
<?php

echo <<<EOP
</head>
<body onLoad="top.document.title=self.document.title;">\n
<div class="toolbar">
<h1 id="pageTitle">{$ptitle}</h1>
<a name="top" id="backButton" class="button" href="./iphone.php">TOP</a>
</div>

EOP;


$htm['form_submit'] = <<<EOP
<input class="whiteButton" type="submit" name="submit_save" value="�ύX��ۑ�����">\n<br clear="right">
<input class="whiteButton" type="submit" name="submit_default" value="�f�t�H���g�ɖ߂�" onClick="if (!window.confirm('���[�U�ݒ���f�t�H���g�ɖ߂��Ă���낵���ł����H�i��蒼���͂ł��܂���j')) {return false;}">
EOP;


P2Util::printInfoHtml();

?>
<form method="POST" action="<?php eh($_SERVER['SCRIPT_NAME']); ?>" target="_self">
    <input type="hidden" name="csrfid" value="<?php eh($csrfid); ?>">
    <?php echo P2View::getInputHiddenKTag(); ?>
<?php

echo $htm['form_submit'] . '<br><br>';


echo getGroupSepaHtml('be.2ch.net �A�J�E���g');

echo getEditConfHtml('be_2ch_code', '<a href="http://be.2ch.net/" target="_blank">be.2ch.net</a>�̔F�؃R�[�h(�p�X���[�h�ł͂���܂���)');
echo getEditConfHtml('be_2ch_mail', 'be.2ch.net�̓o�^���[���A�h���X');

echo getGroupSepaHtml('PATH');

//echo getEditConfHtml('first_page', '�E�������ɍŏ��ɕ\�������y�[�W�B�I�����C��URL���B');
echo getEditConfHtml('brdfile_online', 
    '���X�g�̎w��i�I�����C��URL�j<br>
    ���X�g���I�����C��URL���玩���œǂݍ��ށB
    �w���� menu.html �`���A2channel.brd �`���̂ǂ���ł��悢�B
    <!-- �K�v�Ȃ���΁A�󔒂ɁB --><br>

    2ch��{ <a href="http://menu.2ch.net/bbsmenu.html" target="_blank">http://menu.2ch.net/bbsmenu.html</a><br>
    2ch + �O��BBS <a href="http://azlucky.s25.xrea.com/2chboard/bbsmenu.html" target="_blank">http://azlucky.s25.xrea.com/2chboard/bbsmenu.html</a><br>
    ');


/*080725 �ꕔiPhone�p�ɍ폜 */
echo getGroupSepaHtml('subject');

echo getEditConfHtml('refresh_time', '�X���b�h�ꗗ�̎����X�V�Ԋu (���w��B0�Ȃ玩���X�V���Ȃ�)');

echo getEditConfHtml('sb_show_motothre', '�X���b�h�ꗗ�Ŗ��擾�X���ɑ΂��Č��X���ւ̃����N�i�E�j��\��');
echo getEditConfHtml('sb_show_one', 'PC�{�����A�X���b�h�ꗗ�i�\���j��>>1��\��');
echo getEditConfHtml('k_sb_show_first', '�g�т̃X���b�h�ꗗ�i�\���j���珉�߂ẴX�����J�����̕\�����@');
echo getEditConfHtml('sb_show_spd', '�X���b�h�ꗗ�ł��΂₳�i���X�Ԋu�j��\��');
echo getEditConfHtml('sb_show_ikioi', '�X���b�h�ꗗ�Ő����i1��������̃��X���j��\��');
echo getEditConfHtml('sb_show_fav', '�X���b�h�ꗗ�ł��C�ɃX���}�[�N����\��');
echo getEditConfHtml('sb_sort_ita', '�\���̃X���b�h�ꗗ�ł̃f�t�H���g�̃\�[�g�w��');
echo getEditConfHtml('sort_zero_adjust', '�V���\�[�g�ł́u�����Ȃ��v�́u�V�����[���v�ɑ΂���\�[�g�D�揇��');
echo getEditConfHtml('cmp_dayres_midoku', '�����\�[�g���ɐV�����X�̂���X����D��');
echo getEditConfHtml('k_sb_disp_range', '�g�щ{�����A��x�ɕ\������X���̐�');
echo getEditConfHtml('viewall_kitoku', '�����X���͕\�������Ɋւ�炸�\��');

echo getGroupSepaHtml('read');

echo getEditConfHtml('respointer', '�X�����e�\�����A���ǂ̉��R�O�̃��X�Ƀ|�C���^�����킹�邩');
echo getEditConfHtml('before_respointer', 'PC�{�����A�|�C���^�̉��R�O�̃��X����\�����邩');
echo getEditConfHtml('before_respointer_new', '�V���܂Ƃߓǂ݂̎��A�|�C���^�̉��R�O�̃��X����\�����邩');
echo getEditConfHtml('rnum_all_range', '�V���܂Ƃߓǂ݂ň�x�ɕ\�����郌�X��');
echo getEditConfHtml('preview_thumbnail', '�摜URL�̐�ǂ݃T���l�C����\��');
echo getEditConfHtml('pre_thumb_limit', '�摜URL�̐�ǂ݃T���l�C������x�ɕ\�����鐧����');
//echo getEditConfHtml('preview_thumbnail', '�摜�T���l�C���̏c�̑傫�����w�� (�s�N�Z��)');
////echo getEditConfHtml('pre_thumb_width', '�摜�T���l�C���̉��̑傫�����w�� (�s�N�Z��)');
echo getEditConfHtml('link_youtube', 'YouTube�̃����N���v���r���[�\��');
echo getEditConfHtml('link_niconico', '�j�R�j�R����̃����N���v���r���[�\��');
echo getEditConfHtml('link_yourfilehost', 'YourFileHost�����URL�Ƀ_�E�����[�h�p�����N��t��');
echo getEditConfHtml('show_be_icon', '2ch��BE�A�C�R����\��');
echo getEditConfHtml('iframe_popup', 'HTML�|�b�v�A�b�v');
//echo getEditConfHtml('iframe_popup_delay', 'HTML�|�b�v�A�b�v�̕\���x������ (�b)');
echo getEditConfHtml('flex_idpopup', '�X�����œ��� ID:xxxxxxxx ������΁AID�t�B���^�p�̃����N�ɕϊ�');
echo getEditConfHtml('ext_win_target', '�O���T�C�g���փW�����v���鎞�ɊJ���E�B���h�E�̃^�[�Q�b�g��');
echo getEditConfHtml('bbs_win_target', 'p2�Ή�BBS�T�C�g���ŃW�����v���鎞�ɊJ���E�B���h�E�̃^�[�Q�b�g��');
echo getEditConfHtml('bottom_res_form', '�X���b�h�����ɏ������݃t�H�[����\��');
echo getEditConfHtml('quote_res_view', '���p���X��\��');

echo getEditConfHtml('enable_headbar', 'PC �w�b�h�o�[��\��');
echo getEditConfHtml('enable_spm', '���X�ԍ�����X�}�[�g�|�b�v�A�b�v���j���[(SPM)��\��');
//echo getEditConfHtml('spm_kokores', '�X�}�[�g�|�b�v�A�b�v���j���[�Łu����Ƀ��X�v��\��');


echo getEditConfHtml('k_rnum_range', '�g�щ{�����A��x�ɕ\�����郌�X�̐�');
echo getEditConfHtml('ktai_res_size', '�g�щ{�����A��̃��X�̍ő�\���T�C�Y�i0�Ȃ�ȗ����Ȃ��j');
echo getEditConfHtml('ktai_ryaku_size', '�g�щ{�����A���X���ȗ������Ƃ��̕\���T�C�Y');
echo getEditConfHtml('k_aa_ryaku_size', '�g�щ{�����AAA�炵�����X���ȗ�����T�C�Y�i0�Ȃ�ȗ����Ȃ��j');
echo getEditConfHtml('before_respointer_k', '�g�щ{�����A�|�C���^�̉��R�O�̃��X����\�����邩');
echo getEditConfHtml('k_use_tsukin', '�g�щ{�����A�O�������N�ɒʋ΃u���E�U(��)�𗘗p');
echo getEditConfHtml('k_use_picto', '�g�щ{�����A�摜�����N��pic.to(��)�𗘗p');

echo getEditConfHtml('k_motothre_template', '�g�щ{�����A���X��URL�̃J�X�^�}�C�Y�w��');
echo getEditConfHtml('k_motothre_external', '�g�щ{�����A���X��URL�̃J�X�^�}�C�Y�w����O���ł��L���ɂ���');

echo getEditConfHtml('k_bbs_noname_name', '�g�щ{�����A�f�t�H���g�̖���������\��');
echo getEditConfHtml('k_clip_unique_id', '�g�щ{�����A�d�����Ȃ�ID�͖����݂̂̏ȗ��\��');
echo getEditConfHtml('k_date_zerosuppress', '�g�щ{�����A���t��0���ȗ��\��');
echo getEditConfHtml('k_clip_time_sec', '�g�щ{�����A�����̕b���ȗ��\��');
echo getEditConfHtml('mobile.id_underline', '�g�щ{�����AID������"O"�i�I�[�j�ɉ�����ǉ�');
echo getEditConfHtml('k_copy_divide_len', '�g�щ{�����A�u�ʁv�̃R�s�[�p�e�L�X�g�{�b�N�X�𕪊����镶����');

echo getEditConfHtml('read_k_thread_title_color', '�g�щ{�����A�X���b�h�^�C�g���F�iHTML�J���[ ��:"#1144aa"�j');
echo getEditConfHtml('k_bgcolor', '�g�щ{�����A��{�w�i�F�iHTML�J���[�j');
echo getEditConfHtml('k_color', '�g�щ{�����A��{�e�L�X�g�F�iHTML�J���[�j');
echo getEditConfHtml('k_acolor', '�g�щ{�����A��{�����N�F�iHTML�J���[�j');
echo getEditConfHtml('k_acolor_v', '�g�щ{�����A��{�K��ς݃����N�F�iHTML�J���[�j');
echo getEditConfHtml('k_post_msg_cols', '�g�щ{�����A�������݃t�H�[���̉���');
echo getEditConfHtml('k_post_msg_rows', '�g�щ{�����A�������݃t�H�[���̍���');

echo getGroupSepaHtml('ETC');

echo getEditConfHtml('frame_menu_width', '�t���[���� ���j���[ �̕\����');
echo getEditConfHtml('frame_subject_width', '�t���[���E�� �X���ꗗ �̕\����');
echo getEditConfHtml('frame_read_width', '�t���[���E�� �X���{�� �̕\����');

echo getEditConfHtml('my_FROM', '���X�������ݎ��̃f�t�H���g�̖��O');
echo getEditConfHtml('my_mail', '���X�������ݎ��̃f�t�H���g��mail');

echo getEditConfHtml('editor_srcfix', 'PC�{�����A�\�[�X�R�[�h�̃R�s�y�ɓK�����␳������`�F�b�N�{�b�N�X��\��');

echo getEditConfHtml('get_new_res', '�V�����X���b�h���擾�������ɕ\�����郌�X��(�S�ĕ\������ꍇ:"all")');
echo getEditConfHtml('rct_rec_num', '�ŋߓǂ񂾃X���̋L�^��');
echo getEditConfHtml('res_hist_rec_num', '�������ݗ����̋L�^��');
echo getEditConfHtml('res_write_rec', '�������ݓ��e���O���L�^');
echo getEditConfHtml('through_ime', '�O��URL�W�����v����ۂɒʂ��Q�[�g');
echo getEditConfHtml('join_favrank', '<a href="http://akid.s17.xrea.com/favrank/favrank.html" target="_blank">���C�ɃX�����L</a>�ɎQ��');
echo getEditConfHtml('enable_menu_new', '���j���[�ɐV������\��');
echo getEditConfHtml('menu_refresh_time', '���j���[�����̎����X�V�Ԋu (���w��B0�Ȃ玩���X�V���Ȃ��B)');
echo getEditConfHtml('mobile.match_color', '�g�щ{�����A�t�B���^�����O�Ń}�b�`�����L�[���[�h�̐F');
echo getEditConfHtml('k_save_packet', '�g�щ{�����A�p�P�b�g�ʂ����炷���߁A�S�p�p���E�J�i�E�X�y�[�X�𔼊p�ɕϊ�');
echo getEditConfHtml('ngaborn_daylimit', '���̊��ԁANG���ځ[���HIT���Ȃ���΁A�o�^���[�h�������I�ɊO���i�����j');
echo getEditConfHtml('proxy_use', '�v���L�V�𗘗p'); 
echo getEditConfHtml('proxy_host', '�v���L�V�z�X�g ex)"127.0.0.1", "www.p2proxy.com"'); 
echo getEditConfHtml('proxy_port', '�v���L�V�|�[�g ex)"8080"'); 
echo getEditConfHtml('precede_openssl', '�����O�C�����A�܂���openssl�Ŏ��݂�B��PHP 4.3.0�ȍ~�ŁAOpenSSL���ÓI�Ƀ����N����Ă���K�v������B');
echo getEditConfHtml('precede_phpcurl', 'curl���g�����A�R�}���h���C���ł�PHP�֐��łǂ����D�悷�邩');



echo $htm['form_submit'];

?>
</form>
</body>
</html>
<?php
exit;


//=====================================================================
// �֐� �i���̃t�@�C�����ł̂ݗ��p�j
//=====================================================================
/**
 * ���[���ݒ�i$conf_user_rules�j�Ɋ�Â��āA�t�B���^�����i�f�t�H���g�Z�b�g�j���s��
 *
 * @return  void
 */
function applyRules()
{
    global $conf_user_rules, $conf_user_def;
    
    if (is_array($conf_user_rules)) {
        foreach ($conf_user_rules as $k => $v) {
            if (isset($_POST['conf_edit'][$k])) {
                $def = isset($conf_user_def[$k]) ? $conf_user_def[$k] : null;
                foreach ($v as $func) {
                    $_POST['conf_edit'][$k] = call_user_func($func, $_POST['conf_edit'][$k], $def);
                }
            }
        }
    }
}

// emptyToDef() �Ȃǂ̃t�B���^��EditConfFiter�N���X�Ȃǂɂ܂Ƃ߂�\��

/**
 * CSS�l�̂��߂̃t�B���^�����O���s��
 */
function filterCssValue($str, $def = '')
{
    return preg_replace('/[^0-9a-zA-Z-%]/', '', $str);
}

/**
 * empty�̎��́A�f�t�H���g�Z�b�g����
 */
function emptyToDef($val, $def)
{
    if (empty($val)) {
        $val = $def;
    }
    return $val;
}

/**
 * ���̐������ł��鎞�͐��̐������i0���܂ށj���A
 * �ł��Ȃ����́A�f�t�H���g�Z�b�g����
 */
function notIntExceptMinusToDef($val, $def)
{
    // �S�p�����p ����
    $val = mb_convert_kana($val, 'a');
    // �������ł���Ȃ�
    if (is_numeric($val)) {
        // ����������
        $val = intval($val);
        // ���̐��̓f�t�H���g��
        if ($val < 0) {
            $val = intval($def);
        }
    // �������ł��Ȃ����̂́A�f�t�H���g��
    } else {
        $val = intval($def);
    }
    return $val;
}

/**
 * �I�����ɂȂ��l�̓f�t�H���g�Z�b�g����
 */
function notSelToDef()
{
    global $conf_user_def, $conf_user_sel;
    
    $names = array_keys($conf_user_sel);
    
    if (is_array($names)) {
        foreach ($names as $n) {
            if (isset($_POST['conf_edit'][$n])) {
                if (!array_key_exists($_POST['conf_edit'][$n], $conf_user_sel[$n])) {
                    $_POST['conf_edit'][$n] = $conf_user_def[$n];
                }
            }
        }
    }
}

/**
 * �O���[�v�����p��HTML�𓾂�i�֐�����PC�A�g�їp�\����U�蕪���j
 *
 * @return  string
 */
function getGroupSepaHtml($title)
{
    global $_conf;
    
   $ht = "<ul><li class=\"group\">{$title}<a name=\"{$title}\"></a></li></ul>"."\n";
    
    return $ht;
}

/**
 * �ҏW�t�H�[��input�pHTML�𓾂�i�֐�����PC�A�g�їp�\����U�蕪���j
 *
 * @return  string
 */
function getEditConfHtml($name, $description_ht)
{
    global $_conf, $conf_user_def, $conf_user_sel;

    // �f�t�H���g�l�̋K�肪�Ȃ���΁A�󔒂�Ԃ�
    if (!isset($conf_user_def[$name])) {
        return '';
    }
    // �g�тł͕ҏW�\�����Ȃ�����
    if ($_conf['ktai']) {
        $noKtais = array(
            'enable_headbar', 'enable_spm', 'spm_kokores',
            'frame_menu_width', 'frame_subject_width', 'frame_read_width'
        );
        if (in_array($name, $noKtais)) {
            return sprintf(
                '<input type="hidden" name="conf_edit[%s]" value="%s">' . "\n",
                hs($name), hs($_conf[$name])
            );
        }
    }

    $name_view = $_conf[$name];
    
    if (empty($_conf['ktai'])) {
        $input_size_at = ' size="38"';
    } else {
        $input_size_at = '';
    }
    
    // select �I���`���Ȃ�
    if (isset($conf_user_sel[$name])) {
        $form_ht = getEditConfSelHtml($name);
        $key = $conf_user_def[$name];
        $def_views[$name] = htmlspecialchars($conf_user_sel[$name][$key], ENT_QUOTES);
    // input ���͎��Ȃ�
    } else {
        $form_ht = <<<EOP
<input type="text" name="conf_edit[{$name}]" value="{$name_view}"{$input_size_at}>\n
EOP;
        if (is_string($conf_user_def[$name])) {
            $def_views[$name] = htmlspecialchars($conf_user_def[$name], ENT_QUOTES);
        } else {
            $def_views[$name] = $conf_user_def[$name];
        }
    }
    
    
$r = <<<EOP
[{$name}]<br>
{$description_ht}<br>
{$form_ht}<br>
<br>\n
EOP;
    
    
    return $r;
}

/**
 * �ҏW�t�H�[��select�pHTML�𓾂�
 *
 * @return  string
 */
function getEditConfSelHtml($name)
{
    global $_conf, $conf_user_def, $conf_user_sel;

    $options_ht = '';
    foreach ($conf_user_sel[$name] as $key => $value) {
        /*
        if ($value == "") {
            continue;
        }
        */
        $selected = "";
        if ($_conf[$name] == $key) {
            $selected = " selected";
        }
        $key_ht = htmlspecialchars($key, ENT_QUOTES);
        $value_ht = htmlspecialchars($value, ENT_QUOTES);
        $options_ht .= "\t<option value=\"{$key_ht}\"{$selected}>{$value_ht}</option>\n";
    }
    
    $form_ht = <<<EOP
        <select name="conf_edit[{$name}]">
        {$options_ht}
        </select>\n
EOP;
    return $form_ht;
}

