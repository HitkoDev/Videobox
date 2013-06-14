<?php
/*------------------------------------------------------------------------
# plg_videobox - Videobox
# ------------------------------------------------------------------------
# author    HiTKO
# copyright Copyright (C) 2012 hitko.si. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://hitko.si
-------------------------------------------------------------------------*/
// This file MUST be directly accessible because it is included like this: <img src="/plugins/content/videobox/showthumb.php?img=http%3A%2F%2Fi2.ytimg.com%2Fvi%2Fz--83CE8Tr8%2Fhqdefault.jpg&amp;width=206&amp;height=155">
// It doesn't connect with any core files, exstensions, databases or anything. It recieves image url and desired Width + Height and returns downsized version of input image.
// defined( '_JEXEC' ) or die( 'Restricted access' ); 

if($_GET['img'] == "")
{
    exit("No parameters!");
}

$img = rawurldecode($_GET['img']);
$imgdata = pathinfo($img);
if(strpos('mp4,ogv,webm,m4v,oga,mp3,m4a,webma,wav', $imgdata['extension'])!==false){
	$imgthumb = $imgdata['dirname'].'/'.$imgdata['filename'];
	if(@getimagesize($imgthumb.'.png')){
		$img = $imgthumb.'.png'; //thumbnail is provided, filetype .png
	} elseif(@getimagesize($imgthumb.'.jpg')){
		$img = $imgthumb.'.jpg'; //thumbnail is provided, filetype .jpg
	} elseif(@getimagesize($imgthumb.'.jpeg')){
		$img = $imgthumb.'.jpeg'; //thumbnail is provided, filetype .jpeg
	} elseif(@getimagesize($imgthumb.'.gif')){
		$img = $imgthumb.'.gif';  //thumbnail is provided, filetype .gif
	} elseif(strpos('oga,mp3,m4a,webma,wav', $imgdata['extension'])!==false){
		$url = pathinfo(__FILE__);
		$img = $url['dirname'].'/css/nobg_a.png';
	} elseif(strpos('mp4,ogv,webm,m4v', $imgdata['extension'])!==false) {
		$url = pathinfo(__FILE__);
		$img = $url['dirname'].'/css/nobg_v.png';
	}
} else {
	if((strlen($img)>11)&(!is_numeric($img))){
		$img = urldecode($img);
		$coun_v = preg_match_all('/<a.*?>([^`]*?)<\/a>/', $img, $vvvvv);
		if($coun_v!=0) $img = $vvvvv[1][0];
		if(strpos($img, 'youtube')!==false){
			$v_urls = explode ('?', $img);
			$v_urls = explode ('#', $v_urls[1]);
			$v_urls = explode ('&', $v_urls[0]);
			foreach($v_urls as $v_url){
				if(($v_url{0}=='v')&($v_url{1}=='=')) $img = substr($v_url, 2);
			}
		} else { 
			$v_urls = explode ('/', $img);
			$v_urls = explode ('#', $v_urls[count($v_urls)-1]);
			$v_urls = explode ('&', $v_urls[0]);
			$img = $v_urls[0];
		}
	}

	if(!is_numeric($img)) {
		$img = 'http://i2.ytimg.com/vi/'.$img.'/hqdefault.jpg';
	} else {
		$hash = unserialize(file_get_contents('http://vimeo.com/api/v2/video/'.$img.'.php'));
		$img = $hash[0]['thumbnail_large'];
	}
}

$width = $_GET['width'];
$height = $_GET['height'];

$imagedata = getimagesize($img);

if(!$imagedata[0])
{
    exit();
}

$offset_h = 0;
$offset_w = 0;

if((($width*$imagedata[1])/$height)>=$imagedata[0]){
	$new_h = $height;
	$new_w = (int)(($height*$imagedata[0])/$imagedata[1]);
	$offset_w = (int)(($width - $new_w)/2);
} else {
	$new_w = $width;
	$new_h = (int)(($width*$imagedata[1])/$imagedata[0]);
	$offset_h = (int)(($height - $new_h)/2);
}

header("Content-type: image/jpg");
$dst_img = imagecreatetruecolor($width, $height);
$black = imagecolorallocate($dst_img, 0, 0, 0);
imagefilledrectangle($dst_img, 0, 0, $width, $height, $black);

switch(strtolower(substr($img, -3))){
	case 'peg': 
		$src_img = imagecreatefromjpeg($img);
		break;
	case 'jpg': 
		$src_img = imagecreatefromjpeg($img);
		break;
	case 'png': 
		$src_img = imagecreatefrompng($img);
		break;
	case 'gif': 
		$src_img = imagecreatefromgif($img);
		break;
}

imagecopyresampled($dst_img, $src_img, $offset_w, $offset_h, 0, 0, $new_w, $new_h, $imagedata[0], $imagedata[1]);
imagejpeg($dst_img, null, 100);

?>