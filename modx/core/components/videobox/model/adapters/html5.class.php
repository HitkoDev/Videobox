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

class HTML5Video extends VideoboxAdapter {
	
	public static $vid;
	public static $aud;
	
	function __construct($id, $title = '', $start = 0, $end = 0, $properties = array()) {
		parent::__construct($id, $title, $start, $end, $properties);
		if(in_array(strtolower($properties['ext']), self::$aud[0])) $this->type = 'a';
	}

	function getTitle($forced = false){
		if($forced && $this->title==''){
			return $this->properties['paths']['urlFullWithPath'] . '.' . $this->properties['ext'];
		} else {
			return $this->title; 
		}
	}
	
	function getThumb(){
		if($this->properties['local']){
			$orig = $this->properties['paths']['pathAbsoluteWithPath'] . '.' . $this->properties['ext'];
			$dest = $this->properties['paths']['pathAbsoluteWithPath'];
			if(!is_file($dest . '.jpg')) shell_exec($this->properties['scriptsDir'] . 'thumb_' . $this->type . '.sh ' . escapeshellarg($orig) . ' ' . escapeshellarg($dest));
			if(is_file($dest . '.jpg')) return array($dest . '.jpg', IMAGETYPE_JPEG);
		} else {
			return array($this->properties['paths']['urlFullWithPath'] . '.jpg', IMAGETYPE_JPEG);
		}
		return '';
	}
	
	function getPlayerLink($autoplay = false){
		$props = array(
			'vb-video' => $this->id,
			'autoplay' => $autoplay ? 1 : 0
		);
		if($this->title) $props['title'] = $this->title;
		if($this->start > 0) $props['start'] = $this->splitOffset($this->start);
		if($this->end > 0) $props['end'] = $this->splitOffset($this->end);
		return $this->properties['modx']->makeUrl($this->properties['modx']->resourceIdentifier, '', $props, 'full');
	}
	
	function getSourceUrl(){
		if($this->properties['local'] && $this->properties['h5Convert']){
			$orig = $this->properties['paths']['pathAbsoluteWithPath'] . '.' . $this->properties['ext'];
			$dest = $this->properties['paths']['pathAbsoluteWithPath'];
			if($this->type == 'a'){
				shell_exec($this->properties['scriptsDir'] . 'audio.sh ' . escapeshellarg($orig) . ' ' . escapeshellarg($dest) . ' > /dev/null 2>/dev/null &');
			} else {
				shell_exec($this->properties['scriptsDir'] . 'video.sh ' . escapeshellarg($orig) . ' ' . escapeshellarg($dest) . ' > /dev/null 2>/dev/null &');
			}
		}
		return $this->properties['paths']['urlFullWithPath'];
	}
	
	function getSourceFormats(){
		$ret = array();
		if($this->type == 'v'){
			for($i = 0; $i < count(self::$vid[0]) && $i < count(self::$vid[1]); $i++){
				if($this->properties['local'] && is_file($this->properties['paths']['pathAbsoluteWithPath'] . '.' . self::$vid[0][$i])) $ret[] = array(self::$vid[0][$i], self::$vid[1][$i]);
			}
		}
		if($this->type == 'a'){
			for($i = 0; $i < count(self::$aud[0]) && $i < count(self::$aud[1]); $i++){
				if($this->properties['local'] && is_file($this->properties['paths']['pathAbsoluteWithPath'] . '.' . self::$aud[0][$i])) $ret[] = array(self::$aud[0][$i], self::$aud[1][$i]);
			}
		}
		return $ret;
	}
	
}