<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (!defined('PATH_tslib')) {
	define('PATH_tslib', PATH_site.'typo3/sysext/cms/tslib/');  
}

$PATH_timtab_embeddedvideo = t3lib_extMgm::extPath('timtab_embeddedvideo');
require_once($PATH_timtab_embeddedvideo.'class.tx_timtab_embeddedvideo_fe.php');

$TYPO3_CONF_VARS['EXTCONF']['tt_news']['extraItemMarkerHook'][]        = 'tx_timtab_embeddedvideo_fe';
?>
