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

if(!isset($videos) && isset($video)) $videos = $video;
if(!isset($videos)) return;
$v = $modx->parseChunk($videos, array());
if($v) $videos = $v;
$videos = explode('|,', $videos);

require_once($vbCore . 'model/adapters/adapter.class.php');
$processors = array_map('trim', explode(',', $processors));
$proc = array();
foreach($processors as $key => $processor){
	$p = $modx->getObject('modSnippet', array('name' => $processor));
	if($p) $proc[] = $processor;
}
$processors = $proc;

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

if(!isset($display) || !$display) $display = count($videos) > 1 ? $multipleDisplay : $singleDisplay;
if($display == 'link') $display = 'links';
if($display == 'links' && $player == 'vbinline') $player = 'videobox';		//	inline player isn't meant to be used with links
$scriptProperties['display'] = $display;
$scriptProperties['palyer'] = $player;
unset($scriptProperties['multipleDisplay']);
unset($scriptProperties['singleDisplay']);

if(count($videos) > 1){
	$tpl = $display == 'links' ? $linkTpl : $thumbTpl;
	$start = 0;
	$pagination = '';
	
	if($display == 'gallery'){
		$videobox->gallery++;
		$start = $videobox->getPage();
		$scriptProperties['gallery_number'] = $videobox->gallery;
		$scriptProperties['gallery_page'] = $start;
		$pagination = $videobox->pagination(count($videos), $start, $perPage);
		$start = $start*$perPage;
		if($player == 'vbinline'){
			$pWidth = $tWidth;
			$pHeight = $tHeight;
		}
	}
	
	ksort($scriptProperties);
	$propHash = 'Vb_gallery_' . md5(serialize($scriptProperties));
	$content = $modx->cacheManager->get($propHash);
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
				'linkText' => $video->getTitle(), 
				'link' => $video->getPlayerLink(true), 
				'thumb' => $videobox->videoThumbnail($video, $tWidth, $tHeight, $display == 'flow'),
			);
			if($display == 'gallery' && $n == ($start + $perPage)) break;
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
		$n = 0;
		foreach($filtered as $video){
			$v = $modx->parseChunk($tpl, array_merge($props, $video, array('thumb' => $video['thumb'][0], 'tWidth' => $video['thumb'][1], 'tHeight' => $video['thumb'][2])));
			switch($display){
				case 'links':
					$v = ($n == 0 ? '' : $separator) . $v;
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
			$n++;
			$content .= $v;
		}
		$b = 0.25*$maxW*$minR;
		if($display == 'gallery') for(; $n < $perPage; $n++){
			$v = $modx->parseChunk($galleryItemTpl, array('ratio' => 1, 'basis' => $b));
			$content .= $v;
		}
		$modx->cacheManager->set($propHash, $content, 0);
	}
	switch($display){
		case 'links':
			return $content;
		case 'slider':
			return $modx->parseChunk($sliderTpl, array_merge($scriptProperties, array('content' => $content)));
		default:
			return $modx->parseChunk($galleryTpl, array_merge($scriptProperties, array('content' => $content, 'pagination' => $pagination)));
	}
} else {
	ksort($scriptProperties);
	$propHash = 'Vb_video_' . md5(serialize($scriptProperties));
	$data = $modx->cacheManager->get($propHash);
	if($data) return $data;
	$video = $videos[0];
	$props = array_merge(array('rel' => $player, 'pWidth' => $pWidth, 'pHeight' => $pHeight, 'tWidth' => $tWidth, 'tHeight' => $tHeight), array('title' => $video->getTitle(), 'link' => $video->getPlayerLink($display != 'player' || $autoPlay), 'ratio' => (100*$pHeight/$pWidth)));
	switch($display){
		case 'links':
			$props['linkText'] = isset($linkText) ? trim($linkText) : $props['title'];
			$v = $modx->parseChunk($linkTpl, $props);
			break;
		case 'box':
			$thumb = $videobox->videoThumbnail($video, $tWidth, $tHeight);
			$v = $modx->parseChunk($boxTpl, array_merge($scriptProperties, $props, array('thumb' => $thumb[0], 'tWidth' => $thumb[1], 'tHeight' => $thumb[2])));
			break;
		default:
			$v = $modx->parseChunk($playerTpl, $props);
			break;
	}
	$modx->cacheManager->set($propHash, $v, 0);
	return $v;
}