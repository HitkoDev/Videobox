/*
	Videobox v2 - jQuery lightbox clone for displaying iframe videos
	Based on Slimbox 2.04 
		(c) 2007-2010 Christophe Beyls <http://www.digitalia.be>
		MIT-style license.
*/

(function vb($) {

	// Global variables, accessible to videobox only
	var win = $(window), options, videos, activevideo = -1, activeURL, compatibleOverlay, middle, centerWidth, centerHeight,
		ie6 = !window.XMLHttpRequest, hiddenElements = [], documentElement = document.documentElement,

	// DOM elements
	overlay, center, video, bottomContainer, bottom, caption, number, button;

	/*
		Initialization
	*/

	$(function vbll() {
		// Append the videobox HTML code at the bottom of the document
		$("body").append(
			$([
				overlay = $('<div id="vbOverlay" />').click($.vb_close)[0],
				center = $('<div id="vbCenter" />')[0],
				bottomContainer = $('<div id="vbBottomContainer" />')[0]
			]).css("display", "none")
		);

		video = $('<iframe id="vbVideo" frameborder="0" allowfullscreen="true" oallowfullscreen msallowfullscreen webkitallowfullscreen mozallowfullscreen />').appendTo(center)[0];

		bottom = $('<div id="vbBottom" />').appendTo(bottomContainer).append([
			button = $('<a id="vbCloseLink" href="#" />').click($.vb_close)[0],
			caption = $('<div id="vbCaption" />')[0],
			number = $('<div id="vbNumber" />')[0],
			$('<div style="clear: both;" />')[0]
		])[0];
	});


	/*
		API
	*/

	// Open videobox with the specified parameters
	$.videobox = function(_videos, startvideo, _options) {
		options = $.extend({
			loop: false,				// Allows to navigate between first and last videos
			overlayOpacity: 0.8,			// 1 is opaque, 0 is completely transparent (change the color in the CSS file)
			overlayFadeDuration: 400,		// Duration of the overlay fade-in and fade-out animations (in milliseconds)
			resizeDuration: 400,			// Duration of each of the box resize animations (in milliseconds)
			resizeEasing: "swing",			// "swing" is jQuery's default easing
			initialWidth: 250,			// Initial width of the box (in pixels)
			initialHeight: 250,			// Initial height of the box (in pixels)
			videoWidth: 640,			// Default width of videobox
			videoHeight: 363,			// Default height of videobox
			videoFadeDuration: 400,			// Duration of the video fade-in animation (in milliseconds)
			captionAnimationDuration: 400,		// Duration of the caption animation (in milliseconds)
			counterText: "Video {x} of {y}",	// Translate or change as you wish, or set it to false to disable counter text for video groups
			disableClose: false			// Disable manual close of Videobox
		}, _options);

		// The function is called for a single video, with URL and Title as first two arguments
		if (typeof _videos == "string") {
			_videos = [[_videos, startvideo]];
			startvideo = 0;
		}
		
		if (options.disableClose) {
			$(button).hide();
			$(button).unbind("click");
			$(overlay).unbind("click");		
			options.closeKeys = [];
		}

		middle = win.scrollTop() + (win.height() / 2);
		centerWidth = options.initialWidth;
		centerHeight = options.initialHeight;
		$(center).css({top: Math.max(0, middle - (centerHeight / 2)), width: centerWidth, height: centerHeight, marginLeft: -centerWidth/2}).show();
		compatibleOverlay = ie6 || (overlay.currentStyle && (overlay.currentStyle.position != "fixed"));
		if (compatibleOverlay) overlay.style.position = "absolute";
		$(overlay).css("opacity", options.overlayOpacity).fadeIn(options.overlayFadeDuration);
		vbposition();
		vbsetup(1);

		videos = _videos;
		options.loop = options.loop && (videos.length > 1);
		return vbchangevideo(startvideo);
	};

	// Close an open videobox
	$.vb_close = function() {
		if (activevideo >= 0) {
			vbstop();
			activevideo = -1;
			$(center).hide();
			$(overlay).stop().fadeOut(options.overlayFadeDuration, vbsetup);
		}
		
		$(button).show();
		$(button).click($.vb_close);
		$(overlay).click($.vb_close);	

		return false;
	}

	/*
		options:	Optional options object, see jQuery.videobox()
		linkMapper:	Optional function taking a link DOM element and an index as arguments and returning an array containing 2 elements:
				the video URL and the video caption (may contain HTML)
		linksFilter:	Optional function taking a link DOM element and an index as arguments and returning true if the element is part of
				the video collection that will be shown on click, false if not. "this" refers to the element that was clicked.
				This function must always return true when the DOM element argument is "this".
	*/
	$.fn.videobox = function(_options, linkMapper, linksFilter) {
		linkMapper = linkMapper || function vbl(el) {
			return [el.href, el.title];
		};

		linksFilter = linksFilter || function vbll() {
			return true;
		};

		var links = this;

		return links.unbind("click").click(function vbll() {
			// Build the list of videos that will be displayed
			var link = this, startIndex = 0, filteredLinks, i = 0, length;
			filteredLinks = $.grep(links, function(el, i) {
				return linksFilter.call(link, el, i);
			});

			// We cannot use jQuery.map() because it flattens the returned array
			for (length = filteredLinks.length; i < length; ++i) {
				if (filteredLinks[i] == link) startIndex = i;
				filteredLinks[i] = linkMapper(filteredLinks[i], i);
			}

			return $.videobox(filteredLinks, startIndex, _options);
		});
	};


	/*
		Internal functions
	*/

	function vbposition() {
		var l = win.scrollLeft(), w = win.width();
		$([center, bottomContainer]).css("left", l + (w / 2));
		if (compatibleOverlay) $(overlay).css({left: l, top: win.scrollTop(), width: w, height: win.height()});
	}

	function vbsetup(open) {
		if (open) {
			$("object").add(ie6 ? "select" : "embed").each(function(index, el) {
				hiddenElements[index] = [el, el.style.visibility];
				el.style.visibility = "hidden";
			});
		} else {
			$.each(hiddenElements, function(index, el) {
				el[0].style.visibility = el[1];
			});
			hiddenElements = [];
		}
		var fn = open ? "bind" : "unbind";
		win[fn]("scroll resize", vbposition);
	}

	function vbchangevideo(videoIndex) {
		if (videoIndex >= 0) {
			activevideo = videoIndex;
			activeURL = videos[activevideo][0];

			vbstop();

			vbanimateBox();
		}

		return false;
	}

	function vbanimateBox() {
		center.className = "";
		var width = videos[activevideo][2] || options.videoWidth;
		var height = videos[activevideo][3] || options.videoHeight;
		if (width > (win.width()-20)) width = win.width()-20;
		height = (height * width)/(videos[activevideo][2] || options.videoWidth);

		$(video).css({visibility: "hidden", display: ""});
		video.src = activeURL;
		$(video).width(width);
		$(video).height(height);
		
		$(caption).html(videos[activevideo][1] || "");
		$(number).html((((videos.length > 1) && options.counterText) || "").replace(/{x}/, activevideo + 1).replace(/{y}/, videos.length));

		centerWidth = parseInt(width)+20;
		centerHeight = parseInt(height)+20;
		var top = Math.max(0, middle - (centerHeight / 2));
		$(center).animate({height: centerHeight, top: top, width: centerWidth, marginLeft: -centerWidth/2}, options.resizeDuration, options.resizeEasing);
		$(center).queue(function vbll() {
			$(bottomContainer).css({width: centerWidth, top: top + centerHeight, marginLeft: -centerWidth/2, visibility: "hidden", display: ""});
			$(video).css({display: "none", visibility: "", opacity: ""}).fadeIn(options.videoFadeDuration, vbanimateCaption);
		});
	}

	function vbanimateCaption() {
		$(bottom).css("marginTop", -bottom.offsetHeight).animate({marginTop: 0}, options.captionAnimationDuration);
		bottomContainer.style.visibility = "";
	}

	function vbstop() {
		$([center, video,  bottom]).stop(true);
		$([video, bottomContainer]).hide();
		video.src = '';
		video.title = '';
	}

})(jQuery);

// Content player (no-lightbox) effec
var displayvideo;
var displayvideolink;
var open_frame;
jQuery(document).ready(function($) {	
	displayvideolink = function (vid, src, vwidth, vheight){
		var frame = document.getElementById('video_'+vid);
		var close = document.getElementById('close_'+vid);
		var title = document.getElementById('title_'+vid);
		var separator = document.getElementById('separator_'+vid);
		var span = frame.parentNode.parentNode.parentNode.parentNode;
		if((frame.getAttribute('style').indexOf('block')==-1)){
			if(open_frame!=null){
				displayvideolink(open_frame, '', 0, 0);
			}
			open_frame = vid;
			title.style.display = 'none';
			separator.style.display = 'none';
			frame.style.display = 'block';
			close.style.display = 'block';
			frame.parentNode.parentNode.style.display = 'block';
			frame.src = src;
			span.style.display = 'inline-block';
			$(frame).animate({height: vheight, width: vwidth}, { duration: 400, easing: 'swing', queue: false });
			span.style.styleFloat = 'right';
			span.style.cssFloat = 'right';
		} else {
			close.style.display = 'none';
			$(frame).animate({height: 0, width: 0}, { duration: 0, easing: 'swing', queue: false });
			title.style.display = 'inline';
			separator.style.display = 'inline';
			frame.src = '';
			if (document.cancelFullScreen) {
				document.cancelFullScreen();
			} else if (document.mozCancelFullScreen) {
				document.mozCancelFullScreen();
			} else if (document.webkitCancelFullScreen) {
				document.webkitCancelFullScreen();
			} else if (document.oCancelFullScreen) {
				document.oCancelFullScreen();
			} else if (document.msCancelFullScreen) {
				document.msCancelFullScreen();
			}
			frame.style.display = 'none';
			frame.parentNode.parentNode.style.display = 'none';
			span.style.display = 'inline';
			span.style.styleFloat = '';
			span.style.cssFloat = '';
			open_frame = null;
		}
	}
	
	displayvideo = function (vid, src, vwidth, vheight, twidth, theight){
		var frame = document.getElementById('video_'+vid);
		var image = document.getElementById('thumb_'+vid);
		var close = document.getElementById('close_'+vid);
		var title = document.getElementById('title_'+vid);
		if((frame.getAttribute('style').indexOf('block')==-1)){
			image.style.display = 'none';
			frame.style.display = 'block';
			frame.parentNode.style.display = 'block';
			close.style.display = 'block';
			frame.src = src;
			$(frame).animate({height: vheight, width: vwidth}, { duration: 400, easing: 'swing', queue: false });
			$(title).animate({width: vwidth}, { duration: 400, easing: 'swing', queue: false });
		} else {
			close.style.display = 'none';
			$(frame).animate({height: theight, width: twidth}, { duration: 0, easing: 'swing', queue: false });
			title.style.width = twidth+'px';
			frame.src = '';
			if (document.cancelFullScreen) {
				document.cancelFullScreen();
			} else if (document.mozCancelFullScreen) {
				document.mozCancelFullScreen();
			} else if (document.webkitCancelFullScreen) {
				document.webkitCancelFullScreen();
			} else if (document.oCancelFullScreen) {
				document.oCancelFullScreen();
			} else if (document.msCancelFullScreen) {
				document.msCancelFullScreen();
			}
			frame.style.display = 'none';
			frame.parentNode.style.display = 'none';
			image.style.display = 'block';
		}
	}
});

// AUTOLOAD CODE BLOCK (MAY BE CHANGED OR REMOVED)
jQuery(function vb($) {
$("a[rel^='videobox']").videobox({ /* Put custom options here */ }, function vbl(el) {
		return [el.href, el.getAttribute("title"), el.getAttribute("videowidth"), el.getAttribute("videoheight")];
}, function vbl(el) {
  return (this == el) || ((this.rel.length > 8) && (this.rel == el.rel));
 });
});