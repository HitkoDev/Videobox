/**	
	author		HitkoDev
	copyright	Copyright (C) 2015 HitkoDev All Rights Reserved.
	@license	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
	Website		http://hitko.eu/videobox
	Based on Slimbox 2.05
		(c) 2007-2013 Christophe Beyls <http://www.digitalia.be>
		MIT-style license.
*/

(function($) {
	
	var videos, activeURL, activeVideo, center, content, responsive, video, bottomContainer, bottom, caption, button, closeText, win = $(window), options, open = false,

	defaults = {
		videoWidth: 660,
		videoHeight: 383,
		closeText: 'close',
		padding: 30,
	};

	$(function() {
		$("body").append(
			$([
				overlay = $('<div id="vbOverlay" />').click($.vb_close)[0],
				sizer = $('<div id="vbSizer" />')[0]
			])
		); 
		center = $('<div id="vbCenter" />').appendTo(sizer)[0];
		content = $('<div id="vbContent" />').appendTo(center).append([
			responsive = $('<div id="vbResponsive" />')[0],
			bottomContainer = $('<div id="vbBottomContainer" />').on('webkitTransitionEnd transitionend mozTransitionEnd oTransitionEnd', showVideo)[0],
		])[0];
		video = $('<iframe id="vbVideo" frameborder="0" allowfullscreen="true" oallowfullscreen msallowfullscreen webkitallowfullscreen mozallowfullscreen />').css('display', 'none').appendTo(responsive)[0];
		bottom = $('<div id="vbBottom" />').appendTo(bottomContainer).append([
			button = $('<a id="vbCloseLink" href="#" ><span id="vbCloseText">' + defaults.closeText + '</span><i class="vb-icon-close"></i></a>').click($.vb_close)[0], 
			caption = $('<span id="vbCaption" />')[0]
		])[0];
		closeText = $(bottom).find('#vbCloseText')[0];
	});

	$.videobox = function(_videos, startVideo, _options) {
		options = $.extend(defaults, _options);
		
		if(typeof _videos == "string"){
			_videos = [[_videos, startVideo]];
			startVideo = 0;
		}
		
		setup(); 
		
		videos = _videos;
		changeVideo(startVideo);
		return false;
	}; 

	$.fn.videobox = function(_options, linkMapper, linksFilter) {
		linkMapper = linkMapper || function(el) {
			return [el.getAttribute("href"), el.getAttribute("title"), el.getAttribute("data-videowidth"), el.getAttribute("data-videoheight")];
		};

		linksFilter = linksFilter || function() {
			return true
		};
		
		var links = this;
		return links.unbind("click").click(function() {
			
			var link = this, startIndex = 0, filteredLinks;
			filteredLinks = $.grep(links, function(el, i) {
				return linksFilter.call(link, el, i);
			});
			
			for(var i = 0; i < filteredLinks.length; i++){
				if(filteredLinks[i] == link) startIndex = i;
				filteredLinks[i] = linkMapper(filteredLinks[i], i);
			}

			return $.videobox(filteredLinks, startIndex, _options);
		});
		return false;
	};

	$.vb_close = function() {
		stop();
		$([sizer, bottomContainer, overlay]).toggleClass('visible', false);
		$(sizer).css({
			width: '',
			top: 0,
			left: 0,
			right: 0
		});
		if(activeVideo >= 0) activeVideo = -1;
		return false;
	};

	function setup() {
		$(closeText).html(options.closeText);
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
		$(sizer).css({
			top: win.scrollTop(),
			left: win.scrollLeft(),
			right: -win.scrollLeft()
		});
		
		if(width + 2*options.padding > $(overlay).innerWidth()) width = $(overlay).innerWidth() - 2*options.padding;
		height = (height * width)/(videos[activeVideo][2] || options.videoWidth);
		if(height + 2*options.padding > $(overlay).innerHeight()) height = $(overlay).innerHeight() - 2*options.padding;
		
		var ratio = (height*100)/width;
		$(responsive).css('padding-bottom', ratio + '%');
		$([sizer, bottomContainer, overlay]).toggleClass('visible', true);
		$(sizer).css({
			width: parseInt(videos[activeVideo][2] || options.videoWidth) + 2*options.padding,
			padding: options.padding,
		});
	}

	function showVideo(){
		if(!open || $(video).attr('src') != '') return;
		$(video).show();
		video.src = activeURL;
	}

	function stop() {
		open = false;
		video.src = "";
		$(video).hide();
	}

	$(window).on("resize", function() {
		if(activeVideo >= 0) animateBox();
	});

	// AUTOLOAD CODE BLOCK (MAY BE CHANGED OR REMOVED)
	$("a[rel^='videobox']").videobox({ /* Put custom options here */ }, null, function vbl(el) {
		return (this == el) || ((this.rel.length > 8) && (this.rel == el.rel));
	});
	
})(jQuery);