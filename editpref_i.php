<?php
/*
    p2 -  設定管理
*/
/* 2008/7/25 iPhone専用にカスタマイズ*/

require_once './conf/conf.inc.php';
require_once './iphone/conf.inc.php';

require_once P2_LIB_DIR . '/filectl.class.php';
require_once P2_LIB_DIR . '/P2View.php';
$_login->authorize(); // ユーザ認証

// {{{ ホストの同期用設定

if (!isset($rh_idx))     { $rh_idx     = $_conf['pref_dir'] . '/p2_res_hist.idx'; }
if (!isset($palace_idx)) { $palace_idx = $_conf['pref_dir'] . '/p2_palace.idx'; }

$synctitle = array(
    basename($_conf['favita_path'])  => 'お気に板',
    basename($_conf['favlist_file']) => 'お気にスレ',
    basename($_conf['rct_file'])     => '最近読んだスレ',
    basename($rh_idx)                => '書き込み履歴',
    basename($palace_idx)            => 'スレの殿堂'
);

// }}}
// {{{ 設定変更処理

// ホストを同期する
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
// {{{ 書き出し用変数

$ptitle = '設定管理';

if ($_conf['ktai']) {
    $status_st      = 'ステータス';
    $autho_user_st  = '認証ユーザー';
    $client_host_st = '端末ホスト';
    $client_ip_st   = '端末IPアドレス';
    $browser_ua_st  = 'ブラウザUA';
    $p2error_st     = 'p2エラー';
} 

$autho_user_ht = '';

// }}}

//=========================================================
// HTMLを表示する
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
<a class="button" href="edit_conf_user_i.php?b=k">ユーザ設定</a>
<a id="backButton" class="button" href="./iphone.php">TOP</a>
</div>
EOP;




// iPhone用表示 NG/ｱﾎﾞﾝﾜｰﾄﾞ

$ng_name_txt_bn = basename($ng_name_txt);
$ng_mail_txt_bn = basename($ng_mail_txt);
$ng_msg_txt_bn = basename($ng_msg_txt);
$ng_id_txt_bn = basename($ng_id_txt);
$aborn_name_txt_bn = basename($aborn_name_txt);
$aborn_mail_txt_bn = basename($aborn_mail_txt);
$aborn_msg_txt_bn = basename($aborn_msg_txt);
$aborn_id_txt_bn = basename($aborn_id_txt);
echo <<<EOP
<ul><li class="group">NG/アボンワード編集</li></ul>
<div id="usage" class="panel"><filedset>
<form method="GET" action="edit_aborn_word.php">
{$_conf['k_input_ht']}
<select name="path">
<option value="{$ng_name_txt_bn}">NG:名前</option>
<option value="{$ng_mail_txt_bn}">NG:メール</option>
<option value="{$ng_msg_txt_bn}">NG:メール</option>
<option value="{$ng_id_txt_bn}">NG:ID</option>
<option value="{$aborn_name_txt_bn}">アボン:名前</option>
<option value="{$aborn_mail_txt_bn}">アボン:メール</option>
<option value="{$aborn_msg_txt_bn}">アボン:メッセージ</option>
<option value="{$aborn_id_txt_bn}">アボン:ID</option>
</select>
<input type="submit" value="編集">
</form>
</filedset></div>
EOP;



// 新着まとめ読みのキャッシュリンクHTMLを表示する
echo <<<EOP
<ul><li class="group">新着まとめ</li></ul>
<div id="usage" class="panel">
<h2>前回キャッシュ表示</h2>
<filedset>
EOP;

printMatomeCacheLinksHtml();

echo <<<EOP
</filedset>
</div>
EOP;



// PC - ホストの同期 HTMLを表示 
$sync_htm = "<ul><li class=\"group\">ﾎｽﾄの同期</li></ul>\n<div id=\"usage\" class=\"panel\"><felidset>2chの板移転に対応します。<br>通常は自動で行われるので、この操作は特に必要ありません）\n";
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
    // echo "<p>ﾎｽﾄの同期は必要ありません</p>";
}

echo '</filedset><div>';
echo '</body></html>';


exit;


//==============================================================================
// 関数
//==============================================================================
/**
 * 設定ファイル編集ウインドウを開くフォームHTMLを表示する
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
 * ホストの同期用フォームのHTMLを取得する
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
 * 新着まとめ読みのキャッシュリンクHTMLを表示する
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

