<?php
/*------------------------------------------------------------------------
# plg_videobox - Videobox
# ------------------------------------------------------------------------
# author	HitkoDev
# copyright	Copyright (C) 2014 HitkoDev. All Rights Reserved.
# @license	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites:	http://hitko.eu/software/videobox
-------------------------------------------------------------------------*/
// no direct access
defined( '_JEXEC' ) or die( 'Restricted Access' );

include_once('video.php');

class aolVideo extends Video {
	
	/*
	*	$id - link to the video (http://on.aol.com/video/violinist-david-garrett-talks-about-his-latest-album-518187279)
	*/
	static function adapterSwitch($id, $title, $offset, $vb){
		if(strpos($id, 'aol.com')!==false){
			preg_match('/(\d*)$/isU', $id, $v_urls);
			return new self($v_urls[1], $title, $offset);
		}
		return false;
	}

	function getTitle($forced = false){
		if($forced && $this->title==''){
			return 'http://on.aol.com/video/' . $this->id;
		} else {
			return $this->title; 
		}
	}
	
	function getThumb(){
		$th = parent::getThumb();
		if($th !== false) return $th;
		$data = json_decode(file_get_contents('http://api.5min.com/video/' . $this->id . '/info.json'), true);
		$img = $data['items'][0]['image'];
		return array($img, IMAGETYPE_JPEG);
	}
	
	function getPlayerLink($autoplay = false){
		$src = 'https://embed.5min.com/' . $this->id;
		if($autoplay) $src .= '?autoStart=true';
		return $src;
	}
	
}