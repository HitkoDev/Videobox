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

class Video {
	
	function __construct($id, $title = '', $offset = 0){
		$this->id = $id;
		$this->title = $title;
		$this->offset = $offset;
		$this->type = 'v';
	}

	function getTitle($forced = false){
		if($forced && $this->title==''){
			return $this->id;
		} else {
			return $this->title; 
		}
	}
	
	function getThumb($id = false){
		if($id === false) $id = $this->id;
		$ext = array('.png', '.jpg', '.jpeg', '.gif');
		foreach($ext as $ex){
			if(is_file('thumbs/' . $id . $ex)){
				$im = @getimagesize('thumbs/' . $id . $ex);
				if($im !== false) return array('thumbs/' . $id . $ex, $im[2]);
			}
		}
		return false;
	}
	
	function getPlayerLink($autoplay = false){
		$src = $this->id;
		if($autoplay) $src .= '?autoplay=1';
		if($this->offset != 0) $src .= '&t=' . $this->splitOffset();
		return $src;
	}

	protected function splitOffset(){
		if($this->offset != 0){
			$off = '';
			$offset = $this->offset;
			$s = $offset%60;
			$off = $s . 's';
			$offset = ($offset - $s)/60;
			if($offset > 0){
				$m = $offset%60;
				$off = $m . 'm' . $off;
				$h = ($offset - $m)/60;
				if($h > 0) $off = $h . 'h' . $off;
			}
			return $off;
		}
		return '';
	}
	
}