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

$vbCore = $modx->getOption('videobox.core_path', null, $modx->getOption('core_path').'components/videobox/');
$videobox = $modx->getService('videobox', 'Videobox', $vbCore . 'model/videobox/', $scriptProperties);
if(!($videobox instanceof Videobox)) return '';
$videobox->setConfig($scriptProperties);

if(!isset($videos) && isset($video)) $videos = $video;
if(!isset($videos)) return;
$scriptProperties['color'] = trim(str_replace('#', '', $scriptProperties['color']));
if(!$scriptProperties['color']) $scriptProperties['color'] = '00a645';
if(strlen($scriptProperties['color']) != 6) $scriptProperties['color'] = '';
$v = $videobox->parseTemplate($videos);
if($v) $videos = $v;
$videos = explode('|,', $videos);

$processors = $videobox->getProcessors();

$vid = array();
foreach($videos as $key => $video){
	$video = explode('|', $video);
	$title = '';
	if(isset($video[1])) $title = trim($video[1]);
	$title = $videobox->htmldec($title);
	$title = $videobox->htmlenc($title);
	$video = explode('#', $video[0]);
	$id = trim($video[0]);
	$start = 0;
	$end = 0;
	if(count($video) > 1){
		$video = explode('-', trim($video[count($video) - 1]));
		if(is_numeric(str_replace(':', '', trim($video[0])))){
			$off = explode (':', trim($video[0]));
			foreach($off as $off1){
				$start = $start*60 + $off1;
			}
		}
		if(is_numeric(str_replace(':', '', trim($video[1])))){
			$off = explode (':', trim($video[1]));
			foreach($off as $off1){
				$end = $end*60 + $off1;
			}
		}
	}
	$prop = array_merge($scriptProperties, array('id' => $id, 'title' => $title, 'start' => $start, 'end' => $end));
	
	$v = $videobox->getVideo(array('id' => $id, 'title' => $title, 'start' => $start, 'end' => $end));
	if($v) $vid[] = $v;
	
}
$videos = $vid;

if(count($videos) < 1) return;
$videobox->loadAssets();

if(!isset($display) || !$display) $display = count($videos) > 1 ? $scriptProperties['multipleDisplay'] : $scriptProperties['singleDisplay'];
if($display == 'link') $display = 'links';
if($display == 'links' && $scriptProperties['player'] == 'vbinline') $scriptProperties['player'] = 'videobox';
$scriptProperties['display'] = $display;
unset($scriptProperties['multipleDisplay']);
unset($scriptProperties['singleDisplay']);

if(count($videos) > 1){
	$tpl = $display == 'links' ? $scriptProperties['linkTpl'] : $scriptProperties['thumbTpl'];
	$start = 0;
	$pagination = '';
	
	if($display == 'gallery'){
		$videobox->gallery++;
		$start = $videobox->getPage();
		$scriptProperties['gallery_number'] = $videobox->gallery;
		$scriptProperties['gallery_page'] = $start;
		$pagination = $videobox->pagination(count($videos), $start, $scriptProperties['perPage']);
		$start = $start*$scriptProperties['perPage'];
	}
	
	if($scriptProperties['player'] == 'vbinline' && ($display == 'gallery' || $display == 'slider')){
		$scriptProperties['pWidth'] = $scriptProperties['tWidth'];
		$scriptProperties['pHeight'] = $scriptProperties['tHeight'];
	}

	// if($display == 'slider'){ 
		// $scriptProperties['class'] = isset($scriptProperties['class']) ? $scriptProperties['class'] . ' vb_slider' : 'vb_slider';
	// }
	
	ksort($scriptProperties);
	$propHash = 'Vb_gallery_' . md5(serialize($scriptProperties));
	$content = $videobox->getCache($propHash);
	if(!$content){
		$n = 0;
		$content = '';
		$props = array('rel' => $scriptProperties['player'], 'pWidth' => $scriptProperties['pWidth'], 'pHeight' => $scriptProperties['pHeight']);
		$filtered = array();
		foreach($videos as $video){
			$n++;
			if($start > 0 && $n <= $start) continue;
			$filtered[] = array(
				'title' => $video->getTitle(), 
				'linkText' => $video->getTitle(true), 
				'link' => $video->getPlayerLink(true), 
				'thumb' => $videobox->videoThumbnail($video, $display == 'flow'),
			);
			if($display == 'gallery' && $n == ($start + $scriptProperties['perPage'])) break;
		}
		$maxR = 0;
		$maxW = $scriptProperties['tWidth'];
		foreach($filtered as $video){
			$r = $video['thumb'][1]/$video['thumb'][2];
			if($r > $maxR) $maxR = $r;
			//if($slika['thumb'][1] > $maxW) $maxW = $slika['thumb'][1];
		}
		$minR = 0.6;
		foreach($filtered as $video){
			$r = $video['thumb'][1]/($maxR*$video['thumb'][2]);
			if($r && $r < $minR) $minR = $r;
		}
		$minR = 1 - log($minR);
	/*	foreach($slike as $slika){
			$r = $video['thumb'][1]/($maxR*$video['thumb'][2]);
			$b = 0.25*$r*$maxW*$minR;
			$so .= '<li class="galerija_item" style="flex: ' . $r . ' ' . $r . ' ' . $b . 'px;"><a class="galerija_slika_link" href="'.$slika['small'][0].'" rel="lightbox.gal"><img class="thumb" width="'.$slika['thumb'][1].'" height="'.$slika['thumb'][2].'" src="'.$slika['thumb'][0].'"></a></li>';
		}
		$b = 0.25*$maxW*$minR;*/
		$n = 0;
		foreach($filtered as $video){
			$v = $videobox->parseTemplate($tpl, array_merge($props, $video, array('thumb' => $video['thumb'][0], 'tWidth' => $video['thumb'][1], 'tHeight' => $video['thumb'][2])));
			switch($display){
				case 'links':
					$v = ($n == 0 ? '' : $scriptProperties['delimiter']) . $v;
					break;
				case 'slider':
					$r = $video['thumb'][1]/($maxR*$video['thumb'][2]);
					$b = 0.25*$r*$maxW*$minR;
					$v = $videobox->parseTemplate($scriptProperties['sliderItemTpl'], array_merge($scriptProperties, array('content' => $v, 'ratio' => $r, 'basis' => $b)));
					break;
				default:
					$r = $video['thumb'][1]/($maxR*$video['thumb'][2]);
					$b = 0.25*$r*$maxW*$minR;
					$v = $videobox->parseTemplate($scriptProperties['galleryItemTpl'], array_merge($scriptProperties, array('content' => $v, 'ratio' => $r, 'basis' => $b)));
					break;
			}
			$n++;
			$content .= $v;
		}
		$b = 0.25*$maxW*$minR;
		if($display == 'gallery') for(; $n < $scriptProperties['perPage']; $n++){
			$v = $videobox->parseTemplate($scriptProperties['galleryItemTpl'], array('ratio' => 1, 'basis' => $b));
			$content .= $v;
		}
		$videobox->setCache($propHash, $content);
	}
	switch($display){
		case 'links':
			return $content;
		case 'slider':
			return $videobox->parseTemplate($scriptProperties['sliderTpl'], array_merge($scriptProperties, array('content' => $content, 'basis' => $scriptProperties['tWidth']/2)));
		default:
			return $videobox->parseTemplate($scriptProperties['galleryTpl'], array_merge($scriptProperties, array('content' => $content, 'pagination' => $pagination)));
	}
} else {
	$autoPlay = isset($autoPlay) && $autoPlay && $display == 'player' && (!isset($videobox->autoPlay) || !$videobox->autoPlay);
	$scriptProperties['autoPlay'] = $autoPlay;
	if($autoPlay) $videobox->autoPlay = true;
	ksort($scriptProperties);
	$propHash = 'Vb_video_' . md5(serialize($scriptProperties));
	$data = $videobox->getCache($propHash);
	if($data) return $data;
	$video = $videos[0];
	$props = array_merge(array('rel' => $scriptProperties['player'], 'pWidth' => $scriptProperties['pWidth'], 'pHeight' => $scriptProperties['pHeight'], 'tWidth' => $scriptProperties['tWidth'], 'tHeight' => $scriptProperties['tHeight']), array('title' => $video->getTitle(), 'link' => $video->getPlayerLink($display != 'player' || $autoPlay), 'ratio' => (100*$scriptProperties['pHeight']/$scriptProperties['pWidth'])));
	switch($display){
		case 'links':
			$props['linkText'] = isset($linkText) ? trim($linkText) : $video->getTitle(true);
			$v = $videobox->parseTemplate($scriptProperties['linkTpl'], $props);
			break;
		case 'box':
			$thumb = $videobox->videoThumbnail($video);
			$v = $videobox->parseTemplate($scriptProperties['boxTpl'], array_merge($scriptProperties, $props, array('thumb' => $thumb[0], 'tWidth' => $thumb[1], 'tHeight' => $thumb[2])));
			break;
		default:
			$v = $videobox->parseTemplate($scriptProperties['playerTpl'], $props);
			break;
	}
	$videobox->setCache($propHash, $v);
	return $v;
}