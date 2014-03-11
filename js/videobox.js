/**	@license
	Videobox v2 - jQuery lightbox clone for displaying iframe videos
	Based on Slimbox 2.04 
		(c) 2007-2010 Christophe Beyls <http://www.digitalia.be>
		MIT-style license.
*/

// Videobox player (lightbox) effect
(function vb(f){var e=f(window),d,k,z=-1,b,c,y,o,l,i=!window.XMLHttpRequest,n=[],x=document.documentElement,r,w,q,v,j,s,a;f(function g(){f("body").append(f([r=f('<div id="vbOverlay" />').click(f.vb_close)[0],w=f('<div id="vbCenter" />')[0],v=f('<div id="vbBottomContainer" />')[0]]).css("display","none"));q=f('<iframe id="vbVideo" frameborder="0" allowfullscreen="true" oallowfullscreen msallowfullscreen webkitallowfullscreen mozallowfullscreen />').appendTo(w)[0];j=f('<div id="vbBottom" />').appendTo(v).append([a=f('<a id="vbCloseLink" href="#" />').click(f.vb_close)[0],s=f('<div id="vbCaption" />')[0],f('<div style="clear: both;" />')[0]])[0]});f.videobox=function(C,B,A){d=f.extend({overlayOpacity:0.8,overlayFadeDuration:400,resizeDuration:400,resizeEasing:"swing",videoWidth:640,videoHeight:363,videoFadeDuration:400,captionAnimationDuration:400},A);if(typeof C=="string"){C=[[C,B]];B=0}f.vbi_close();y=e.scrollTop()+(e.height()/2);o=0;l=0;f(w).css({top:Math.max(0,y-(l/2)),width:o,height:l,marginLeft:-o/2}).show();c=i||(r.currentStyle&&(r.currentStyle.position!="fixed"));if(c){r.style.position="absolute"}f(r).css("opacity",d.overlayOpacity).fadeIn(d.overlayFadeDuration);u();m(1);k=C;z=B;b=k[z][0];p();h();return false};f.vb_close=function(){if(z>=0){p();z=-1;f(w).hide();f(r).stop().fadeOut(d.overlayFadeDuration,m)}return false};f.fn.videobox=function(B,F,D){F=F||function A(G){return[G.href,G.title]};D=D||function E(){return true};var C=this;return C.unbind("click").click(function E(){var I=this,K=0,J,G=0,H;J=f.grep(C,function(M,L){return D.call(I,M,L)});for(H=J.length;G<H;++G){if(J[G]==I){K=G}J[G]=F(J[G],G)}return f.videobox(J,K,B)})};function u(){var B=e.scrollLeft(),A=e.width();f([w,v]).css("left",B+(A/2));if(c){f(r).css({left:B,top:e.scrollTop(),width:A,height:e.height()})}}function m(A){if(A){f("object").add(i?"select":"embed").each(function(C,D){n[C]=[D,D.style.visibility];D.style.visibility="hidden"})}else{f.each(n,function(C,D){D[0].style.visibility=D[1]});n=[]}var B=A?"bind":"unbind";e[B]("scroll resize",u)}function h(){w.className="";var B=k[z][2]||d.videoWidth;var A=k[z][3]||d.videoHeight;if(B>(e.width()-20)){B=e.width()-20}A=(A*B)/(k[z][2]||d.videoWidth);if(A>(e.height()-20)){A=e.height()-20}f(q).css({visibility:"hidden",display:""});q.src=b;f(q).width(B);f(q).height(A);f(s).html(k[z][1]||"");o=parseInt(B)+20;l=parseInt(A)+20;var C=Math.max(0,y-(l/2));f(w).animate({height:l,top:C,width:o,marginLeft:-o/2},d.resizeDuration,d.resizeEasing);f(w).queue(function D(){f(v).css({width:o,top:C+l,marginLeft:-o/2,visibility:"hidden",display:""});f(q).css({display:"none",visibility:"",opacity:""}).fadeIn(d.videoFadeDuration,t)})}function t(){f(j).css("marginTop",-j.offsetHeight).animate({marginTop:0},d.captionAnimationDuration);v.style.visibility=""}function p(){f([w,q,j]).stop(true);f([q,v]).hide();q.src="";q.title=""}})(jQuery);

// Inline player (no-lightbox) effect
(function vbi(h){var g=h(window),f,l,t=-1,d,j=!window.XMLHttpRequest,m=[],o,p,q,b,n,r,a;a="";h(function s(){h("body").append([o=h('<div id="vbiHidden" />').css("display","none")[0]]);n=h('<div id="vbiPlayer" />').appendTo(o).append([b=h('<a id="vbiCloseLink" href="#" />').click(h.vbi_close)[0],r=h('<div id="vbiVideoCont" />')[0],q=h('<div id="vbiCaption" />')[0]])[0];p=h('<iframe id="vbiVideo" frameborder="0" allowfullscreen="true" oallowfullscreen msallowfullscreen webkitallowfullscreen mozallowfullscreen />').appendTo(r)[0]});h.vbinline=function(w,v,u){f=h.extend({resizeDuration:400,resizeEasing:"swing",videoWidth:640,videoHeight:363},u);if(typeof w=="string"){w=[[w,v]];v=0}h.vb_close();c(1);l=w;return k(v)};h.vbi_close=function(){if(t>=0){i();t=-1}h(b).show();h(b).click(h.vbi_close);return false};h.fn.vbinline=function(u,z,y){z=z||function x(A){return[A.href,A.title,A.videowidth,A.videoheight,A]};y=y||function w(){return true};var v=this;return v.unbind("click").click(function w(){var C=this,E=0,D,A=0,B;D=h.grep(v,function(G,F){return y.call(C,G,F)});for(B=D.length;A<B;++A){if(D[A]==C){E=A}D[A]=z(D[A],A)}return h.vbinline(D,E,u)})};function c(u){if(u){h("object").add(j?"select":"embed").each(function(v,w){m[v]=[w,w.style.visibility];w.style.visibility="hidden"})}else{h.each(m,function(v,w){w[0].style.visibility=w[1]});m=[]}}function k(u){if(u>=0){t=u;d=l[t][0];i();e()}return false}function e(){var w=l[t][4].width();var y=l[t][4].height();n.setAttribute("style",l[t][5]);h(n).width(w);h(q).html(l[t][1]||"");a=l[t][4];h(n).insertAfter(l[t][4]);var v=h(q).outerHeight();var x=l[t][2]||f.videoWidth;var u=l[t][3]||f.videoHeight;if(x>(g.width()-10)){x=g.width()-10}u=(u*x)/(l[t][2]||f.videoWidth);if(u>(g.height()-10)){u=g.height()-10}h(p).css({display:""});p.src=d;y-=v;y-=10;h(p).height(y);h(q).html(l[t][1]||"");l[t][4].css({display:"none"});playerWidth=parseInt(x)+10;videoHeight=parseInt(u);h(n).animate({width:playerWidth},f.resizeDuration,f.resizeEasing);h(p).animate({height:videoHeight},f.resizeDuration,f.resizeEasing)}function i(){if(a!=""){a.css({display:""})}a="";h(n).appendTo(o);h([p]).stop(true);h([p]).hide();p.src="";p.title=""}})(jQuery);

// AUTOLOAD CODE BLOCK (MAY BE CHANGED OR REMOVED)
jQuery(function vb($) {
	$("a[rel^='videobox']").videobox({ /* Put custom options here */ }, function vbl(el) {
		return [el.href, el.getAttribute("title"), el.getAttribute("videowidth"), el.getAttribute("videoheight")];
	}, function vbl(el) {
		return (this == el) || ((this.rel.length > 8) && (this.rel == el.rel));
	});
});

// AUTOLOAD FOR INLINE PLAYER
jQuery(function vbi($) {
	$("a[rel^='vbinline']").vbinline({ /* Put custom options here */ }, function vbil(el) {
		return [el.href, el.getAttribute("title"), el.getAttribute("videowidth"), el.getAttribute("videoheight"), $(el), el.getAttribute("videoStyle")];
	}, function vbil(el) {
		return (this == el) || ((this.rel.length > 8) && (this.rel == el.rel));
	});
});