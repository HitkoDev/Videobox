/**	
 *	@author		HitkoDev http://hitko.eu/videobox
 *	@copyright	Copyright (C) 2015 HitkoDev All Rights Reserved.
 *	@license	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

(function($) {
	
	var videos, activeURL, activeVideo, center, content, responsive, video, bottomContainer, bottom, wrap, caption, button, closeText, win = $(window), options, open = false,

	defaults = {
		videoWidth: 720,		//	default player width
		videoHeight: 405,		//	default player height
		closeText: 'close',		//	text for the close button
		padding: 30,			//	player padding
	};
	
	/**
	 *	Opens Videobox
	 *
	 *	@param _videos - array of videos as [player_url, title, player_width, player_height]
	 *	@param startVideo - index of the curent video
	 *	@param _options - Videobox options, see defaults for details
	 *	@param origin - {x, y} window coordinates where the pop-up should appear from (default is window center)
	 */
	$.videobox = function(_videos, startVideo, _options, origin) {
		options = $.extend(defaults, _options);
		setup(origin); 
		videos = _videos;
		changeVideo(startVideo);
		return false;
	}; 
	
	/**
	 *	Closes the Videobox
	 */
	$.vbClose = function() {
		stop();
		$([wrap, bottomContainer, overlay]).toggleClass('visible', false);
		$(wrap).css({
			top: 0,
			left: 0
		});
		if(activeVideo >= 0) activeVideo = -1;
		return false;
	};

	/**
	 *	Maps Videobox to elements
	 *
	 *	@param this - list of elements, for example $(selector)
	 *	@param _options - Videobox options, see defaults for details
	 *	@callback linkMapper - receives an element, returns array of video attributes [player_url, title, player_width, player_height]
	 */
	$.fn.videobox = function(_options, linkMapper) {
		linkMapper = linkMapper || function(el) {
			return [el.getAttribute("href"), el.getAttribute("title"), el.getAttribute("data-videowidth"), el.getAttribute("data-videoheight")];
		};
		
		var links = this;
		
		return links.unbind("click").click(function(evt) {
			
			var link = this, startIndex = 0, mappedLinks = links.slice();
			
			for(var i = 0; i < mappedLinks.length; i++){
				if(mappedLinks[i] == link) startIndex = i;
				mappedLinks[i] = linkMapper(mappedLinks[i], i);
			}

			return $.videobox(mappedLinks, startIndex, _options, {
				x: evt.clientX,
				y: evt.clientY,
			});
		});
		return false;
	};
	
	// append Videobox elements
	$(function() {
		$("body").append(
			$([
				overlay = $('<div id="vbOverlay" />').click($.vbClose)[0],
				wrap = $('<div id="vbWrap" />')[0]
			])
		); 
		center = $('<div id="vbCenter" />').appendTo(wrap)[0];
		content = $('<div id="vbContent" />').appendTo(center).append([
			responsive = $('<div id="vbResponsive" />')[0],
			bottomContainer = $('<div id="vbBottomContainer" />').on('webkitTransitionEnd transitionend mozTransitionEnd oTransitionEnd', showVideo)[0],
		])[0];
		video = $('<iframe id="vbVideo" frameborder="0" allowfullscreen="true" oallowfullscreen msallowfullscreen webkitallowfullscreen mozallowfullscreen />').css('display', 'none').appendTo(responsive)[0];
		bottom = $('<div id="vbBottom" />').appendTo(bottomContainer).append([
			button = $('<a id="vbCloseLink" href="#" ><span id="vbCloseText">' + defaults.closeText + '</span><i class="vb-icon-close"></i></a>').click($.vbClose)[0], 
			caption = $('<span id="vbCaption" />')[0]
		])[0];
		closeText = $(bottom).find('#vbCloseText')[0];
	});

	function setup(origin) {
		$(closeText).html(options.closeText);
		$(center).css({
			top: origin ? -($(wrap).innerHeight()/2-origin.y) : 0,
			left: origin ? -($(wrap).innerWidth()/2-origin.x) : 0,
			'max-width': '',
		});
	}

	function changeVideo(i) {
		if(i >= 0){
			activeVideo = i;
			activeURL = videos[i][0];
			stop();
			$(caption).html(videos[activeVideo][1] || "");
			open = true;
			animateBox();
		}
		return false;
	}

	function animateBox(){
		var width = parseInt(videos[activeVideo][2] || options.videoWidth);
		var height = parseInt(videos[activeVideo][3] || options.videoHeight);
		
		// move wrapper to the visible area
		$(wrap).css({
			top: win.scrollTop(),
			left: win.scrollLeft()
		});
		
		// downsize player if needed
		if(width + 2*options.padding > $(wrap).innerWidth()) width = $(wrap).innerWidth() - 2*options.padding;
		height = (height * width)/(videos[activeVideo][2] || options.videoWidth);
		if(height + 2*options.padding > $(wrap).innerHeight()) height = $(wrap).innerHeight() - 2*options.padding;
		
		var ratio = (height*100)/width;
		$(responsive).css('padding-bottom', ratio + '%');					// set player ratio
		$([wrap, bottomContainer, overlay]).toggleClass('visible', true);	// show player
		$(wrap).toggleClass('animating', true);
		setTimeout(function(){
			$(center).css({
				top: 0,
				left: 0,
				'max-width': parseInt(videos[activeVideo][2] || options.videoWidth) + 2*options.padding,
				padding: options.padding,
			});
		}, 10);
	}

	function showVideo(){
		if(!open || $(video).attr('src') != '') return;
		$(video).show();
		video.src = activeURL;
		$(wrap).toggleClass('animating', false);
	}

	function stop() {
		open = false;
		video.src = "";
		$(video).hide();
	}

	win.on("resize", function() {
		if(activeVideo >= 0) animateBox();
	});

	// AUTOLOAD CODE BLOCK (MAY BE CHANGED OR REMOVED)
	$("a[rel^='videobox']").videobox({ /* Put custom options here */ });
	
})(jQuery);