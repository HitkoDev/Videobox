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
require_once($vbCore . 'model/adapters/html5.class.php');

/*
 *	$scriptProperties['id'] - url ending with one of the known file extensions
 */
HTML5Video::$vid = array(
	array('mp4', 'ogv', 'webm'), 
	array('video/mp4', 'video/ogg', 'video/webm')
);
HTML5Video::$aud = array(
	array('mp3', 'oga', 'wav', 'webm'), 
	array('audio/mpeg', 'audio/ogg', 'audio/wav', 'audio/webm')
);
HTML5Video::$img = array(
	array('jpg', 'jpeg', 'png', 'gif'), 
	array(IMAGETYPE_JPEG, IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF)
); 
$ext = pathinfo($scriptProperties['id']);
$file = $ext['dirname'] . '/' . $ext['filename'];
$ext = $ext['extension'];

if(in_array(strtolower($ext), HTML5Video::$vid[0]) || in_array(strtolower($ext), HTML5Video::$aud[0])){
	$file = str_replace(rtrim($modx->getOption('site_url'), '/'), '', $file);
	$local = true;
	if(substr($file, 0, 7) == 'http://' || substr($file, 0, 8) == 'https://' || substr($file, 0, 2) == '//') $local = false;
	$source = $modx->getObject('modMediaSource', $modx->getOption('default_media_source'));
	$source->initialize();
	$paths = $source->getBases($file);
	if($local){
		$paths['urlFullWithPath'] = $paths['urlAbsoluteWithPath'];
	} else {
		$paths['urlFullWithPath'] = $file;
	}
	$scriptProperties['paths'] = $paths;
	$scriptProperties['local'] = $local;
	$scriptProperties['ext'] = $ext;
	$scriptProperties['scriptsDir'] = rtrim($vbCore, '/') . '/scripts/';
	$scriptProperties['modx'] = $modx;
	return new HTML5Video($scriptProperties);
}
return false;