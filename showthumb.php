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

$img = urldecode($_GET['img']);
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

switch(strtolower(substr($_GET['img'], -3))){
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