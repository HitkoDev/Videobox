<?php
/*------------------------------------------------------------------------
# plg_videobox - Videobox
# ------------------------------------------------------------------------
# author    HiTKO
# copyright Copyright (C) 2012 hitko.si. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Website: http://hitko.si
-------------------------------------------------------------------------*/
// no direct access
defined( '_JEXEC' ) or die( 'Restricted Access' );

include_once('video.php');

class h5Video extends Video {
	
	function __construct($id, $title = '', $offset = 0){
		parent::__construct($id, $title, $offset);
		if(in_array(pathinfo($id, PATHINFO_EXTENSION), array('oga', 'mp3', 'm4a', 'webma', 'wav'))) $this->type = 'a';
	}
	
	function getThumb(){
		$id = $this->id;
		if(substr($id, 0, 2) != '//' && substr($id, 0, 7) != 'http://' && substr($id, 0, 8) != 'https://'){
			$id = str_replace('//', '/', $_SERVER['DOCUMENT_ROOT'] . '/' . $id);
		} else {
			$id = str_replace(array('http://' . $_SERVER['HTTP_HOST'], 'https://' . $_SERVER['HTTP_HOST'], '//' . $_SERVER['HTTP_HOST']), $_SERVER['DOCUMENT_ROOT'] . '/', $id, $count);
			if($count > 0){
				$id = str_replace('//', '/', $id);
			} else {
				$id = str_replace(' ', '%20', $id);
			}
		}
		$pi = pathinfo($id);
		$id = $pi['dirname'] . '/' . $pi['filename'];
		$ext = array('.png', '.jpg', '.jpeg', '.gif');
		foreach($ext as $ex){
			$im = @getimagesize($id . $ex);
			if($im !== false) return array($id . $ex, $im[2]);
		}
		return false;
	}
	
	function getPlayerLink($autoplay = false){
		$src = JURI::root() . '/plugins/system/videobox/player.php?video=' . rawurlencode($this->id);
		if(isset($this->poster) && $this->poster != '') $src .= '&poster=' . rawurlencode($this->poster);
		if($autoplay) $src .= '&autoplay=1';
		if($this->offset != 0) $src .= '&start=' . $this->offset;
		return $src;
	}
	
}