<?php
/*
    p2 -  �ݒ�Ǘ�
*/
/* 2008/7/25 iPhone��p�ɃJ�X�^�}�C�Y*/

require_once './conf/conf.inc.php';
require_once './iphone/conf.inc.php';

require_once P2_LIB_DIR . '/filectl.class.php';
require_once P2_LIB_DIR . '/P2View.php';
$_login->authorize(); // ���[�U�F��

// {{{ �z�X�g�̓����p�ݒ�

if (!isset($rh_idx))     { $rh_idx     = $_conf['pref_dir'] . '/p2_res_hist.idx'; }
if (!isset($palace_idx)) { $palace_idx = $_conf['pref_dir'] . '/p2_palace.idx'; }

$synctitle = array(
    basename($_conf['favita_path'])  => '���C�ɔ�',
    basename($_conf['favlist_file']) => '���C�ɃX��',
    basename($_conf['rct_file'])     => '�ŋߓǂ񂾃X��',
    basename($rh_idx)                => '�������ݗ���',
    basename($palace_idx)            => '�X���̓a��'
);

// }}}
// {{{ �ݒ�ύX����

// �z�X�g�𓯊�����
if (isset($_POST['sync'])) {
    require_once P2_LIB_DIR . '/BbsMap.class.php';
    $syncfile = $_conf['pref_dir'] . '/' . $_POST['sync'];
    $sync_name = $_POST['sync'];
    if ($syncfile == $_conf['favita_path']) {
        BbsMap::syncBrd($syncfile);
    } elseif (in_array($syncfile, array($_conf['favlist_file'], $_conf['rct_file'], $rh_idx, $palace_idx))) {
        BbsMap::syncIdx($syncfile);
    }
}

// }}}
// {{{ �����o���p�ϐ�

$ptitle = '�ݒ�Ǘ�';

if ($_conf['ktai']) {
    $status_st      = '�X�e�[�^�X';
    $autho_user_st  = '�F�؃��[�U�[';
    $client_host_st = '�[���z�X�g';
    $client_ip_st   = '�[��IP�A�h���X';
    $browser_ua_st  = '�u���E�UUA';
    $p2error_st     = 'p2�G���[';
} 

$autho_user_ht = '';

// }}}

//=========================================================
// HTML��\������
//=========================================================
P2Util::headerNoCache();
P2View::printDoctypeTag();
echo <<<EOP
<html lang="ja">
<head>
    {$_conf['meta_charset_ht']}
    <meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
    <meta http-equiv="Content-Style-Type" content="text/css">
    <meta http-equiv="Content-Script-Type" content="text/javascript">
    <style type="text/css" media="screen">@import "./iui/iui.css";</style>
    <title>{$ptitle}</title>\n
EOP;


echo <<<EOP
</head>
<body>\n
EOP;

P2Util::printInfoHtml();

$aborn_res_txt  = $_conf['pref_dir'] . '/p2_aborn_res.txt';
$aborn_name_txt = $_conf['pref_dir'] . '/p2_aborn_name.txt';
$aborn_mail_txt = $_conf['pref_dir'] . '/p2_aborn_mail.txt';
$aborn_msg_txt  = $_conf['pref_dir'] . '/p2_aborn_msg.txt';
$aborn_id_txt   = $_conf['pref_dir'] . '/p2_aborn_id.txt';
$ng_name_txt    = $_conf['pref_dir'] . '/p2_ng_name.txt';
$ng_mail_txt    = $_conf['pref_dir'] . '/p2_ng_mail.txt';
$ng_msg_txt     = $_conf['pref_dir'] . '/p2_ng_msg.txt';
$ng_id_txt      = $_conf['pref_dir'] . '/p2_ng_id.txt';

echo <<<EOP
<div class="toolbar">
<h1 id="pageTitle">{$ptitle}</h1>
<a class="button" href="edit_conf_user_i.php?b=k">���[�U�ݒ�</a>
<a id="backButton" class="button" href="./iphone.php">TOP</a>
</div>
EOP;




// iPhone�p�\�� NG/����ܰ��

$ng_name_txt_bn = basename($ng_name_txt);
$ng_mail_txt_bn = basename($ng_mail_txt);
$ng_msg_txt_bn = basename($ng_msg_txt);
$ng_id_txt_bn = basename($ng_id_txt);
$aborn_name_txt_bn = basename($aborn_name_txt);
$aborn_mail_txt_bn = basename($aborn_mail_txt);
$aborn_msg_txt_bn = basename($aborn_msg_txt);
$aborn_id_txt_bn = basename($aborn_id_txt);
echo <<<EOP
<ul><li class="group">NG/�A�{�����[�h�ҏW</li></ul>
<div id="usage" class="panel"><filedset>
<form method="GET" action="edit_aborn_word.php">
{$_conf['k_input_ht']}
<select name="path">
<option value="{$ng_name_txt_bn}">NG:���O</option>
<option value="{$ng_mail_txt_bn}">NG:���[��</option>
<option value="{$ng_msg_txt_bn}">NG:���[��</option>
<option value="{$ng_id_txt_bn}">NG:ID</option>
<option value="{$aborn_name_txt_bn}">�A�{��:���O</option>
<option value="{$aborn_mail_txt_bn}">�A�{��:���[��</option>
<option value="{$aborn_msg_txt_bn}">�A�{��:���b�Z�[�W</option>
<option value="{$aborn_id_txt_bn}">�A�{��:ID</option>
</select>
<input type="submit" value="�ҏW">
</form>
</filedset></div>
EOP;



// �V���܂Ƃߓǂ݂̃L���b�V�������NHTML��\������
echo <<<EOP
<ul><li class="group">�V���܂Ƃ�</li></ul>
<div id="usage" class="panel">
<h2>�O��L���b�V���\��</h2>
<filedset>
EOP;

printMatomeCacheLinksHtml();

echo <<<EOP
</filedset>
</div>
EOP;



// PC - �z�X�g�̓��� HTML��\�� 
$sync_htm = "<ul><li class=\"group\">νĂ̓���</li></ul>\n<div id=\"usage\" class=\"panel\"><felidset>2ch�̔ړ]�ɑΉ����܂��B<br>�ʏ�͎����ōs����̂ŁA���̑���͓��ɕK�v����܂���j\n";
$exist_sync_flag = false;
foreach ($synctitle as $syncpath => $syncname) {
    if (is_writable($_conf['pref_dir'] . '/' . $syncpath)) {
        $exist_sync_flag = true;
        $sync_htm .= getSyncFavoritesFormHt($syncpath, $syncname);
    }
}

if ($exist_sync_flag) {
    echo $sync_htm;
} else {
    // echo "<p>νĂ̓����͕K�v����܂���</p>";
}

echo '</filedset><div>';
echo '</body></html>';


exit;


//==============================================================================
// �֐�
//==============================================================================
/**
 * �ݒ�t�@�C���ҏW�E�C���h�E���J���t�H�[��HTML��\������
 *
 * @return  void
 */
function printEditFileForm($path_value, $submit_value)
{
    global $_conf;
    
    if ((file_exists($path_value) && is_writable($path_value)) ||
        (!file_exists($path_value) && is_writable(dirname($path_value)))
    ) {
        $onsubmit = '';
        $disabled = '';
    } else {
        $onsubmit = ' onsubmit="return false;"';
        $disabled = ' disabled';
    }
    
    $rows = 36; // 18
    $cols = 92; // 90

    if (preg_match('/^p2_(aborn|ng)_(name|mail|id|msg)\.txt$/', basename($path_value))) {
        $edit_php = 'edit_aborn_word.php';
        $target = '_self';
        $path_value = basename($path_value);
    } else {
        $edit_php = 'editfile.php';
        $target = 'editfile';
    }
    
    $ht = <<<EOFORM
<form action="{$edit_php}" method="POST" target="{$target}" class="inline-form"{$onsubmit}>
    {$_conf['k_input_ht']}
    <input type="hidden" name="path" value="{$path_value}">
    <input type="hidden" name="encode" value="Shift_JIS">
    <input type="hidden" name="rows" value="{$rows}">
    <input type="hidden" name="cols" value="{$cols}">
    <input type="submit" value="{$submit_value}"{$disabled}>
</form>

EOFORM;

    if (strstr($_SERVER['HTTP_USER_AGENT'], 'MSIE')) {
        $ht = '&nbsp;' . preg_replace('/>\s+</', '><', $ht);
    }
    echo $ht;
}

/**
 * �z�X�g�̓����p�t�H�[����HTML���擾����
 *
 * @return  string
 */
function getSyncFavoritesFormHt($path_value, $submit_value)
{
    global $_conf;
    
    $ht = <<<EOFORM
<form action="editpref_i.php" method="POST" target="_self" class="inline-form">
    {$_conf['k_input_ht']}
    <input type="hidden" name="sync" value="{$path_value}">
    <input type="submit" value="{$submit_value}">
</form>

EOFORM;

    if (strstr($_SERVER['HTTP_USER_AGENT'], 'MSIE')) {
        $ht = '&nbsp;' . preg_replace('/>\s+</', '><', $ht);
    }
    return $ht;
}

/**
 * �V���܂Ƃߓǂ݂̃L���b�V�������NHTML��\������
 *
 * @return  void
 */
function printMatomeCacheLinksHtml()
{
    global $_conf;
    
    $max = $_conf['matome_cache_max'];
    $links = array();
    for ($i = 0; $i <= $max; $i++) {
        $dnum = $i ? '.' . $i : '';
        $ai = '&amp;cnum=' . $i;
        $file = $_conf['matome_cache_path'] . $dnum . $_conf['matome_cache_ext'];
        //echo '<!-- ' . $file . ' -->';
        if (file_exists($file)) {
            $filemtime = filemtime($file);
            $date = date('Y/m/d G:i:s', $filemtime);
            $b = filesize($file) / 1024;
            $kb = round($b, 0);
            $url = 'read_new_i.php?cview=1' . $ai . '&amp;filemtime=' . $filemtime;
            $links[] = '<a href="' . $url . '" target="read">' . $date . '</a> ' . $kb . 'KB' ."\n";
        }
    }
    if ($links) {
        echo implode('<br>', $links);
        
        if ($_conf['ktai']) {
            echo "\n";
        }
    }
}

