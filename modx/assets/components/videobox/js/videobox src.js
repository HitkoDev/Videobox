/**	
 *	@author		HitkoDev http://hitko.eu/videobox
 *	@copyright	Copyright (C) 2015 HitkoDev All Rights Reserved.
 *	@license	http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 *
 *	This program is free software: you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License as published by
 *	the Free Software Foundation, either version 3 of the License, or
 *	any later version.
 *
 *	This program is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *	GNU General Public License for more details.
 *
 *	You should have received a copy of the GNU General Public License
 *	along with this program. If not, see <http://www.gnu.org/licenses/>
 */

(function($) {
	
	var videos, activeURL, activeVideo, win = $(window), open = false, 
	wrap, center, content, responsive, video, bottomContainer, bottom, caption, button, closeText,

	options = defaults = {
		videoWidth: 720,		//	default player width
		videoHeight: 405,		//	default player height
		closeText: 'Close',		//	text for the close button
		padding: 30,			//	player padding
	};
	
	/**
	 *	Opens Videobox
	 *
	 *	@param _videos - array of videos as [player_url, title, player_width, player_height]
	 *	@param startVideo - index of the curent video
	 *	@param _options - Videobox options, see defaults for details
	 *	@param origin - {x, y, w} window coordinates where the pop-up should appear from (default is window center) and width (default 15%)
	 */
	$.videobox = function(_videos, startVideo, _options, origin) {
		options = $.extend(defaults, _options);
		$.vbiClose();
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
		$(wrap).toggleClass('animating', false);
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
			
			var link = this, startIndex = 0, mappedLinks = links.slice(), target = $($(this).find($(this).attr("data-target"))[0] || this);
			
			for(var i = 0; i < mappedLinks.length; i++){
				if(mappedLinks[i] == link) startIndex = i;
				mappedLinks[i] = linkMapper(mappedLinks[i], i);
			}
			
			target.toggleClass('vb_line_fix', true);		//	fix multi-line targets
			var origin = {
				x: target.offset().left - win.scrollLeft() + $(target).innerWidth()/2,
				y: target.offset().top - win.scrollTop() + $(target).innerHeight()/2,
				w: target.innerWidth(),
			};
			target.toggleClass('vb_line_fix', false);
			
			return $.videobox(mappedLinks, startIndex, _options, origin);
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
	
	// set initial pop-up parameters
	function setup(origin) {
		$(closeText).html(options.closeText);
		$(center).css({
			top: origin ? -($(wrap).innerHeight()/2-origin.y) : 0,
			left: origin ? -($(wrap).innerWidth()/2-origin.x) : 0,
			padding: options.padding,
			'max-width': origin ? origin.w+2*options.padding : '',
		});
	}

	function changeVideo(i) {
		if(i >= 0 && i < videos.length){
			activeVideo = i;
			activeURL = videos[i][0];
			stop();
			$(caption).html(videos[activeVideo][1] || "");
			
			setPlayer();
			
			// animate player
			open = true;
			$([wrap, bottomContainer, overlay]).toggleClass('visible', true);
			$(wrap).toggleClass('animating', true);
			setTimeout(function(){		// add some delay to let FF trigger transitions
				$(center).css({
					top: 0,
					left: 0,
					'max-width': parseInt(videos[activeVideo][2] || options.videoWidth) + 2*options.padding,
				});
			}, 10);
			
		}
		return false;
	}
	
	// set player
	function setPlayer(){
		var width = parseInt(videos[activeVideo][2] || options.videoWidth);
		var height = parseInt(videos[activeVideo][3] || options.videoHeight);
		
		// move player to the visible area
		$(wrap).css({
			top: win.scrollTop(),
			left: win.scrollLeft()
		});
		
		// downsize player if needed
		if(width + 2*options.padding > $(wrap).innerWidth()) width = $(wrap).innerWidth() - 2*options.padding;
		height = (height * width)/(videos[activeVideo][2] || options.videoWidth);
		if(height + 2*options.padding > $(wrap).innerHeight()) height = $(wrap).innerHeight() - 2*options.padding;
		
		// set ratio
		var ratio = (height*100)/width;
		$(responsive).css('padding-bottom', ratio + '%');
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
		if(activeVideo >= 0) setPlayer();
	});

	// AUTOLOAD CODE BLOCK (MAY BE CHANGED OR REMOVED)
	$("a[rel^='videobox']").videobox({ /* Put custom options here */ });
	
})(jQuery);

(function($) {
	
	var videos, activeURL, activeVideo, win = $(window), open = false, hidden = [], hvt = true, svt = false,
	wrap, responsive, video, caption, button, initialised = false,

	options = defaults = {
		videoWidth: 720,		//	default player width
		videoHeight: 405,		//	default player height
		closeText: 'Close',		//	text for the close button
		padding: 30,			//	player padding
		baseWidth: 200,			//	initial player width
		baseHeight: 150,		//	initial player height
		target: ''				//	empty target
	};
	
	/**
	 *	Opens Videobox
	 *
	 *	@param _videos - array of videos as [player_url, title, player_width, player_height]
	 *	@param startVideo - index of the curent video
	 *	@param _options - Videobox options, see defaults for details
	 *	@param origin - {x, y, w} window coordinates where the pop-up should appear from (default is window center) and width (default 15%)
	 */
	$.vbinline = function(_videos, startVideo, _options, target) {
		options = $.extend(defaults, _options);
		$.vbClose();
		videos = _videos;
		activeVideo = startVideo;
		svt = true;
		stop();
		return false;
	}; 
	
	/**
	 *	Closes the Videobox
	 */
	$.vbiClose = function(){
		if(activeVideo >= 0) activeVideo = -1;
		stop();
		return false;
	};

	/**
	 *	Maps Videobox to elements
	 *
	 *	@param this - list of elements, for example $(selector)
	 *	@param _options - Videobox options, see defaults for details
	 *	@callback linkMapper - receives an element, returns array of video attributes [player_url, title, player_width, player_height]
	 */
	$.fn.vbinline = function(_options, linkMapper) {
		linkMapper = linkMapper || function(el) {
			return [el.getAttribute("href"), el.getAttribute("title"), el.getAttribute("data-videowidth"), el.getAttribute("data-videoheight"), el.getAttribute("data-style"), el.getAttribute("data-class")];
		};
		
		var links = this;
		
		return links.unbind("click").click(function(evt) {
			
			var link = this, startIndex = 0, mappedLinks = links.slice(), target = $($(link).find($(link).attr("data-target"))[0] || link);
			
			for(var i = 0; i < mappedLinks.length; i++){
				if(mappedLinks[i] == link) startIndex = i;
				mappedLinks[i] = linkMapper(mappedLinks[i], i);
			}
			
			_options = $.extend(_options, {
				target: link,
				baseWidth: target.width(),
				baseHeight: target.height(),
			});
			
			return $.vbinline(mappedLinks, startIndex, _options);
		});
		return false;
	};
	
	// create vbInline elements
	$(function() {
		wrap = $('<div id="vbiWrap" />').append([
			responsive = $('<div id="vbiResponsive" />').on('webkitTransitionEnd transitionend mozTransitionEnd oTransitionEnd', showVideo)[0],
			caption = $('<span class="vb_video_title"></span>')[0],
			button = $('<div id="vbiClose"><i class="vb-icon-circle-close-invert"></i></div>').click($.vbiClose)[0],
		]).on('webkitTransitionEnd transitionend mozTransitionEnd oTransitionEnd', hideVideo)[0];
		video = $('<iframe id="vbiVideo" frameborder="0" allowfullscreen="true" oallowfullscreen msallowfullscreen webkitallowfullscreen mozallowfullscreen />').css('display', 'none').appendTo(responsive)[0];
	});
	
	// set player parameters
	function setup() {
		$(options.target).after(wrap);
		$(responsive).css('padding-bottom', (options.baseHeight*100)/options.baseWidth + '%');
		$(wrap).css('max-width', options.baseWidth);
		$(options.target).hide();
		hidden = [options.target];
	}

	function changeVideo() {
		if(activeVideo >= 0 && activeVideo < videos.length){
			activeURL = videos[activeVideo][0];
			$(wrap).attr('style', videos[activeVideo][4]);
			$(wrap).attr('class', videos[activeVideo][5]);
			
			setup();
			
			$(caption).html(videos[activeVideo][1] || "");
			$(wrap).toggleClass('visible', true);
			
			open = true;
			
			setTimeout(function(){		// add some delay to let FF trigger transitions
			
				$(wrap).css('max-width', parseInt(videos[activeVideo][2] || options.videoWidth) + 2*options.padding);
				var width = parseInt(videos[activeVideo][2] || options.videoWidth);
				var height = parseInt(videos[activeVideo][3] || options.videoHeight);
				$(responsive).css('padding-bottom', (height*100)/width + '%');
				
			}, 10);
		}
	}

	function showVideo(){
		if(!open || $(video).attr('src') != '') return;
		$(video).show();
		video.src = activeURL;
		$(wrap).toggleClass('animating', false);
	}
	
	function hideVideo(){
		if(hvt) return;
		hvt = true;
		$(wrap).detach();
		$(wrap).toggleClass('visible', false);
		for(a in hidden) $(hidden[a]).show();
		if(svt){
			svt = false;
			changeVideo();
		}
	}

	function stop() {
		open = false;
		video.src = "";
		$(video).hide();
		hvt = false;
		$(responsive).css('padding-bottom', (options.baseHeight*100)/options.baseWidth + '%');
		$(wrap).css('max-width', options.baseWidth);
		setTimeout(function(){ if(!hvt) hideVideo(); }, 600);
	}

	// AUTOLOAD CODE BLOCK (MAY BE CHANGED OR REMOVED)
	$("a[rel^='vbinline']").vbinline({ /* Put custom options here */ });
	
})(jQuery);

(function($) {
	
	var sliders = [];
	
	$.vbSlider = function(target, _options){
		
		var elements = $(target).children();
		var outer = $('<div class="vb_slider_outer"></div>').insertAfter(target);
		var wrap = $('<div class="vb_slider_wrap"></div>').appendTo(outer);
		
		var slider = {
			wrap: wrap,
			slider: outer,
			prev: $('<div class="vb_slider_prev"></div>').prependTo(outer),
			cont: $('<div class="vb_slider_cont"></div>').append(target).appendTo(wrap),
			next: $('<div class="vb_slider_next"></div>').appendTo(outer),
			el: [],
			target: $(target),
			originWidth: elements.css('width'),
			showPrev: function(){
				move(slider, 'r');
			},
			showNext: function(){
				move(slider, 'l');
			},
			skip: function(){
				if(!slider.rm) return;
				var rm = slider.rm;
				slider.rm = false;
				slider.cont.toggleClass('animating', false);
				slider.cont.css('margin-left', 0);
				slider.cont.css('margin-right', 0);
				var attached = slider.target.children();
				if(attached.length < 1) return;
				var el;
				if(rm == 'l'){
					el = attached[0];
					slider.el.push(el);
				} else {
					el = attached[attached.length - 1];
					slider.el.unshift(el);
				}
				$(el).detach();
				if(slider.stack.length > 0) move(slider, slider.stack.pop());
			},
			rm: false,
			stack: [],
		};
		
		setWidth(slider);
		slider.prev.click(slider.showPrev);
		slider.next.click(slider.showNext);
		slider.cont.on('webkitTransitionEnd transitionend mozTransitionEnd oTransitionEnd', slider.skip);
		sliders.push(slider);
		
		return [slider];
	}
	
	$.fn.vbSlider = function(_options){
		var sl = [];
		for(var i = 0; i < this.length; i++) sl.concat($.vbSlider(this[i], _options));
		return sl;
	}
	
	function move(slider, dir){
		if(slider.rm){
			if(slider.stack.length > 0 && slider.stack[slider.stack.length - 1] != dir){
				slider.stack.pop();
			} else {
				slider.stack.push(dir);
			}
			return;
		}
		
		var el = dir == 'l' ? slider.el.shift() : slider.el.pop();
		dir == 'l' ? slider.target.append(el) : slider.target.prepend(el);
		$(el).css('width', slider.width);
		var w = $(el).outerWidth(true);
		slider.cont.css('margin-left', dir == 'l' ? 0 : -w);
		slider.cont.css('margin-right', -w);
		var attached = slider.target.children();
		var fel = attached[dir == 'l' ? 0 : attached.length - 1];
		$(fel).detach();
		var h = slider.target.outerHeight(true);
		dir == 'l' ? slider.target.prepend(fel) : slider.target.append(fel);
		setTimeout(function(){		// add some delay to let FF trigger transitions
			slider.rm = dir;
			slider.cont.toggleClass('animating', true);
			slider.cont.css({
				'margin-left': dir == 'l' ? -w : 0,
				'height': h,
			});
		}, 10);
	}
	
	function setWidth(slider){
		slider.target.children().css('width', slider.originWidth);
		var w = slider.target.innerWidth();
		var iw = slider.target.children().innerWidth();
		var ow = slider.target.children().outerWidth(true);
		var n = Math.floor(w/ow);
		if(n < 1){
			n = 1;
		} else {
			var w1 = 2 - ow/(w/n);
			var w2 = ow/(w/(n+1));
			if(w2 < w1) n++;
		}
		w = w/n + iw - ow;
		slider.target.children().css('width', w);
		slider.width = w;
		setAttached(slider, n);
	}
	
	function setAttached(slider, n){
		var attached = slider.target.children();
		if(attached.length < n){
			for(var i = attached.length; i < n && slider.el.length > 0; i++){
				var el = slider.el.shift();
				slider.target.append(el);
				$(el).css('width', slider.width);
			}
		} else if(attached.length > n){
			for(var i = attached.length - 1; i >= n; i--){
				slider.el.unshift(attached[i]);
				$(attached[i]).detach();
			}
		}
		var h = slider.target.outerHeight(true);
		slider.cont.css('height', h);
	}

	$(window).on("resize", function() {
		for(var i = 0; i < sliders.length; i++) setWidth(sliders[i]);
	});
	
	$(".vb_slider").vbSlider({ /* Put custom options here */ });
	
})(jQuery);