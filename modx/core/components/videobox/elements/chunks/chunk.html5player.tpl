<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<title>[[+title]]</title>
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<script src="/video.js/build/temp/ie8/videojs-ie8.js"></script>
	<link href="/video.js/build/temp/video-js.css" rel="stylesheet" type="text/css">
	<script src="/video.js/build/temp/video.js"></script>
	<script>
		videojs.options.flash.swf = '/video.js/build/temp/video-js.swf';
	</script>

	<style>
		body, html,
		video, #vid1 {
			margin: 0;
			padding: 0;
			position: fixed;
			top: 0;
			bottom: 0;
			left: 0;
			right: 0;
			width: 100%;
			height: 100%;
		}
	</style>

</head>
<body>

	<video id="vid1" class="video-js vjs-default-skin" controls="controls" preload="auto" poster="[[+poster]]" data-setup='{}'>
		[[+sources]]
		<p class="vjs-no-js">To view this video please enable JavaScript, and consider upgrading to a web browser that <a href="http://videojs.com/html5-video-support/" target="_blank">supports HTML5 video</a></p>
	</video>
	
</body>
</html>