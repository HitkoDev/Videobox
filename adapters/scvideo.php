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

class scVideo extends Video {
	
	function __construct($id, $title = '', $offset = 0, $visual = true){
		parent::__construct($id, $title, $offset);
		$this->visual = $visual;
		$this->type = 'a';
	}
	
	function getThumb(){
		$data = json_decode(file_get_contents('http://soundcloud.com/oembed?url=' . rawurlencode($this->id) . '&format=json'));
		$data = explode('?', $data->thumbnail_url);
		$img = $data[0];
		$im = @getimagesize($img);
		if($im !== false) return array($img, $im[2]);
		return false;
	}
	
	function getPlayerLink($autoplay = false){
		$src = 'https://w.soundcloud.com/player/?url=' . rawurlencode($this->id) . '&show_artwork=true';
		if($autoplay) $src .= '&auto_play=true';
		if($this->visual){
			$src .= '&visual=true';
		} else {
			$src .= '&visual=false';
		}
		return $src;
	}
	
}