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

class ytVideo extends Video {
	
	/*
	*	$id - one of the following:
	*		- 11 characters YouTube video ID
	*		- link to the video (https://www.youtube.com/watch?v=KKWTdo5YW_I)
	*		- YouTube sharing link (http://youtu.be/KKWTdo5YW_I)
	*/
	static function adapterSwitch($id, $title, $offset, $vb){
		if(strlen($id)==11 && preg_match('/([a-zA-Z0-9_-]{11})/', $id)==1){
			return new self($id, $title, $offset);
		}
		if(strpos($id, 'youtube')!==false){
			preg_match('/v=([a-zA-Z0-9_-]{11}?)/isU', $id, $v_urls);
			return new self($v_urls[1], $title, $offset);
		}
		if(strpos($id, 'youtu.be')!==false){
			preg_match('/youtu\.be\/([a-zA-Z0-9_-]{11}?)/isU', $id, $v_urls);
			return new self($v_urls[1], $title, $offset);
		}
		return false;
	}

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