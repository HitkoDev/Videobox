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



class plgContentVideobox extends JPlugin
{

	public function onContentPrepare( $context, &$article, &$params, $limitstart )
	{
		$app = JFactory::getApplication();
		jimport('joomla.version');
		$version = new JVersion();
		$version = $version->RELEASE;
		
		// setup what to look for in the content		
		$regex = '/{videobox}(.*){\/videobox}/iU';
		
		// find all instances of the video players
		preg_match_all( $regex, $article->text, $matches );
		
		$co = 0;
		foreach($matches[1] as $match){
			
			$co++;
			
			// breakdown the string of videos being passed		
			$parametri = explode('||', $match);
			$videos = explode('|,', $parametri[0]);
			
			// count the number of vidoes		
			$count = count($videos);
			
			// get parameters		
			$parametri = explode(',', $parametri[1]);
			$parametri_a = $parametri;
			$parametri = array();
			$parametri['pages'] = $this->params->get('pages');
			$parametri['box'] = 0;	
			$parametri['break'] = $this->params->get('break');
			$parametri['t_width'] = 206;
			$parametri['t_height'] = 155;
			$parametri['width'] = 640;
			$parametri['height'] = 363;
			$parametri['style'] = '';
			
			if($count>1){
				$parametri['t_width'] = $this->params->get('width_gt');
				$parametri['t_height'] = $this->params->get('height_gt');
				$parametri['width'] = $this->params->get('width_g');
				$parametri['height'] = $this->params->get('height_g');
				$parametri['lightbox'] = $this->params->get('no_lb');
			} else {
				$parametri['width'] = $this->params->get('width');
				$parametri['height'] = $this->params->get('height');		
			}
			
			foreach($parametri_a as $parameter){
				$parameter = explode('=',$parameter);
				$parametri[trim($parameter[0])] = trim($parameter[1]);
			}
			
			if($parametri['box']==1){
				$parametri['t_width'] = $this->params->get('width_bt');
				$parametri['t_height'] = $this->params->get('height_bt');
				$parametri['width'] = $this->params->get('width_b');
				$parametri['height'] = $this->params->get('height_b');
				$parametri['lightbox'] = $this->params->get('no_lb_b');
				foreach($parametri_a as $parameter){
					$parameter = explode('=',$parameter);
				$parametri[trim($parameter[0])] = trim($parameter[1]);
				}			
			}
			
			foreach($parametri_a as $parameter){
				$parameter = explode('=',$parameter);
				$parametri[trim($parameter[0])] = trim($parameter[1]);
			}
			
			if($parametri['pages']==0) $parametri['pages'] = 99999999;
			if($parametri['break']==0) $parametri['break'] = 99999999;
			
			//create pagination (if necessary)		
			$start = 0;
			$pagination = '';
			if(($count>$parametri['pages'])&($parametri['pages']!=0)&($version{0}=='3')){
				$url1 = JURI::getInstance();
				$url1 = $url1->toString();
				$url1 = str_replace('%26', '&', $url1);
				$url2 = explode('&', $url1);
				$start = (int)$url2[$co];
				$path = $url2[0];
				for($h = 1; $h<$co; $h++){
					$path .= '&'.$url2[$h];
				}
				$after = '';
				for($h = $co+1; $h<count($url2); $h++){
					$after .= '&'.$url2[$h];
				}				
				$pages = (int)($count/$parametri['pages']);
				if($count%$parametri['pages']>0) $pages++;
				$page = (int)($start/$parametri['pages']);
				if($start%$parametri['pages']>0) $page++;
				$pagination = '<div class="pagination"><p class="counter pull-right">Page '.($page+1).' of '.$pages.'</p><ul class="pagination-list">';
				if($page==0){
					$pagination .= '<li class="disabled"><a><i class="icon-first"></i></a></li><li class="disabled"><a><i class="icon-previous"></i></a></li>';
				} else {
					$pagination .= '<li><a title="Start" href="'.$path.'&0'.$after.'" class="pagenav">Start</a></li><li><a title="Prev" href="'.$path.'&'.($page-1)*$parametri['pages'].$after.'" class="pagenav">Prev</a></li>';
				}
				for($j = 0; $j<$pages; $j++){
					if($j==$page){
						$pagination .= '<li class="active"><a>'.($j+1).'</a></li>';
					}else{
						$pagination .= '<li><a title="'.($j+1).'" href="'.$path.'&'.$j*$parametri['pages'].$after.'" class="pagenav">'.($j+1).'</a></li>';
					}
				}
				if($page==($pages-1)){
					$pagination .= '<li class="disabled"><a><i class="icon-next"></i></a></li><li class="disabled"><a><i class="icon-last"></i></a></li>';
				} else {
					$pagination .= '<li><a title="Next" href="'.$path.'&'.($page+1)*$parametri['pages'].$after.'" class="pagenav">Next</a></li><li><a title="End" href="'.$path.'&'.($pages-1)*$parametri['pages'].$after.'" class="pagenav">End</a></li>';
				}	
				$pagination .= '</ul></div>';
			}
			if(($count>$parametri['pages'])&($parametri['pages']!=0)&($version{0}!='3')){
				$url1 = JURI::getInstance();
				$url1 = $url1->toString();
				$url1 = str_replace('%26', '&', $url1);
				$url2 = explode('&', $url1);
				$start = (int)$url2[$co];
				$path = $url2[0];
				for($h = 1; $h<$co; $h++){
					$path .= '&'.$url2[$h];
				}
				$after = '';
				for($h = $co+1; $h<count($url2); $h++){
					$after .= '&'.$url2[$h];
				}				
				$pages = (int)($count/$parametri['pages']);
				if($count%$parametri['pages']>0) $pages++;
				$page = (int)($start/$parametri['pages']);
				if($start%$parametri['pages']>0) $page++;
				$pagination = '<div class="pagination"><p class="counter">Page '.($page+1).' of '.$pages.'</p><div class="pagination">';
				if($page==0){
					$pagination .= '<span>Start</span><span>Prev</span>';
				} else {
					$pagination .= '<a href="'.$path.'&0'.$after.'" title="Start">Start</a><a href="'.$path.'&'.($page-1)*$parametri['pages'].$after.'" title="Prev">Prev</a>';
				}
				for($j = 0; $j<$pages; $j++){
					if($j==$page){
						$pagination .= '<strong><span>'.($j+1).'</span></strong>';
					}else{
						$pagination .= '<strong><a href="'.$path.'&'.$j*$parametri['pages'].$after.'" title="'.($j+1).'">'.($j+1).'</a></strong>';
					}
				}
				if($page==($pages-1)){
					$pagination .= '<span>Next</span><span>End</span>';
				} else {
					$pagination .= '<a href="'.$path.'&'.($page+1)*$parametri['pages'].$after.'" title="Next">Next</a><a href="'.$path.'&'.($pages-1)*$parametri['pages'].$after.'" title="End">End</a>';
				}	
				$pagination .= '</div></div>';
			}


			
			//include necessary header content
			$document = JFactory::getDocument();
			if($document instanceof JDocumentHTML) {
				$arr = $document->getHeadData();
				$trr = false;
				$rrr = false;
				foreach($arr['custom'] as $rr){
					if(strpos($rr, 'videobox.css')!==false) $trr = true;		
					if(strpos($rr, 'videobox.js')!==false) $rrr = true;
				}
				if($trr===false){
					$document->addCustomTag('<link rel="stylesheet" href="plugins/content/videobox/css/videobox.css" type="text/css" media="screen" />');			
				}
				if (($count>=2)||($parametri['box']==1)) {
					if($rrr===false){
						$document->addCustomTag('<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js"></script><script type="text/javascript">jQuery.noConflict();</script><script type="text/javascript" src="plugins/content/videobox/videobox.js"></script><script type="text/javascript">
							var displayvideo;
							jQuery(document).ready(function($) {
								displayvideo = function (vid, src, vwidth, vheight, twidth, theight){
									var frame = document.getElementById(\'video_\'+vid);
									var image = document.getElementById(\'thumb_\'+vid);
									var close = document.getElementById(\'close_\'+vid);
									var title = document.getElementById(\'title_\'+vid);
									if(frame.src!=src){
										frame.src = src;
										image.style.display = \'none\';
										frame.style.display = \'block\';
										close.style.display = \'block\';
										$(frame).animate({height: vheight, width: vwidth},400,"swing");
										$(title).animate({width: vwidth},400,"swing");
									} else {
										close.style.display = \'none\';
										$(frame).animate({height: theight, width: twidth},400,"swing");
										$(title).animate({width: twidth},400,"swing");
										frame.src = \'\';
										frame.style.display = \'none\';
										image.style.display = \'block\';
									}
								}
							});
						</script>');
					}
				}
			}
			
			// display videos
			if ( $count ) {		  
				$video_content = '';
				$thumbnails    = '';
				$i = 1;
				$n = 1;

				foreach ($videos as $video) {
					$video = str_replace("<br />","",$video);
					$video = explode ('|', $video);
					if (($count == 1)&($parametri['box']!=1)) {
						$video_content .= $this->_videoCode($video, $params, $i, $parametri['width'], $parametri['height']);
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
							$thumbnails .= ' ' . $this->_videoBox($video, $parametri, $co, $parametri['t_width'], $parametri['t_height'], $parametri['width'], $parametri['height'], $parametri['style'], $n) . ' ';
							$i++;
							$n++;
						}
					}
				}
				
				if($parametri['box']!=1){
					$article->text = preg_replace( $regex, $video_content . '<div style="display: table;"><ul class="video">' . $thumbnails . '</ul></div>'.$pagination, $article->text, 1);
				}else{
					$article->text = preg_replace( $regex, $video_content . '' . $thumbnails . '', $article->text, 1);
				}
			}
		}
		return true;
	}
	
	protected function _videoCode( $video, $params, $i, $v_width, $v_height ) {
		if(!is_numeric($video[0])) {
			$src = 'http://www.youtube.com/embed/' . $video[0] . '?wmode=transparent&rel=0';
		} else {
			$src = 'http://player.vimeo.com/video/'.$video[0];
		}
		$html  = '<div id="videoFrame"><iframe width="'.$v_width.'" height="'.$v_height.'" src="'.$src.'" frameborder="0" allowfullscreen=""></iframe></div>';
		return $html;
	}

	protected function _videoThumb( $video, $params, $i, $t_width, $t_height, $v_width, $v_height, $n ) {
		if(!is_numeric($video[0])) {
			$src = 'http://www.youtube.com/embed/' . $video[0] . '?wmode=transparent&rel=0&autoplay=1';
			$img = '/plugins/content/videobox/showthumb.php?img='.urlencode('http://i2.ytimg.com/vi/' . $video[0] . '/hqdefault.jpg').'&width='.$t_width.'&height='.$t_height;
		} else {
			$hash = unserialize(file_get_contents('http://vimeo.com/api/v2/video/'.$video[0].'.php'));
			$src = 'http://player.vimeo.com/video/'.$video[0].'?autoplay=1';
			$img = '/plugins/content/videobox/showthumb.php?img='.urlencode($hash[0]['thumbnail_large']).'&width='.$t_width.'&height='.$t_height;
		}
		if($params['lightbox']=='0'){
			$thumb  = '<li class="video_cont_0"><a class="video_close" onclick="displayvideo(\''.$i.'_'.$n.'\',\''.$src.'\',\''.$t_width.'\',\''.$t_height.'\',\''.$t_width.'\',\''.$t_height.'\')" id="close_'.$i.'_'.$n.'"></a><a onclick="displayvideo(\''.$i.'_'.$n.'\',\''.$src.'\',\''.$t_width.'\',\''.$t_height.'\',\''.$t_width.'\',\''.$t_height.'\')" ><span class="video_thumb"><iframe id="video_'.$i.'_'.$n.'" style="width: 0px; height: 0px; display: none;"></iframe><img src="'.$img.'" id="thumb_'.$i.'_'.$n.'"></span><span class="video_title">' . $video[1] . '</span></a></li>';
		} else {
			$thumb  = '<li class="video_cont_0"><a href="'.$src.'" rel="videobox.sig'.$i.'" title="' . $video[1] . '" videowidth="'.$v_width.'" videoheight="'.$v_height.'"><span class="video_thumb"><img src="'.$img.'" id="thumb_'.$i.'_'.$n.'"></span><span class="video_title">' . $video[1] . '</span></a></li>';
		}
		return $thumb;
	}
	
	protected function _videoBox( $video, $params, $i, $t_width, $t_height, $v_width, $v_height, $style, $n ) {
		if(!is_numeric($video[0])) {
			$src = 'http://www.youtube.com/embed/' . $video[0] . '?wmode=transparent&rel=0&autoplay=1';
			$img = '/plugins/content/videobox/showthumb.php?img='.urlencode('http://i2.ytimg.com/vi/' . $video[0] . '/hqdefault.jpg').'&width='.$t_width.'&height='.$t_height;
		} else {
			$hash = unserialize(file_get_contents('http://vimeo.com/api/v2/video/'.$video[0].'.php'));
			$src = 'http://player.vimeo.com/video/'.$video[0].'?autoplay=1';
			$img = '/plugins/content/videobox/showthumb.php?img='.urlencode($hash[0]['thumbnail_large']).'&width='.$t_width.'&height='.$t_height;
		}
		if($params['lightbox']=='0'){
			$thumb  = '<span class="video_box_0" style="'.$style.'"><a class="video_close" onclick="displayvideo(\''.$i.'_'.$n.'\',\''.$src.'\',\''.$v_width.'\',\''.$v_height.'\',\''.$t_width.'\',\''.$t_height.'\')" id="close_'.$i.'_'.$n.'"></a><a onclick="displayvideo(\''.$i.'_'.$n.'\',\''.$src.'\',\''.$v_width.'\',\''.$v_height.'\',\''.$t_width.'\',\''.$t_height.'\')" ><span class="video_thumb"><iframe id="video_'.$i.'_'.$n.'" style="width: '.$t_width.'px; height: '.$t_height.'px; display: none;"></iframe><img src="'.$img.'" id="thumb_'.$i.'_'.$n.'"></span><span class="video_title" id="title_'.$i.'_'.$n.'" style="width: '.$t_width.'px;">' . $video[1] . '</span></a></span>';
		} else {
			$thumb  = '<span class="video_box_0" style="'.$style.'"><a href="'.$src.'" rel="videobox.sib'.$i.'" title="' . $video[1] . '" videowidth="'.$v_width.'" videoheight="'.$v_height.'"><span class="video_thumb"><img src="'.$img.'" id="thumb_'.$i.'_'.$n.'"></span><span class="video_title" style="width: '.$t_width.'px;">' . $video[1] . '</span></a></span>';
		}
		return $thumb;
	}
}