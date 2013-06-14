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
				foreach($parametri_a as $parameter){
					$parameter = explode('=',$parameter);
				$parametri[trim($parameter[0])] = trim($parameter[1]);
				}			
			}
			
			if($parametri['pages']==0) $parametri['pages'] = 99999999;
			if($parametri['break']==0) $parametri['break'] = 99999999;
			
			//create pagination (if necessary)		
			$start = 0;
			$pagination = '';
			if(($count>$parametri['pages'])&($parametri['pages']!=0)){
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
						$document->addCustomTag('<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js"></script><script type="text/javascript">jQuery.noConflict();</script><script type="text/javascript" src="plugins/content/videobox/videobox.js"></script>');
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
					if (($count == 1)&($parametri['box']!=1)) {
						$video_content .= $this->_videoCode($video, $params, $i, $parametri['width'], $parametri['height']);
					}else{
						if($parametri['box']!=1){
							if(($n>$start)&($n<=($start+$parametri['pages']))){
								if($i==($parametri['break']+1)){
									$i = 1;
									$thumbnails .= '</ul><ul class="video">';
								}
								$thumbnails .= ' ' . $this->_videoThumb($video, $params, $co, $parametri['t_width'], $parametri['t_height'], $parametri['width'], $parametri['height']) . ' ';
								$i++;
							}
							$n++;
						}else{
							$thumbnails .= ' ' . $this->_videoBox($video, $params, $co, $parametri['t_width'], $parametri['t_height'], $parametri['width'], $parametri['height'], $parametri['style']) . ' ';
							$i++;
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
	
	protected function _videoCode( $video_id, $params, $i, $v_width, $v_height ) {
		$video = explode ('|', $video_id);
		if(!is_numeric($video[0])) {
			$src = 'http://www.youtube.com/embed/' . $video[0] . '?wmode=transparent&rel=0';
		} else {
			$src = 'http://player.vimeo.com/video/'.$video[0];
		}
		$html  = '<div id="videoFrame"><iframe width="'.$v_width.'" height="'.$v_height.'" src="'.$src.'" frameborder="0" allowfullscreen=""></iframe></div>';
		return $html;
	}

	protected function _videoThumb( $video_id, $params, $i, $t_width, $t_height, $v_width, $v_height ) {
		$video_id = str_replace("<br />","",$video_id);
		$video = explode ('|', $video_id);
		if(!is_numeric($video[0])) {
			$src = 'http://www.youtube.com/embed/' . $video[0] . '?wmode=transparent&rel=0&autoplay=1';
			$img = '/plugins/content/videobox/showthumb.php?img='.urlencode('http://i2.ytimg.com/vi/' . $video[0] . '/hqdefault.jpg').'&width='.$t_width.'&height='.$t_height;
		} else {
			$hash = unserialize(file_get_contents('http://vimeo.com/api/v2/video/'.$video[0].'.php'));
			$src = 'http://player.vimeo.com/video/'.$video[0].'?autoplay=1';
			$img = '/plugins/content/videobox/showthumb.php?img='.urlencode($hash[0]['thumbnail_large']).'&width='.$t_width.'&height='.$t_height;
		}
		$thumb  = '<li class="video_cont_0"><span class="video_thumb"><a href="'.$src.'" rel="videobox.sig'.$i.'" title="' . $video[1] . '" videowidth="'.$v_width.'" videoheight="'.$v_height.'"><img src="'.$img.'"></a></span><span class="video_title"><a href="http://www.youtube.com/embed/' . $video[0] . '?wmode=transparent&rel=0&autoplay=1" rel="videobox.si'.$i.'" title="' . $video[1] . '" videowidth="'.$v_width.'" videoheight="'.$v_height.'">' . $video[1] . '</a></span></li>';
		return $thumb;
	}
	
	protected function _videoBox( $video_id, $params, $i, $t_width, $t_height, $v_width, $v_height, $style ) {
		$video_id = str_replace("<br />","",$video_id);
		$video = explode ('|', $video_id);
		if(!is_numeric($video[0])) {
			$src = 'http://www.youtube.com/embed/' . $video[0] . '?wmode=transparent&rel=0&autoplay=1';
			$img = '/plugins/content/videobox/showthumb.php?img='.urlencode('http://i2.ytimg.com/vi/' . $video[0] . '/hqdefault.jpg').'&width='.$t_width.'&height='.$t_height;
		} else {
			$hash = unserialize(file_get_contents('http://vimeo.com/api/v2/video/'.$video[0].'.php'));
			$src = 'http://player.vimeo.com/video/'.$video[0].'?autoplay=1';
			$img = '/plugins/content/videobox/showthumb.php?img='.urlencode($hash[0]['thumbnail_large']).'&width='.$t_width.'&height='.$t_height;
		}
		$thumb  = '<span class="video_box_0" style="'.$style.'"><span class="video_thumb"><a href="'.$src.'" rel="videobox.sib'.$i.'" title="' . $video[1] . '" videowidth="'.$v_width.'" videoheight="'.$v_height.'"><img src="'.$img.'"></a></span><span class="video_title" style="width: '.$t_width.'px;"><a href="http://www.youtube.com/embed/' . $video[0] . '?wmode=transparent&rel=0&autoplay=1" rel="videobox.sb'.$i.'" title="' . $video[1] . '" videowidth="'.$v_width.'" videoheight="'.$v_height.'">' . $video[1] . '</a></span></span>';
		return $thumb;
	}
}