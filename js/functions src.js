/**	
	author		HitkoDev
	copyright	Copyright (C) 2014 HitkoDev All Rights Reserved.
	@license	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
	Website		http://hitko.eu/software/videobox
*/

(function cfr($) {
	
	var win = $(window), ie6 = !window.XMLHttpRequest, hiddenElements = [], middle, compatibleOverlay, centerWidth, centerHeight,
	
	options = defaults = {
		overlayOpacity: 0.8,			// 1 is opaque, 0 is completely transparent (change the color in the CSS file)
		overlayFadeDuration: 400,		// Duration of the overlay fade-in and fade-out animations (in milliseconds)
		resizeDuration: 400,			// Duration of each of the box resize animations (in milliseconds)
		resizeEasing: "swing",			// "swing" is jQuery's default easing
		width: 400,						// Default width of videobox
	},
	
	// DOM elements
	overlay, center, message, close;

	$(function cfrl() {
		$("body").append(
			$([
				overlay = $('<div id="cfrOverlay" />').click($.cfr_close)[0],
				center = $('<div id="cfrCenter" />')[0]
			]).css("display", "none")
		);
		
		message = $('<div id="cfrContent" />').appendTo(center)[0];
		close = $('<div id="cfrClose" />').click($.cfr_close).appendTo(center)[0];
	});
	
	$.cfr = function(msg, _options){
		options = $.extend(defaults, _options);
		
		$.vb_close();
		$.vbi_close();
		
		message.innerHTML = msg;
		
		middle = win.scrollTop() + (win.height() / 2);
		compatibleOverlay = ie6 || (overlay.currentStyle && (overlay.currentStyle.position != "fixed"));
		if (compatibleOverlay) overlay.style.position = "absolute";
		$(overlay).css("opacity", options.overlayOpacity).fadeIn(options.overlayFadeDuration);
		$(overlay).unbind("click").click($.cfr_close);
		$(close).unbind("click").click($.cfr_close);
		cfrPosition();
		animateCenter();
	}

	$.cfr_close = function() {
		$(overlay).stop().fadeOut(options.overlayFadeDuration, cfrsetup);
		cfrstop();
		return false;
	}

	function cfrsetup(open) {
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
		win[fn]("scroll resize", cfrPosition);
	}

	function cfrPosition() {
		var l = win.scrollLeft(), w = win.width();
		$(center).css("left", l + (w / 2));
		if (compatibleOverlay) $(overlay).css({left: l, top: win.scrollTop(), width: w, height: win.height()});
	}
	
	function animateCenter(){
		
		$(center).show();
		centerWidth = $(center).width();
		if(centerWidth > win.width()) centerWidth = win.width();
		if(centerWidth > options.width) centerWidth = options.width;
		centerHeight = $(center).height();
		var top = Math.max(0, middle - (centerHeight / 2));
		$(center).animate({top: top, marginLeft: -centerWidth/2}, options.resizeDuration, options.resizeEasing);
		if(centerWidth != $(center).width()) $(center).css("max-width", centerWidth);
		$(message).css("width", (centerWidth - 20));
		
	}
	
	function cfrstop() {
		$(center).hide();
		$(message).css("width", "");
	}
	
})(jQuery);

/**
	jQuery.contextMenu - Show a custom context when right clicking something
	Jonas Arnklint, http://github.com/arnklint/jquery-contextMenu
	Released into the public domain
	Date: Jan 14, 2011
	@author Jonas Arnklint
	@version 1.7
 */
 
(function(a){jQuery.fn.contextMenu=function(c,h,m){var k=this,j=a(window),e=a('<ul id="'+c+'" class="context-menu"></ul>').hide().appendTo("body"),f=null,d=null,l=function(){a(".context-menu:visible").each(function(){a(this).trigger("closed");a(this).hide();a("body").unbind("click",l);e.unbind("closed")})},i={shiftDisable:false,disable_native_context_menu:false,leftClick:false},m=a.extend(i,m);a(document).bind("contextmenu",function(n){if(m.disable_native_context_menu){n.preventDefault()}l()});a.each(h,function(q,o){if(o.link){var p=o.link}else{var p='<a href="#">'+q+"</a>"}var n=a("<li>"+p+"</li>");if(o.klass){n.attr("class",o.klass)}n.appendTo(e).bind("click",function(r){o.click(f,d);r.preventDefault()})});if(m.leftClick){var b="click"}else{var b="contextmenu"}var g=function(p){if(m.shiftDisable&&p.shiftKey){return true}l();f=a(this);d=p;if(m.showMenu){m.showMenu.call(e,f)}if(m.hideMenu){e.bind("closed",function(){m.hideMenu.call(e,f)})}e.css({visibility:"hidden",position:"absolute",zIndex:1000});var n=e.outerWidth(true),r=e.outerHeight(true),q=((p.pageX-j.scrollLeft())+n<j.width())?p.pageX:p.pageX-n,o=((p.pageY-j.scrollTop())+r<j.height())?p.pageY:p.pageY-r;e.show(0,function(){a("body").bind("click",l)}).css({visibility:"visible",top:o+"px",left:q+"px",zIndex:1000});return false};if(m.delegateEventTo){return k.on(b,m.delegateEventTo,g)}else{return k.bind(b,g)}}})(jQuery);