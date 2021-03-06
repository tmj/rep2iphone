<?php
/* vim: set fileencoding=cp932 ai et ts=4 sw=4 sts=0 fdm=marker: */
/* mi: charset=Shift_JIS */

// p2 - 携帯版レスフィルタリング

require_once './conf/conf.inc.php';
require_once './iphone/conf.inc.php';


$_login->authorize(); // ユーザ認証

// {{{ スレッド情報

$host = $_GET['host'];
$bbs  = $_GET['bbs'];
$key  = $_GET['key'];
$ttitle = base64_decode($_GET['ttitle_en']);
$ttitle_back = (isset($_SERVER['HTTP_REFERER']))
    ? '<a href="' . htmlspecialchars($_SERVER['HTTP_REFERER'], ENT_QUOTES) . '" title="戻る">' . $ttitle . '</a>'
    : $ttitle;

// }}}
// {{{ 前回フィルタ値読み込み

require_once P2_LIB_DIR . '/filectl.class.php';

$cachefile = $_conf['pref_dir'] . '/p2_res_filter.txt';

if (file_exists($cachefile) and $res_filter_cont = file_get_contents($cachefile)) {
    $res_filter = unserialize($res_filter_cont);
}

$field = array('hole'=>'', 'msg'=>'', 'name'=>'', 'mail'=>'', 'date'=>'', 'id'=>'', 'beid'=>'', 'belv'=>'');
$match = array('on'=>'', 'off'=>'');
$method = array('and' => '', 'or' => '', 'just' => '', 'regex' => '', 'similar' => '');

$field[$res_filter['field']]   = ' selected';
$match[$res_filter['match']]   = ' selected';
$method[$res_filter['method']] = ' selected';

// }}}

/**
 * 検索フォームページ HTML表示
 * s1, s2と二つ検索 submit name があるけど一緒ぽい。s1, s2 は見ずに wordで判定している
 */
P2Util::header_nocache();
echo $_conf['doctype'];
echo <<<EOF
<html>
<head>
    {$_conf['meta_charset_ht']}
    <meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
    <link rel="stylesheet" type="text/css" href="./iui/read.css">
    <title>スレ内検索</title>
</head>
<body{$k_color_settings}>
<div class="toolbar">
<h1>{$ttitle_back}</h1>
<a id="backButton" class="button" href="iphone.php">TOP</a>
</div>


<form class="panel" id="header" method="get" action="{$_conf['read_php']}" accept-charset="{$_conf['accept_charset']}">
<h2>検索ワード</h2>
<filedset>
<input type="hidden" name="detect_hint" value="◎◇">
<input type="hidden" name="host" value="{$host}">
<input type="hidden" name="bbs" value="{$bbs}">
<input type="hidden" name="key" value="{$key}">
<input type="hidden" name="ls" value="all">
<input type="hidden" name="offline" value="1">
<input class="serch" id="word" name="word">
<input class="whitebutton" type="submit" name="s1" value="検索">
</filedset>
<br>
<h2>検索オプション</h2>
<filedset>
<select class="serch" id="field" name="field">
<option value="hole"{$field['hole']}>全体</option>
<option value="msg"{$field['msg']}>メッセージ</option>
<option value="name"{$field['name']}>名前</option>
<option value="mail"{$field['mail']}>メール</option>
<option value="date"{$field['date']}>日付</option>
<option value="id"{$field['id']}>ID</option>
<!-- <option value="belv"{$field['belv']}>ポイント</option> -->
</select>
に
<select class="serch" id="method" name="method">
<option value="or"{$method['or']}>いずれか</option>
<option value="and"{$method['and']}>すべて</option>
<option value="just"{$method['just']}>そのまま</option>
<option value="regex"{$method['regex']}>正規表現</option>
</select>
を
<select class="serch" id="match" name="match">
<option value="on"{$match['on']}>含む</option>
<option value="off"{$match['off']}>含まない</option>
</select><br>
<input class="whitebutton" type="submit" name="s2" value="検索">

{$_conf['k_input_ht']}
</form>

</filedset>
</div>
</body>
</html>
EOF;
