<?php

/**	
 *	@author		HitkoDev http://hitko.eu/videobox
 *	@copyright	Copyright (C) 2015 HitkoDev All Rights Reserved.
 *	@license	http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 *
 *	This program is free software: you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License as published by
 *	the Free Software Foundation, either version 3 of the License, or
 *	any later version.
 *
 *	This program is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *	GNU General Public License for more details.
 *
 *	You should have received a copy of the GNU General Public License
 *	along with this program. If not, see <http://www.gnu.org/licenses/>
 */

require_once('adapter.class.php');

class YouTubeVideo extends VideoboxAdapter {

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
		if($this->start != 0) $src .= '&start=' . $this->start;
		if($this->end != 0) $src .= '&end=' . $this->end;
		return $src;
	}
	
}