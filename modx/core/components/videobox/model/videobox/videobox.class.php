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

class Videobox {
	
	public $modx;
    public $config = array();
	const VB_GALLERY_NUMBER = 'vb_gallery_number';

	function __construct(modX &$modx, array $config = array()){
		$this->modx =& $modx;
		$this->config = array_merge(array(
			'assets_url' => $modx->getOption('videobox.assets_url', null, $modx->getOption('assets_url').'components/videobox/'),
			'assets_path' => $modx->getOption('videobox.assets_path', null, MODX_ASSETS_PATH.'components/videobox/'),
		), $config);
		
		if($this->gallery == null) $this->gallery = -1;
		
		$this->pages = array();
		if(isset($_GET['vbpages'])){
			$p = explode(',', rawurldecode($_GET['vbpages']));
			foreach($p as $page){
				$this->pages[] = (int) $page;
			}
		}
	}
	
	function getPage(){
		if(isset($this->pages[$this->gallery])) return $this->pages[$this->gallery];
		return 0;
	}
	
	function htmldec($string){
		return str_replace(array('&lt;', '&gt;', '&quot;'), array('<', '>', '"'), $string);
	}
	
	function htmlenc($string){
		return str_replace(array('<', '>', '"'), array('&lt;', '&gt;', '&quot;'), $string);
	}

	function videoThumbnail($video, $tWidth, $tHeight, $no_border = false, $n = 0) {
		// Prevent infinite loop
		if($n > 1) return '';
		
		// Get name suffixes
		$name = '';
		if($no_border){
			$name .= '-no_border';
		} else {
			$name .= '-'.$tWidth.'-'.$tHeight;
		}
		
		// If $video is a VideoboxAdapter object, get its data, otherwise get nobg data
		if($video instanceof VideoboxAdapter){
			$nobg = 'nobg_' . $video->type;
			$hash = md5($video->id . $name);
			$img = $video->getThumb();
		} else {
			$nobg = $video;
			$hash = md5($video . $name);
			$img = array($this->config['assets_path'] . 'images/'.$nobg.'.png', IMAGETYPE_PNG);
		}
		
		if(!is_dir($this->config['assets_path'] . 'cache')) $this->modx->cacheManager->writeTree($this->config['assets_path'] . 'cache');
		
		$target = $this->config['assets_path'] . 'cache/'.$hash.'.jpg';
		
		$img_hash = md5($target);
		
		$ret = $this->modx->cacheManager->get($img_hash);
		if($ret) return $ret;
		
		$target_info = getimagesize($target);
		if($target_info){
			$ret = array($this->config['assets_url'] . 'cache/'.$hash.'.jpg', $target_info[0], $target_info[1]);
			$this->modx->cacheManager->set($img_hash, $ret, 0);
			return $ret;
		}
		
		if(!extension_loaded('imagick')){
		
			try {
				switch($img[1]){
					case IMAGETYPE_JPEG: 
						$src_img = imagecreatefromjpeg($img[0]);
						break;
					case IMAGETYPE_PNG: 
						$src_img = imagecreatefrompng($img[0]);
						break;
					case IMAGETYPE_GIF: 
						$src_img = imagecreatefromgif($img[0]);
						break;
					default:
						return $this->videoThumbnail($nobg, $tWidth, $tHeight, $no_border, $n + 1);
				}
			} catch (Exception $e) {
				return $this->videoThumbnail($nobg, $tWidth, $tHeight, $no_border, $n + 1);
			}
			if(!$src_img) return $this->videoThumbnail($nobg, $tWidth, $tHeight, $no_border, $n + 1);
			
			$imagedata = array(imagesx($src_img), imagesy($src_img));
			
			$b_t = 0;
			$b_b = 0;
			$b_l = 0;
			$b_r = 0;

			// Remove border added by video provider
			if($imagedata[0] && $imagedata[1]){

				if($imagedata[0]<=1920 && $imagedata[1]<=1080){
				
					for($y = 3; $y < $imagedata[1]; $y++) {
						for($x = 3; $x < $imagedata[0]; $x++) {
							if($this->_chkB(_gdRGB($src_img, $x, $y))) break 2;
						}
						$b_t = $y;
					}

					for($y = $imagedata[1]-4; $y >= 0; $y--) {
						for($x = 3; $x < $imagedata[0] - 3; $x++) {
							if($this->_chkB(_gdRGB($src_img, $x, $y))) break 2;
						}
						$b_b = $imagedata[1] - 1 - $y;
					}

					for($x = 3; $x < $imagedata[0]; $x++) {
						for($y = 3; $y < $imagedata[1]; $y++) {
							if($this->_chkB(_gdRGB($src_img, $x, $y))) break 2;
						}
						$b_l = $x;
					}

					for($x = $imagedata[0]-4; $x >= 0; $x--) {
						for($y = 3; $y < $imagedata[1]; $y++) {
							if($this->_chkB(_gdRGB($src_img, $x, $y))) break 2;
						}
						$b_r = $imagedata[0] - 1 - $x;
					}
				
				}

			} else {
				return $this->videoThumbnail($nobg, $tWidth, $tHeight, $no_border, $n + 1);
			}
			
			$imagedata[0] -= $b_l + $b_r;
			$imagedata[1] -= $b_t + $b_b;
			
			// Copy and crop
			if($no_border){
				$tWidth = $imagedata[0];
				$tHeight = $imagedata[1];
				$newimg = imagecreatetruecolor($tWidth, $tHeight);
				$black = imagecolorallocate($newimg, 0, 0, 0);
				imagefilledrectangle($newimg, 0, 0, $tWidth, $tHeight, $black);
				imagecopyresampled($newimg, $src_img, 0, 0, $b_l, $b_t, $tWidth, $tHeight, $tWidth, $tHeight);
			} else {
			
				// Calculate new size and offset
				$new_w = $imagedata[0];
				$new_h = $imagedata[1];		
				
				$new_w = ($tHeight*$new_w) / $new_h;
				$new_h = $tHeight;
				if($new_w > $tWidth){
					$new_h = ($tWidth*$new_h) / $new_w;
					$new_w = $tWidth;
				}		
				
				$new_w = (int)$new_w;
				$new_h = (int)$new_h;
				$off_w = (int)(($tWidth - $new_w)/2);
				$off_h = (int)(($tHeight - $new_h)/2);
				$newimg = imagecreatetruecolor($tWidth, $tHeight);
				$black = imagecolorallocate($newimg, 0, 0, 0);
				imagefilledrectangle($newimg, 0, 0, $tWidth, $tHeight, $black);
				imagecopyresampled($newimg, $src_img, $off_w, $off_h, $b_l, $b_t, $new_w, $new_h, $imagedata[0], $imagedata[1]);
			}
			
			// Save the image and return
			imagejpeg($newimg, $target.'__', 95);
			imagedestroy($src_img);
			imagedestroy($newimg);
			
		} else {
			
			$imgM = new Imagick($img[0]);
			$imagedata = array($imgM->getImageWidth(), $imgM->getImageHeight());
			
			$b_t = 0;
			$b_b = 0;
			$b_l = 0;
			$b_r = 0;

			// Remove border added by video provider
			if($imagedata[0] && $imagedata[1]){

				if($imagedata[0]<=1920 && $imagedata[1]<=1080){
				
					for($y = 3; $y < $imagedata[1]; $y++) {
						for($x = 3; $x < $imagedata[0]; $x++) {
							if($this->_chkB($imgM->getImagePixelColor($x, $y)->getColor())) break 2;
						}
						$b_t = $y + 1;
					}

					for($y = $imagedata[1]-4; $y >= 0; $y--) {
						for($x = 3; $x < $imagedata[0] - 3; $x++) {
							if($this->_chkB($imgM->getImagePixelColor($x, $y)->getColor())) break 2;
						}
						$b_b = $imagedata[1] - $y;
					}

					for($x = 3; $x < $imagedata[0]; $x++) {
						for($y = 3; $y < $imagedata[1]; $y++) {
							if($this->_chkB($imgM->getImagePixelColor($x, $y)->getColor())) break 2;
						}
						$b_l = $x + 1;
					}

					for($x = $imagedata[0]-4; $x >= 0; $x--) {
						for($y = 3; $y < $imagedata[1]; $y++) {
							if($this->_chkB($imgM->getImagePixelColor($x, $y)->getColor())) break 2;
						}
						$b_r = $imagedata[0] - $x;
					}
				
				}

			} else {
				return $this->videoThumbnail($nobg, $tWidth, $tHeight, $no_border, $n + 1);
			}
			
			$imagedata[0] -= $b_l + $b_r;
			$imagedata[1] -= $b_t + $b_b;
			
			$imgM->cropImage($imagedata[0], $imagedata[1], $b_l, $b_t);
			if($no_border){
				$tWidth = $imagedata[0];
				$tHeight = $imagedata[1];
			} else {
				
				// Calculate new size and offset
				$new_w = $imagedata[0];
				$new_h = $imagedata[1];		
				
				$new_w = ($tHeight*$new_w) / $new_h;
				$new_h = $tHeight;
				if($new_w > $tWidth){
					$new_h = ($tWidth*$new_h) / $new_w;
					$new_w = $tWidth;
				}		
				
				$new_w = (int)$new_w;
				$new_h = (int)$new_h;
				$off_w = (int)(($tWidth - $new_w)/2);
				$off_h = (int)(($tHeight - $new_h)/2);
				
				$imgM->setImageBackgroundColor(new ImagickPixel("rgb(0, 0, 0)"));
				$imgM->resizeImage($new_w, $new_h, imagick::FILTER_CATROM, 1);
				$imgM->extentImage($tWidth, $tHeight, -$off_w, -$off_h);
			}
			$imgM->setImageFormat('jpeg');
			$imgM->setImageCompressionQuality(95);
			$imgM->stripImage();
			$imgM->writeImage($target.'__');
			
		}
		rename($target.'__', $target);
		$ret = array($this->config['assets_url'] . 'cache/'.$hash.'.jpg', $tWidth, $tHeight);
		$this->modx->cacheManager->set($img_hash, $ret, 0);
		return $ret;
	}
	
	function pagination($total, $current, $perPage){
		global $modx;
		if($perPage < 1) return '';
		if($total < $perPage) return '';
		$pages = floor(($total - 1) / $perPage + 1);
		$output = '';
		$id = $modx->resource->get('id');
		$que = $_GET;
		$rq = trim($modx->getOption('request_param_id'));
		$ra = trim($modx->getOption('request_param_alias'));
		if($rq) unset($que[$rq]);
		if($ra) unset($que[$ra]);
		unset($que['vbpages']);
		$pref = '';
		$i = 0;
		for(; $i < $this->gallery; $i++) $pref .= (isset($this->pages[$i]) ? $this->pages[$i] : 0) . ',';
		$post = '';
		$i++;
		for(; $i < count($this->pages); $i++) $post .= ',' . (isset($this->pages[$i]) ? $this->pages[$i] : 0);
		for($i = 0; $i < $pages; $i++){
			$pg = preg_replace("/(^,)|((?<=,),+)|((?<=0)0+)|((,|,0)+$)/m", '', $pref . $i . $post);	//	clean 1) leading comas, 2) multiple comas, 3) multiple zeros, 4) trailing comas and zeros
			$output .= '<li '.($i == $current ? 'class="active"' : '').'><a href="'.$modx->makeUrl($id, '', ($pg ? array_merge($que, array('vbpages' => $pg)) : $que)).'">'.($i+1).'</a></li>';
		}
		return '<ul class="pagination">'.$output.'</ul>';
	}
	
	protected function _gdRGB($img, $x, $y){
		$rgb = imagecolorat($img, $x, $y);
		$r = ($rgb >> 16) & 0xFF;
		$g = ($rgb >> 8) & 0xFF;
		$b = $rgb & 0xFF;
		return array(
			'r' => $r,
			'g' => $g,
			'b' => $b,
			'a' => 0
		);
	}
	
	// calculate & check luminosity (black border detection)
	protected function _chkB($rgb){
		
		$var_R = ($rgb['r'] / 255);
		$var_G = ($rgb['g'] / 255);
		$var_B = ($rgb['b'] / 255);

		$var_R = ($var_R > 0.04045) ? pow((($var_R + 0.055)/1.055), 2.4) : $var_R/12.92;
		$var_G = ($var_G > 0.04045) ? pow((($var_G + 0.055)/1.055), 2.4) : $var_G/12.92;
		$var_B = ($var_B > 0.04045) ? pow((($var_B + 0.055)/1.055), 2.4) : $var_B/12.92;
		
		$y = $var_R * 0.2126 + $var_G * 0.7152 + $var_B * 0.0722;
		$y = ($y > 0.008856) ? pow($y, 1/3) : 7.787*$y;
		$y = 116*$y;
		
		return $y > 20;
	}
}
?>