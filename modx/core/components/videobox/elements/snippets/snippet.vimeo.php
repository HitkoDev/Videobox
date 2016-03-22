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
require_once($vbCore . 'model/adapters/vimeo.class.php');

/*
 *	$scriptProperties['id'] - one of the following:
 *		- numeric Vimeo video ID
 *		- link to the video (http://vimeo.com/4700344)
 */
if(is_numeric($scriptProperties['id'])){
	return new VimeoVideo($scriptProperties);
}
if(strpos($scriptProperties['id'], 'vimeo')!==false){
	preg_match('/vimeo.com\/([0-9]*?)/isU', $scriptProperties['id'], $v_urls);
	return new VimeoVideo(array_merge($scriptProperties, array('id' => $v_urls[1])));
}
return false;