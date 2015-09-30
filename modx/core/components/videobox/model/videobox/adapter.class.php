<?php

class VideoboxAdapter {
	
	function __construct($id, $title = '', $start = 0, $end = 0){
		$this->id = $id;
		$this->title = $title;
		$this->start = $start;
		$this->end = $end;
		$this->type = 'v';
	}
	
	function getThumb(){
		$id = $this->id;
		$ext = array('.png', '.jpg', '.jpeg', '.gif');
		$file = DOC_ROOT . '/plugins/system/videobox/thumbs/' . $id;
		foreach($ext as $ex){
			if(is_file($file . $ex)){
				$im = @getimagesize($file . $ex);
				if($im !== false) return array($file . $ex, $im[2]);
			}
		}
		return false;
	}

	function getTitle($forced = false){
		if($forced && $this->title==''){
			return $this->id;
		} else {
			return $this->title; 
		}
	}
	
	function getPlayerLink($autoplay = false){
		$src = $this->id;
		if($autoplay) $src .= '?autoplay=1';
		if($this->start != 0) $src .= '&start=' . $this->splitOffset($this->start);
		if($this->end != 0) $src .= '&end=' . $this->splitOffset($this->end);
		return $src;
	}
	
	final function getID(){
		return ('id="_vbVideo_' . crc32($this->id . $this->title) . '"');
	}
	
	protected final function getParam($name, $suffix='', $default=null){
		return $this->params->get($name . $suffix, $this->params->get($name, $default));
	}

	protected final function splitOffset($offset = 0){
		if($offset != 0){
			$off = '';
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