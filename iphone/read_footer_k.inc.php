<?php
/*
    p2 -  �X���b�h�\�� -  �t�b�^���� -  �g�їp for read.php
*/
//require_once P2_LIB_DIR . '/dataphp.class.php';
//=====================================================================
// �t�b�^
//=====================================================================
// �\���͈�
if (isset($GLOBALS['word']) && $aThread->rescount) {
    $filter_range['end'] = min($filter_range['to'], $_filter_hits);
    $read_range_on = "{$filter_range['start']}-{$filter_range['end']}/{$_filter_hits}hit";

} elseif ($aThread->resrange_multi) {
    $read_range_on = hs($aThread->ls);

} elseif ($aThread->resrange['start'] == $aThread->resrange['to']) {
    $read_range_on = $aThread->resrange['start'];

} else {
    $read_range_on = "{$aThread->resrange['start']}-{$aThread->resrange['to']}";
}

$read_range_hs = $read_range_on . '/' . $aThread->rescount;
if (!empty($_GET['onlyone'])) {
    $read_range_hs = '�v���r���[>>1';
}

// ���X�Ԏw��ړ� etc. iphone
//$goto_ht = _kspform($aThread, isset($GLOBALS['word']) ? $last_hit_resnum : $aThread->resrange['to']);

// �t�B���^�[�\�� Edit 080727 by 240
$seafrm_ht =  CreateFilterForm(isset($GLOBALS['word']) ? $last_hit_resnum : $aThread->resrange['to'], $aThread);
$hr = P2View::getHrHtmlK();


//=====================================================================
// HTML�o��
//=====================================================================
if (($aThread->rescount or !empty($_GET['onlyone']) && !$aThread->diedat)) { // and (!$_GET['renzokupop'])

    if (!$aThread->diedat) {
        if (!empty($_conf['disable_res'])) {
            $dores_ht = <<<EOP
      | <a href="{$motothre_url}" target="_blank" >{$dores_st}</a>
EOP;
        } else {
            $dores_ht = <<<EOP
<a href="post_form_i.php?host={$aThread->host}{$bbs_q}{$key_q}&amp;rescount={$aThread->rescount}{$ttitle_en_q}{$_conf['k_at_a']}" >{$dores_st}</a>
EOP;
        }
    }
//iPhone �\���p�t�b�^ 080725
//�@�O�A���A�V�� �������͍�
if($read_navi_latest_btm){
   $new_btm = "<li class=\"new\">{$read_navi_latest_btm}</li>";
}
if($read_footer_navi_new_btm){
    $new_btm = "<li class=\"new\">{$read_footer_navi_new_btm}</li>"; 
}
if($read_navi_previous){ 
    $read_navi_previous_tab = "<li class=\"prev\">{$read_navi_previous} </li>";
}else{
    $read_navi_previous_tab = "<li id=\"blank\" class=\"prev\"></li>";
}
    if($read_navi_next_btm){
    $read_navi_next_btm_tab = "<li class=\"next\">{$read_navi_next_btm}</li>";
}else{
    $read_navi_next_btm_tab = "<li id=\"blank\" class=\"next\"></li>";
}
    echo <<<EOP
{$toolbar_back_board}
<div class="footform">
<a id="footer" name="footer"></a>
{$goto_select_ht}
</div>
<div id="footbar01">
  <div class="footbar">
    <ul>
    <li class="home"><a href="iphone.php">TOP</a></li>
    {$read_navi_previous_tab} 
    {$new_btm}
    <li class="res" id="writeId" title="off"><a onclick="footbarFormPopUp(1);all.item('footbar02').style.visibility='hidden';">��������</a></li>
    <li class="other"><a onclick="all.item('footbar02').style.visibility='visible';footbarFormPopUp(0, 1);footbarFormPopUp(1, 1);">���̑�</a></li>
    {$read_navi_next_btm_tab}
    </ul>
  </div>
</div>
<div id="footbar02" class="dialog_other">
<filedset>
<ul>
    <li class="whiteButton" id="serchId" title="off" onclick="footbarFormPopUp(0);all.item('footbar02').style.visibility='hidden'">�t�B���^����</li>
    {$toolbar_right_ht} 
    <li class="grayButton" onclick="all.item('footbar02').style.visibility='hidden'">�L�����Z��</li>
</ul>
</filedset>
</div>
{$seafrm_ht}
EOP;

/* �������݃t�H�[��------------------------------------ */
    $bbs        = $aThread->bbs;
    $key        = $aThread->key;
    $host       = $aThread->host;
    $rescount   = $aThread->rescount;
    $ttitle_en  = base64_encode($aThread->ttitle);
    
    $submit_value = '��������';

    $key_idx = $aThread->keyidx;

    // �t�H�[���̃I�v�V�����ǂݍ���
    require_once P2_IPHONE_LIB_DIR . '/post_options_loader_popup.inc.php';

// sage�`�F�b�N�{�^���̍쐬
        $on_check_sage = ' onChange="checkSage();"';
    	$sage_cb_ht = <<<EOP
<input id="sage" type="checkbox" onClick="mailSage();">
EOP;

// �X���b�h�^�C�g���̍쐬
    $htm['resform_ttitle'] = <<<EOP
<p><b class="thre_title">{$aThread->ttitle_hd}</b></p>
EOP;

// �t�H�[���̍쐬
   require_once P2_IPHONE_LIB_DIR . '/post_form_popup.inc.php';

    $res_form_ht = <<<EOP
{$htm['post_form']}
EOP;

    $onmouse_showform_ht = <<<EOP
 onMouseover="document.getElementById('kakiko').style.display = 'block';"
EOP;


$sid_q = defined('SID') ? '&amp;' . strip_tags(SID) : '';

    // �v�����g
    echo <<<EOP
{$res_form_ht}
EOP;
/* ------------------------------------------------------------ */
    if ($diedat_msg) {
        echo '<hr>';
        echo $diedat_msg;
        echo '<p>';
        echo  $motothre_ht;
        echo '</p>' . "\n";
    }
}
//echo "<hr>" . $_conf['k_to_index_ht'] . "\n";
/*
080726 �t�b�^�ύX�̂��ߍ폜��������
<ul><li class="group">{$hd['read_range']}</li></ul>
<div id="usage" class="panel">
<div class="row"><label>
{$goto_ht}\n
</label>
</div>
</div>
*/

echo '</body></html>';


//=====================================================================
// �֐�
//=====================================================================
/**
 * ���X�ԍ����w�肵�� �ړ��E�R�s�[(+���p)�EAAS ����t�H�[���𐶐�����
 *
 * @return string
 */
function _kspform($default = '', &$aThread)
{
    global $_conf;

    //$numonly_at = 'maxlength="4" istyle="4" format="*N" mode="numeric"';
    $numonly_at = 'maxlength="4" istyle="4" format="4N" mode="numeric"';

    $form = "<form method=\"get\" action=\"{$_conf['read_php']}\">";
    $form .= $_conf['k_input_ht'];

    $required_params = array('host', 'bbs', 'key');
    foreach ($required_params as $k) {
        if (!empty($_REQUEST[$k])) {
            $v = htmlspecialchars($_REQUEST[$k], ENT_QUOTES);
            $form .= "<input type=\"hidden\" name=\"{$k}\" value=\"{$v}\">";
        } else {
            return '';
        }
    }
    $form .= '<input type="hidden" name="offline" value="1">';
    $form .= '<input type="hidden" name="rescount" value="' . $aThread->rescount . '">';
    $form .= '<input type="hidden" name="ttitle_en" value="' . base64_encode($aThread->ttitle) . '">';

    $form .= '<select name="ktool_name">';
    $form .= '<option value="goto">GO</option>';
    $form .= '<option value="copy">��</option>';
    $form .= '<option value="copy_quote">&gt;��</option>';
    $form .= '<option value="res_quote">&gt;ڽ</option>';
    /*
    2006/03/06 aki �m�[�}��p2�ł͖��Ή�
    if ($_conf['expack.aas.enabled']) {
        $form .= '<option value="aas">AAS</option>';
        $form .= '<option value="aas_rotate">AAS*</option>';
    }
    */
    $form .= '</select>';

    $form .= "<input type=\"text\" size=\"3\" name=\"ktool_value\" value=\"{$default}\" {$numonly_at}>";
    $form .= '<input type="submit" value="OK" title="OK">';

    $form .= '</form>';

    return $form;
}
/**
 * �� <a>
 *
 * @return  string  HTML
 */
function _getDoResATag($aThread, $dores_st, $motothre_url)
{
    global $_conf;
    
    $dores_atag = null;
    
    if (!empty($_conf['disable_res'])) {
        $dores_atag = P2View::tagA(
            $motothre_url,
            hs("{$_conf['k_accesskey']['res']}.{$dores_st}"),
            array(
                'target' => '_blank',
                $_conf['accesskey'] => $_conf['k_accesskey']['res']
            )
        );

    } else {
        $dores_atag = P2View::tagA(
            P2Util::buildQueryUri(
                'post_form.php',
                array(
                    'host' => $aThread->host,
                    'bbs'  => $aThread->bbs,
                    'key'  => $aThread->key,
                    'rescount' => $aThread->rescount,
                    'ttitle_en' => base64_encode($aThread->ttitle),
                    UA::getQueryKey() => UA::getQueryValue()
                )
            ),
            hs("{$_conf['k_accesskey']['res']}.{$dores_st}"),
            array(
                $_conf['accesskey'] => $_conf['k_accesskey']['res']
            )
        );
    }
    
    return $dores_atag;
}
//=====================================================================
// �֐�
//=====================================================================
/**
 * �t�B���^�[�\���t�H�[�����쐬����
 * Edit 080727 by 240
 * @return string
 */
function CreateFilterForm($default = '', &$aThread)
{
	global $_conf;
    global $res_filter, $read_navi_prev_header; // read only
    // read_footer.inc.php �ł��Q�Ƃ��Ă���
    global $all_st, $latest_st, $motothre_url, $p2frame_ht, $toolbar_right_ht, $goto_ht;
    global $rnum_range, $latest_show_res_num; // conf�ɂ��������悳����
    
    $headbar_htm = '';
    
    // {{{ ���X�t�B���^ form HTML

    if ($aThread->rescount and empty($_GET['renzokupop'])) {

        $selected_field = array('hole' => '', 'name' => '', 'mail' => '', 'date' => '', 'id' => '', 'msg' => '');
        $selected_field[($res_filter['field'])] = ' selected';

        $selected_match = array('on' => '', 'off' => '');
        $selected_match[($res_filter['match'])] = ' selected';
    
        // �g������
        if ($_conf['enable_exfilter']) {
            $selected_method = array('and' => '', 'or' => '', 'just' => '', 'regex' => '');
            $selected_method[($res_filter['method'])] = ' selected';
            $select_method_ht = <<<EOP
    <select id="method" name="method">
        <option value="or"{$selected_method['or']}>�����ꂩ</option>
        <option value="and"{$selected_method['and']}>���ׂ�</option>
        <option value="just"{$selected_method['just']}>���̂܂�</option>
        <option value="regex"{$selected_method['regex']}>���K�\��</option>
    </select>
EOP;
        }
    
        $word_hs = htmlspecialchars($GLOBALS['word'], ENT_QUOTES);

	
	$headbar_htm = <<< EOP
	
<form id="searchForm" name="searchForm" class="dialog_filter" action="{$_conf['read_php']}" accept-charset="{$_conf['accept_charset']}" style="white-space:nowrap">
	<fieldset>
     	<select id="field" name="field">
			<option value="hole"{$selected_field['hole']}>�S��</option>
			<option value="name"{$selected_field['name']}>���O</option>
			<option value="mail"{$selected_field['mail']}>���[��</option>
			<option value="date"{$selected_field['date']}>���t</option>
			<option value="id"{$selected_field['id']}>ID</option>
			<option value="msg"{$selected_field['msg']}>ү����</option>
    	</select>
   		{$select_method_ht}
		<select id="match" name="match">
	        <option value="on"{$selected_match['on']}>�܂�</option>
	        <option value="off"{$selected_match['off']}>�܂܂Ȃ�</option>
    	</select>
    	<br>
		<label>Word:</label>
     	<input id="word" name="word" type="text" value="">
     	<br>
    	<input type="submit" class="whitebutton" id="s2" name="s2" value="�t�B���^�\��" onclick="footbarFormPopUp(0, 1)"><br><br>

		<input type="hidden" name="detect_hint" value="����">
	    <input type="hidden" name="bbs" value="{$aThread->bbs}">
	    <input type="hidden" name="key" value="{$aThread->key}">
	    <input type="hidden" name="host" value="{$aThread->host}">
	    <input type="hidden" name="ls" value="all">
	    <input type="hidden" name="offline" value="1">
	    <input type="hidden" name="b" value="k">

	</fieldset>

</form>\n

EOP;
	}

	return $headbar_htm;
}


