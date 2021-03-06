<?php
/*
    p2 - レス書き込みフォーム
*/

require_once './conf/conf.inc.php';
require_once './iphone/conf.inc.php';
require_once P2_LIB_DIR . '/dataphp.class.php';

$_login->authorize(); // ユーザ認証

//==================================================
// 変数
//==================================================
if (empty($_GET['host'])) {
    // 引数エラー
    P2Util::printSimpleHtml('p2 error: host が指定されていません');
    die;
} else {
    $host = $_GET['host'];
}

$bbs = isset($_GET['bbs']) ? $_GET['bbs'] : '';
$key = isset($_GET['key']) ? $_GET['key'] : '';

$rescount   = isset($_GET['rescount']) ? intval($_GET['rescount']) : 1;
$popup      = isset($_GET['popup']) ? intval($_GET['popup']) : 0;

$itaj = P2Util::getItaName($host, $bbs);
if (!$itaj) { $itaj = $bbs; }

$ttitle_en  = isset($_GET['ttitle_en']) ? $_GET['ttitle_en'] : '';
$ttitle     = (strlen($ttitle_en) > 0) ? base64_decode($ttitle_en) : '';
$ttitle_hs  = htmlspecialchars($ttitle, ENT_QUOTES);

$idx_host_dir = P2Util::idxDirOfHost($host);
$key_idx = $idx_host_dir . '/' . $bbs . '/' . $key . '.idx';

// フォームのオプション読み込み
require_once P2_LIB_DIR . '/post_options_loader.inc.php';

// 表示指定
if (!$_conf['ktai']) {
    $class_ttitle = ' class="thre_title"';
    $target_read = ' target="read"';
    $sub_size_at = ' size="40"';
}

// {{{ スレ立てなら

if (!empty($_GET['newthread'])) {
    //$ptitle = "{$itaj} - 新規スレッド作成";
    $ptitle = "新規スレッド作成";
    // machibbs、JBBS@したらば なら
    if (P2Util::isHostMachiBbs($host) or P2Util::isHostJbbsShitaraba($host)) {
        $submit_value = "新規書き込み";
    // 2chなら
    } else {
        $submit_value = "新規スレッド作成";
    }
    
    $htm['subject'] = <<<EOP
<div class="row"><label><span{$class_ttitle}>タイトル</span></label><input type="text" id="subject" name="subject"{$sub_size_at} value="{$hd['subject']}"></div>
EOP;
    if ($_conf['ktai']) {
        //$htm['subject'] = "<a id=\"backButton\" class=\"button\" href=\"{$_conf['subject_php']}?host={$host}&amp;bbs={$bbs}{$_conf['k_at_a']}\">{$itaj}</a><br>" . $htm['subject'];
    $htm['back'] = "<a id=\"backButton\" class=\"button\" href=\"{$_conf['subject_php']}?host={$host}&amp;bbs={$bbs}{$_conf['k_at_a']}\">{$itaj}</a>";
    }
    $newthread_hidden_ht = '<input type="hidden" name="newthread" value="1">';

// }}}
// {{{ 書き込みなら

} else {
    //$ptitle = "{$itaj} - レス書き込み";
    $ptitle = "レス書き込み";
    $submit_value = "書き込む";

    $htm['resform_ttitle'] = <<<EOP
<p><a id="backButton" class="button" href="{$_conf['read_php']}?host={$host}&amp;bbs={$bbs}&amp;key={$key}{$_conf['k_at_a']}"{$target_read}>{$ttitle_hs}</a></p>
EOP;
    $newthread_hidden_ht = '';
}

// }}}

$readnew_hidden_ht = !empty($_GET['from_read_new']) ? '<input type="hidden" name="from_read_new" value="1">' : '';


//==========================================================
// HTML 表示出力
//==========================================================
if (!$_conf['ktai']) {
    $body_on_load = <<<EOP
 onLoad="setFocus('MESSAGE'); checkSage();"
EOP;
}

echo $_conf['doctype'];
echo <<<EOHEADER
<html lang="ja">
<head>
    {$_conf['meta_charset_ht']}
    <meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
    <meta http-equiv="Content-Style-Type" content="text/css">
    <meta http-equiv="Content-Script-Type" content="text/javascript">
<style type="text/css" media="screen">@import "./iui/iui.css";</style>
<meta name="viewport" content="width=320; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;"/>
<script type="text/javascript"> 
<!-- 
window.onload = function() { 
setTimeout(scrollTo, 100, 0, 1); 
} 
// --> 
</script> 
<title>{$ptitle}</title>\n
EOHEADER;
if (!$_conf['ktai']) {
    include_once './style/style_css.inc';
    include_once './style/post_css.inc';
echo <<<EOSCRIPT
    <script type="text/javascript" src="js/basic.js?v=20061206"></script>
    <script type="text/javascript" src="js/post_form.js?v=20061209"></script>\n
EOSCRIPT;
}
echo <<<EOP
</head>
<body{$body_on_load}>\n
<div class="toolbar">
<h1 id="pageTitle">{$itaj}</h1>
</div>

EOP;

P2Util::printInfoHtml();

// $htm['post_form'] を取得
require_once P2_IPHONE_LIB_DIR . '/post_form.inc.php';

echo $htm['post_form'];

echo '</body></html>';

