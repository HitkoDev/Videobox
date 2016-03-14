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

$req_video = isset($_REQUEST['vb-video']) ? trim($_REQUEST['vb-video']) : '';
if($req_video){

	$vbCore = $modx->getOption('videobox.core_path', null, $modx->getOption('core_path').'components/videobox/');
	$videobox = $modx->getService('videobox', 'Videobox', $vbCore . 'model/videobox/', $scriptProperties);
	if(!($videobox instanceof Videobox)) exit();

	require_once($vbCore . 'model/adapters/adapter.class.php');
	$processors = array_map('trim', explode(',', $processors));
	$proc = array();
	foreach($processors as $key => $processor){
		$p = $modx->getObject('modSnippet', array('name' => $processor));
		if($p) $proc[] = $processor;
	}
	$processors = $proc;

	$autoplay = isset($_REQUEST['autoplay']) ? trim($_REQUEST['autoplay']) : '';
	$title = isset($_REQUEST['title']) ? trim($_REQUEST['title']) : '';
	$s = isset($_REQUEST['start']) ? trim($_REQUEST['start']) : '';
	$e = isset($_REQUEST['end']) ? trim($_REQUEST['end']) : '';
	$start = 0;
	$end = 0;
	
	if(is_numeric(str_replace(':', '', $s))){
		$off = explode (':', $s);
		foreach($off as $off1){
			$start = $start*60 + $off1;
		}
	}
	if(is_numeric(str_replace(':', '', $e))){
		$off = explode (':', $e);
		foreach($off as $off1){
			$end = $end*60 + $off1;
		}
	}
	
	$video = null;
	$prop = array('id' => $req_video, 'title' => $title, 'start' => $start, 'end' => $end);
	foreach($processors as $processor){
		$v = $modx->runSnippet($processor, $prop);
		if($v){
			$video = $v;
			break;
		}
	}
	if($video){
		$thumb = $videobox->videoThumbnail($video, $tWidth, $tHeight, true);
		$_video = array(
			'poster' => $thumb[0],
			'url' => $video->getSourceUrl(),
			'assets' => $videobox->config['assets_url'],
			'title' => $video->getTitle(),
			'type' => $video->type == 'a' ? 'vjs-audio' : '',
			'start' => $video->start,
			'end' => $video->end > $video->start ? $video->end : 0,
			'auto' => $autoplay ? 1 : 0
		);
		$sources = '';
		foreach($video->getSourceFormats() as $source){
			$sources .= '<source src="' . $_video['url'] . '.' . $source[0] . '" type="' . $source[1] . '">';
		}
		$_video['sources'] = $sources;
		echo($modx->parseChunk($playerTpl, array_merge($scriptProperties, $_video)));
	}
	exit();
}