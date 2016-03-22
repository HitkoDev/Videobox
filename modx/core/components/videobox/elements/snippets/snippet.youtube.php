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
require_once($vbCore . 'model/adapters/youtube.class.php');

/*
 *	$scriptProperties['id'] - one of the following:
 *		- 11 characters YouTube video ID
 *		- YouTube sharing link (http://youtu.be/KKWTdo5YW_I)
 *		- link to the video (https://www.youtube.com/watch?v=KKWTdo5YW_I)
 */
if(strlen($scriptProperties['id'])==11 && preg_match('/([a-zA-Z0-9_-]{11})/', $scriptProperties['id'])==1){
	return new YouTubeVideo($scriptProperties);
}
if(strpos($scriptProperties['id'], 'youtube')!==false){
	preg_match('/v=([a-zA-Z0-9_-]{11}?)/isU', $scriptProperties['id'], $v_urls);
	return new YouTubeVideo(array_merge($scriptProperties, array('id' => $v_urls[1])));
}
if(strpos($scriptProperties['id'], 'youtu.be')!==false){
	preg_match('/youtu\.be\/([a-zA-Z0-9_-]{11}?)/isU', $scriptProperties['id'], $v_urls);
	return new YouTubeVideo(array_merge($scriptProperties, array('id' => $v_urls[1])));
}
return false;