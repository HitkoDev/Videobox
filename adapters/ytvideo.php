<?php
/*------------------------------------------------------------------------
# plg_videobox - Videobox
# ------------------------------------------------------------------------
# author    HitkoDev
# copyright Copyright (C) 2012 hitko.si. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://hitko.eu/software/videobox
-------------------------------------------------------------------------*/
// no direct access
defined( '_JEXEC' ) or die( 'Restricted Access' );

include_once('video.php');

class ytVideo extends Video {

	function getTitle($forced = false){
		if($forced && $this->title==''){
			return 'http://youtu.be/' . $this->id;
		} else {
			return $this->title; 
		}
	}
	
	function getThumb(){
		$th = parent::getThumb();
		if($th !== false) return $th;
		$img = 'http://i2.ytimg.com/vi/' . $this->id . '/hqdefault.jpg';
		return array($img, IMAGETYPE_JPEG);
	}
	
	function getPlayerLink($autoplay = false){
		$src = 'https://www.youtube.com/embed/' . $this->id . '?wmode=transparent&rel=0&fs=1';
		if($autoplay) $src .= '&autoplay=1';
		if($this->offset != 0) $src .= '&start=' . $this->offset;
		return $src;
	}
	
}