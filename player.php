<?php
/*------------------------------------------------------------------------
# plg_videobox - Videobox
# ------------------------------------------------------------------------
# author    HitkoDev
# copyright Copyright (C) 2012 hitko.si. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://hitko.eu/software/videobox
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
$poster = '';
if(isset($_GET['poster'])) $poster = $_GET['poster'];

if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']!='off' && $_SERVER['HTTPS']!=null){
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
$source = str_replace(' ', '%20', $videoinfo['dirname'].'/'.$videoinfo['filename']);
if(strpos($videotypes, $videoinfo['extension'])===false){
	$sources = '[ 
		[".mp3", "audio/mpeg"],
		[".oga", "audio/ogg"],
		[".webma", "audio/webm"],
		[".wav", "audio/wav"],
		[".m4a", "audio/mp4"]
	]';
} else {
	$sources = '[
		[".webm", "video/webm"],
		[".ogv", "video/ogg"],
		[".mp4", "video/mp4"],
		[".m4v", "video/mp4"]
	]';
}
$auto = '';
if($autoplay=='1'){
	$auto = 'autoplay';
}
$ended = '';
$button = '';
$pp = '';

$output = '<!doctype html>
<html>
	<head>
		<script type="text/javascript" src="js/jquery.min.js"></script>
		<script type="text/javascript" src="js/video.js"></script>
		<script type="text/javascript">
			var sou = "";
			var sources = '.$sources.';
			var n = 0;
			var str = "";
			var onl = false;
			
			function loadPlayer(){
				var video = videojs(\'vb-player\', {}, function(){});
				video.on("loadedmetadata", function(){
					this.currentTime('.$start.');
				});
			}
			
			function incN(){
				str = \'<video '.$auto.' id="vb-player" class="video-js vjs-default-skin" width="100%" poster="'.$poster.'" height="100%" controls preload="none" >\' + sou + \'</video>\';
				n++;
				if(onl){
					document.body.innerHTML = str;
					if(n==sources.length) loadPlayer();
				}
			}
			
			function urlExists(source){
				$.ajax({
					type: "HEAD",
					url: "'.$source.'" + source[0],
					crossDomain: true,
					success: function () {
						sou += "<source src=\"'.$source.'" + source[0] + "\" type=\"" + source[1] + "\" />";
						incN();
					},
					error: function () {
						incN();
					}
				});
			}
			
			for (key in sources){
				urlExists(sources[key]);
			}
		</script>
		<link href="css/video-js.min.css" rel="stylesheet" type="text/css">
		<script>videojs.options.flash.swf = "video-js.swf";</script>
	</head>
	<body onload="document.body.innerHTML = str; onl = true; if(n==sources.length) loadPlayer();"></body>
</html>';

echo $output;

?>