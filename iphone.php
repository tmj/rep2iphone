<?php
// rep2 -  �C���f�b�N�X�y�[�W

require_once './conf/conf.inc.php';
require_once './iphone/conf.inc.php';
require_once P2_LIB_DIR . '/filectl.class.php';

$_login->authorize(); // ���[�U�F��

// �O����
// �A�N�Z�X���ۗp��.htaccess���f�[�^�f�B���N�g���ɍ쐬����
_makeDenyHtaccess($_conf['pref_dir']);
_makeDenyHtaccess($_conf['dat_dir']);
_makeDenyHtaccess($_conf['idx_dir']);

// �ϐ��ݒ�
$me_url = P2Util::getMyUrl();
$me_dir_url = dirname($me_url);


// url�w�肪����΁A���̂܂܃X���b�h�ǂ݂֔�΂�
if (!empty($_GET['url']) || !empty($_GET['nama_url'])) {
    header('Location: ' . $me_dir_url . '/' . $_conf['read_php'] . '?' . $_SERVER['QUERY_STRING']);
    exit;
}
require_once P2_IPHONE_LIB_DIR . '/index_print_k.inc.php';

index_print_k();

exit;

//============================================================================
// �֐��i���̃t�@�C�����ł̂ݗ��p�j
//============================================================================
/**
 * �f�B���N�g���Ɂi�A�N�Z�X���ۂ̂��߂́j .htaccess ���Ȃ���΁A�����Ő�������
 *
 * @return  void
 */
function _makeDenyHtaccess($dir)
{
    $hta = $dir . '/.htaccess';
    if (!file_exists($hta)) {
        $data = 'Order allow,deny' . "\n"
              . 'Deny from all' . "\n";
        file_put_contents($hta, $data, LOCK_EX);
    }
}
?>