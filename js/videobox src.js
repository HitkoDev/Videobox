/**	
	author		HitkoDev
	copyright	Copyright (C) 2014 HitkoDev All Rights Reserved.
	@license	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
	Website		http://hitko.eu/software/videobox
	Based on Slimbox 2.04 
		(c) 2007-2010 Christophe Beyls <http://www.digitalia.be>
		MIT-style license.
*/

// Videobox player (lightbox) effect
(function vb($) {

	// Global variables, accessible to videobox only
	var win = $(window), options, videos, activevideo = -1, activeURL, compatibleOverlay, middle, centerWidth, centerHeight,
		ie6 = !window.XMLHttpRequest, hiddenElements = [], documentElement = document.documentElement,

	// DOM elements
	overlay, center, video, bottomContainer, bottom, caption, button;

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
			$('<div style="clear: both;" />')[0]
		])[0];
	});


	/*
		API
	*/

	// Open videobox with the specified parameters
	$.videobox = function(_videos, startvideo, _options) {
		options = $.extend({
			overlayOpacity: 0.8,			// 1 is opaque, 0 is completely transparent (change the color in the CSS file)
			overlayFadeDuration: 400,		// Duration of the overlay fade-in and fade-out animations (in milliseconds)
			resizeDuration: 400,			// Duration of each of the box resize animations (in milliseconds)
			resizeEasing: "swing",			// "swing" is jQuery's default easing
			videoWidth: 640,				// Default width of videobox
			videoHeight: 363,				// Default height of videobox
			videoFadeDuration: 400,			// Duration of the video fade-in animation (in milliseconds)
			captionAnimationDuration: 400,	// Duration of the caption animation (in milliseconds)
		}, _options);

		// The function is called for a single video, with URL and Title as first two arguments
		if (typeof _videos == "string") {
			_videos = [[_videos, startvideo]];
			startvideo = 0;
		}
		
		$.vbi_close();

		middle = win.scrollTop() + (win.height() / 2);
		centerWidth = 0;
		centerHeight = 0;
		$(center).css({top: Math.max(0, middle - (centerHeight / 2)), width: centerWidth, height: centerHeight, marginLeft: -centerWidth/2}).show();
		compatibleOverlay = ie6 || (overlay.currentStyle && (overlay.currentStyle.position != "fixed"));
		if (compatibleOverlay) overlay.style.position = "absolute";
		$(overlay).css("opacity", options.overlayOpacity).fadeIn(options.overlayFadeDuration);
		vbposition();
		vbsetup(1);

		videos = _videos;
		activevideo = startvideo;
		activeURL = videos[activevideo][0];
		vbstop();
		vbanimateBox();
		return false;
	};

	// Close an open videobox
	$.vb_close = function() {
		if (activevideo >= 0) {
			vbstop();
			activevideo = -1;
			$(center).hide();
			$(overlay).stop().fadeOut(options.overlayFadeDuration, vbsetup);
		}
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

	function vbanimateBox() {
		center.className = "";
		var width = videos[activevideo][2] || options.videoWidth;
		var height = videos[activevideo][3] || options.videoHeight;
		if (width > (win.width()-20)) width = win.width()-20;
		height = (height * width)/(videos[activevideo][2] || options.videoWidth);
		if (height > (win.height()-20)) height = win.height()-20;

		$(video).css({visibility: "hidden", display: ""});
		video.src = activeURL;
		$(video).width(width);
		$(video).height(height);
		
		$(caption).html(videos[activevideo][1] || "");

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

// Inline player (no-lightbox) effect
(function vbi($) {

	// Global variables, accessible to vbinline only
	var win = $(window), options, videos, activevideo = -1, activeURL,
		ie6 = !window.XMLHttpRequest, hiddenElements = [],

	// DOM elements
	hidden, video, caption, button, player, videoCont, hiddenVideo;
	
	hiddenVideo = '';

	/*
		Initialization
	*/

	$(function vbill() {
		// Append the vbinline HTML code at the bottom of the document
		$("body").append([
			hidden = $('<div id="vbiHidden" />').css("display", "none")[0]
		]);
		
		player = $('<div id="vbiPlayer" />').appendTo(hidden).append([
			button = $('<a id="vbiCloseLink" href="#" />').click($.vbi_close)[0],
			videoCont = $('<div id="vbiVideoCont" />')[0],
			caption = $('<div id="vbiCaption" />')[0]
		])[0];
		
		video = $('<iframe id="vbiVideo" frameborder="0" allowfullscreen="true" oallowfullscreen msallowfullscreen webkitallowfullscreen mozallowfullscreen />').appendTo(videoCont)[0];

	});


	/*
		API
	*/

	// Open vbinline with the specified parameters
	$.vbinline = function(_videos, startvideo, _options){
		options = $.extend({
			resizeDuration: 400,		// Duration of each of the box resize animations (in milliseconds)
			resizeEasing: "swing",		// "swing" is jQuery's default easing
			videoWidth: 640,			// Default width of vbinline player
			videoHeight: 363,			// Default height of vbinline player
		}, _options);

		// The function is called for a single video, with URL and Title as first two arguments
		if (typeof _videos == "string") {
			_videos = [[_videos, startvideo]];
			startvideo = 0;
		}
		
		$.vb_close();
		
		vbisetup(1);

		videos = _videos;
		return vbichangevideo(startvideo);
	};

	// Close an open vbinline player
	$.vbi_close = function() {
		if (activevideo >= 0) {
			vbistop();
			activevideo = -1;
		}
		
		$(button).show();
		$(button).click($.vbi_close);

		return false;
	}

	/*
		options:	Optional options object, see jQuery.vbinline()
		linkMapper:	Optional function taking a link DOM element and an index as arguments and returning an array containing 2 elements:
				the video URL and the video caption (may contain HTML)
		linksFilter:	Optional function taking a link DOM element and an index as arguments and returning true if the element is part of
				the video collection that will be shown on click, false if not. "this" refers to the element that was clicked.
				This function must always return true when the DOM element argument is "this".
	*/
	$.fn.vbinline = function(_options, linkMapper, linksFilter) {
		linkMapper = linkMapper || function vbil(el) {
			return [el.href, el.title, el.videowidth, el.videoheight, el];
		};

		linksFilter = linksFilter || function vbill() {
			return true;
		};

		var links = this;

		return links.unbind("click").click(function vbill() {
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

			return $.vbinline(filteredLinks, startIndex, _options);
		});
	};


	/*
		Internal functions
	*/

	function vbisetup(open) {
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
	}

	function vbichangevideo(videoIndex) {
		if (videoIndex >= 0) {
			activevideo = videoIndex;
			activeURL = videos[activevideo][0];

			vbistop();

			vbianimateBox();
		}

		return false;
	}

	function vbianimateBox() {
		
		var initalW = videos[activevideo][4].width();
		var initalH = videos[activevideo][4].height();
		player.setAttribute('style', videos[activevideo][5]);
		$(player).width(initalW);
		$(caption).html(videos[activevideo][1] || "");
		hiddenVideo = videos[activevideo][4];
		$(player).insertAfter(videos[activevideo][4]);
		var captionH = $(caption).outerHeight();
		
		var width = videos[activevideo][2] || options.videoWidth;
		var height = videos[activevideo][3] || options.videoHeight;
		if (width > (win.width()-10)) width = win.width()-10;
		height = (height * width)/(videos[activevideo][2] || options.videoWidth);
		if (height > (win.height()-10)) height = win.height()-10;

		$(video).css({display: ""});
		video.src = activeURL;
		initalH -= captionH;
		initalH -= 10;
		$(video).height(initalH);
		
		$(caption).html(videos[activevideo][1] || "");
		
		videos[activevideo][4].css({display: "none"});
		
		playerWidth = parseInt(width)+10;
		videoHeight = parseInt(height);
		
		$(player).animate({width: playerWidth}, options.resizeDuration, options.resizeEasing);
		$(video).animate({height: videoHeight}, options.resizeDuration, options.resizeEasing);
		
	}

	function vbistop() {
		if(hiddenVideo!='') hiddenVideo.css({display: ""});
		hiddenVideo = '';
		$(player).appendTo(hidden);
		$([video]).stop(true);
		$([video]).hide();
		video.src = '';
		video.title = '';
	}

})(jQuery);

jQuery(function vb($) {
	
	// AUTOLOAD FOR VIDEOBOX
	$("a[rel^='videobox']").videobox({ /* Put custom options here */ }, function vbl(el) {
		return [el.href, el.getAttribute("title"), el.getAttribute("videowidth"), el.getAttribute("videoheight")];
	}, function vbl(el) {
		return (this == el) || ((this.rel.length > 8) && (this.rel == el.rel));
	});
	
	// AUTOLOAD FOR INLINE PLAYER
	$("a[rel^='vbinline']").vbinline({ /* Put custom options here */ }, function vbil(el) {
		return [el.href, el.getAttribute("title"), el.getAttribute("videowidth"), el.getAttribute("videoheight"), $(el)];
	}, function vbil(el) {
		return (this == el) || ((this.rel.length > 8) && (this.rel == el.rel));
	});
	
	// START LINKED PLAYER
	$(document).ready(function() {
		
		// Add context menu for lightbox / inline videos
		$.each($("[id^='_vbVideo_']"), function(i, v){
			$(v).contextMenu("context-menu", {
				"Link for this video": {
					click: function(){
						$.cfr("<div class=\"input-container\"><input readonly onclick=\"select()\" value=\"" + document.location.href.match(/(^[^#]*)/)[0] + "#" + v.id.substring(1) + "\" /></div>", {});
					},
					klass: "menu-item"
				}
			});
		});
		
		jumpHash();
	});
	
	// Open the linked player
	function jumpHash(){
		if(window.location.hash){
			el = $('#_' + window.location.hash.substring(1))[0];
			if(el && (el.rel.indexOf('videobox') == 0 || el.rel.indexOf('vbinline') == 0)){
				el.click();
			}
		}
	}
	
	if("onhashchange" in window){
		$(window).bind('hashchange', jumpHash);
	}
	
});