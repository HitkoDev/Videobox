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

class ykVideo extends Video {
	
	/*
	*	$id - one of the following:
	*		- YouKu video ID (id_XNjk4MDIyNDMy)
	*		- link to the video (http://v.youku.com/v_show/id_XNjk4MDIyNDMy.html)
	*/
	static function adapterSwitch($id, $title, $offset, $vb){
		if(strlen($id) == 16 && substr($video[0], 0, 3) == 'id_'){
			return new self(substr($id, 3), $title, $offset);
		}
		if(strpos($id, 'youku')!==false){
			preg_match('/id_(.{13})\.html/isU', $id, $v_urls);
			return new self($v_urls[1], $title, $offset);
		}
		return false;
	}

	function getTitle($forced = false){
		if($forced && $this->title==''){
			return 'http://v.youku.com/v_show/id_' . $this->id . '.html';
		} else {
			return $this->title; 
		}
	}
	
	function getThumb(){
		$th = parent::getThumb();
		if($th !== false) return $th;
		$data = json_decode(file_get_contents('http://v.youku.com/player/getPlayList/VideoIDS/' . $this->id));
		$img = $data->data[0]->logo.'?u='.$data->data[0]->userid;;
		$im = @getimagesize($img);
		if($im !== false) return array($img, $im[2]);
		return false;
	}
	
	function getPlayerLink($autoplay = false){
		$src = 'http://player.youku.com/embed/' . $this->id;
		if($autoplay) $src .= '&autoplay=1';
		if($autoplay && $this->offset != 0){
			$src .= '&';
		} else {
			$src .= '?';
		}
		if($this->offset != 0) $src .= 'start=' . $this->splitOffset();
		return $src;
	}
	
}