<?php

/**	
 *	@author		HitkoDev http://hitko.eu/videobox
 *	@copyright	Copyright (C) 2016 HitkoDev All Rights Reserved.
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

class VimeoVideo extends VideoboxAdapter {

	function getTitle($forced = false){
		if($forced && $this->title==''){
			return 'http://vimeo.com/' . $this->id;
		} else {
			return $this->title; 
		}
	}
	
	function getThumb(){
		$th = parent::getThumb();
		if($th !== false) return $th;
		$data = unserialize(file_get_contents('http://vimeo.com/api/v2/video/' . $this->id . '.php'));
		$img = $data[0]['thumbnail_large'];
		$im = @getimagesize($img);
		if($im !== false) return array($img, $im[2]);
		return false;
	}
	
	function getPlayerLink($autoplay = false){
		$src = 'https://player.vimeo.com/video/' . $this->id . '?byline=0&portrait=0&color=' . $color;
		if($autoplay) $src .= '&autoplay=1';
		if(isset($this->properties['color']) && $this->properties['color']) $src .= '&color=' . $this->properties['color'];
		if($this->start != 0) $src .= '#t=' . $this->splitOffset($this->start);
		return $src;
	}
	
}