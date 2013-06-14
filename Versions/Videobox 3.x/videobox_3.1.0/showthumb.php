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

if(!isset($_GET['img']))
{
    exit("No parameters!");
}

$url = pathinfo(__FILE__);
$img = rawurldecode($_GET['img']);
$imgdata = pathinfo($img);
$youku = false;
if(!isset($imgdata['extension'])) $imgdata['extension'] = 'ddd';
if(strpos('mp4,ogv,webm,m4v,oga,mp3,m4a,webma,wav', $imgdata['extension'])!==false){
	$imgthumb = $imgdata['dirname'].'/'.$imgdata['filename'];
	
	$rootdir = str_replace($_SERVER['PHP_SELF'], '', $_SERVER['SCRIPT_FILENAME']).'/';
	
	if(($SERVER_['HTTPS']!='off')&&($SERVER_['HTTPS']!=null)){
		$protocol = 'https://';
	} else {
		$protocol = 'http://';
	}
	$domain = $protocol.$_SERVER['HTTP_HOST'].'/';
	$imgthumb = str_replace($domain, $rootdir, $imgthumb);
	$imgthumb = str_replace('//', '/', $imgthumb);

	if(strpos($imgthumb, 'http')===0){
		$imgthumb = str_replace(" ", "%20", $imgthumb);	
	} elseif(strpos($imgthumb, $rootdir)===false) {
		$imgthumb = $rootdir.$imgthumb;
	}
	if(@getimagesize($imgthumb.'.png')){
		$img = $imgthumb.'.png'; //thumbnail is provided, filetype .png
	} elseif(@getimagesize($imgthumb.'.jpg')){
		$img = $imgthumb.'.jpg'; //thumbnail is provided, filetype .jpg
	} elseif(@getimagesize($imgthumb.'.jpeg')){
		$img = $imgthumb.'.jpeg'; //thumbnail is provided, filetype .jpeg
	} elseif(@getimagesize($imgthumb.'.gif')){
		$img = $imgthumb.'.gif';  //thumbnail is provided, filetype .gif
	} elseif(strpos('mp4,ogv,webm,m4v', $imgdata['extension'])!==false) {
		$img = $url['dirname'].'/css/nobg_v.png';
	} elseif(strpos('oga,mp3,m4a,webma,wav', $imgdata['extension'])!==false){
		$img = $url['dirname'].'/css/nobg_a.png';
	}
} else {

	if((strlen($img)>16)&(!is_numeric($img))){
		if(strpos($img, 'youtube')!==false){
			preg_match('/v=(.{11}?)/isU', $img, $v_urls);
			$img = $v_urls[1];
		} elseif(strpos($img, 'youku')!==false) {
			preg_match('/id_(.*?).html/isU', $img, $v_urls);
			$img = $v_urls[1];
		} elseif(strpos($img, 'youtu.be')!==false){
			preg_match('/youtu.be\/(.{11}?)/isU', $img, $v_urls);
			$img = $v_urls[1];
		} else { 
			preg_match('/vimeo.com\/([0-9]*?)/isU', $img, $v_urls);
			$img = $v_urls[1];
		}
	} else {
		if((substr($img, 0, 3)=='id_')&(strlen($img)==16)){
			$img = substr($img, 3);
		}
	}
	
	if(@getimagesize($url['dirname'].'/thumbs/'.$img.'.png')){
		$img = $url['dirname'].'/thumbs/'.$img.'.png'; //thumbnail is provided, filetype .png
	} elseif(@getimagesize($url['dirname'].'/thumbs/'.$img.'.jpg')){
		$img = $url['dirname'].'/thumbs/'.$img.'.jpg'; //thumbnail is provided, filetype .jpg
	} elseif(@getimagesize($url['dirname'].'/thumbs/'.$img.'.jpeg')){
		$img = $url['dirname'].'/thumbs/'.$img.'.jpeg'; //thumbnail is provided, filetype .jpeg
	} elseif(@getimagesize($url['dirname'].'/thumbs/'.$img.'.gif')){
		$img = $url['dirname'].'/thumbs/'.$img.'.gif';  //thumbnail is provided, filetype .gif
	} elseif(!is_numeric($img)) {
		if(strlen($img)==11){
			$img = 'http://i2.ytimg.com/vi/'.$img.'/hqdefault.jpg';
		} else {
			$hash = json_decode(file_get_contents('http://v.youku.com/player/getPlayList/VideoIDS/'.$img));
			$img = $hash->data[0]->logo.'?u='.$hash->data[0]->userid;
			$youku = true;
		}
	} else {
		$hash = unserialize(file_get_contents('http://vimeo.com/api/v2/video/'.$img.'.php'));
		$img = $hash[0]['thumbnail_large'];
	}
}

if(!isset($_GET['play'])){
	$play = 0;
} else {
	if($_GET['play']==0){
		$play = 0;
	} else {
		$play = imagecreatefrompng($url['dirname'].'/css/play.png');
	}
}	

$width = $_GET['width'];
$height = $_GET['height'];

try {	
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
	$imagedata[0] = imagesx($src_img);
	$imagedata[1] = imagesy($src_img);
} catch (Exception $e) {
	exit("Image can't be loaded");
}

$b_top = 0;
$b_btm = 0;
$b_lft = 0;
$b_rt = 0;

if(($imagedata[0]!==false)&&($imagedata[1]!==false)&&($imagedata[0]<=1920)&&($imagedata[1]<=1080)){

	for($y = 0; $y < $imagedata[1]; $y++) {
		for($x = 0; $x < $imagedata[0]; $x++) {
			if(imagecolorat($src_img, $x, $y) > 0x000000) {
				break 2;
			}
		}
		$b_top++;
	}

	for($y = $imagedata[1]-1; $y >= 0; $y--) {
		for($x = 0; $x < $imagedata[0]; $x++) {
			if(imagecolorat($src_img, $x, $y) > 0x000000) {
				break 2;
			}
		}
		$b_btm++;
	}

	for($x = 0; $x < $imagedata[0]; $x++) {
		for($y = 0; $y < $imagedata[1]; $y++) {
			if(imagecolorat($src_img, $x, $y) > 0x000000) {
				break 2;
			}
		}
		$b_lft++;
	}

	for($x = $imagedata[0]-1; $x >= 0; $x--) {
		for($y = 0; $y < $imagedata[1]; $y++) {
			if(imagecolorat($src_img, $x, $y) > 0x000000) {
				break 2;
			}
		}
		$b_rt++;
	}

} else {
	exit("Image contains errors or is too big to be processed");
}

$newimg = imagecreatetruecolor(imagesx($src_img)-($b_lft+$b_rt), imagesy($src_img)-($b_top+$b_btm));
imagecopy($newimg, $src_img, 0, 0, $b_lft, $b_top, imagesx($newimg), imagesy($newimg));

imagedestroy($src_img);

$imagedata[0] = imagesx($newimg);
$imagedata[1] = imagesy($newimg);

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
$dst_img = imagecreatetruecolor($width, $height);
imagealphablending($dst_img, true);
imagesavealpha($dst_img, true);
$black = imagecolorallocatealpha($dst_img, 0, 0, 0, 0);
imagefilledrectangle($dst_img, 0, 0, $width, $height, $black);


imagecopyresampled($dst_img, $newimg, $offset_w, $offset_h, 0, 0, $new_w, $new_h, $imagedata[0], $imagedata[1]);

if(($play!=0)&($width>=160)&($height>=120)){
	imagecopyresampled($dst_img, $play, ($width-100)/2, ($height-80)/2, 0, 0, 100, 80, 100, 80);
}

header("Content-type: image/jpg");
imagejpeg($dst_img, null, 100);

?>