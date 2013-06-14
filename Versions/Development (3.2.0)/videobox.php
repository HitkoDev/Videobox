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
				$document->addCustomTag('<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script><script type="text/javascript">jQuery.noConflict();</script>');
			}
			$document->addCustomTag('<script src="http://api.html5media.info/1.1.5/html5media.min.js" type="text/javascript"></script><script type="text/javascript" src="'.JURI::root().'/plugins/system/videobox/videobox.js"></script>');
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
			$buffer = str_replace(array("&#123;", "&#125;"), array("{", "}"), $buffer);
			preg_match_all('/<(\s*)textarea(.*)\/(\s*)textarea(\s*)>/s', $buffer, $areas);
			foreach($areas[0] as $area){
				$area1 = str_replace(array("{", "}"), array("&#123;", "&#125;"), $area);
				$buffer = str_replace($area, $area1, $buffer);
			}
		
			preg_match_all('/{raw}([\s\S]*){\/raw}/isU', $buffer, $matches);			
			foreach($matches[1] as $match) {
				$raw_text = str_replace(array("{", "}"), array("&#123;", "&#125;"), $match);
				$buffer = preg_replace('/{raw}([\s\S]*){\/raw}/isU', $raw_text, $buffer, 1);
			}
			
			$hits = array();
				
			$regex = '/{videobox}(.*){\/videobox}/isU';
			
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
				
				$parametri = explode('||', $match);
				$videos = explode('|,', $parametri[0]);
					
				$count = count($videos);
				
				$parametri_a = array();
				$separator = false;
				if(isset($parametri[1])){
					if(preg_match('/"([^"]+)"/', $parametri[1], $separator)===1){
						$parametri[1] = str_replace($separator[0], '', $parametri[1]);
						$separator = $separator[1];
					} else {
						$separator = false;
					}
					$parametri_a = explode(',', $parametri[1]);
				}
				
				$parametri = array();
				
				if($separator!==false) $parametri['separator'] = $separator; 
				
				foreach($parametri_a as $parameter){
					$parameter = explode('=',$parameter);
					if((isset($parameter[0]))&&(isset($parameter[1]))){
						if((trim($parameter[0])!='')&(trim($parameter[1])!='')) $parametri[trim($parameter[0])] = trim($parameter[1]);
					}
				}
				
				$parametri['full_url'] = $this->params->get('full_url');
				
				if($count>1){
					if(!isset($parametri['links'])) $parametri['links'] = $this->params->get('links_g');
					if($parametri['links']==1){
						if(!isset($parametri['lightbox'])) $parametri['lightbox'] = $this->params->get('no_lb_lg');
						if(!isset($parametri['class'])) $parametri['class'] = $this->params->get('class_lg');
						if(!isset($parametri['style'])) $parametri['style'] = $this->params->get('style_lg');
						if(!isset($parametri['separator'])) $parametri['separator'] = $this->params->get('separator');
						if(($parametri['lightbox']==0)&&($this->params->get('cs_nlb_lg')==1)){
							if(!isset($parametri['width'])) $parametri['width'] = $this->params->get('width_nlb_lg');
							if(!isset($parametri['height'])) $parametri['height'] = $this->params->get('height_nlb_lg');
						} else {
							if(!isset($parametri['width'])) $parametri['width'] = $this->params->get('width_lg');
							if(!isset($parametri['height'])) $parametri['height'] = $this->params->get('height_lg');
						}
					} else {
						if(!isset($parametri['lightbox'])) $parametri['lightbox'] = $this->params->get('no_lb_g');
						if(!isset($parametri['class'])) $parametri['class'] = $this->params->get('class_g');
						if(!isset($parametri['style'])) $parametri['style'] = $this->params->get('style_g');
						if(($parametri['lightbox']==0)&&($this->params->get('cs_nlb_g')==1)){
							if(!isset($parametri['t_width'])) $parametri['t_width'] = $this->params->get('width_nlb_g');
							if(!isset($parametri['t_height'])) $parametri['t_height'] = $this->params->get('height_nlb_g');	
							if(!isset($parametri['button'])) $parametri['button'] = $this->params->get('play_nlb_g');	
							if(!isset($parametri['break'])) $parametri['break'] = $this->params->get('break_nlb');
							if(!isset($parametri['pages'])) $parametri['pages'] = $this->params->get('pages_nlb');
						} else {
							if(!isset($parametri['t_width'])) $parametri['t_width'] = $this->params->get('width_gt');
							if(!isset($parametri['t_height'])) $parametri['t_height'] = $this->params->get('height_gt');
							if(!isset($parametri['button'])) $parametri['button'] = $this->params->get('play_g');	
							if(!isset($parametri['break'])) $parametri['break'] = $this->params->get('break');
							if(!isset($parametri['pages'])) $parametri['pages'] = $this->params->get('pages');
						}
					}
				} else {
					if(!isset($parametri['box'])) $parametri['box'] = $this->params->get('box');
					if($parametri['box']==1){
						if(!isset($parametri['lightbox'])) $parametri['lightbox'] = $this->params->get('no_lb_b');
						if(!isset($parametri['class'])) $parametri['class'] = $this->params->get('class_l');
						if(!isset($parametri['style'])) $parametri['style'] = $this->params->get('style_l');
						if(!isset($parametri['t_width'])) $parametri['t_width'] = $this->params->get('width_bt');
						if(!isset($parametri['t_height'])) $parametri['t_height'] = $this->params->get('height_bt');
						if(($parametri['lightbox']==0)&&($this->params->get('cs_nlb_b')==1)){
							if(!isset($parametri['width'])) $parametri['width'] = $this->params->get('width_nlb_b');
							if(!isset($parametri['height'])) $parametri['height'] = $this->params->get('height_nlb_b');	
							if(!isset($parametri['button'])) $parametri['button'] = $this->params->get('play_nlb_b');
						} else {
							if(!isset($parametri['width'])) $parametri['width'] = $this->params->get('width_b');
							if(!isset($parametri['height'])) $parametri['height'] = $this->params->get('height_b');
							if(!isset($parametri['button'])) $parametri['button'] = $this->params->get('play_b');						
						}
					} else {
						if(!isset($parametri['links'])) $parametri['links'] = $this->params->get('links');
						if($parametri['links']==1){
							if(!isset($parametri['class'])) $parametri['class'] = $this->params->get('class_l');
							if(!isset($parametri['style'])) $parametri['style'] = $this->params->get('style_l');
							if(!isset($parametri['lightbox'])) $parametri['lightbox'] = $this->params->get('no_lb_l');
							if(($parametri['lightbox']==0)&&($this->params->get('cs_nlb_l')==1)){
								if(!isset($parametri['width'])) $parametri['width'] = $this->params->get('width_nlb_l');
								if(!isset($parametri['height'])) $parametri['height'] = $this->params->get('height_nlb_l');
							} else {
								if(!isset($parametri['width'])) $parametri['width'] = $this->params->get('width_l');
								if(!isset($parametri['height'])) $parametri['height'] = $this->params->get('height_l');
							}
						} else {
							if(!isset($parametri['width'])) $parametri['width'] = $this->params->get('width');
							if(!isset($parametri['height'])) $parametri['height'] = $this->params->get('height');
							if(!isset($parametri['class'])) $parametri['class'] = $this->params->get('class');
							if(!isset($parametri['style'])) $parametri['style'] = $this->params->get('style');
							if(!isset($parametri['play'])) $parametri['play'] = $this->params->get('autoplay');
						}
					}
				}
				
				if(!isset($parametri['separator'])) $parametri['separator'] = ', ';
				if(!isset($parametri['break'])) $parametri['break'] = 0;
				if(!isset($parametri['pages'])) $parametri['pages'] = 0;
				if(!isset($parametri['box'])) $parametri['box'] = 0;
				if(!isset($parametri['button'])) $parametri['button'] = 0;	
				if(!isset($parametri['links'])) $parametri['links'] = 0;
				if(!isset($parametri['lightbox'])) $parametri['lightbox'] = 1;
				if(!isset($parametri['width'])) $parametri['width'] = 640;
				if(!isset($parametri['height'])) $parametri['height'] = 363;
				if(!isset($parametri['class'])) $parametri['class'] = '';
				if(!isset($parametri['style'])) $parametri['style'] = '';
				if(!isset($parametri['play'])) $parametri['play'] = 0;
								
				if($parametri['pages']==0) $parametri['pages'] = 99999999;
				if($parametri['break']==0) $parametri['break'] = 99999999;
				if($separator!==false) $parametri['separator'] = $separator;
				
				$start = 0;
				$pagination = '';
				if(($count>$parametri['pages'])&&($parametri['pages']!=0)&&($parametri['links']==0)){
					
					$paginations = array();
					
					$start = 0;
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
					$pages = (int)($count/$parametri['pages']);
					if($count%$parametri['pages']>0) $pages++;
					$page = (int)($start/$parametri['pages']);
					if($start%$parametri['pages']>0) $page++;
					$paginations['counter']['count'] = $page+1;
					$paginations['counter']['of'] = $pages;
					if($page==0){
						$paginations['start']['link'] = '';
						$paginations['prev']['link'] = '';
					} else {
						$url3 = $path.',0'.$after;
						if($url3{0}==',') $url3 = substr($url3, 1);
						$url_params = array_merge($url1->getQuery(true), array('vblimits' => $url3));
						$url_params = $url1->buildQuery($url_params);
						$url_link = JURI::getInstance();
						$url_link->setQuery($url_params);
						$paginations['start']['link'] = $url_link->toString();
						$url3 = $path.','.($page-1)*$parametri['pages'].$after;
						if($url3{0}==',') $url3 = substr($url3, 1);
						$url_params = array_merge($url1->getQuery(true), array('vblimits' => $url3));
						$url_params = $url1->buildQuery($url_params);
						$url_link = JURI::getInstance();
						$url_link->setQuery($url_params);
						$paginations['prev']['link'] = $url_link->toString();
					}
					for($j = 0; $j<$pages; $j++){
						if($j==$page){
							$paginations['links'][($j+1)] = '';
						}else{
							$url3 = $path.','.$j*$parametri['pages'].$after;
							if($url3{0}==',') $url3 = substr($url3, 1);
							$url_params = array_merge($url1->getQuery(true), array('vblimits' => $url3));
							$url_params = $url1->buildQuery($url_params);
							$url_link = JURI::getInstance();
							$url_link->setQuery($url_params);
							$paginations['links'][($j+1)] = $url_link->toString();
						}
					}
					if($page==($pages-1)){
						$paginations['next']['link'] = '';
						$paginations['end']['link'] = '';
					} else {
						$url3 = $path.','.($page+1)*$parametri['pages'].$after;
						if($url3{0}==',') $url3 = substr($url3, 1);
						$url_params = array_merge($url1->getQuery(true), array('vblimits' => $url3));
						$url_params = $url1->buildQuery($url_params);
						$url_link = JURI::getInstance();
						$url_link->setQuery($url_params);
						$paginations['next']['link'] = $url_link->toString();
						$url3 = $path.','.($pages-1)*$parametri['pages'].$after;
						if($url3{0}==',') $url3 = substr($url3, 1);
						$url_params = array_merge($url1->getQuery(true), array('vblimits' => $url3));
						$url_params = $url1->buildQuery($url_params);
						$url_link = JURI::getInstance();
						$url_link->setQuery($url_params);
						$paginations['end']['link'] = $url_link->toString();
					}
					
					if($version{0}=='3'){
					
						$pagination = '<div class="pagination"><p class="counter">'.JText::sprintf('JLIB_HTML_PAGE_CURRENT_OF_TOTAL', $paginations['counter']['count'], $paginations['counter']['of']).'</p><ul>';
						if($paginations['start']['link']==''){
							$pagination .= '<li class="pagination-start"><span class="pagenav">'.JText::_('JLIB_HTML_START').'</span></li>';
						} else {
							$pagination .= '<li class="pagination-start"><a title="'.JText::_('JLIB_HTML_START').'" href="'.$paginations['start']['link'].'" class="pagenav">'.JText::_('JLIB_HTML_START').'</a></li>';
						}						
						if($paginations['prev']['link']==''){
							$pagination .= '<li class="pagination-prev"><span class="pagenav">'.JText::_('JPREV').'</span></li>';
						} else {
							$pagination .= '<li class="pagination-prev"><a title="'.JText::_('JPREV').'" href="'.$paginations['prev']['link'].'" class="pagenav">'.JText::_('JPREV').'</a></li>';
						}						
						foreach($paginations['links'] as $j => $link){
							if($link==''){
								$pagination .= '<li><span class="pagenav">'.$j.'</span></li>';
							} else {
								$pagination .= '<li><a title="'.$j.'" href="'.$link.'" class="pagenav">'.$j.'</a></li>';
							}
						}						
						if($paginations['next']['link']==''){
							$pagination .= '<li class="pagination-next"><span class="pagenav">'.JText::_('JNEXT').'</span></li>';
						} else {
							$pagination .= '<li class="pagination-next"><a title="'.JText::_('JNEXT').'" href="'.$paginations['next']['link'].'" class="pagenav">'.JText::_('JNEXT').'</a></li>';
						}						
						if($paginations['end']['link']==''){
							$pagination .= '<li class="pagination-end"><span class="pagenav">'.JText::_('JLIB_HTML_END').'</span></li>';
						} else {
							$pagination .= '<li class="pagination-end"><a title="'.JText::_('JLIB_HTML_END').'" href="'.$paginations['end']['link'].'" class="pagenav">'.JText::_('JLIB_HTML_END').'</a></li>';
						}						
						$pagination .= '</ul></div>';
						
					} else {
						
						$pagination = '<div class="pagination"><p class="counter">'.JText::sprintf('JLIB_HTML_PAGE_CURRENT_OF_TOTAL', $paginations['counter']['count'], $paginations['counter']['of']).'</p><div class="pagination">';
						if($paginations['start']['link']==''){
							$pagination .= '<span>'.JText::_('JLIB_HTML_START').'</span>';
						} else {
							$pagination .= '<a href="'.$paginations['start']['link'].'" title="'.JText::_('JLIB_HTML_START').'">'.JText::_('JLIB_HTML_START').'</a>';
						}						
						if($paginations['prev']['link']==''){
							$pagination .= '<span>'.JText::_('JPREV').'</span>';
						} else {
							$pagination .= '<a href="'.$paginations['prev']['link'].'" title="'.JText::_('JPREV').'">'.JText::_('JPREV').'</a>';
						}						
						foreach($paginations['links'] as $j => $link){
							if($link==''){
								$pagination .= '<strong><span>'.$j.'</span></strong>';
							} else {
								$pagination .= '<strong><a href="'.$link.'" title="'.$j.'">'.$j.'</a></strong>';
							}
						}						
						if($paginations['next']['link']==''){
							$pagination .= '<span>'.JText::_('JNEXT').'</span>';
						} else {
							$pagination .= '<a href="'.$paginations['next']['link'].'" title="'.JText::_('JNEXT').'">'.JText::_('JNEXT').'</a>';
						}						
						if($paginations['end']['link']==''){
							$pagination .= '<span>'.JText::_('JLIB_HTML_END').'</span>';
						} else {
							$pagination .= '<a href="'.$paginations['end']['link'].'" title="'.JText::_('JLIB_HTML_END').'">'.JText::_('JLIB_HTML_END').'</a>';
						}						
						$pagination .= '</div></div>';
						
					}
				}
				
				if($count){	  
					$thumbnails = '';
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
						$video[1] = $this->htmldec($video[1]);
						$video[1] = $this->htmlenc($video[1]);
						if($parametri['links']==1){
							if(($count==1)){
								$thumbnails .= $this->_videoLink($video, $parametri, $co, $parametri['width'], $parametri['height'], $n, '');
								$n++;
							} else {
								if($n!=$count){
									$thumbnails .= $this->_videoLink($video, $parametri, $co, $parametri['width'], $parametri['height'], $n, $parametri['separator']);
								} else {
									$thumbnails .= $this->_videoLink($video, $parametri, $co, $parametri['width'], $parametri['height'], $n, '');
								}
								$n++;
							}
						} else {
							if (($count == 1)&($parametri['box']!=1)) {
								$thumbnails .= $this->_videoCode($video, $parametri, $co, $parametri['width'], $parametri['height'], $n);
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
					}
					
					$thumbnails = str_replace('&', '&amp;', $thumbnails);
					if(isset($pagination)) $pagination = str_replace('&', '&amp;', $pagination);
					
					if(($parametri['links']==1)||($count==1)){
						$buffer = preg_replace($regex, $thumbnails, $buffer, 1);
					} else {
						$buffer = preg_replace($regex, '<div style="display: table; '.$parametri['style'].'" class="'.$parametri['class'].'"><ul class="video">' . $thumbnails . '</ul></div>'.$pagination, $buffer, 1);
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
	
	protected function _videoLink($video, $params, $i, $v_width, $v_height, $n, $separator){
		if($video[5]){
			$src = $video[9].'/plugins/system/videobox/player.php?video='.$video[0].'&autoplay=1'.$video[4];
		} else {
			if(!is_numeric($video[0])) {
				if(strlen($video[0])==11) {
					$src = 'http://www.youtube.com/embed/' . $video[0] . '?wmode=transparent&rel=0&fs=1&autoplay=1'.$video[4];
				} else {
					$src = 'http://player.youku.com/embed/' . $video[0] . '?autoplay=1';
				}
			} else {
				$src = 'http://player.vimeo.com/video/'.$video[0].'?autoplay=1'.$video[4];
			}
		}
		if($params['lightbox']=='0'){
			$html = '<span class="'.$params['class'].'" style="'.$params['style'].'"><a class="video_close" onclick="displayvideolink(\''.$i.'_'.$n.'\',\''.$src.'\',\''.$v_width.'\',\''.$v_height.'\')" id="close_'.$i.'_'.$n.'" style="cursor: pointer;"></a><a class="video_link_a" onclick="displayvideolink(\''.$i.'_'.$n.'\',\''.$src.'\',\''.$v_width.'\',\''.$v_height.'\')" style="cursor: pointer;" ><span class="video_link" style="display: none;"><span><iframe allowfullscreen oallowfullscreen msallowfullscreen webkitallowfullscreen mozallowfullscreen id="video_'.$i.'_'.$n.'" style="display: none;"></iframe></span></span><span id="title_'.$i.'_'.$n.'" style="" >' . $video[1] . '</span></a><span id="separator_'.$i.'_'.$n.'">'.$separator.'</span></span>';
		} else {
			$html = '<a class="'.$params['class'].' video_link_a" style="'.$params['style'].'" href="'.$src.'" rel="videobox.sig'.$i.'" title="'.$video[1].'" videowidth="'.$v_width.'" videoheight="'.$v_height.'">'.$video[1].'</a>'.$separator;
		}
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
						<img src="'.$img.'" id="thumb_'.$i.'_'.$n.'" alt="'.$video[1].'" />
					</span>
					<span class="video_title" id="title_'.$i.'_'.$n.'" style="width: '.$t_width.'px;" >' . $video[1] . '</span>
				</a>
			</li>';
		} else {
			$thumb  = '<li class="video_cont_0">
				<a href="'.$src.'" rel="videobox.sig'.$i.'" title="' . $video[1] . '" videowidth="'.$v_width.'" videoheight="'.$v_height.'">
					<span class="video_thumb">
						<img src="'.$img.'" id="thumb_'.$i.'_'.$n.'" alt="'.$video[1].'" />
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
						<img src="'.$img.'" id="thumb_'.$i.'_'.$n.'" alt="'.$video[1].'" />
					</span>
					<span class="video_title" id="title_'.$i.'_'.$n.'" style="width: '.$t_width.'px;">'.$video[1].'</span>
				</a>
			</span>';
		} else {
			$thumb  = '<span class="video_box_0 '.$params['class'].'" style="'.$params['style'].'">
				<a href="'.$src.'" rel="videobox.sib'.$i.'" title="'.$video[1].'" videowidth="'.$v_width.'" videoheight="'.$v_height.'">
					<span class="video_thumb">
						<img src="'.$img.'" id="thumb_'.$i.'_'.$n.'" alt="'.$video[1].'" />
					</span>
					<span class="video_title" style="width: '.$t_width.'px;">'.$video[1].'</span>
				</a>
			</span>';
		}
		return $thumb;
	}
	
	protected function htmldec($string){
		return str_replace(array('&lt;', '&gt;', '&quot;'), array('<', '>', '"'), $string);
	}
	
	protected function htmlenc($string){
		return str_replace(array('<', '>', '"'), array('&lt;', '&gt;', '&quot;'), $string);
	}
}