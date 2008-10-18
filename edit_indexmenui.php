<?php
// p2 �g��TOP���j���[�̕ҏW

require_once './conf/conf.inc.php';
require_once './iphone/conf.inc.php';
require_once P2_LIB_DIR . '/filectl.class.php';

require_once P2_LIB_DIR . '/UA.php';

$_login->authorize(); // ���[�U�F��

// {{{ ����ȑO����

// ���ёւ�
if (isset($_GET['code']) && isset($_GET['set'])) {
    _setOrderIndexMenuK($_GET['code'], $_GET['set']);

} elseif (isset($_REQUEST['setfrom1'])) {
    P2Util::setConfUser('index_menu_k_from1', (int)$_REQUEST['setfrom1']);

// �f�t�H���g�ɖ߂�
} elseif (isset($_GET['setdef'])) {
    P2Util::setConfUser('index_menu_k', $conf_user_def['index_menu_k']);
    P2Util::setConfUser('index_menu_k_from1', $conf_user_def['index_menu_k_from1']);
}

// }}}

require_once P2_LIB_DIR . '/index_print_k.inc.php';

$setfrom1 = (int) !$_conf['index_menu_k_from1'];

$menuKLinkHtmls = getMenuKLinkHtmls($_conf['menuKIni'], $noLink = true);

$body_at    = P2View::getBodyAttrK();
$hr         = P2View::getHrHtmlK();

//================================================================
// �w�b�_HTML�\��
//================================================================
P2Util::headerNoCache();
P2View::printDoctypeTag();
?>
<html lang="ja">
<head>
<?php
P2View::printHeadMetasHtml();
?>
    <title>rep2 - �g��TOP�ƭ��̕��ёւ�</title>
<?php

if (!$_conf['ktai']) {
    P2View::printIncludeCssHtml('style');
    P2View::printIncludeCssHtml('editfavita');
}

echo <<<EOP
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
<style type="text/css" media="screen">@import "./iui/iui.css";</style>
</head>
<body{$body_at}>\n
EOP;

P2Util::printInfoHtml();


// ���ёւ� ���C��HTML���o�͂���


if (UA::isK()) {
    echo P2View::getBackToIndexKATag();
    echo '<hr>';
}
?>
<div class="toolbar">
<h1 id="pageTitle">���j���[����</h1>
<a id="backButton" class="button" href="./iphone.php">TOP</a>
</div>
<div id="usage" class="panel"><filedset>
<table>
<?php
foreach ($menuKLinkHtmls as $code => $html) {
    echo <<<EOP
    <tr>
        <td>$html</td>
        <td><a class="te" href="{$_SERVER['SCRIPT_NAME']}?code={$code}&amp;set=top{$_conf['k_at_a']}" title="��ԏ�Ɉړ�">��</a></td>
        <td><a class="te" href="{$_SERVER['SCRIPT_NAME']}?code={$code}&amp;set=up{$_conf['k_at_a']}" title="���Ɉړ�">��</a></td>
        <td><a class="te" href="{$_SERVER['SCRIPT_NAME']}?code={$code}&amp;set=down{$_conf['k_at_a']}" title="����Ɉړ�">��</a></td>
        <td><a class="te" href="{$_SERVER['SCRIPT_NAME']}?code={$code}&amp;set=bottom{$_conf['k_at_a']}" title="��ԉ��Ɉړ�">��</a></td>
    </tr>
EOP;
}
?>
</table>
<a class='whiteButton' href="<?php eh($_SERVER['SCRIPT_NAME']); ?>?setdef=1<?php echo $_conf['k_at_a']; ?>">��̫�Ăɖ߂�</a>
</filedset>
</div>
<?php
// �t�b�^HTML��\������

if (UA::isK()) {
    echo $hr . P2View::getBackToIndexKATag();
}

?></body></html><?php

exit;

//======================================================================
// �֐��i���̃t�@�C�����ł̂ݗ��p�j
//======================================================================
/**
 * �g��TOP���j���[�̏��Ԃ�ύX����֐�
 *
 * @access  public
 * @param   string  $code
 * @param   string  $set  top, up, down, bottom
 * @return  boolean
 */
function _setOrderIndexMenuK($code, $set)
{
    global $_conf;

    if (!preg_match('/^[\\w]+$/', $code) || !preg_match('/^[\\w]+$/', $set)) {
        P2Util::pushInfoHtml('<p>p2 error: �������ςł�</p>');
        return false;
    }
    
    /*
$_conf['index_menu_k'] = array(
    'recent_shinchaku', // 0.�ŋߓǂ񂾽ڂ̐V��
    'recent',           // 1.�ŋߓǂ񂾽ڂ̑S��
    'fav_shinchaku',    // 2.���C�ɽڂ̐V��
    'fav',              // 3.���C�ɽڂ̑S��
    'favita',           // 4.���C�ɔ�
    'cate',             // 5.��ؽ�
    'res_hist',         // 6.�������� #.۸�
    'palace',           // 7.�ڂ̓a��
    'setting',          // 8.۸޲݊Ǘ�
    'editpref'          // 9.�ݒ�Ǘ�
);
*/
    /*
    // �����ȃR�[�h
    if (!in_array($code, $_conf['index_menu_k'])) {
        return false;
    }
    */
    $menu = $_conf['index_menu_k'];
    if (false === $menu = _getMenuKMovedIndex($menu, $code, $set)) {
        return false;
    }

    if (false === P2Util::setConfUser('index_menu_k', $menu)) {
        return false;
    }
    
    return true;
}

/**
 * @param  array   $menu
 * @param  integer $to      0-
 * @param  string  $code
 * @return array
 */
function _getMenuKMovedIndex($menu, $code, $set)
{
    if (false === $r = _getMenuKIndexToMove($menu, $code, $set)) {
        return false;
    }
    list($from, $to) = $r;
    
    return _getArrayMovedIndex($menu, $from, $to);
}

/**
 * @param  array   $menu
 * @param  string  $code
 * @param  string  $set
 * return  integer|false
 */
function _getMenuKIndexToMove($menu, $code, $set)
{
    if (false === $from = array_search($code, $menu)) {
        return false;
    }
    if ($set == 'top') {
        $to = 0;
    } elseif ($set == 'up') {
        $to = max(0, $from - 1);
    } elseif ($set == 'down') {
        $to = min($from + 1, count($menu));
    } elseif ($set == 'bottom') {
        $to = count($menu);
    } else {
        return false;
    }
    return array($from, $to);
}

/**
 * @param  array   $array
 * @param  integer $from    0-
 * @param  integer $to      0-
 * @return array
 */
function _getArrayMovedIndex($array, $from, $to)
{
    if ($from == $to) {
        return $array;
    }
    $item = $array[$from];
    $post = $array;
    $div = ($from < $to) ? $to + 1 : $to;
    $pre  = array_splice($post, 0, $div);
    if ($from < $to) {
        unset($pre[$from]);
    } elseif ($to < $from) {
        unset($post[$from - $to]);
    }
    $newArray = array_merge($pre, array($item), $post);
    $newArray = array_unique($newArray);
    return $newArray;
}
