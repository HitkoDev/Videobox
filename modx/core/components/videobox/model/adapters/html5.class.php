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

class HTML5Video extends VideoboxAdapter {
	
	public static $vid;
	public static $aud;
    public static $img;
	
	function __construct($id, $title = '', $start = 0, $end = 0, $properties = array()) {
		parent::__construct($properties);
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
		} 
        for($i = 0; $i < count(self::$img[0]) && $i < count(self::$img[1]); $i++){
            if($this->properties['local']){
                $dest = $this->properties['paths']['pathAbsoluteWithPath'];
                if(is_file($dest . '.' . self::$img[0][$i])) return array($dest . '.' . self::$img[0][$i], self::$img[1][$i]);
            } else {
                $dest = $this->properties['paths']['urlFullWithPath'];
                if(self::is_file_remote($dest . '.' . self::$img[0][$i])) return array($dest . '.' . self::$img[0][$i], self::$img[1][$i]);
            }
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
				if($this->properties['local']){
                    if(is_file($this->properties['paths']['pathAbsoluteWithPath'] . '.' . self::$vid[0][$i])) $ret[] = array(self::$vid[0][$i], self::$vid[1][$i]);
                } else {
                    if(self::is_file_remote($this->properties['paths']['urlFullWithPath'] . '.' . self::$vid[0][$i])) $ret[] = array(self::$vid[0][$i], self::$vid[1][$i]);
                }
			}
		}
		if($this->type == 'a'){
			for($i = 0; $i < count(self::$aud[0]) && $i < count(self::$aud[1]); $i++){
				if($this->properties['local']){
                    if(is_file($this->properties['paths']['pathAbsoluteWithPath'] . '.' . self::$aud[0][$i])) $ret[] = array(self::$aud[0][$i], self::$aud[1][$i]);
                } else {
                    if(self::is_file_remote($this->properties['paths']['urlFullWithPath'] . '.' . self::$aud[0][$i])) $ret[] = array(self::$aud[0][$i], self::$aud[1][$i]);
                }
			}
		}
		return $ret;
	}
    
    static function is_file_remote($url){
        stream_context_set_default(array(
            'http' => array(
                'method' => 'HEAD'
            )
        ));
        $headers = get_headers($url);
        stream_context_set_default(array(
            'http' => array(
                'method' => 'GET'
            )
        ));
        return self::parseHeaders($headers)['status'] == 200;
    }
    
    static function parseHeaders(array $headers, $header = null){
        $output = array();

        if ('HTTP' === substr($headers[0], 0, 4)) {
            list(, $output['status'], $output['status_text']) = explode(' ', $headers[0]);
            unset($headers[0]);
        }

        foreach ($headers as $v) {
            $h = preg_split('/:\s*/', $v);
            $output[strtolower($h[0])] = $h[1];
        }

        if (null !== $header) {
            if (isset($output[strtolower($header)])) {
                return $output[strtolower($header)];
            }

            return;
        }

        return $output;
    }
	
}