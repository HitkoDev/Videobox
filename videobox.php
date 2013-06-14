<?php
/*------------------------------------------------------------------------
# plg_videobox - Videobox
# ------------------------------------------------------------------------
# author    HiTKO
# copyright Copyright (C) 2012 hitko.si. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://hitko.si
-------------------------------------------------------------------------*/
// no direct access
defined( '_JEXEC' ) or die( 'Restricted Access' );

jimport( 'joomla.plugin.plugin' );



class plgSystemVideobox extends JPlugin
{

	public function onAfterDispatch(){
		$app = JFactory::getApplication();
		$document = JFactory::getDocument();
		if(($app->isSite())&&(method_exists($document, 'addCustomTag'))){
			$document->addCustomTag('<link rel="stylesheet" href="'.JURI::root().'/plugins/system/videobox/css/videobox.css" type="text/css" media="screen" />');
			if($this->params->get('loadjq')=='1'){
				$document->addCustomTag('<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script><script type="text/javascript">jQuery.noConflict();</script>');
			}
			$document->addCustomTag('<script src="http://api.html5media.info/1.1.5/html5media.min.js"></script><script type="text/javascript" src="'.JURI::root().'/plugins/system/videobox/videobox.js"></script><script type="text/javascript">
				var displayvideo;
				var vb_site_base = "'.JPATH_BASE.'/";
				var vb_site_root = "'.JURI::root().'";
				jQuery(document).ready(function($) {
					displayvideo = function (vid, src, vwidth, vheight, twidth, theight){
						var frame = document.getElementById(\'video_\'+vid);
						var image = document.getElementById(\'thumb_\'+vid);
						var close = document.getElementById(\'close_\'+vid);
						var title = document.getElementById(\'title_\'+vid);
						if((frame.getAttribute(\'style\').indexOf(\'block\')==-1)){
							image.style.display = \'none\';
							frame.style.display = \'block\';
							frame.parentNode.style.display = \'block\';
							close.style.display = \'block\';
							frame.src = src;
							$(frame).animate({height: vheight, width: vwidth}, { duration: 400, easing: \'swing\', queue: false });
							$(title).animate({width: vwidth}, { duration: 400, easing: \'swing\', queue: false });
						} else {
							close.style.display = \'none\';
							$(frame).animate({height: theight, width: twidth}, { duration: 0, easing: \'swing\', queue: false });
							title.style.width = twidth+\'px\';
							frame.src = \'\';
							if (document.cancelFullScreen) {
								document.cancelFullScreen();
							} else if (document.mozCancelFullScreen) {
								document.mozCancelFullScreen();
							} else if (document.webkitCancelFullScreen) {
								document.webkitCancelFullScreen();
							} else if (document.oCancelFullScreen) {
								document.oCancelFullScreen();
							} else if (document.msCancelFullScreen) {
								document.msCancelFullScreen();
							}
							frame.style.display = \'none\';
							frame.parentNode.style.display = \'none\';
							image.style.display = \'block\';
						}
					}
				});
			</script>');
		}
	}

	public function onAfterRender(){
		$app = JFactory::getApplication();
		$document = JFactory::getDocument();
		if(($app->isSite())&&(method_exists($document, 'addCustomTag'))){
			jimport('joomla.version');
			$version = new JVersion();
			$version = $version->RELEASE;
			$custom_tag = preg_replace("/[^a-zA-Z0-9]/", "", $this->params->get('tag'));
			$pageload = JResponse::getBody();
			preg_match('/<(\s*)body(.*)\/(\s*)body(\s*)>/s', $pageload, $buffer);
			$buffer = $buffer[2];
			$old_buffer = $buffer;
			preg_match_all('/<(\s*)textarea(.*)\/(\s*)textarea(\s*)>/s', $buffer, $areas);
			foreach($areas[0] as $area){
				$area1 = str_replace(array("{", "}"), array("&#123;", "&#125;"), $area);
				$buffer = str_replace($area, $area1, $buffer);
			}
			
			$hits = array();
			
			// setup what to look for in the content		
			$regex = '/{videobox}(.*){\/videobox}/isU';
			
			// find all instances of the video players
			preg_match_all( $regex, $buffer, $matches );
			
			foreach($matches[1] as $match){
				$hit[0] = $match;
				$hit[1] = $regex;	
				$hits[] = $hit;
			}
			
			$matches = array();
			
			if(($custom_tag!='videobox')&($custom_tag!='')){
				$regex = '/{'.$custom_tag.'}(.*){\/'.$custom_tag.'}/iU';
				
				preg_match_all( $regex, $buffer, $matches );
				
				foreach($matches[1] as $match){
					$hit[0] = $match;
					$hit[1] = $regex;
					$hits[] = $hit;
				}
				
				$regex = '/{'.$custom_tag.'}(.*){\/videobox}/iU';
				
				preg_match_all( $regex, $buffer, $matches );
				
				foreach($matches[1] as $match){
					$hit[0] = $match;
					$hit[1] = $regex;
					$hits[] = $hit;
				}
				
				$regex = '/{videobox}(.*){\/'.$custom_tag.'}/iU';
				
				preg_match_all( $regex, $buffer, $matches );
				
				foreach($matches[1] as $match){
					$hit[0] = $match;
					$hit[1] = $regex;
					$hits[] = $hit;
				}
			}
			$pageslink = '/index.php';
			$cg = 0;
			foreach($_GET as $key => $get){
				if($cg == 0){
					$pageslink .= '?'.$key.'='.$get;
				} else {
					$pageslink .= '&'.$key.'='.$get;
				}
				$cg++;
			}
			//var_dump($pageslink);
			$url1 = JURI::getInstance();
			$url2 = array(0);
			if(isset($_GET['vblimits'])) $url2 = explode(',', $_GET['vblimits']);
			$url_params = $url1->getQuery(true);
			$org_params = $url1->buildQuery($url_params);
			$videos_root = JURI::root();
			
			$co = 0;
			foreach($hits as $match){
				
				$co++;
				$regex = $match[1];
				$match = strip_tags($match[0]);
				
				// breakdown the string of videos being passed		
				$parametri = explode('||', $match);
				$videos = explode('|,', $parametri[0]);
				
				// count the number of vidoes		
				$count = count($videos);
				
				// get parameters	
				$parametri_a = array();
				if(isset($parametri[1])) $parametri_a = explode(',', $parametri[1]);
				$parametri = array();
				$parametri['pages'] = $this->params->get('pages');
				$parametri['box'] = 0;	
				$parametri['break'] = $this->params->get('break');
				$parametri['full_url'] = $this->params->get('full_url');
				$parametri['play'] = $this->params->get('autoplay');
				$parametri['t_width'] = 206;
				$parametri['t_height'] = 155;
				$parametri['width'] = 640;
				$parametri['height'] = 363;
				$parametri['style'] = '';
				$parametri['class'] = '';
				$parametri['box'] = '0';
				
				if($count>1){
					$parametri['t_width'] = $this->params->get('width_gt');
					$parametri['t_height'] = $this->params->get('height_gt');
					$parametri['width'] = $this->params->get('width_g');
					$parametri['height'] = $this->params->get('height_g');
					$parametri['style'] = $this->params->get('style_g');
					$parametri['class'] = $this->params->get('class_g');
				} else {
					$parametri['width'] = $this->params->get('width');
					$parametri['height'] = $this->params->get('height');
					$parametri['style'] = $this->params->get('style');
					$parametri['class'] = $this->params->get('class');		
					$parametri['box'] = $this->params->get('box');		
				}
				
				foreach($parametri_a as $parameter){
					$parameter = explode('=',$parameter);
					if((trim($parameter[0])!='')&(trim($parameter[1])!='')) $parametri[trim($parameter[0])] = trim($parameter[1]);
				}
				
				if($count>1){				
					if(!isset($parametri['lightbox'])) $parametri['lightbox'] = $this->params->get('no_lb');
					if($parametri['lightbox']==0){
						$parametri['button'] = $this->params->get('play_nlb_g');
					} else {
						$parametri['button'] = $this->params->get('play_g');
					}
				}
				
				if($parametri['box']==1){
					$parametri['t_width'] = $this->params->get('width_bt');
					$parametri['t_height'] = $this->params->get('height_bt');
					$parametri['width'] = $this->params->get('width_b');
					$parametri['height'] = $this->params->get('height_b');
					$parametri['style'] = $this->params->get('style_b');
					$parametri['class'] = $this->params->get('class_b');
					if(!isset($parametri['lightbox'])) $parametri['lightbox'] = $this->params->get('no_lb_b');
					if($parametri['lightbox']==0){
						$parametri['button'] = $this->params->get('play_nlb_b');
					} else {
						$parametri['button'] = $this->params->get('play_b');
					}
				}
				
				foreach($parametri_a as $parameter){
					$parameter = explode('=',$parameter);
					if((trim($parameter[0])!='')&(trim($parameter[1])!='')) $parametri[trim($parameter[0])] = trim($parameter[1]);
				}
				
				if($parametri['pages']==0) $parametri['pages'] = 99999999;
				if($parametri['break']==0) $parametri['break'] = 99999999;
				
				//create pagination (if necessary)		
				$start = 0;
				$pagination = '';
				if(($count>$parametri['pages'])&($parametri['pages']!=0)&($version{0}=='3')){
					$start = (int)$url2[$co-1];
					$path = '';
					for($h = 0; $h<$co-1; $h++){
						if($url2[$h]=='') $url2[$h]='0';
						$path .= ','.$url2[$h];
					}
					$after = '';
					for($h = $co; $h<count($url2); $h++){
						if($url2[$h]=='') $url2[$h]='0';
						$after .= ','.$url2[$h];
					}				
					$pages = (int)($count/$parametri['pages']);
					if($count%$parametri['pages']>0) $pages++;
					$page = (int)($start/$parametri['pages']);
					if($start%$parametri['pages']>0) $page++;
					$pagination = '<div class="pagination"><p class="counter pull-right">Page '.($page+1).' of '.$pages.'</p><ul class="pagination-list">';
					if($page==0){
						$pagination .= '<li class="disabled"><a><i class="icon-first"></i></a></li><li class="disabled"><a><i class="icon-previous"></i></a></li>';
					} else {
						$url3 = $path.',0'.$after;
						if($url3{0}==',') $url3 = substr($url3, 1);
						$url_params = array_merge($url1->getQuery(true), array('vblimits' => $url3));
						$url_params = $url1->buildQuery($url_params);
						$url_link = JURI::getInstance();
						$url_link->setQuery($url_params);
						$pagination .= '<li><a title="Start" href="'.$url_link->toString().'" class="pagenav">Start</a></li>';
						$url3 = $path.','.($page-1)*$parametri['pages'].$after;
						if($url3{0}==',') $url3 = substr($url3, 1);
						$url_params = array_merge($url1->getQuery(true), array('vblimits' => $url3));
						$url_params = $url1->buildQuery($url_params);
						$url_link = JURI::getInstance();
						$url_link->setQuery($url_params);
						$pagination .= '<li><a title="Prev" href="'.$url_link->toString().'" class="pagenav">Prev</a></li>';
					}
					for($j = 0; $j<$pages; $j++){
						if($j==$page){
							$pagination .= '<li class="active"><a>'.($j+1).'</a></li>';
						}else{
							$url3 = $path.','.$j*$parametri['pages'].$after;
							if($url3{0}==',') $url3 = substr($url3, 1);
							$url_params = array_merge($url1->getQuery(true), array('vblimits' => $url3));
							$url_params = $url1->buildQuery($url_params);
							$url_link = JURI::getInstance();
							$url_link->setQuery($url_params);
							$pagination .= '<li><a title="'.($j+1).'" href="'.$url_link->toString().'" class="pagenav">'.($j+1).'</a></li>';
						}
					}
					if($page==($pages-1)){
						$pagination .= '<li class="disabled"><a><i class="icon-next"></i></a></li><li class="disabled"><a><i class="icon-last"></i></a></li>';
					} else {
						$url3 = $path.','.($page+1)*$parametri['pages'].$after;
						if($url3{0}==',') $url3 = substr($url3, 1);
						$url_params = array_merge($url1->getQuery(true), array('vblimits' => $url3));
						$url_params = $url1->buildQuery($url_params);
						$url_link = JURI::getInstance();
						$url_link->setQuery($url_params);
						$pagination .= '<li><a title="Next" href="'.$url_link->toString().'" class="pagenav">Next</a></li>';
						$url3 = $path.','.($pages-1)*$parametri['pages'].$after;
						if($url3{0}==',') $url3 = substr($url3, 1);
						$url_params = array_merge($url1->getQuery(true), array('vblimits' => $url3));
						$url_params = $url1->buildQuery($url_params);
						$url_link = JURI::getInstance();
						$url_link->setQuery($url_params);
						$pagination .= '<li><a title="End" href="'.$url_link->toString().'" class="pagenav">End</a></li>';
					}	
					$pagination .= '</ul></div>';
				}
				if(($count>$parametri['pages'])&($parametri['pages']!=0)&($version{0}!='3')){
					$start = (int)$url2[$co-1];
					$path = '';
					for($h = 0; $h<$co-1; $h++){
						if($url2[$h]=='') $url2[$h]='0';
						$path .= ','.$url2[$h];
					}
					$after = '';
					for($h = $co; $h<count($url2); $h++){
						if($url2[$h]=='') $url2[$h]='0';
						$after .= ','.$url2[$h];
					}				
					$pages = (int)($count/$parametri['pages']);
					if($count%$parametri['pages']>0) $pages++;
					$page = (int)($start/$parametri['pages']);
					if($start%$parametri['pages']>0) $page++;
					$pagination = '<div class="pagination"><p class="counter">Page '.($page+1).' of '.$pages.'</p><div class="pagination">';
					if($page==0){
						$pagination .= '<span>Start</span><span>Prev</span>';
					} else {
						$url3 = $path.',0'.$after;
						if($url3{0}==',') $url3 = substr($url3, 1);
						$url_params = array_merge($url1->getQuery(true), array('vblimits' => $url3));
						$url_params = $url1->buildQuery($url_params);
						$url_link = JURI::getInstance();
						$url_link->setQuery($url_params);
						$pagination .= '<a href="'.$url_link->toString().'" title="Start">Start</a>';
						$url3 = $path.','.($page-1)*$parametri['pages'].$after;
						if($url3{0}==',') $url3 = substr($url3, 1);
						$url_params = array_merge($url1->getQuery(true), array('vblimits' => $url3));
						$url_params = $url1->buildQuery($url_params);
						$url_link = JURI::getInstance();
						$url_link->setQuery($url_params);
						$pagination .= '<a href="'.$url_link->toString().'" title="Prev">Prev</a>';
					}
					for($j = 0; $j<$pages; $j++){
						if($j==$page){
							$pagination .= '<strong><span>'.($j+1).'</span></strong>';
						}else{
							$url3 = $path.','.$j*$parametri['pages'].$after;
							if($url3{0}==',') $url3 = substr($url3, 1);
							$url_params = array_merge($url1->getQuery(true), array('vblimits' => $url3));
							$url_params = $url1->buildQuery($url_params);
							$url_link = JURI::getInstance();
							$url_link->setQuery($url_params);
							$pagination .= '<strong><a href="'.$url_link->toString().'" title="'.($j+1).'">'.($j+1).'</a></strong>';
						}
					}
					if($page==($pages-1)){
						$pagination .= '<span>Next</span><span>End</span>';
					} else {
						$url3 = $path.','.($page+1)*$parametri['pages'].$after;
						if($url3{0}==',') $url3 = substr($url3, 1);
						$url_params = array_merge($url1->getQuery(true), array('vblimits' => $url3));
						$url_params = $url1->buildQuery($url_params);
						$url_link = JURI::getInstance();
						$url_link->setQuery($url_params);
						$pagination .= '<a href="'.$url_link->toString().'" title="Next">Next</a>';
						$url3 = $path.','.($pages-1)*$parametri['pages'].$after;
						if($url3{0}==',') $url3 = substr($url3, 1);
						$url_params = array_merge($url1->getQuery(true), array('vblimits' => $url3));
						$url_params = $url1->buildQuery($url_params);
						$url_link = JURI::getInstance();
						$url_link->setQuery($url_params);
						$pagination .= '<a href="'.$url_link->toString().'" title="End">End</a>';
					}	
					$pagination .= '</div></div>';
				}
				
				// display videos
				if ( $count ) {		  
					$video_content = '';
					$thumbnails    = '';
					$i = 1;
					$n = 1;

					foreach ($videos as $video) {
						$video = explode ('|', $video);
						if(!isset($video[1])) $video[1] = '';
						$video = array_map('trim', $video);
						$offset = explode ('#', $video[0]);
						$video[0] = trim($offset[0]);
						$video[0] = str_replace($videos_root, '', $video[0]);
						$video[4] = '';
						$videoinfo = pathinfo($video[0]);
						if(!isset($videoinfo['extension'])) $videoinfo['extension'] = 'ddd';
						$html5extensions = 'mp4,ogv,webm,m4v,oga,mp3,m4a,webma,wav';
						if(((strlen($video[0])>16)&(!is_numeric($video[0]))&($parametri['full_url']=='1'))&(strpos($html5extensions, $videoinfo['extension'])===false)){
							if(strpos($video[0], 'youtube')!==false){
								preg_match('/v=(.{11}?)/isU', $video[0], $v_urls);
								$video[0] = $v_urls[1];
							} elseif(strpos($video[0], 'youku')!==false) {
								preg_match('/id_(.*?).html/isU', $video[0], $v_urls);
								$video[0] = $v_urls[1];
							} elseif(strpos($video[0], 'youtu.be')!==false){
								preg_match('/youtu.be\/(.{11}?)/isU', $video[0], $v_urls);
								$video[0] = $v_urls[1];
							} else { 
								preg_match('/vimeo.com\/([0-9]*?)/isU', $video[0], $v_urls);
								$video[0] = $v_urls[1];
							}
						} else {
							if((substr($video[0], 0, 3)=='id_')&(strlen($video[0])==16)){
								$video[0] = substr($video[0], 3);
							}
						}
						if((is_numeric(str_replace(':', '', $offset[count($offset)-1])))&(count($offset)!=1)){
							$offset = explode (':', $offset[count($offset)-1]);
							$video[4] = 0;
							foreach($offset as $offset1){
								$video[4] = $video[4]*60+$offset1;
							}
						}
						$video[5] = false;
						$video[6] = false;
						$video[7] = '';
						$video[9] = JURI::root();
						if(strpos($html5extensions, $videoinfo['extension'])!==false){
							$video[5] = true;
							$video[6] = true;							
							if (strpos('mp4,ogv,webm,m4v', $videoinfo['extension'])!==false) $video[6] = false;
							$video[0] = $videoinfo['dirname'].'/'.$videoinfo['filename'].'.'.$videoinfo['extension'];
							$video[4] = '&start='.$video[4];
							if((strpos($video[0], 'http')!==0)&&(strpos($video[0], '/')!==0)) $video[0] = '/'.$video[0];
						} elseif(!is_numeric($video[0])){
							$video[4] = '&start='.$video[4];
						} else {
							$s = $video[4]%60;
							$video[4] = ($video[4]-$s)/60;
							$m = $video[4]%60;
							$h = ($video[4]-$m)/60;
							$video[4] = '&t='.$h.'h'.$m.'m'.$s.'s';
						}
						if (($count == 1)&($parametri['box']!=1)) {
							$video_content .= $this->_videoCode($video, $parametri, $co, $parametri['width'], $parametri['height'], $n);
							$n++;
						}else{
							if($parametri['box']!=1){
								if(($n>$start)&($n<=($start+$parametri['pages']))){
									if($i==($parametri['break']+1)){
										$i = 1;
										$thumbnails .= '</ul><ul class="video">';
									}
									$thumbnails .= ' ' . $this->_videoThumb($video, $parametri, $co, $parametri['t_width'], $parametri['t_height'], $parametri['width'], $parametri['height'], $n) . ' ';
									$i++;
								}
								$n++;
							}else{
								if($n==1) $thumbnails .= ' ' . $this->_videoBox($video, $parametri, $co, $parametri['t_width'], $parametri['t_height'], $parametri['width'], $parametri['height'], $n) . ' ';
								$i++;
								$n++;
							}
						}
					}
					
					if(($count != 1)){
						$buffer = preg_replace( $regex, $video_content . '<div style="display: table; '.$parametri['style'].'" class="'.$parametri['class'].'"><ul class="video">' . $thumbnails . '</ul></div>'.$pagination, $buffer, 1);
					}else{
						$buffer = preg_replace( $regex, $video_content . '' . $thumbnails . '', $buffer, 1);
					}
				}
			}
			$url1->setQuery($org_params);
			$pageload = str_replace($old_buffer, $buffer, $pageload);
			JResponse::setBody($pageload);
			return true;
		}
	}
	
	protected function _videoCode( $video, $params, $i, $v_width, $v_height, $n ) {
		if($video[5]){
			if($params['play']==true){
				$src = $video[9].'/plugins/system/videobox/player.php?video='.$video[0].'&autoplay=1'.$video[4];					
			} else {
				$src = $video[9].'/plugins/system/videobox/player.php?video='.$video[0].'&autoplay=0'.$video[4];	
			}
		} else {
			if(!is_numeric($video[0])) {
				if(strlen($video[0])==11) {
					if($params['play']==true){
						$src = 'http://www.youtube.com/embed/' . $video[0] . '?wmode=transparent&rel=0&fs=1&autoplay=1'.$video[4];					
					} else {
						$src = 'http://www.youtube.com/embed/' . $video[0] . '?wmode=transparent&rel=0&fs=1&autoplay=0'.$video[4];
					}
				} else {
					if($params['play']==true){
						$src = 'http://player.youku.com/embed/' . $video[0] . '?autoplay=1';
					} else {
						$src = 'http://player.youku.com/embed/' . $video[0] . '?autoplay=0';
					}
				}
			} else {
				if($params['play']==true){
					$src = 'http://player.vimeo.com/video/'.$video[0].'?autoplay=1'.$video[4];
				} else {
					$src = 'http://player.vimeo.com/video/'.$video[0].'?autoplay=0'.$video[4];
				}
			}
		}
		$html  = '<div style="width: '.$v_width.'px; '.$params['style'].'" class="videoFrame '.$params['class'].'"><iframe width="'.$v_width.'" height="'.$v_height.'" src="'.$src.'" frameborder="0" allowfullscreen oallowfullscreen msallowfullscreen webkitallowfullscreen mozallowfullscreen style="display: block; background: #000;"></iframe></div>';
		return $html;
	}

	protected function _videoThumb( $video, $params, $i, $t_width, $t_height, $v_width, $v_height, $n ) {
		if($video[5]){	
			$src = $video[9].'/plugins/system/videobox/player.php?video='.$video[0].'&autoplay=1'.$video[4];
		} elseif(!is_numeric($video[0])) {
			if(strlen($video[0])==11){
				$src = 'http://www.youtube.com/embed/' . $video[0] . '?wmode=transparent&rel=0&autoplay=1&fs=1'.$video[4];
			} else {
				$src = 'http://player.youku.com/embed/' . $video[0] . '?autoplay=1';
			}
		} else {
			$src = 'http://player.vimeo.com/video/'.$video[0].'?autoplay=1'.$video[4];
		}
		$play = '&play=0';
		if($params['button']=='1') $play = '&play=1';
		$img = $video[9].'/plugins/system/videobox/showthumb.php?img='.rawurlencode($video[0].$video[7]).'&width='.$t_width.'&height='.$t_height.$play;
		if($params['lightbox']=='0'){
			$thumb  = '<li class="video_cont_0">
				<a class="video_close" onclick="displayvideo(\''.$i.'_'.$n.'\',\''.$src.'\',\''.$t_width.'\',\''.$t_height.'\',\''.$t_width.'\',\''.$t_height.'\')" id="close_'.$i.'_'.$n.'"></a>
				<a onclick="displayvideo(\''.$i.'_'.$n.'\',\''.$src.'\',\''.$t_width.'\',\''.$t_height.'\',\''.$t_width.'\',\''.$t_height.'\')" >
					<span class="video_thumb">
						<span style="display: none;">
							<iframe allowfullscreen oallowfullscreen msallowfullscreen webkitallowfullscreen mozallowfullscreen id="video_'.$i.'_'.$n.'" style="width: '.$t_width.'px; height: '.$t_height.'px; display: none;"></iframe>
						</span>
						<img src="'.$img.'" id="thumb_'.$i.'_'.$n.'">
					</span>
					<span class="video_title" id="title_'.$i.'_'.$n.'" style="width: '.$t_width.'px;" >' . $video[1] . '</span>
				</a>
			</li>';
		} else {
			$thumb  = '<li class="video_cont_0">
				<a href="'.$src.'" rel="videobox.sig'.$i.'" title="' . $video[1] . '" videowidth="'.$v_width.'" videoheight="'.$v_height.'">
					<span class="video_thumb">
						<img src="'.$img.'" id="thumb_'.$i.'_'.$n.'">
					</span>
					<span class="video_title" style="width: '.$t_width.'px;" >' . $video[1] . '</span>
				</a>
			</li>';
		}
		return $thumb;
	}
	
	protected function _videoBox( $video, $params, $i, $t_width, $t_height, $v_width, $v_height, $n ) {
		if($video[5]){	
			$src = $video[9].'/plugins/system/videobox/player.php?video='.$video[0].'&autoplay=1'.$video[4];
		} elseif(!is_numeric($video[0])) {
			if(strlen($video[0])==11){
				$src = 'http://www.youtube.com/embed/' . $video[0] . '?wmode=transparent&rel=0&autoplay=1&fs=1'.$video[4];
			} else {
				$src = 'http://player.youku.com/embed/' . $video[0] . '?autoplay=1';
			}
		} else {
			$src = 'http://player.vimeo.com/video/'.$video[0].'?autoplay=1'.$video[4];
		}
		$play = '&play=0';
		if($params['button']=='1') $play = '&play=1';
		$img = $video[9].'/plugins/system/videobox/showthumb.php?img='.rawurlencode($video[0].$video[7]).'&width='.$t_width.'&height='.$t_height.$play;
		if($params['lightbox']=='0'){
			$thumb  = '<span class="video_box_0 '.$params['class'].'" style="'.$params['style'].'">
				<a class="video_close" onclick="displayvideo(\''.$i.'_'.$n.'\',\''.$src.'\',\''.$v_width.'\',\''.$v_height.'\',\''.$t_width.'\',\''.$t_height.'\')" id="close_'.$i.'_'.$n.'"></a>
				<a onclick="displayvideo(\''.$i.'_'.$n.'\',\''.$src.'\',\''.$v_width.'\',\''.$v_height.'\',\''.$t_width.'\',\''.$t_height.'\')" >
					<span class="video_thumb">
						<span style="display: none;">
							<iframe allowfullscreen oallowfullscreen msallowfullscreen webkitallowfullscreen mozallowfullscreen id="video_'.$i.'_'.$n.'" style="width: '.$t_width.'px; height: '.$t_height.'px; display: none;"></iframe>
						</span>
						<img src="'.$img.'" id="thumb_'.$i.'_'.$n.'">
					</span>
					<span class="video_title" id="title_'.$i.'_'.$n.'" style="width: '.$t_width.'px;">' . $video[1] . '</span>
				</a>
			</span>';
		} else {
			$thumb  = '<span class="video_box_0 '.$params['class'].'" style="'.$params['style'].'">
				<a href="'.$src.'" rel="videobox.sib'.$i.'" title="' . $video[1] . '" videowidth="'.$v_width.'" videoheight="'.$v_height.'">
					<span class="video_thumb">
						<img src="'.$img.'" id="thumb_'.$i.'_'.$n.'">
					</span>
					<span class="video_title" style="width: '.$t_width.'px;">' . $video[1] . '</span>
				</a>
			</span>';
		}
		return $thumb;
	}
}