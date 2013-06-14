<?php
/*------------------------------------------------------------------------
# plg_videobox - Videobox
# ------------------------------------------------------------------------
# author    HiTKO
# copyright Copyright (C) 2012 hitko.si. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://hitko.si
-------------------------------------------------------------------------*/
// This file MUST be directly accessible because it provides the palyer for HTML5 media which is inserted in iframe on the page
// It doesn't connect with any core files, exstensions, databases or anything. It recieves URL of the media file to be inserted and creates a player that can be inserted in an iframe later on
// defined( '_JEXEC' ) or die( 'Restricted access' ); 

if($_GET['video'] == ""){
    exit("No video!");
}

$video = rawurldecode($_GET['video']);
$start = 0;
if(isset($_GET['start'])) $start = $_GET['start'];
$autoplay = 0;
if(isset($_GET['autoplay'])) $autoplay = $_GET['autoplay'];

if(($SERVER_['HTTPS']!='off')&&($SERVER_['HTTPS']!=null)){
	$protocol = 'https://';
} else {
	$protocol = 'http://';
}

$path = $_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];

$http_root = $protocol.substr($path, 0, -34);

$html5extensions = 'mp4,ogv,webm,m4v,oga,mp3,m4a,webma,wav';
$videotypes = 'mp4,ogv,webm,m4v';

$videoinfo = pathinfo($video);
if(strpos($html5extensions, $videoinfo['extension'])===false){
	exit("Unsupported media type!");
}
$source = $videoinfo['dirname'].'/'.$videoinfo['filename'];
if(strpos($videotypes, $videoinfo['extension'])===false){
	$sources = '
		<source src="'.$source.'.oga" type="audio/ogg">
		<source src="'.$source.'.mp3" type="audio/mpeg">
		<source src="'.$source.'.wav" type="audio/wav">
		<source src="'.$source.'.m4a" type="audio/mp4">
		<source src="'.$source.'.webma" type="audio/webm">
	';
} else {
	$sources = '
		<source src="'.$source.'.webm" type="video/webm">
		<source src="'.$source.'.ogv" type="video/ogg">
		<source src="'.$source.'.mp4" type="video/mp4">
		<source src="'.$source.'.m4v" type="video/mp4">
	';
}
$auto = '';
if($autoplay=='1'){
	$auto = ' autoplay="autoplay"';
}

$poster = $http_root.'/plugins/system/videobox/showthumb.php?img='.$source.'.'.$videoinfo['extension'].'&amp;width=640&amp;height=363';

$output = '<html>
	<head>
		<style type="text/css">
			html, body {margin: 0; padding: 0; background: #000;}
		</style>
		<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
		<script type="text/javascript">jQuery.noConflict();</script>
		<script src="http://api.html5media.info/1.1.5/html5media.min.js"></script>
	</head>
	<body>
		<video controls="controls"'.$auto.' poster="'.$poster.'" id="vb_HTML5_video" style="display: block; background: #000; width: 100%; height: 100%">
			'.$sources.'
		</video>
		<script type="text/javascript">
			try {
				document.getElementById(\'vb_HTML5_video\').addEventListener(\'loadedmetadata\', function load(event){
					document.getElementById(\'vb_HTML5_video\').currentTime = '.(int)$start.';
					
				}, false);
			} catch(err) {
			
			}
		</script>
	</body>
</html>';

echo $output;

?>