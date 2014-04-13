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

jimport( 'joomla.plugin.plugin' );

class plgSystemVideobox extends JPlugin
{

	public function onAfterDispatch(){
		$app = JFactory::getApplication();
		$document = JFactory::getDocument();
		if($app->isSite() && method_exists($document, 'addCustomTag')){
			$document->addCustomTag('<link rel="stylesheet" href="'.JURI::root().'/plugins/system/videobox/css/videobox.css" type="text/css" media="screen" />');
			$document->addCustomTag('<link rel="stylesheet" href="'.JURI::root().'/plugins/system/videobox/css/functions.css" type="text/css" media="screen" />');
			if($this->params->get('loadjq')=='1'){
				$document->addCustomTag('<script type="text/javascript" src="'.JURI::root().'/plugins/system/videobox/js/jquery.min.js"></script><script type="text/javascript">jQuery.noConflict();</script>');
			}
			$document->addCustomTag('<script type="text/javascript" src="'.JURI::root().'/plugins/system/videobox/js/videobox.js"></script>');
			$document->addCustomTag('<script type="text/javascript" src="'.JURI::root().'/plugins/system/videobox/js/functions.js"></script>');
		}
	}

	public function onAfterRender(){
		$app = JFactory::getApplication();
		$document = JFactory::getDocument();
		if($app->isSite() && method_exists($document, 'addCustomTag')){
			$vb_path = str_replace('//', '/', JPATH_BASE . '/plugins/system/videobox/');
			
			// Clear cache and unset the clear parameter
			if($this->params->get('clear')){
				$this->params->set('clear', '0');
				$db = JFactory::getDBO();
				$query = $db->getQuery(true);
				$query->update('#__extensions AS a');
				$query->set('a.params = ' . $db->quote((string)$this->params));
				$query->where('a.element = "videobox"');
				$db->setQuery($query);
				$db->query();
				foreach(new DirectoryIterator($vb_path.'cache') as $fileInfo) {
					if(!$fileInfo->isDot() && $fileInfo->getFilename() != 'index.html') unlink($vb_path.'cache/'.$fileInfo->getFilename());
				}
			}
			
			// Get content
			$pageload = JResponse::getBody();
			preg_match('/<(\s*)body(.*)\/(\s*)body(\s*)>/s', $pageload, $buffer);
			$buffer = $buffer[2];
			$old_buffer = $buffer;
			$buffer = str_replace(array("&#123;", "&#125;"), array("{", "}"), $buffer);
			
			// Avoid rendering Videobox inside texteditors
			preg_match_all('/<(\s*)textarea([^>]*)>(.*?)<\/(\s*)textarea(\s*)>/s', $buffer, $areas);
			foreach($areas[0] as $area){
				$area1 = str_replace(array("{", "}"), array("&#123;", "&#125;"), $area);
				$buffer = str_replace($area, $area1, $buffer);
			}
			
			// Avoid rendering Videobox when {raw} is used
			preg_match_all('/{raw}([\s\S]*){\/raw}/isU', $buffer, $matches);			
			foreach($matches[1] as $match) {
				$raw_text = str_replace(array("{", "}"), array("&#123;", "&#125;"), $match);
				$buffer = preg_replace('/{raw}([\s\S]*){\/raw}/isU', $raw_text, $buffer, 1);
			}
			
			// Get custom tag
			$tags = array('videobox');
			$tag = preg_replace("/[^a-zA-Z0-9]/", "", $this->params->get('tag', 'videobox'));
			if($tag != 'videobox' && $tag != '') $tags[] = $tag;
			
			foreach($tags as $tag){
			
				// Match Videobox calls
				$regex = '/{'.$tag.'}(.*){\/'.$tag.'}/isU';
				preg_match_all($regex, $buffer, $matches);
				
				// Get pages for the gallery display
				$url2 = explode(',', JRequest::getVar('vblimitstart', ''));
				
				$co = 0;
				foreach($matches[1] as $match){
					
					$co++;
					$match = strip_tags($match);				
					$this->parametri = explode('||', $match);
					$videos = explode('|,', $this->parametri[0]);
					$count = count($videos);
					
					// Match parameters in code
					$para_match = array(array());
					if(isset($this->parametri[1])){
						preg_match_all('/([^=, ]*)=(([^,]*)|"([^"]*)")/i', $this->parametri[1], $para_match);
					}
					foreach($para_match[0] as $para){
						if(preg_match('/([^=]+)="{0,1}([^"]*)"{0,1}/i', $para, $parameter) > 0){
							if(isset($parameter[1]) && isset($parameter[2])){
								if((trim($parameter[1])!='') && (trim($parameter[2])!='')) $this->parametri[trim($parameter[1])] = trim($parameter[2]);
							}
						}
					}
					if(isset($this->parametri['alternative']) && ($this->parametri['alternative']==1)) $this->parametri['alternative'] = 99;
					if(isset($this->parametri['display'])){
						$this->parametri['display'] = strtolower($this->parametri['display']);
						if($this->parametri['display']=='links' || $this->parametri['display']=='link' || $this->parametri['display']==1){
							$this->parametri['display'] = 1;
						} else if($this->parametri['display']=='box' || $this->parametri['display']==2) {
							$this->parametri['display'] = 2;
						} else {
							$this->parametri['display'] = 0;
						}
					}
					if(isset($this->parametri['player'])){
						$this->parametri['player'] = strtolower($this->parametri['player']);
						if($this->parametri['player']=='inline' || $this->parametri['player']==0){
							$this->parametri['player'] = 0;
						} else {
							$this->parametri['player'] = 1;
						}
					}
					
					// Get the required parameters		
					if(!isset($this->parametri['cache'])) $this->parametri['cache'] = $this->params->get('cache');
					if($count>1){
						if(!isset($this->parametri['display']) || $this->parametri['display']>1) $this->parametri['display'] = $this->params->get('links_g');
						if($this->parametri['display']==1){
							$this->parametri['player'] = 1;
							if(!isset($this->parametri['class'])) $this->parametri['class'] = $this->params->get('class_lg');
							if(!isset($this->parametri['style'])) $this->parametri['style'] = $this->params->get('style_lg');
							if(!isset($this->parametri['separator'])) $this->parametri['separator'] = $this->params->get('separator');
							if(!isset($this->parametri['width'])) $this->parametri['width'] = $this->params->get('width_lg');
							if(!isset($this->parametri['height'])) $this->parametri['height'] = $this->params->get('height_lg');
							if(!isset($this->parametri['sc_visual'])) $this->parametri['sc_visual'] = $this->params->get('sc_visual_lg');
						} else {
							if(!isset($this->parametri['player'])) $this->parametri['player'] = $this->params->get('player_g');
							if(!isset($this->parametri['class'])) $this->parametri['class'] = $this->params->get('class_g');
							if(!isset($this->parametri['style'])) $this->parametri['style'] = $this->params->get('style_g');
							if(!isset($this->parametri['alternative'])) $this->parametri['alternative'] = $this->params->get('cs_g');
							if(($this->parametri['player']!=$this->params->get('player_g') && $this->parametri['alternative']==1) || $this->parametri['alternative']==99){
								if(!isset($this->parametri['width'])) $this->parametri['width'] = $this->params->get('width_nlb_g');
								if(!isset($this->parametri['height'])) $this->parametri['height'] = $this->params->get('height_nlb_g');
								if(!isset($this->parametri['t_width'])) $this->parametri['t_width'] = $this->params->get('width_nlb_gt');
								if(!isset($this->parametri['t_height'])) $this->parametri['t_height'] = $this->params->get('height_nlb_gt');
								if(!isset($this->parametri['button'])) $this->parametri['button'] = $this->params->get('play_nlb_g');	
								if(!isset($this->parametri['break'])) $this->parametri['break'] = $this->params->get('break_nlb');
								if(!isset($this->parametri['pages'])) $this->parametri['pages'] = $this->params->get('pages_nlb');
								if(!isset($this->parametri['sc_visual'])) $this->parametri['sc_visual'] = $this->params->get('sc_visual_nlb_g');
							} else {
								if(!isset($this->parametri['width'])) $this->parametri['width'] = $this->params->get('width_g');
								if(!isset($this->parametri['height'])) $this->parametri['height'] = $this->params->get('height_g');
								if(!isset($this->parametri['t_width'])) $this->parametri['t_width'] = $this->params->get('width_gt');
								if(!isset($this->parametri['t_height'])) $this->parametri['t_height'] = $this->params->get('height_gt');
								if(!isset($this->parametri['button'])) $this->parametri['button'] = $this->params->get('play_g');	
								if(!isset($this->parametri['break'])) $this->parametri['break'] = $this->params->get('break');
								if(!isset($this->parametri['pages'])) $this->parametri['pages'] = $this->params->get('pages');
								if(!isset($this->parametri['pages_results'])) $this->parametri['pages_results'] = $this->params->get('pages_results');
								if(!isset($this->parametri['sc_visual'])) $this->parametri['sc_visual'] = $this->params->get('sc_visual_g');
							}
						}
					} else {
						if(!isset($this->parametri['display'])) $this->parametri['display'] = $this->params->get('display');
						if($this->parametri['display']==2){
							if(!isset($this->parametri['player'])) $this->parametri['player'] = $this->params->get('player_b');
							if(!isset($this->parametri['class'])) $this->parametri['class'] = $this->params->get('class_l');
							if(!isset($this->parametri['style'])) $this->parametri['style'] = $this->params->get('style_l');
							if(!isset($this->parametri['alternative'])) $this->parametri['alternative'] = $this->params->get('cs_b');
							if(($this->parametri['player']!=$this->params->get('player_b') && $this->parametri['alternative']==1) || $this->parametri['alternative']==99){
								if(!isset($this->parametri['t_width'])) $this->parametri['t_width'] = $this->params->get('width_nlb_bt');
								if(!isset($this->parametri['t_height'])) $this->parametri['t_height'] = $this->params->get('height_nlb_bt');
								if(!isset($this->parametri['width'])) $this->parametri['width'] = $this->params->get('width_nlb_b');
								if(!isset($this->parametri['height'])) $this->parametri['height'] = $this->params->get('height_nlb_b');	
								if(!isset($this->parametri['button'])) $this->parametri['button'] = $this->params->get('play_nlb_b');
								if(!isset($this->parametri['sc_visual'])) $this->parametri['sc_visual'] = $this->params->get('sc_visual_nlb_b');
							} else {
								if(!isset($this->parametri['t_width'])) $this->parametri['t_width'] = $this->params->get('width_bt');
								if(!isset($this->parametri['t_height'])) $this->parametri['t_height'] = $this->params->get('height_bt');
								if(!isset($this->parametri['width'])) $this->parametri['width'] = $this->params->get('width_b');
								if(!isset($this->parametri['height'])) $this->parametri['height'] = $this->params->get('height_b');
								if(!isset($this->parametri['button'])) $this->parametri['button'] = $this->params->get('play_b');	
								if(!isset($this->parametri['sc_visual'])) $this->parametri['sc_visual'] = $this->params->get('sc_visual_b');					
							}
						} else {
							if($this->parametri['display']==1){
								$this->parametri['player'] = 1;
								if(!isset($this->parametri['class'])) $this->parametri['class'] = $this->params->get('class_l');
								if(!isset($this->parametri['style'])) $this->parametri['style'] = $this->params->get('style_l');
								if(!isset($this->parametri['width'])) $this->parametri['width'] = $this->params->get('width_l');
								if(!isset($this->parametri['height'])) $this->parametri['height'] = $this->params->get('height_l');
								if(!isset($this->parametri['sc_visual'])) $this->parametri['sc_visual'] = $this->params->get('sc_visual_l');
							} else {
								if(!isset($this->parametri['width'])) $this->parametri['width'] = $this->params->get('width');
								if(!isset($this->parametri['height'])) $this->parametri['height'] = $this->params->get('height');
								if(!isset($this->parametri['class'])) $this->parametri['class'] = $this->params->get('class');
								if(!isset($this->parametri['style'])) $this->parametri['style'] = $this->params->get('style');
								if(!isset($this->parametri['play'])) $this->parametri['play'] = $this->params->get('autoplay');
								if(!isset($this->parametri['sc_visual'])) $this->parametri['sc_visual'] = $this->params->get('sc_visual_s');
							}
						}
					}
					
					$this->parametri['path'] = $vb_path;
					if(!isset($this->parametri['sc_visual']) || $this->parametri['sc_visual']==2) $this->parametri['sc_visual'] = $this->params->get('sc_visual');
					
					if(!isset($this->parametri['pages']) || $this->parametri['pages']==0) $this->parametri['pages'] = 99999999;
					
					// Create video adapter object for each video
					$video_objects = array();
					foreach($videos as $video){
						$video = $this->_getVideo($video);
						if($video) $video_objects[] = $video;
					}
					$videos = $video_objects;
					
					$count = count($videos);
					
					if($count){
					
						// Create pagination if needed
						$start = 0;
						$pagination = '';
						if(($count>$this->parametri['pages']) && ($this->parametri['pages']!=0) && ($this->parametri['display']==0)){
							
							if(isset($url2[$co-1])) $start = (int)$url2[$co-1];
							$path = '';
							for($h = 0; $h<$co-1; $h++){
								if(!isset($url2[$h])) $url2[$h] = '';
								if($url2[$h]=='') $url2[$h]='0';
								$path .= ','.$url2[$h];
							}
							$after = '';
							for($h = $co; $h<count($url2); $h++){
								if($url2[$h]=='') $url2[$h]='0';
								$after .= ','.$url2[$h];
							}
							
							jimport('joomla.html.pagination');
							$pg = new JPagination($count, $start, $this->parametri['pages']);
							$pg->prefix = 'vb';
							$pagination = '<div class="pagination">';
							if($this->parametri['pages_results']) $pagination .= '<p class="counter">'.$pg->getPagesCounter().'</p>';
							$pagination .= $pg->getPagesLinks().'</div>';
							$pagination = str_replace(array('vblimitstart=,', ',,'), array('vblimitstart=', ','), preg_replace('/vblimitstart=(\d+)/', 'vblimitstart='.$path.',$1'.$after, $pagination));
						}
						
						// Create the appropriate display method code
						$thumbnails = '';
						if($count==1){
							
							if($this->parametri['display']==2){
								$thumbnails .= $this->_videoBox($videos[0], $co);
							} elseif($this->parametri['display']==1){
								$thumbnails .= $this->_videoLink($videos[0], $co);
							} else {
								$thumbnails .= $this->_videoCode($videos[0]);
							}
							
						} elseif($count > 1){
							
							if($this->parametri['display']==1){
							
								foreach($videos as $n => $video){
									if($n < ($count-1)){
										$thumbnails .= $this->_videoLink($video, $co, $n, $this->parametri['separator']);
									} else {
										$thumbnails .= $this->_videoLink($video, $co, $n);
									}
								}
								
							} else {
								$thumbnails .= '<div style="'.$this->parametri['style'];
								if($this->parametri['break']!=0) $thumbnails.= ' max-width: '.(($this->parametri['t_width'] + 30) * $this->parametri['break'] - 20).'px;';
								$thumbnails.= '" class="vb_gallery_frame '.$this->parametri['class'].'"><ul class="vb_video">';
								for($n = $start; $n < ($start + $this->parametri['pages']); $n++){
									if(isset($videos[$n])){
										$thumbnails .= $this->_videoThumb($videos[$n], $co, $n);
									} else {
										break;
									}
								}
								$thumbnails .= '</ul></div>';
							}
							
						}
						
						// Insert the code into the content
						$buffer = preg_replace($regex, str_replace('&', '&amp;', $thumbnails).$pagination, $buffer, 1);
					}
				}
			}
			
			$pageload = str_replace($old_buffer, $buffer, $pageload);
			JResponse::setBody($pageload);
			return true;
		}
	}
	
	public function videoThumbnail($video, $no_border = false, $n = 0) {
		// Prevent infinite loop
		if($n > 1) return '';
		
		// Get name suffixes
		$name = '';
		if($no_border){
			$name .= '-no_border';
		} else {
			$name .= '-'.$this->parametri['t_width'].'-'.$this->parametri['t_height'];
			if($this->parametri['button']) $name .= '-button';
		}
		
		// If $video is a Video object, get its data, otherwise get nobg data
		if($video instanceof Video){
			$nobg = 'nobg_v';
			if($video->type == 'a') $nobg = 'nobg_a';
			$hash = md5($video->id . $name);
			if($this->parametri['cache'] && is_file($this->parametri['path'].'cache/'.$hash.'.jpg')){
				return 'cache/'.$hash.'.jpg';
			}
			$img = $video->getThumb();
		} else {
			$nobg = $video;
			$hash = md5($video . $name);
			if($this->parametri['cache'] && is_file($this->parametri['path'].'cache/'.$hash.'.jpg')){
				return 'cache/'.$hash.'.jpg';
			}
			$img = array($this->parametri['path'].'css/'.$nobg.'.png', IMAGETYPE_PNG);
		}
		
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
					return $this->videoThumbnail($nobg, $no_border, $n + 1);
			}
		} catch (Exception $e) {
			return $this->videoThumbnail($nobg, $no_border, $n + 1);
		}
		if(!$src_img) return $this->videoThumbnail($nobg, $no_border, $n + 1);
		
		$imagedata[0] = imagesx($src_img);
		$imagedata[1] = imagesy($src_img);

		// Remove border added by video provider
		if($imagedata[0] && $imagedata[1]){
		
			$b_t = 0;
			$b_b = 0;
			$b_l = 0;
			$b_r = 0;

			if($imagedata[0]<=1920 && $imagedata[1]<=1080){
			
				for($y = 3; $y < $imagedata[1]; $y++) {
					for($x = 3; $x < $imagedata[0]; $x++) {
						if($this->_chkB($src_img, $x, $y)) break 2;
					}
					$b_t = $y;
				}

				for($y = $imagedata[1]-4; $y >= 0; $y--) {
					for($x = 3; $x < $imagedata[0]; $x++) {
						if($this->_chkB($src_img, $x, $y)) break 2;
					}
					$b_b = $imagedata[1] - 1 - $y;
				}

				for($x = 3; $x < $imagedata[0]; $x++) {
					for($y = 3; $y < $imagedata[1]; $y++) {
						if($this->_chkB($src_img, $x, $y)) break 2;
					}
					$b_l = $x;
				}

				for($x = $imagedata[0]-4; $x >= 0; $x--) {
					for($y = 3; $y < $imagedata[1]; $y++) {
						if($this->_chkB($src_img, $x, $y)) break 2;
					}
					$b_r = $imagedata[0] - 1 - $x;
				}
			
			}

		} else {
			return $this->videoThumbnail($nobg, $no_border, $n + 1);
		}
		
		$imagedata[0] -= $b_l + $b_r;
		$imagedata[1] -= $b_t + $b_b;
		
		if($no_border){	
			
			// Just crop the border
			$newimg = imagecreatetruecolor($imagedata[0], $imagedata[1]);
			imagecopy($newimg, $src_img, 0, 0, $b_l, $b_t, $imagedata[0], $imagedata[1]);
			
		} else {
		
			// Calculate new size and offset
			$new_w = $imagedata[0];
			$new_h = $imagedata[1];		
			
			if($new_h > $this->parametri['t_height']){
				$new_w = ($this->parametri['t_height']*$new_w) / $new_h;
				$new_h = $this->parametri['t_height'];
			}
			if($new_w > $this->parametri['t_width']){
				$new_h = ($this->parametri['t_width']*$new_h) / $new_w;
				$new_w = $this->parametri['t_width'];
			}		
			
			$new_w = (int)$new_w;
			$new_h = (int)$new_h;
			$off_w = (int)(($this->parametri['t_width'] - $new_w)/2);
			$off_h = (int)(($this->parametri['t_height'] - $new_h)/2);
			
			// Copy and crop
			$newimg = imagecreatetruecolor($this->parametri['t_width'], $this->parametri['t_height']);
			$black = imagecolorallocate($newimg, 0, 0, 0);
			imagefilledrectangle($newimg, 0, 0, $this->parametri['t_width'], $this->parametri['t_height'], $black);
			imagecopyresampled($newimg, $src_img, $off_w, $off_h, $b_l, $b_t, $new_w, $new_h, $imagedata[0], $imagedata[1]);
			
			// Add button
			if($this->parametri['button']){
				$play = imagecreatefrompng($this->parametri['path'].'css/play.png');
				if($play && $this->parametri['t_width'] >= (imagesx($play) + 50) && $this->parametri['t_height'] >= (imagesy($play) + 50)){
					imagealphablending($newimg, true);
					imagecopyresampled($newimg, $play, ($this->parametri['t_width']-imagesx($play))/2, ($this->parametri['t_height']-imagesy($play))/2, 0, 0, imagesx($play), imagesy($play), imagesx($play), imagesy($play));
				}
			}
		}
		
		// Save the image and return
		imagejpeg($newimg, $this->parametri['path'].'cache/'.$hash.'.jpg', 100);
		return 'cache/'.$hash.'.jpg';
		
	}
	
	protected function _getVideo($video){
		// Break the $video into the $id, $title, and $offset
		$video = explode('|', $video);
		$title = '';
		if(isset($video[1])) $title = trim($video[1]);
		$title = $this->htmldec($title);
		$title = $this->htmlenc($title);
		$video = explode('#', $video[0]);
		$id = trim($video[0]);
		$offset = 0;
		if(count($video) > 1 && is_numeric(str_replace(':', '', trim($video[count($video) - 1])))){
			$off = explode (':', trim($video[count($video) - 1]));
			foreach($off as $off1){
				$offset = $offset*60 + $off1;
			}
		}
		
		$adapters = $this->_getAdapters();
		foreach($adapters as $adapter){
			$video = call_user_func($adapter . '::adapterSwitch', $id, $title, $offset, $this);
			if($video){
				return $video;
			}
		}
		return false;
	}
	
	protected function _videoCode($video) {
		$src = $video->getPlayerLink($this->parametri['play']);
		$html  = '<div style="width: '.$this->parametri['width'].'px; height: '.$this->parametri['height'].'px; max-width: 100%; '.$this->parametri['style'].'" class="vb_videoFrame '.$this->parametri['class'].'"><iframe src="'.$src.'" frameborder="0" allowfullscreen oallowfullscreen msallowfullscreen webkitallowfullscreen mozallowfullscreen style="display: block; width: 100%; height: 100%;"></iframe></div>';
		return $html;
	}
	
	protected function _videoLink($video, $i, $n = 1, $separator = ''){
		$src = $video->getPlayerLink(true);
		$rel = 'videobox';
		if($this->parametri['player']=='0') $rel = 'vbinline';
		$html = '<a class="'.$this->parametri['class'].' vb_video_link_a" ' . $video->getID() . ' style="'.$this->parametri['style'].'" href="'.$src.'" rel="'.$rel.'.sig'.$i.'" title="' . $video->getTitle() . '" videowidth="'.$this->parametri['width'].'" videoheight="'.$this->parametri['height'].'">' . $video->getTitle(true) . '</a>'.$separator;
		return $html;
	}

	protected function _videoThumb($video, $i, $n) {
		$src = $video->getPlayerLink(true);
		$img = JURI::root().'/plugins/system/videobox/'.$this->videoThumbnail($video);
		$rel = 'videobox';
		$v_width = $this->parametri['width'];
		$v_height = $this->parametri['height'];
		if($this->parametri['player']=='0'){
			$rel = 'vbinline';
			$v_width = $this->parametri['t_width'];
			$v_height = $this->parametri['t_height'];
		}
		$thumb  = '<li class="vb_video_cont vb_gal" style="width:'.($this->parametri['t_width'] + 10).'px;">
			<a href="'.$src.'" rel="'.$rel.'.sig'.$i.'" ' . $video->getID() . ' title="' . $video->getTitle() . '" videowidth="'.$v_width.'" videoheight="'.$v_height.'">
				<img src="'.$img.'" id="vb_thumb_'.$i.'_'.$n.'" alt="' . $video->getTitle() . '" />
				<span class="vb_video_title" >' . $video->getTitle() . '</span>
			</a>
		</li>';
		return $thumb;
	}
	
	protected function _videoBox($video, $i, $n = 1) {
		$src = $video->getPlayerLink(true);
		$img = JURI::root().'/plugins/system/videobox/'.$this->videoThumbnail($video);
		$rel = 'videobox';
		if($this->parametri['player']=='0') $rel = 'vbinline';		
		$thumb  = '<span class="vb_video_cont vb_box '.$this->parametri['class'].'" style="'.$this->parametri['style'].'">
			<a href="'.$src.'" rel="'.$rel.'.sib'.$i.'" ' . $video->getID() . ' title="' . $video->getTitle() . '" videowidth="'.$this->parametri['width'].'" videoheight="'.$this->parametri['height'].'">
				<img src="'.$img.'" id="vb_thumb_'.$i.'_'.$n.'" alt="' . $video->getTitle() . '" />
				<span class="vb_video_title" style="max-width:'.$this->parametri['t_width'].'px;" >' . $video->getTitle() . '</span>
			</a>
		</span>';
		return $thumb;
	}
	
	protected function _getAdapters(){
		if(isset($this->adapters)) return $this->adapters;
		$adapters = array_map('trim', $this->params->get('adapters', array()));
		$classes = get_declared_classes();
		foreach($adapters as $a){
			@include_once('adapters/' . $a);
		}
		$diff = array_diff(get_declared_classes(), $classes);
		unset($diff[ array_search('Video', $diff) ]);
		$this->adapters = $diff;
		return $this->adapters;
	}
	
	protected function _chkB($img, $x, $y){
		$rgb = imagecolorat($img, $x, $y);
		$r = ($rgb >> 16) & 0xFF;
		$g = ($rgb >> 8) & 0xFF;
		$b = $rgb & 0xFF;
		return (($r > 31)||($g > 31)||($b > 31));
	}
	
	protected function htmldec($string){
		return str_replace(array('&lt;', '&gt;', '&quot;'), array('<', '>', '"'), $string);
	}
	
	protected function htmlenc($string){
		return str_replace(array('<', '>', '"'), array('&lt;', '&gt;', '&quot;'), $string);
	}
}