<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007 Frank Nägler (typo3@naegler.net)
*  All rights reserved
*
*  This script is part of the Typo3 project. The Typo3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * class.tx_timtab_embeddedvideo_fe.php
 *
 * Class which implements methods to connect to tt_news hooks for parsing
 * the content of an article / post.
 *
 * $Id: class.tx_timtab_embeddedvideo_fe.php,v 0.1 2005/10/30 13:10:33 neoblack Exp $
 *
 * @author Frank Nägler <typo3@naegler.net>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   56: class tx_timtab_embeddedvideo_fe extends tslib_pibase
 *   74:     function main($markerArray, $conf)
 *   88:     function init($markerArray, $conf)
 *  178:     function parseContent()
 *  203:     function pReplace($match)
 *
 *              SECTION: Hook Connectors
 *  281:     function extraItemMarkerProcessor($markerArray, $row, $lConf, &$pObj)
 *
 * TOTAL FUNCTIONS: 5
 * (This index is automatically created/updated by the extension "extdeveval") *
 */

require_once(PATH_tslib.'class.tslib_pibase.php');
require_once(PATH_t3lib.'class.t3lib_tcemain.php');

class tx_timtab_embeddedvideo_fe extends tslib_pibase {
	var $cObj; // The backReference to the mother cObj object set at call time
	// Default plugin variables:
	var $prefixId 		= 'tx_timtab_embeddedvideo_fe';		// Same as class name
	var $scriptRelPath 	= 'class.tx_timtab_embeddedvideo_fe.php';	// Path to this script relative to the extension dir.
	var $extKey 		= 'timtab_embeddedvideo';	// The extension key.

	var $pObj;
	var $conf;
	var $markerArray;

	/**
	 * main function which executes all steps
	 *
	 * @param	array		an array of markers coming from tt_news
	 * @param	array		the configuration coming from tt_news
	 * @return	array		modified marker array
	 */
	function main($markerArray, $conf) {
		$this->init($markerArray, $conf);
		$this->parseContent();

		return $this->markerArray;
	}

	/**
	 * initializes the configuration for the extension
	 *
	 * @param	array		an array of markers coming from tt_news
	 * @param	array		the configuration coming from tt_news
	 * @return	void
	 */
	function init($markerArray, $conf) {
		$this->cObj = t3lib_div::makeInstance('tslib_cObj'); // local cObj.
		$this->pi_loadLL(); // Loading language-labels

		// pi_setPiVarDefaults() does not work since we are in a code library
		// and don't get called as a plugin, so we're getting our conf this way:
		// $this->conf might be set already, so we have to merge both arrays
		if(!is_array($this->conf)) {
			$this->conf = array();
		}
		$this->conf = array_merge($this->conf, $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_timtab_embeddedvideo.']);

		$this->markerArray = $markerArray;

		$this->codes['youtube']   = '<object type="application/x-shockwave-flash" data="http://www.youtube.com/v/###VID###" width="###WIDTH###" height="###HEIGHT###"><param name="wmode" value="transparent" /><param name="movie" value="http://www.youtube.com/v/###VID###" /></object>';
		$this->codes['google']    = '<object type="application/x-shockwave-flash" data="http://video.google.com/googleplayer.swf?docId=###VID###" width="###WIDTH###" height="###HEIGHT###"><param name="wmode" value="transparent" /><param name="movie" value="http://video.google.com/googleplayer.swf?docId=###VID###" /></object>';
		$this->codes['myvideo']   = '<object type="application/x-shockwave-flash" data="http://www.myvideo.de/movie/###VID###" width="###WIDTH###" height="###HEIGHT###"><param name="wmode" value="transparent" /><param name="movie" value="http://www.myvideo.de/movie/###VID###" /></object>';
		$this->codes['clipfish']  = '<object type="application/x-shockwave-flash" data="http://www.clipfish.de/videoplayer.swf?as=0&videoid=###VID###&r=1" width="###WIDTH###" height="###HEIGHT###"><param name="wmode" value="transparent" /><param name="movie" value="http://www.clipfish.de/videoplayer.swf?as=0&videoid=###VID###&r=1" /></object>';
		$this->codes['sevenload'] = '<object type="application/x-shockwave-flash" data="http://page.sevenload.com/swf/player.swf?id=###VID###" width="###WIDTH###" height="###HEIGHT###"><param name="wmode" value="transparent" /><param name="movie" value="http://page.sevenload.com/swf/player.swf?id=###VID###" /></object>';
		$this->codes['revver']    = '<object type="application/x-shockwave-flash" data="http://flash.revver.com/player/1.0/player.swf?mediaId=###VID###" width="###WIDTH###" height="###HEIGHT###"><param name="wmode" value="transparent" /><param name="movie" value="http://flash.revver.com/player/1.0/player.swf?mediaId=###VID###" /></object>';
		$this->codes['metacafe']  = '<object type="application/x-shockwave-flash" data="http://www.metacafe.com/fplayer/###VID###.swf" width="###WIDTH###" height="###HEIGHT###"><param name="wmode" value="transparent" /><param name="movie" value="http://www.metacafe.com/fplayer/###VID###.swf" /></object>';
		$this->codes['yahoo']     = '<object type="application/x-shockwave-flash" data="http://us.i1.yimg.com/cosmos.bcst.yahoo.com/player/media/swf/FLVVideoSolo.swf?id=###VID###" width="###WIDTH###" height="###HEIGHT###"><param name="wmode" value="transparent" /><param name="movie" value="http://us.i1.yimg.com/cosmos.bcst.yahoo.com/player/media/swf/FLVVideoSolo.swf?id=###VID###" /></object>';
		$this->codes['ifilm']     = '<object type="application/x-shockwave-flash" data="http://www.ifilm.com/efp?flvbaseclip=###VID###" width="###WIDTH###" height="###HEIGHT###"><param name="wmode" value="transparent" /><param name="movie" value="http://www.ifilm.com/efp?flvbaseclip=###VID###" /></object>';
		$this->codes['myspace']   = '<object type="application/x-shockwave-flash" data="http://lads.myspace.com/videos/vplayer.swf?m=###VID###&type=video" width="###WIDTH###" height="###HEIGHT###"><param name="wmode" value="transparent" /><param name="movie" value="http://lads.myspace.com/videos/vplayer.swf?m=###VID###&type=video" /></object>';
		$this->codes['brightcove']= '<object type="application/x-shockwave-flash" data="http://admin.brightcove.com/destination/player/player.swf?initVideoId=###VID###&amp;servicesURL=http://services.brightcove.com/services&amp;viewerSecureGatewayURL=https://services.brightcove.com/services/amfgateway&amp;cdnURL=http://admin.brightcove.com&amp;autoStart=false" width="###WIDTH###" height="###HEIGHT###"><param name="wmode" value="transparent" /><param name="movie" value="http://admin.brightcove.com/destination/player/player.swf?initVideoId=###VID###&amp;servicesURL=http://services.brightcove.com/services&amp;viewerSecureGatewayURL=https://services.brightcove.com/services/amfgateway&amp;cdnURL=http://admin.brightcove.com&amp;autoStart=false" /></object>';
		$this->codes['aniboom']   = '<object type="application/x-shockwave-flash" data="http://api.aniboom.com/embedded.swf?videoar=###VID###&amp;allowScriptAccess=sameDomain&amp;quality=high" width="###WIDTH###" height="###HEIGHT###"><param name="wmode" value="transparent" /><param name="movie" value="http://api.aniboom.com/embedded.swf?videoar=###VID###&amp;allowScriptAccess=sameDomain&amp;quality=high" /></object>';
		$this->codes['carmondo']  = '<object width="###WIDTH###" height="###HEIGHT###"><param name="movie" value="http://www.carmondo.de/flash/videoPlayer_v7.swf?videoId=###VID###&autostart=false"></param><param name="wmode" value="transparent"></param><embed src="http://www.carmondo.de/flash/videoPlayer_v7.swf?videoId=###VID###&autostart=false" type="application/x-shockwave-flash" wmode="transparent" width="###WIDTH###" height="###HEIGHT###"></embed></object>';
		$this->codes['vimeo']	  = '<object width="###WIDTH###" height="###HEIGHT###"><param name="allowfullscreen" value="true" /><param name="allowscriptaccess" value="always" /><param name="movie" value="http://www.vimeo.com/moogaloop.swf?clip_id=###VID###&amp;server=www.vimeo.com&amp;show_title=1&amp;show_byline=1&amp;show_portrait=0&amp;color=&amp;fullscreen=1" /><embed src="http://www.vimeo.com/moogaloop.swf?clip_id=###VID###&amp;server=www.vimeo.com&amp;show_title=1&amp;show_byline=1&amp;show_portrait=0&amp;color=&amp;fullscreen=1" type="application/x-shockwave-flash" allowfullscreen="true" allowscriptaccess="always" width="###WIDTH###" height="###HEIGHT###"></embed></object>';
		// FLV-Player from http://www.jeroenwijering.com/
		$this->codes['flv']       = '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,22,0" width="###WIDTH###" height="###HEIGHT###"><param name="movie" value="###PLAYER###" /><param name="quality" value="high" /><param name="bgcolor" value="#FFFFFF" /><param name="flashvars" value="file=###VIDFILE###" /><!--[if !IE]> <--><object data="###PLAYER###" width="###WIDTH###" height="###HEIGHT###" type="application/x-shockwave-flash"><param name="quality" value="high" /><param name="bgcolor" value="#FFFFFF" /><param name="flashvars" value="file=###VIDFILE###" /><param name="pluginurl" value="http://www.macromedia.com/go/getflashplayer" /></object><!--> <![endif]--></object>';
		$this->codes['swf']       = '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,22,0" width="###WIDTH###" height="###HEIGHT###"><param name="movie" value="###PLAYER###" /><param name="quality" value="high" /><param name="bgcolor" value="#FFFFFF" /><param name="flashvars" value="file=###VIDFILE###" /><!--[if !IE]> <--><object data="###PLAYER###" width="###WIDTH###" height="###HEIGHT###" type="application/x-shockwave-flash"><param name="quality" value="high" /><param name="bgcolor" value="#FFFFFF" /><param name="flashvars" value="file=###VIDFILE###" /><param name="pluginurl" value="http://www.macromedia.com/go/getflashplayer" /></object><!--> <![endif]--></object>';
		$this->codes['mp3']       = '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,22,0" width="###WIDTH###" height="###HEIGHT###"><param name="movie" value="###PLAYER###" /><param name="quality" value="high" /><param name="bgcolor" value="#FFFFFF" /><param name="flashvars" value="file=###VIDFILE###&showeq=###SHOWEQ###" /><!--[if !IE]> <--><object data="###PLAYER###" width="###WIDTH###" height="###HEIGHT###" type="application/x-shockwave-flash"><param name="quality" value="high" /><param name="bgcolor" value="#FFFFFF" /><param name="flashvars" value="file=###VIDFILE###&showeq=###SHOWEQ###" /><param name="pluginurl" value="http://www.macromedia.com/go/getflashplayer" /></object><!--> <![endif]--></object>';
		$this->codes['jpg']       = '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,22,0" width="###WIDTH###" height="###HEIGHT###"><param name="movie" value="###PLAYER###" /><param name="quality" value="high" /><param name="bgcolor" value="#FFFFFF" /><param name="flashvars" value="file=###VIDFILE###" /><!--[if !IE]> <--><object data="###PLAYER###" width="###WIDTH###" height="###HEIGHT###" type="application/x-shockwave-flash"><param name="quality" value="high" /><param name="bgcolor" value="#FFFFFF" /><param name="flashvars" value="file=###VIDFILE###" /><param name="pluginurl" value="http://www.macromedia.com/go/getflashplayer" /></object><!--> <![endif]--></object>';
		$this->codes['png']       = '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,22,0" width="###WIDTH###" height="###HEIGHT###"><param name="movie" value="###PLAYER###" /><param name="quality" value="high" /><param name="bgcolor" value="#FFFFFF" /><param name="flashvars" value="file=###VIDFILE###" /><!--[if !IE]> <--><object data="###PLAYER###" width="###WIDTH###" height="###HEIGHT###" type="application/x-shockwave-flash"><param name="quality" value="high" /><param name="bgcolor" value="#FFFFFF" /><param name="flashvars" value="file=###VIDFILE###" /><param name="pluginurl" value="http://www.macromedia.com/go/getflashplayer" /></object><!--> <![endif]--></object>';
		$this->codes['gif']       = '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,22,0" width="###WIDTH###" height="###HEIGHT###"><param name="movie" value="###PLAYER###" /><param name="quality" value="high" /><param name="bgcolor" value="#FFFFFF" /><param name="flashvars" value="file=###VIDFILE###" /><!--[if !IE]> <--><object data="###PLAYER###" width="###WIDTH###" height="###HEIGHT###" type="application/x-shockwave-flash"><param name="quality" value="high" /><param name="bgcolor" value="#FFFFFF" /><param name="flashvars" value="file=###VIDFILE###" /><param name="pluginurl" value="http://www.macromedia.com/go/getflashplayer" /></object><!--> <![endif]--></object>';
		// default embedding code for default formats
		$this->codes['mov']       = '<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" codebase="http://www.apple.com/qtactivex/qtplugin.cab" width="###WIDTH###" height="###HEIGHT###"><param name="src" value="###VIDFILE###" /><param name="autoplay" value="false" /><param name="pluginspage" value="http://www.apple.com/quicktime/download/" /><param name="controller" value="true" /><!--[if !IE]> <--><object data="###VIDFILE###" width="###WIDTH###" height="###HEIGHT###" type="video/quicktime"><param name="pluginurl" value="http://www.apple.com/quicktime/download/" /><param name="controller" value="true" /></object><!--> <![endif]--></object>';
		$this->codes['qt']        = '<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" codebase="http://www.apple.com/qtactivex/qtplugin.cab" width="###WIDTH###" height="###HEIGHT###"><param name="src" value="###VIDFILE###" /><param name="autoplay" value="false" /><param name="pluginspage" value="http://www.apple.com/quicktime/download/" /><param name="controller" value="true" /><!--[if !IE]> <--><object data="###VIDFILE###" width="###WIDTH###" height="###HEIGHT###" type="video/quicktime"><param name="pluginurl" value="http://www.apple.com/quicktime/download/" /><param name="controller" value="true" /></object><!--> <![endif]--></object>';
		$this->codes['wmv']       = '<object classid="clsid:22D6F312-B0F6-11D0-94AB-0080C74C7E95" codebase="http://activex.microsoft.com/activex/controls/mplayer/en/nsmp2inf.cab#Version=6,0,02,902" width="###WIDTH###" height="###HEIGHT###" type="application/x-oleobject"><param name="filename" value="###VIDFILE###" /><param name="autostart" value="false" /><param name="showcontrols" value="true" /><!--[if !IE]> <--><object data="###VIDFILE###" width="###WIDTH###" height="###HEIGHT###" type="application/x-mplayer2"><param name="pluginurl" value="http://www.microsoft.com/Windows/MediaPlayer/" /><param name="controller" value="true" /></object><!--> <![endif]--></object>';
		$this->codes['avi']       = '<object classid="clsid:22D6F312-B0F6-11D0-94AB-0080C74C7E95" codebase="http://activex.microsoft.com/activex/controls/mplayer/en/nsmp2inf.cab#Version=6,0,02,902" width="###WIDTH###" height="###HEIGHT###" type="application/x-oleobject"><param name="filename" value="###VIDFILE###" /><param name="autostart" value="false" /><param name="showcontrols" value="true" /><!--[if !IE]> <--><object data="###VIDFILE###" width="###WIDTH###" height="###HEIGHT###" type="application/x-mplayer2"><param name="pluginurl" value="http://www.microsoft.com/Windows/MediaPlayer/" /><param name="controller" value="true" /></object><!--> <![endif]--></object>';
		$this->codes['mpg']       = '<object classid="clsid:22D6F312-B0F6-11D0-94AB-0080C74C7E95" codebase="http://activex.microsoft.com/activex/controls/mplayer/en/nsmp2inf.cab#Version=6,0,02,902" width="###WIDTH###" height="###HEIGHT###" type="application/x-oleobject"><param name="fileName" value="###VIDFILE###" /><param name="autoStart" value="false" /><param name="showControls" value="true" /><!--[if !IE]> <--><object data="###VIDFILE###" width="###WIDTH###" height="###HEIGHT###" type="application/x-mplayer2"><param name="pluginurl" value="http://www.microsoft.com/Windows/MediaPlayer/" /><param name="controller" value="true" /></object><!--> <![endif]--></object>';
		$this->codes['mpeg']      = '<object classid="clsid:22D6F312-B0F6-11D0-94AB-0080C74C7E95" codebase="http://activex.microsoft.com/activex/controls/mplayer/en/nsmp2inf.cab#Version=6,0,02,902" width="###WIDTH###" height="###HEIGHT###" type="application/x-oleobject"><param name="fileName" value="###VIDFILE###" /><param name="autoStart" value="false" /><param name="showControls" value="true" /><!--[if !IE]> <--><object data="###VIDFILE###" width="###WIDTH###" height="###HEIGHT###" type="application/x-mplayer2"><param name="pluginurl" value="http://www.microsoft.com/Windows/MediaPlayer/" /><param name="controller" value="true" /></object><!--> <![endif]--></object>';
		$this->codes['mp2']       = '<object classid="clsid:22D6F312-B0F6-11D0-94AB-0080C74C7E95" codebase="http://activex.microsoft.com/activex/controls/mplayer/en/nsmp2inf.cab#Version=6,0,02,902" width="###WIDTH###" height="###HEIGHT###" type="application/x-oleobject"><param name="fileName" value="###VIDFILE###" /><param name="autoStart" value="false" /><param name="showControls" value="true" /><!--[if !IE]> <--><object data="###VIDFILE###" width="###WIDTH###" height="###HEIGHT###" type="application/x-mplayer2"><param name="pluginurl" value="http://www.microsoft.com/Windows/MediaPlayer/" /><param name="controller" value="true" /></object><!--> <![endif]--></object>';
		$this->codes['mpa']       = '<object classid="clsid:22D6F312-B0F6-11D0-94AB-0080C74C7E95" codebase="http://activex.microsoft.com/activex/controls/mplayer/en/nsmp2inf.cab#Version=6,0,02,902" width="###WIDTH###" height="###HEIGHT###" type="application/x-oleobject"><param name="fileName" value="###VIDFILE###" /><param name="autoStart" value="false" /><param name="showControls" value="true" /><!--[if !IE]> <--><object data="###VIDFILE###" width="###WIDTH###" height="###HEIGHT###" type="application/x-mplayer2"><param name="pluginurl" value="http://www.microsoft.com/Windows/MediaPlayer/" /><param name="controller" value="true" /></object><!--> <![endif]--></object>';
		$this->codes['mpe']       = '<object classid="clsid:22D6F312-B0F6-11D0-94AB-0080C74C7E95" codebase="http://activex.microsoft.com/activex/controls/mplayer/en/nsmp2inf.cab#Version=6,0,02,902" width="###WIDTH###" height="###HEIGHT###" type="application/x-oleobject"><param name="fileName" value="###VIDFILE###" /><param name="autoStart" value="false" /><param name="showControls" value="true" /><!--[if !IE]> <--><object data="###VIDFILE###" width="###WIDTH###" height="###HEIGHT###" type="application/x-mplayer2"><param name="pluginurl" value="http://www.microsoft.com/Windows/MediaPlayer/" /><param name="controller" value="true" /></object><!--> <![endif]--></object>';
		$this->codes['mpv2']      = '<object classid="clsid:22D6F312-B0F6-11D0-94AB-0080C74C7E95" codebase="http://activex.microsoft.com/activex/controls/mplayer/en/nsmp2inf.cab#Version=6,0,02,902" width="###WIDTH###" height="###HEIGHT###" type="application/x-oleobject"><param name="fileName" value="###VIDFILE###" /><param name="autoStart" value="false" /><param name="showControls" value="true" /><!--[if !IE]> <--><object data="###VIDFILE###" width="###WIDTH###" height="###HEIGHT###" type="application/x-mplayer2"><param name="pluginurl" value="http://www.microsoft.com/Windows/MediaPlayer/" /><param name="controller" value="true" /></object><!--> <![endif]--></object>';
		$this->codes['lsf']       = '<object classid="clsid:22D6F312-B0F6-11D0-94AB-0080C74C7E95" codebase="http://activex.microsoft.com/activex/controls/mplayer/en/nsmp2inf.cab#Version=6,0,02,902" width="###WIDTH###" height="###HEIGHT###" type="application/x-oleobject"><param name="fileName" value="###VIDFILE###" /><param name="autoStart" value="false" /><param name="showControls" value="true" /><!--[if !IE]> <--><object data="###VIDFILE###" width="###WIDTH###" height="###HEIGHT###" type="application/x-mplayer2"><param name="pluginurl" value="http://www.microsoft.com/Windows/MediaPlayer/" /><param name="controller" value="true" /></object><!--> <![endif]--></object>';
		$this->codes['lsx']       = '<object classid="clsid:22D6F312-B0F6-11D0-94AB-0080C74C7E95" codebase="http://activex.microsoft.com/activex/controls/mplayer/en/nsmp2inf.cab#Version=6,0,02,902" width="###WIDTH###" height="###HEIGHT###" type="application/x-oleobject"><param name="fileName" value="###VIDFILE###" /><param name="autoStart" value="false" /><param name="showControls" value="true" /><!--[if !IE]> <--><object data="###VIDFILE###" width="###WIDTH###" height="###HEIGHT###" type="application/x-mplayer2"><param name="pluginurl" value="http://www.microsoft.com/Windows/MediaPlayer/" /><param name="controller" value="true" /></object><!--> <![endif]--></object>';
		$this->codes['asf']       = '<object classid="clsid:22D6F312-B0F6-11D0-94AB-0080C74C7E95" codebase="http://activex.microsoft.com/activex/controls/mplayer/en/nsmp2inf.cab#Version=6,0,02,902" width="###WIDTH###" height="###HEIGHT###" type="application/x-oleobject"><param name="fileName" value="###VIDFILE###" /><param name="autoStart" value="false" /><param name="showControls" value="true" /><!--[if !IE]> <--><object data="###VIDFILE###" width="###WIDTH###" height="###HEIGHT###" type="application/x-mplayer2"><param name="pluginurl" value="http://www.microsoft.com/Windows/MediaPlayer/" /><param name="controller" value="true" /></object><!--> <![endif]--></object>';
		$this->codes['asr']       = '<object classid="clsid:22D6F312-B0F6-11D0-94AB-0080C74C7E95" codebase="http://activex.microsoft.com/activex/controls/mplayer/en/nsmp2inf.cab#Version=6,0,02,902" width="###WIDTH###" height="###HEIGHT###" type="application/x-oleobject"><param name="fileName" value="###VIDFILE###" /><param name="autoStart" value="false" /><param name="showControls" value="true" /><!--[if !IE]> <--><object data="###VIDFILE###" width="###WIDTH###" height="###HEIGHT###" type="application/x-mplayer2"><param name="pluginurl" value="http://www.microsoft.com/Windows/MediaPlayer/" /><param name="controller" value="true" /></object><!--> <![endif]--></object>';
		$this->codes['asx']       = '<object classid="clsid:22D6F312-B0F6-11D0-94AB-0080C74C7E95" codebase="http://activex.microsoft.com/activex/controls/mplayer/en/nsmp2inf.cab#Version=6,0,02,902" width="###WIDTH###" height="###HEIGHT###" type="application/x-oleobject"><param name="fileName" value="###VIDFILE###" /><param name="autoStart" value="false" /><param name="showControls" value="true" /><!--[if !IE]> <--><object data="###VIDFILE###" width="###WIDTH###" height="###HEIGHT###" type="application/x-mplayer2"><param name="pluginurl" value="http://www.microsoft.com/Windows/MediaPlayer/" /><param name="controller" value="true" /></object><!--> <![endif]--></object>';
		$this->codes['ram']       = '<object classid="clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA" width="###WIDTH###" height="###HEIGHT###"><param name="controls" value="ImageWindow" /><param name="autostart" value="false" /><param name="src" value="###VIDFILE###" /></object>';
		$this->codes['rm']        = '<object classid="clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA" width="###WIDTH###" height="###HEIGHT###"><param name="controls" value="ImageWindow" /><param name="autostart" value="false" /><param name="src" value="###VIDFILE###" /></object>';
		
		$this->links['youtube']   = '<br /><a class="tx_timtab_embeddedvideo-link" title="YouTube" href="http://www.youtube.com/watch?v=###VID###">YouTube ###TXT######THING###</a>';
		$this->links['google']    = '<br /><a class="tx_timtab_embeddedvideo-link" title="Google Video" href="http://video.google.com/videoplay?docid=###VID###">Google ###TXT######THING###</a>';
		$this->links['myvideo']   = '<br /><a class="tx_timtab_embeddedvideo-link" title="MyVideo" href="http://www.myvideo.de/watch/###VID###">MyVideo ###TXT######THING###</a>';
		$this->links['clipfish']  = '<br /><a class="tx_timtab_embeddedvideo-link" title="Clipfish" href="http://www.clipfish.de/player.php?videoid=###VID###">Clipfish ###TXT######THING###</a>';
		$this->links['sevenload'] = '<br /><a class="tx_timtab_embeddedvideo-link" title="Sevenload" href="http://sevenload.de/videos/###VID###">Sevenload ###TXT######THING###</a>';
		$this->links['revver']    = '<br /><a class="tx_timtab_embeddedvideo-link" title="Revver" href="http://one.revver.com/watch/###VID###">Revver ###TXT######THING###</a>';
		$this->links['metacafe']  = '<br /><a class="tx_timtab_embeddedvideo-link" title="Metacaf&eacute;" href="http://www.metacafe.com/watch/###VID###/">Metacaf&eacute; ###TXT######THING###</a>';
		$this->links['yahoo']     = '<br /><a class="tx_timtab_embeddedvideo-link" title="Yahoo! Video" href="http://video.yahoo.com/video/play?vid=###YAHOO###.###VID###">Yahoo! ###TXT######THING###</a>';
		$this->links['ifilm']     = '<br /><a class="tx_timtab_embeddedvideo-link" title="ifilm" href="http://www.ifilm.com/video/###VID###">ifilm ###TXT######THING###</a>';
		$this->links['myspace']   = '<br /><a class="tx_timtab_embeddedvideo-link" title="MySpace Video" href="http://vids.myspace.com/index.cfm?fuseaction=vids.individual&videoid=###VID###">MySpace ###TXT######THING###</a>';
		$this->links['brightcove']= '<br /><a class="tx_timtab_embeddedvideo-link" title="brightcove" href="http://www.brightcove.com/title.jsp?title=###VID###">brightcove ###TXT######THING###</a>';
		$this->links['aniboom']   = '<br /><a class="tx_timtab_embeddedvideo-link" title="aniBOOM" href="http://www.aniboom.com/Player.aspx?v=###VID###">aniBOOM ###TXT######THING###</a>';
		$this->links['carmondo']  = '<br /><a class="tx_timtab_embeddedvideo-link" title="Carmondo" href="http://www.carmondo.de/video/detail/###VID###">Carmondo ###TXT######THING###</a>';
		$this->links['vimeo']	  = '<br /><a class="tx_timtab_embeddedvideo-link" title="Vimeo" href="http://www.vimeo.com/###VID###">Vimeo ###TXT######THING###</a>';
		$this->links['flv']       = '<br /><a class="tx_timtab_embeddedvideo-link" title="Video" href="###VIDFILE###">Video ###TXT######THING###</a>';
		$this->links['swf']       = '<br /><a class="tx_timtab_embeddedvideo-link" title="Video" href="###VIDFILE###">Flash ###TXT######THING###</a>';
		$this->links['mp3']       = '<br /><a class="tx_timtab_embeddedvideo-link" title="Video" href="###VIDFILE###">Sound ###TXT######THING###</a>';
		$this->links['jpg']       = '<br /><a class="tx_timtab_embeddedvideo-link" title="Video" href="###VIDFILE###">Image ###TXT######THING###</a>';
		$this->links['png']       = '<br /><a class="tx_timtab_embeddedvideo-link" title="Video" href="###VIDFILE###">Image ###TXT######THING###</a>';
		$this->links['gif']       = '<br /><a class="tx_timtab_embeddedvideo-link" title="Video" href="###VIDFILE###">Image ###TXT######THING###</a>';
		$this->links['mov']       = '<br /><a class="tx_timtab_embeddedvideo-link" title="Video" href="###VIDFILE###">Video ###TXT######THING###</a>';
		$this->links['qt']        = '<br /><a class="tx_timtab_embeddedvideo-link" title="Video" href="###VIDFILE###">Video ###TXT######THING###</a>';
		$this->links['wmv']       = '<br /><a class="tx_timtab_embeddedvideo-link" title="Video" href="###VIDFILE###">Video ###TXT######THING###</a>';
		$this->links['avi']       = '<br /><a class="tx_timtab_embeddedvideo-link" title="Video" href="###VIDFILE###">Video ###TXT######THING###</a>';
		$this->links['mpg']       = '<br /><a class="tx_timtab_embeddedvideo-link" title="Video" href="###VIDFILE###">Video ###TXT######THING###</a>';
		$this->links['mpeg']      = '<br /><a class="tx_timtab_embeddedvideo-link" title="Video" href="###VIDFILE###">Video ###TXT######THING###</a>';
		$this->links['mp2']       = '<br /><a class="tx_timtab_embeddedvideo-link" title="Video" href="###VIDFILE###">Video ###TXT######THING###</a>';
		$this->links['mpa']       = '<br /><a class="tx_timtab_embeddedvideo-link" title="Video" href="###VIDFILE###">Video ###TXT######THING###</a>';
		$this->links['mpe']       = '<br /><a class="tx_timtab_embeddedvideo-link" title="Video" href="###VIDFILE###">Video ###TXT######THING###</a>';
		$this->links['mpv2']      = '<br /><a class="tx_timtab_embeddedvideo-link" title="Video" href="###VIDFILE###">Video ###TXT######THING###</a>';
		$this->links['lsf']       = '<br /><a class="tx_timtab_embeddedvideo-link" title="Video" href="###VIDFILE###">Video ###TXT######THING###</a>';
		$this->links['lsx']       = '<br /><a class="tx_timtab_embeddedvideo-link" title="Video" href="###VIDFILE###">Video ###TXT######THING###</a>';
		$this->links['asf']       = '<br /><a class="tx_timtab_embeddedvideo-link" title="Video" href="###VIDFILE###">Video ###TXT######THING###</a>';
		$this->links['asr']       = '<br /><a class="tx_timtab_embeddedvideo-link" title="Video" href="###VIDFILE###">Video ###TXT######THING###</a>';
		$this->links['asx']       = '<br /><a class="tx_timtab_embeddedvideo-link" title="Video" href="###VIDFILE###">Video ###TXT######THING###</a>';
		$this->links['ram']       = '<br /><a class="tx_timtab_embeddedvideo-link" title="Video" href="###VIDFILE###">Video ###TXT######THING###</a>';
		$this->links['rm']        = '<br /><a class="tx_timtab_embeddedvideo-link" title="Video" href="###VIDFILE###">Video ###TXT######THING###</a>';
	}

	/**
	 * parse the content for special tags
	 *
	 * @return	void
	 */
	function parseContent() {
		$this->itemMarkerArray[] = '###NEWS_SUBHEADER###';
		$this->itemMarkerArray[] = '###NEWS_CONTENT###';
		
		// Adds hook for extensing the itemMarkerArray
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['timtab_embeddedvideo']['extendMarkerHook'])) {
			foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['timtab_embeddedvideo']['extendMarkerHook'] as $funcName) {
				$_params = array();
				t3lib_div::callUserFunction($funcName, $_params, $this);
			}
		}
		
		define("REGEXP_1", "/\[(google|youtube|myvideo|clipfish|sevenload|revver|metacafe|yahoo|ifilm|myspace|brightcove|aniboom|carmondo|vimeo|local) ([[:graph:]]+) (nolink)\]/");
		define("REGEXP_2", "/\[(google|youtube|myvideo|clipfish|sevenload|revver|metacafe|yahoo|ifilm|myspace|brightcove|aniboom|carmondo|vimeo|local) ([[:graph:]]+) ([[:print:]]+)\]/");
		define("REGEXP_3", "/\[(google|youtube|myvideo|clipfish|sevenload|revver|metacafe|yahoo|ifilm|myspace|brightcove|aniboom|carmondo|vimeo|local) ([[:graph:]]+)\]/");

		foreach ($this->itemMarkerArray as $marker) {
			$this->markerArray[$marker] = preg_replace_callback(REGEXP_1, array($this,'pReplace'), $this->markerArray[$marker]);
			$this->markerArray[$marker] = preg_replace_callback(REGEXP_2, array($this,'pReplace'), $this->markerArray[$marker]);
			$this->markerArray[$marker] = preg_replace_callback(REGEXP_3, array($this,'pReplace'), $this->markerArray[$marker]);
		}

	}

	/**
	 * preg_replace_callback function, which replace the placeholder
	 *
	 * @param	array		$match: array of matching parts
	 * @return	string		$output: the content with replaces placeholder
	 */
	function pReplace($match) {
		if ($match[1] == 'local') {
			$parts = explode('.', $match[2]);
			$fileExtension = $parts[count($parts)-1];
			if (isset($this->codes[$fileExtension])) {
				if ($match[3] == "nolink") {
					$output = ($this->conf['disableWrappingInBaseClass']) ? $this->codes[$fileExtension] : $this->pi_wrapInBaseClass($this->codes[$fileExtension]);
				} else {
					$output = ($this->conf['disableWrappingInBaseClass']) ? $this->codes[$fileExtension].$this->links[$fileExtension] : $this->pi_wrapInBaseClass($this->codes[$fileExtension].$this->links[$fileExtension]);
				}
			} else {
				return 'ERROR: no player found for this video type';
			}
			if ($fileExtension == 'flv' || $fileExtension == 'swf' || $fileExtension == 'jpg' || $fileExtension == 'png' || $fileExtension == 'gif') {
				$playerSrc = $this->conf['localPlayer.']['src'];
				//debug(PATH_site.$playerSrc);
				if (!isset($playerSrc) || !file_exists(PATH_site.$playerSrc)) {
					return 'ERROR: no player configured for this video type or player not found, set: plugin.tx_timtab_embeddedvideo.localPlayer.src';
				}
			}
			if ($fileExtension == 'mp3') {
				$playerSrc = $this->conf['mp3Player.']['src'];
				//debug(PATH_site.$playerSrc);
				if (!isset($playerSrc) || !file_exists(PATH_site.$playerSrc)) {
					return 'ERROR: no MP3 player configured for this type or MP3 player not found, set: plugin.tx_timtab_embeddedvideo.mp3Player.src';
				}
			}
		} else {
			if ($match[3] == "nolink") {
				$output = ($this->conf['disableWrappingInBaseClass']) ? $this->codes[$match[1]] : $this->pi_wrapInBaseClass($this->codes[$match[1]]);
			} else {
				$output = ($this->conf['disableWrappingInBaseClass']) ? $this->codes[$match[1]].$this->links[$match[1]] : $this->pi_wrapInBaseClass($this->codes[$match[1]].$this->links[$match[1]]);
			}
		}
		if ((!isset($match[3])) || ($match[3] == "")) $output = str_replace("###TXT###", "", $output);
		else $output = str_replace("###TXT###", $this->conf['link.']['PreText'], $output);
		// special handling of Yahoo! Video
		if ($match[1] == "yahoo") {
			$temp = explode(".", $match[2]);
			$match[2] = $temp[1];
			$output = str_replace("###YAHOO###", $temp[0], $output);
		}
		$player = ($fileExtension == 'mp3')?($this->conf['mp3Player.']['src']):($this->conf['localPlayer.']['src']);
		$output = str_replace("###VID###", $match[2], $output);
		$output = str_replace("###THING###", $match[3], $output);
		$output = str_replace("###WIDTH###", $this->conf['player.']['width'], $output);
		if ($fileExtension == 'mp3') {
			$height = ($this->conf['mp3Player.']['showeq'] == true)?(70):(20);
			$output = str_replace("###HEIGHT###", $height, $output);
		
		} else {
			$output = str_replace("###HEIGHT###", floor($this->conf['player.']['width']*$this->conf['videoportals.'][$match[1].'.']['ratio']), $output);
		}
		$output = str_replace("###PLAYER###", $player, $output);
		$output = str_replace("###SHOWEQ###", $this->conf['mp3Player.']['showeq'], $output);
		$output = str_replace("###VIDFILE###", $match[2], $output);
		
		return ($output);
	}

	
	/**
	 * getCode() API function to get the videoplayer code
	 *
	 * @param	array		$config: config array to get the code
	 * $config['portal'] = VideoPortal-Key, see doku
	 * $config['videoid'] = Video-ID of the videoportal, see doku
	 * $config['width'] = the width of the video player, see doku
	 * @return	string		$output: the embed HTML code for a videoportal
	 */
	function getCode($config) {
		if (!$this->conf) {
			$conf = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_timtab_embeddedvideo.'];
			$this->init(array(), $conf);
		}
		$code   = $this->codes[$config['portal']];
		$vid    = $config['videoid'];
		$width  = ($config['width']) ? $config['width'] : $this->conf['player.']['width'];
		$height = floor($width * $this->conf['videoportals.'][$config['portal'].'.']['ratio']);
		$output = str_replace("###VID###", $vid, $code);
		$output = str_replace("###WIDTH###", $width, $output);
		return str_replace("###HEIGHT###", $height, $output);
	}
	
	/***********************************************
	 *
	 * Hook Connectors
	 *
	 **********************************************/

	/**
	 * connects into tt_news item marker processing hook
	 * and parse the article / post content
	 *
	 * @param	array		an array of markers coming from tt_news
	 * @param	array		the current tt_news record
	 * @param	array		the configuration coming from tt_news
	 * @param	object		the parent object calling this method
	 * @return	array		processed marker array
	 */
	function extraItemMarkerProcessor($markerArray, $row, $lConf, &$pObj) {
		$this->conf['data'] = $row;
		$this->pObj = &$pObj;
		$this->calledBy = $pObj->extKey; //who is calling?

		return $this->main($markerArray, $lConf);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/timtab_embeddedvideo/class.tx_timtab_embeddedvideo_fe.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/timtab_embeddedvideo/class.tx_timtab_embeddedvideo_fe.php']);
}

?>