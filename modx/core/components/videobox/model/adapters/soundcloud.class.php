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

class SoundCloudVideo extends VideoboxAdapter {

	public $type = 'a';
	
	function getThumb(){
		$data = json_decode(file_get_contents('http://soundcloud.com/oembed?url=' . rawurlencode($this->id) . '&format=json&maxheight=1000'));
		if($data){
			$data = explode('?', $data->thumbnail_url);
			$img = $data[0];
			$im = @getimagesize($img);
			if($im !== false) return array($img, $im[2]);
		}
		return false;
	}
	
	function getPlayerLink($autoplay = false){
		$src = 'https://w.soundcloud.com/player/?url=' . rawurlencode($this->id) . '&show_artwork=true';
		if($autoplay) $src .= '&auto_play=true';
		if(isset($this->properties['color']) && $this->properties['color']) $src .= '&color=' . $this->properties['color'];
		if(isset($this->properties['scVisual']) && !$this->properties['scVisual']){
			$src .= '&visual=false';
		} else {
			$src .= '&visual=true';
		}
		return $src;
	}
	
}