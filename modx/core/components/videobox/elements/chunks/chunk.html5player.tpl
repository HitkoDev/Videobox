<!DOCTYPE html>
<html class="vb-html5-player">
<head>
	<meta charset="utf-8" />
	<title>[[+title]]</title>
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link href="[[+assets]]css/videobox.min.css" rel="stylesheet" type="text/css">
</head>
<body class="vb-html5-player">

	<video id="vb-html5-video" class="video-js [[+type]] vjs-default-skin" controls="controls" preload="auto" poster="[[+poster]]" data-setup='{}'>
		[[+sources]]
		<p class="vjs-no-js">To view this video please enable JavaScript, and consider upgrading to a web browser that <a href="http://videojs.com/html5-video-support/" target="_blank">supports HTML5 video</a></p>
	</video>

	<script src="[[+assets]]video-js/video.min.js"></script>
	<script>
		videojs.options.flash.swf = '[[+assets]]/video-js/video-js.swf';
		
		var vid = videojs("vb-html5-video");

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