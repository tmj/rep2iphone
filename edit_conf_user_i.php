<?php
/*
    p2 - ユーザ設定編集UI
*/
/* 2008/7/25 iPhone用にカスタマイズ*/

require_once './conf/conf.inc.php';
require_once './iphone/conf.inc.php';

require_once P2_LIB_DIR . '/dataphp.class.php';

$_login->authorize(); // ユーザ認証

if (!empty($_POST['submit_save']) || !empty($_POST['submit_default'])) {
    if (!isset($_POST['csrfid']) or $_POST['csrfid'] != P2Util::getCsrfId()) {
        P2Util::printSimpleHtml("p2 error: 不正なポストです");
        die;
    }
}

//=====================================================================
// 前処理
//=====================================================================

// {{{ 保存ボタンが押されていたら、設定を保存

if (!empty($_POST['submit_save'])) {

    // 値の適正チェック、矯正
    
    // トリム
    $_POST['conf_edit'] = array_map('trim', $_POST['conf_edit']);
    
    // 選択肢にないもの → デフォルト矯正
    notSelToDef();
    
    // ルールを適用する
    applyRules();

    /**
     * デフォルト値 $conf_user_def と変更値 $_POST['conf_edit'] の両方が存在していて、
     * デフォルト値と変更値が異なる場合のみ設定保存する（その他のデータは保存されず、破棄される）
     */
    $conf_save = array();
    foreach ($conf_user_def as $k => $v) {
        if (isset($conf_user_def[$k]) && isset($_POST['conf_edit'][$k])) {
            if ($conf_user_def[$k] != $_POST['conf_edit'][$k]) {
                $conf_save[$k] = $_POST['conf_edit'][$k];
            }
            
        // 特別（edit_conf_user.php 以外でも設定されうるものは残す）
        } elseif (in_array($k, array('maru_kakiko'))) {
            $conf_save[$k] = $_conf[$k];
        }
    }

    // シリアライズして保存
    FileCtl::make_datafile($_conf['conf_user_file'], $_conf['conf_user_perm']);
    if (file_put_contents($_conf['conf_user_file'], serialize($conf_save), LOCK_EX) === false) {
        P2Util::pushInfoHtml("<p>×設定を更新保存できませんでした</p>");
        trigger_error("file_put_contents(" . $_conf['conf_user_file'] . ")", E_USER_WARNING);
        
    } else {
        P2Util::pushInfoHtml("<p>○設定を更新保存しました</p>");
        // 変更があれば、内部データも更新しておく
        $_conf = array_merge($_conf, $conf_user_def);
        $_conf = array_merge($_conf, $conf_save);
    }

// }}}
// {{{ デフォルトに戻すボタンが押されていたら

} elseif (!empty($_POST['submit_default'])) {
    if (file_exists($_conf['conf_user_file']) and unlink($_conf['conf_user_file'])) {
        P2Util::pushInfoHtml("<p>○設定をデフォルトに戻しました</p>");
        // 変更があれば、内部データも更新しておく
        $_conf = array_merge($_conf, $conf_user_def);
    }
}

// }}}

//=====================================================================
// プリント設定
//=====================================================================
$ptitle = 'ユーザ設定編集';

$csrfid = P2Util::getCsrfId();

$me = P2Util::getMyUrl();

//=====================================================================
// プリント
//=====================================================================
// ヘッダHTMLをプリント
P2Util::headerNoCache();
P2View::printDoctypeTag();
echo <<<EOP
<html lang="ja">
<head>
    {$_conf['meta_charset_ht']}
    <meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
    <meta http-equiv="Content-Style-Type" content="text/css">
    <meta http-equiv="Content-Script-Type" content="text/javascript">
    <script type="text/javascript" src="./iui/smooth.pack.js"></script>
<style type="text/css" media="screen">@import "./iui/iui.css";
body{background:url(iui/pinstripes.png)}input,select {float: right;}
</style>
    <title>{$ptitle}</title>\n
EOP;


echo <<<EOP
</head>
<body onLoad="top.document.title=self.document.title;">\n
<div class="toolbar">
<h1 id="pageTitle">{$ptitle}</h1>
<a name="top" id="backButton" class="button" href="./iphone.php">TOP</a>
</div>

EOP;


$htm['form_submit'] = <<<EOP
    <input class="whiteButton" type="submit" name="submit_save" value="変更を保存する">\n<br clear="right">
EOP;


P2Util::printInfoHtml();

echo <<<EOP
<form method="POST" action="{$_SERVER['SCRIPT_NAME']}" target="_self">
    <input type="hidden" name="csrfid" value="{$csrfid}">\n
    {$_conf['k_input_ht']}
EOP;

echo $htm['form_submit'];



echo getGroupSepaHtml('be.2ch.net アカウント');

echo getEditConfHtml('be_2ch_code', '<a href="http://be.2ch.net/" target="_blank">be.2ch.net</a>の認証コード(パスワードではありません)');
echo getEditConfHtml('be_2ch_mail', 'be.2ch.netの登録メールアドレス');

echo getGroupSepaHtml('PATH');

//echo getEditConfHtml('first_page', '右下部分に最初に表示されるページ。オンラインURLも可。');
echo getEditConfHtml('brdfile_online', 
    '板リストの指定（オンラインURL）<br>
    板リストをオンラインURLから自動で読み込む。
    指定先は menu.html 形式、2channel.brd 形式のどちらでもよい。
    <!-- 必要なければ、空白に。 --><br>

    2ch基本 <a href="http://menu.2ch.net/bbsmenu.html" target="_blank">http://menu.2ch.net/bbsmenu.html</a><br>
    2ch + 外部BBS <a href="http://azlucky.s25.xrea.com/2chboard/bbsmenu.html" target="_blank">http://azlucky.s25.xrea.com/2chboard/bbsmenu.html</a><br>
    ');


/*080725 一部iPhone用に削除 */
echo getGroupSepaHtml('subject');

echo getEditConfHtml('refresh_time', 'スレッド一覧の自動更新間隔 (分指定。0なら自動更新しない)');

echo getEditConfHtml('sb_show_motothre', 'スレッド一覧で未取得スレに対して元スレへのリンク（・）を表示 (する, しない)');
// echo getEditConfHtml('sb_show_one', 'PC閲覧時、スレッド一覧（板表示）で>>1を表示 (する, しない, ニュース系のみ)');
echo getEditConfHtml('k_sb_show_first', 'iPhoneのスレッド一覧（板表示）から初めてのスレを開く時の表示方法 (ﾌﾟﾚﾋﾞｭｰ>>1, 1からN件表示, 最新N件表示)');
echo getEditConfHtml('sb_show_spd', 'スレッド一覧ですばやさ（レス間隔）を表示 (する, しない)');
echo getEditConfHtml('sb_show_ikioi', 'スレッド一覧で勢い（1日あたりのレス数）を表示 (する, しない)');
echo getEditConfHtml('sb_show_fav', 'スレッド一覧でお気にスレマーク★を表示 (する, しない)');
echo getEditConfHtml('sb_sort_ita', '板表示のスレッド一覧でのデフォルトのソート指定');
echo getEditConfHtml('sort_zero_adjust', '新着ソートでの「既得なし」の「新着数ゼロ」に対するソート優先順位 (上位, 混在, 下位)');
echo getEditConfHtml('cmp_dayres_midoku', '勢いソート時に新着レスのあるスレを優先 (する, しない)');
echo getEditConfHtml('k_sb_disp_range', 'iPhone閲覧時、一度に表示するスレの数');
echo getEditConfHtml('viewall_kitoku', '既得スレは表示件数に関わらず表示 (する, しない)');

echo getGroupSepaHtml('read');

echo getEditConfHtml('respointer', 'スレ内容表示時、未読の何コ前のレスにポインタを合わせるか');
//echo getEditConfHtml('before_respointer', 'PC閲覧時、ポインタの何コ前のレスから表示するか');
echo getEditConfHtml('before_respointer_new', '新着まとめ読みの時、ポインタの何コ前のレスから表示するか');
echo getEditConfHtml('rnum_all_range', '新着まとめ読みで一度に表示するレス数');
echo getEditConfHtml('preview_thumbnail', '画像URLの先読みサムネイルを表示（する, しない)');
echo getEditConfHtml('pre_thumb_limit', '画像URLの先読みサムネイルを一度に表示する制限数');
//echo getEditConfHtml('preview_thumbnail', '画像サムネイルの縦の大きさを指定 (ピクセル)');
////echo getEditConfHtml('pre_thumb_width', '画像サムネイルの横の大きさを指定 (ピクセル)');
//echo getEditConfHtml('link_youtube', 'YouTubeのリンクをプレビュー表示（する, しない)');
//echo getEditConfHtml('link_niconico', 'ニコニコ動画のリンクをプレビュー表示（する, しない)');
echo getEditConfHtml('iframe_popup', 'HTMLポップアップ (する, しない, pでする, 画像でする)');
//echo getEditConfHtml('iframe_popup_delay', 'HTMLポップアップの表示遅延時間 (秒)');
echo getEditConfHtml('flex_idpopup', 'スレ内で同じ ID:xxxxxxxx があれば、IDフィルタ用のリンクに変換 (する, しない)');
echo getEditConfHtml('ext_win_target', '外部サイト等へジャンプする時に開くウィンドウのターゲット名 (同窓:"", 新窓:"_blank")');
echo getEditConfHtml('bbs_win_target', 'p2対応BBSサイト内でジャンプする時に開くウィンドウのターゲット名 (同窓:"", 新窓:"_blank")');
//echo getEditConfHtml('bottom_res_form', 'スレッド下部に書き込みフォームを表示 (マウスオーバーでする, 常にする, しない)');
echo getEditConfHtml('quote_res_view', '引用レスを表示 (する, しない)');

if (!$_conf['ktai']) {
    echo getEditConfHtml('enable_headbar', 'PC ヘッドバーを表示 (する, しない)');
    echo getEditConfHtml('enable_spm', 'レス番号からスマートポップアップメニュー(SPM)を表示 (する, しない)');
    //echo getEditConfHtml('spm_kokores', 'スマートポップアップメニューで「これにレス」を表示');
}

echo getEditConfHtml('k_rnum_range', '携帯閲覧時、一度に表示するレスの数');
echo getEditConfHtml('ktai_res_size', '携帯閲覧時、一つのレスの最大表示サイズ');
echo getEditConfHtml('ktai_ryaku_size', '携帯閲覧時、レスを省略したときの表示サイズ');
echo getEditConfHtml('k_aa_ryaku_size', '携帯閲覧時、AAらしきレスを省略するサイズ（0なら省略しない）');
echo getEditConfHtml('before_respointer_k', '携帯閲覧時、ポインタの何コ前のレスから表示するか');
echo getEditConfHtml('k_use_tsukin', '携帯閲覧時、外部リンクに(窓)を利用(する, しない)');
echo getEditConfHtml('k_use_picto', '携帯閲覧時、画像リンクにpic.to(ﾋﾟ)を利用(する, しない)');

echo getEditConfHtml('k_bbs_noname_name', '携帯閲覧時、デフォルトの名無し名を表示（する, しない）');
echo getEditConfHtml('k_clip_unique_id', '携帯閲覧時、重複しないIDは末尾のみの省略表示（する, しない）');
echo getEditConfHtml('k_date_zerosuppress', '携帯閲覧時、日付の0を省略表示（する, しない）');
echo getEditConfHtml('k_clip_time_sec', '携帯閲覧時、時刻の秒を省略表示（する, しない）');
echo getEditConfHtml('mobile.id_underline', '携帯閲覧時、ID末尾の"O"（オー）に下線を追加（する, しない）');
echo getEditConfHtml('k_copy_divide_len', '携帯観覧時、「写」のコピー用テキストボックスを分割する文字数');

echo getGroupSepaHtml('ETC');

echo getEditConfHtml('my_FROM', 'レス書き込み時のデフォルトの名前');
echo getEditConfHtml('my_mail', 'レス書き込み時のデフォルトのmail');

//echo getEditConfHtml('editor_srcfix', 'PC閲覧時、ソースコードのコピペに適した補正をするチェックボックスを表示（する, しない, pc鯖のみ）');

echo getEditConfHtml('get_new_res', '新しいスレッドを取得した時に表示するレス数(全て表示する場合:"all")');
echo getEditConfHtml('rct_rec_num', '最近読んだスレの記録数');
echo getEditConfHtml('res_hist_rec_num', '書き込み履歴の記録数');
echo getEditConfHtml('res_write_rec', '書き込み内容ログを記録(する, しない)');
echo getEditConfHtml('through_ime', '外部URLジャンプする際に通すゲート (直接:"", p2 ime(自動転送):"p2", p2 ime(手動転送):"p2m", p2 ime(pのみ手動転送):"p2pm")');
echo getEditConfHtml('join_favrank', '<a href="http://akid.s17.xrea.com/favrank/favrank.html" target="_blank">お気にスレ共有</a>に参加(する, しない)');
echo getEditConfHtml('enable_menu_new', '板メニューに新着数を表示 (する:1, しない:0, お気に板のみ:2)');
echo getEditConfHtml('menu_refresh_time', '板メニュー部分の自動更新間隔 (分指定。0なら自動更新しない。)');
echo getEditConfHtml('mobile.match_color', '携帯閲覧時、フィルタリングでマッチしたキーワードの色');
echo getEditConfHtml('k_save_packet', '携帯閲覧時、パケット量を減らすため、全角英数・カナ・スペースを半角に変換 (する, しない)');
echo getEditConfHtml('ngaborn_daylimit', 'この期間、NGあぼーんにHITしなければ、登録ワードを自動的に外す（日数）');
echo getEditConfHtml('proxy_use', 'プロキシを利用 (する, しない)'); 
echo getEditConfHtml('proxy_host', 'プロキシホスト ex)"127.0.0.1", "www.p2proxy.com"'); 
echo getEditConfHtml('proxy_port', 'プロキシポート ex)"8080"'); 
echo getEditConfHtml('precede_openssl', '●ログインを、まずはopensslで試みる。※PHP 4.3.0以降で、OpenSSLが静的にリンクされている必要がある。');
echo getEditConfHtml('precede_phpcurl', 'curlを使う時、コマンドライン版とPHP関数版どちらを優先するか (コマンドライン版:0, PHP関数版:1)');



echo $htm['form_submit'];


echo '</form>' . "\n";


echo '</body></html>';

exit;


//=====================================================================
// 関数 （このファイル内でのみ利用）
//=====================================================================
/**
 * ルール設定（$conf_user_rules）に基づいて、フィルタ処理（デフォルトセット）を行う
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

// emptyToDef() などのフィルタはEditConfFiterクラスなどにまとめる予定

/**
 * CSS値のためのフィルタリングを行う
 */
function filterCssValue($str, $def = '')
{
    return preg_replace('/[^0-9a-zA-Z-%]/', '', $str);
}

/**
 * emptyの時は、デフォルトセットする
 */
function emptyToDef($val, $def)
{
    if (empty($val)) {
        $val = $def;
    }
    return $val;
}

/**
 * 正の整数化できる時は正の整数化（0を含む）し、
 * できない時は、デフォルトセットする
 */
function notIntExceptMinusToDef($val, $def)
{
    // 全角→半角 矯正
    $val = mb_convert_kana($val, 'a');
    // 整数化できるなら
    if (is_numeric($val)) {
        // 整数化する
        $val = intval($val);
        // 負の数はデフォルトに
        if ($val < 0) {
            $val = intval($def);
        }
    // 整数化できないものは、デフォルトに
    } else {
        $val = intval($def);
    }
    return $val;
}

/**
 * 選択肢にない値はデフォルトセットする
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
 * グループ分け用のHTMLを得る（関数内でPC、携帯用表示を振り分け）
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
 * 編集フォームinput用HTMLを得る（関数内でPC、携帯用表示を振り分け）
 *
 * @return  string
 */
function getEditConfHtml($name, $description_ht)
{
    global $_conf, $conf_user_def, $conf_user_sel;

    // デフォルト値の規定がなければ、空白を返す
    if (!isset($conf_user_def[$name])) {
        return '';
    }

    $name_view = $_conf[$name];
    
    if (empty($_conf['ktai'])) {
        $input_size_at = ' size="38"';
    } else {
        $input_size_at = '';
    }
    
    // select 選択形式なら
    if (isset($conf_user_sel[$name])) {
        $form_ht = getEditConfSelHtml($name);
        $key = $conf_user_def[$name];
        $def_views[$name] = htmlspecialchars($conf_user_sel[$name][$key], ENT_QUOTES);
    // input 入力式なら
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
 * 編集フォームselect用HTMLを得る
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

