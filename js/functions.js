/**	
	author		HitkoDev
	copyright	Copyright (C) 2014 HitkoDev All Rights Reserved.
	@license	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
	Website		http://hitko.eu/software/videobox
*/

(function cfr(e){var d=e(window),f=!window.XMLHttpRequest,k=[],r,b,m,g,c=defaults={overlayOpacity:0.8,overlayFadeDuration:400,resizeDuration:400,resizeEasing:"swing",width:400},p,q,i,j;e(function n(){e("body").append(e([p=e('<div id="cfrOverlay" />').click(e.cfr_close)[0],q=e('<div id="cfrCenter" />')[0]]).css("display","none"));i=e('<div id="cfrContent" />').appendTo(q)[0];j=e('<div id="cfrClose" />').click(e.cfr_close).appendTo(q)[0]});e.cfr=function(t,s){c=e.extend(defaults,s);e.vb_close();e.vbi_close();i.innerHTML=t;r=d.scrollTop()+(d.height()/2);b=f||(p.currentStyle&&(p.currentStyle.position!="fixed"));if(b){p.style.position="absolute"}e(p).css("opacity",c.overlayOpacity).fadeIn(c.overlayFadeDuration);e(p).unbind("click").click(e.cfr_close);e(j).unbind("click").click(e.cfr_close);a();o()};e.cfr_close=function(){e(p).stop().fadeOut(c.overlayFadeDuration,h);l();return false};function h(s){if(s){e("object").add(f?"select":"embed").each(function(u,v){k[u]=[v,v.style.visibility];v.style.visibility="hidden"})}else{e.each(k,function(u,v){v[0].style.visibility=v[1]});k=[]}var t=s?"bind":"unbind";d[t]("scroll resize",a)}function a(){var t=d.scrollLeft(),s=d.width();e(q).css("left",t+(s/2));if(b){e(p).css({left:t,top:d.scrollTop(),width:s,height:d.height()})}}function o(){e(q).show();m=e(q).width();if(m>d.width()){m=d.width()}if(m>c.width){m=c.width}g=e(q).height();var s=Math.max(0,r-(g/2));e(q).animate({top:s,marginLeft:-m/2},c.resizeDuration,c.resizeEasing);if(m!=e(q).width()){e(q).css("max-width",m)}e(i).css("width",(m-20))}function l(){e(q).hide();e(i).css("width","")}})(jQuery);

/**
	jQuery.contextMenu - Show a custom context when right clicking something
	Jonas Arnklint, http://github.com/arnklint/jquery-contextMenu
	Released into the public domain
	Date: Jan 14, 2011
	@author Jonas Arnklint
	@version 1.7
 */
 
(function(a){jQuery.fn.contextMenu=function(c,h,m){var k=this,j=a(window),e=a('<ul id="'+c+'" class="context-menu"></ul>').hide().appendTo("body"),f=null,d=null,l=function(){a(".context-menu:visible").each(function(){a(this).trigger("closed");a(this).hide();a("body").unbind("click",l);e.unbind("closed")})},i={shiftDisable:false,disable_native_context_menu:false,leftClick:false},m=a.extend(i,m);a(document).bind("contextmenu",function(n){if(m.disable_native_context_menu){n.preventDefault()}l()});a.each(h,function(q,o){if(o.link){var p=o.link}else{var p='<a href="#">'+q+"</a>"}var n=a("<li>"+p+"</li>");if(o.klass){n.attr("class",o.klass)}n.appendTo(e).bind("click",function(r){o.click(f,d);r.preventDefault()})});if(m.leftClick){var b="click"}else{var b="contextmenu"}var g=function(p){if(m.shiftDisable&&p.shiftKey){return true}l();f=a(this);d=p;if(m.showMenu){m.showMenu.call(e,f)}if(m.hideMenu){e.bind("closed",function(){m.hideMenu.call(e,f)})}e.css({visibility:"hidden",position:"absolute",zIndex:1000});var n=e.outerWidth(true),r=e.outerHeight(true),q=((p.pageX-j.scrollLeft())+n<j.width())?p.pageX:p.pageX-n,o=((p.pageY-j.scrollTop())+r<j.height())?p.pageY:p.pageY-r;e.show(0,function(){a("body").bind("click",l)}).css({visibility:"visible",top:o+"px",left:q+"px",zIndex:1000});return false};if(m.delegateEventTo){return k.on(b,m.delegateEventTo,g)}else{return k.bind(b,g)}}})(jQuery);