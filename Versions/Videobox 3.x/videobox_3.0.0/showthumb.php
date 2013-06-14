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
		} elseif(strpos($video[0], 'youku')!==false) {
			preg_match('/id_(.*?).html/isU', $img, $v_urls);
			$img = $v_urls[1];
		} else { 
			$v_urls = explode ('/', $img);
			$v_urls = explode ('#', $v_urls[count($v_urls)-1]);
			$v_urls = explode ('&', $v_urls[0]);
			$img = $v_urls[0];
		}
	}
	
	if(@getimagesize($url['dirname'].'/thumbs/'.$img.'png')){
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
			//var_dump($hash);
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

if($youku==false){
	$imagedata = getimagesize($img);

	if(!$imagedata[0])
	{
		exit();
	}
} else {
	$src_img = imagecreatefromjpeg($img);
	$imagedata[0] = imagesx($src_img);
	$imagedata[1] = imagesy($src_img);
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
$dst_img = imagecreatetruecolor($width, $height);
imagealphablending($dst_img, true);
imagesavealpha($dst_img, true);
$black = imagecolorallocatealpha($dst_img, 0, 0, 0, 0);
imagefilledrectangle($dst_img, 0, 0, $width, $height, $black);

if($youku==false){
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
}

imagecopyresampled($dst_img, $src_img, $offset_w, $offset_h, 0, 0, $new_w, $new_h, $imagedata[0], $imagedata[1]);

if(($play!=0)&($width>=160)&($height>=120)){
	imagecopyresampled($dst_img, $play, ($width-100)/2, ($height-80)/2, 0, 0, 100, 80, 100, 80);
}

header("Content-type: image/jpg");
imagejpeg($dst_img, null, 100);

?>