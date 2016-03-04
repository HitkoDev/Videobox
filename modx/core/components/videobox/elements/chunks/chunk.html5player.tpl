<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<title>[[+title]]</title>
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link href="[[+assets]]videojs/video-js.min.css" rel="stylesheet" type="text/css">
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

	<video id="vid1" class="video-js [[+type]] vjs-default-skin" controls="controls" preload="auto" poster="[[+poster]]" data-setup='{ starttime: 15 }'>
		[[+sources]]
		<p class="vjs-no-js">To view this video please enable JavaScript, and consider upgrading to a web browser that <a href="http://videojs.com/html5-video-support/" target="_blank">supports HTML5 video</a></p>
	</video>

	<script src="[[+assets]]videojs/video.min.js"></script>
	<script>
		videojs.options.flash.swf = '[[+assets]]/videojs/video-js.swf';
		
		var vid = videojs("vid1");

		var start = [[+start]];
		vid.on('loadedmetadata', function(){
			if(start > 0) vid.currentTime(start);
			var auto = [[+auto]];
			if(auto) vid.play();
		});
		
		var end = [[+end]];
		var onEnd = function(){
			if(vid.currentTime() >= end) {
				vid.pause();
				vid.off('timeupdate', onEnd);
			}
		};
		if(end > 0) vid.on('timeupdate', onEnd);
	</script>
	
</body>
</html>