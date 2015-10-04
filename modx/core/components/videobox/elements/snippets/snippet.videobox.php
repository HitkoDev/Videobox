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

$vbCore = $modx->getOption('videobox.core_path', null, $modx->getOption('core_path').'components/videobox/');
$videobox = $modx->getService('videobox', 'Videobox', $vbCore . 'model/videobox/', $scriptProperties);
if(!($videobox instanceof Videobox)) return '';

ksort($scriptProperties);
$prop_hash = md5(serialize($scriptProperties));
if(!isset($videos)) return;
$v = $modx->parseChunk($videos, array());
if($v) $videos = $v;
$videos = explode('|,', $videos);

require_once($vbCore . 'model/adapters/adapter.class.php');
$processors = explode(',', $processors);
array_map('trim', $processors);
$proc = array();
foreach($processors as $key => $processor){
	$p = $modx->getObject('modSnippet', array('name' => $processor));
	if($p) $proc[] = $processor;
}
$processors = $proc;

$modx->cacheManager->get($chacheKey);
$vid = array();
foreach($videos as $key => $video){
	$video_hash = md5($video, $prop_hash);
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
	foreach($processors as $processor){
		$v = $modx->runSnippet($processor, $prop);
		if($v){
			$vid[] = $v;
			break;
		}
	}
}
$videos = $vid;

if(count($videos) < 1) return;
$modx->regClientCSS($videobox->config['assets_url'] . 'css/videobox.css');
$modx->regClientScript($videobox->config['assets_url'] . 'js/jquery.min.js');
$modx->regClientScript($videobox->config['assets_url'] . 'js/videobox%20src.js');
if(count($videos) > 1){
	if(isset($display)) $multipleDisplay = $display;
	$tpl = $multipleDisplay == 'links' ? $linkTpl : $thumbTpl;
	$start = 0;
	
	if($multipleDisplay == 'gallery'){
		$videobox->gallery++;
		$start = $videobox->getPage();
		$pagination = $multipleDisplay == 'gallery' ? $videobox->pagination(count($videos), $start, $perPage) : '';
		$start = $start*$perPage;
	}
	
	$chacheKey = 'Videobox_page_'.$start.'_'.$propHash;
	$content = $modx->cacheManager->get($chacheKey);
	if(!$content){
		$n = 0;
		$content = '';
		$props = array('rel' => $player, 'pWidth' => $pWidth, 'pHeight' => $pHeight);
		$filtered = array();
		foreach($videos as $video){
			$n++;
			if($start > 0 && $n <= $start) continue;
			$filtered[] = array(
				'title' => $video->getTitle(), 
				'link' => $video->getPlayerLink(true), 
				'thumb' => $videobox->videoThumbnail($video, $tWidth, $tHeight, $multipleDisplay == 'flow'),
			);
			if($multipleDisplay == 'gallery' && $n == ($start + $perPage)) break;
		}
		$maxR = 0;
		$maxW = $tWidth;
		foreach($filtered as $video){
			$r = $video['thumb'][1]/$video['thumb'][2];
			if($r > $maxR) $maxR = $r;
			//if($slika['thumb'][1] > $maxW) $maxW = $slika['thumb'][1];
		}
		$minR = 0.6;
		foreach($filtered as $video){
			$r = $video['thumb'][1]/($maxR*$video['thumb'][2]);
			if($r < $minR) $minR = $r;
		}
		$minR = 1 - log($minR);
	/*	foreach($slike as $slika){
			$r = $video['thumb'][1]/($maxR*$video['thumb'][2]);
			$b = 0.25*$r*$maxW*$minR;
			$so .= '<li class="galerija_item" style="flex: ' . $r . ' ' . $r . ' ' . $b . 'px;"><a class="galerija_slika_link" href="'.$slika['small'][0].'" rel="lightbox.gal"><img class="thumb" width="'.$slika['thumb'][1].'" height="'.$slika['thumb'][2].'" src="'.$slika['thumb'][0].'"></a></li>';
		}
		$b = 0.25*$maxW*$minR;*/
		foreach($filtered as $video){
			$v = $modx->parseChunk($tpl, array_merge($props, $video, array('thumb' => $video['thumb'][0], 'tWidth' => $video['thumb'][1], 'tHeight' => $video['thumb'][2])));
			switch($multipleDisplay){
				case 'links':
					$v = ($n == 1 ? '' : $separator) . $v;
					break;
				case 'slider':
					$v = $modx->parseChunk($sliderItemTpl, array_merge($scriptProperties, array('content' => $v)));
					break;
				default:
					$r = $video['thumb'][1]/($maxR*$video['thumb'][2]);
					$b = 0.25*$r*$maxW*$minR;
					$v = $modx->parseChunk($galleryItemTpl, array_merge($scriptProperties, array('content' => $v, 'ratio' => $r, 'basis' => $b)));
					break;
			}
			
			$content .= $v;
		}
		$b = 0.25*$maxW*$minR;
		if($multipleDisplay == 'gallery') for(; $n < $start + $perPage; $n++){
			$v = $modx->parseChunk($galleryItemTpl, array('ratio' => 1, 'basis' => $b));
			$content .= $v;
		}
		$modx->cacheManager->set($chacheKey, $content, 0);
	}
	switch($multipleDisplay){
		case 'links':
			return $content;
		case 'slider':
			return $modx->parseChunk($sliderTpl, array_merge($scriptProperties, array('content' => $content)));
		default:
			return $modx->parseChunk($galleryTpl, array_merge($scriptProperties, array('content' => $content, 'pagination' => $pagination)));
	}
} else {
	if(isset($display)) $singleDisplay = $display;
	$content = '';
	foreach($videos as $chacheKey => $video){
		$data = $modx->cacheManager->get($chacheKey);
		if($data) return $data;
		
		$props = array_merge(array('rel' => $player, 'pWidth' => $pWidth, 'pHeight' => $pHeight, 'tWidth' => $tWidth, 'tHeight' => $tHeight), array('title' => $video->getTitle(), 'link' => $video->getPlayerLink($singleDisplay != 'player' || $autoPlay), 'ratio' => (100*$pHeight/$pWidth), 'thumb' => $videobox->videoThumbnail($video, $tWidth, $tHeight)));
		switch($singleDisplay){
			case 'link':
				$v = $modx->parseChunk($linkTpl, $props);
				break;
			case 'box':
				$v = $modx->parseChunk($thumbTpl, $props);
				$v = $modx->parseChunk($boxTpl, array_merge($scriptProperties, array('content' => $v)));
				break;
			default:
				$v = $modx->parseChunk($playerTpl, $props);
				break;
		}
		$modx->cacheManager->set($chacheKey, $v, 0);
		return $v;
	}
}