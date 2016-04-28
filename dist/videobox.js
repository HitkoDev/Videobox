/*!
 *	@author		HitkoDev http://hitko.eu/videobox
 *	@copyright	Copyright (C) 2016 HitkoDev All Rights Reserved.
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

/// <reference path="helpers.d.ts" />
/// <reference path="interfaces.d.ts" />
/// <reference path="vbinline.ts" />
(function ($) {
    var closeText, center, caption, wrap, responsive, overlay, bottomContainer, video, bottom, button, win = $(window), activeVideo, open = false, animations = [], defaults = {
        width: 720,
        height: 405,
        closeText: 'Close',
        padding: 30,
        initialWidth: '15%',
        root: $("body")[0],
        animation: {
            duration: 500,
            iterations: 1,
            delay: 0,
            easing: 'ease-in-out'
        }
    };
    $.videobox = function (_video) {
        $.vbiClose();
        $.vbClose();
        _video.options = $.extend(true, {}, defaults, _video.options);
        setup(_video);
        var link = _video.origin.target;
        var target = $($(link).find($(link).attr("data-target"))[0] || link);
        var bw = wrap.getBoundingClientRect();
        var bt = target[0].getBoundingClientRect();
        target.toggleClass('vb_line_fix', true);
        _video.origin = $.extend(true, {}, {
            x: bt.left - bw.left + target.innerWidth() / 2,
            y: bt.top - bw.top + target.innerHeight() / 2,
            width: target.innerWidth(),
            height: target.innerHeight()
        }, _video.origin);
        target.toggleClass('vb_line_fix', false);
        changeVideo(_video);
        return false;
    };
    $.vbClose = function () {
        stop();
        $([wrap, bottomContainer, overlay]).toggleClass('visible', false);
        $(wrap).css({
            top: 0,
            left: 0
        });
        activeVideo = null;
        return false;
    };
    $.fn.videobox = function (_options, linkMapper) {
        if (_options === void 0) { _options = {}; }
        if (linkMapper === void 0) { linkMapper = function (el) {
            var v = {
                url: el.getAttribute("href") || "",
                title: el.getAttribute("title") || "",
                options: JSON.parse(el.getAttribute("data-videobox")) || {},
                origin: { target: el }
            };
            return v;
        }; }
        var links = this;
        links.unbind("click").click(function (evt) {
            var _video = linkMapper(this);
            _video.options = $.extend(true, {}, _options, _video.options);
            return $.videobox(_video);
        });
        return false;
    };
    function setup(newVideo) {
        $(closeText).html(newVideo.options.closeText);
        $(newVideo.options.root).append([overlay, wrap]);
        $(wrap).css({
            top: $(newVideo.options.root).scrollTop(),
            left: $(newVideo.options.root).scrollLeft()
        });
    }
    function changeVideo(newVideo) {
        activeVideo = newVideo;
        $(caption).html(activeVideo.title);
        var targetRatio = setPlayerSizePosition();
        open = true;
        var centerOrigin = {
            top: (activeVideo.origin ? -($(wrap).innerHeight() / 2 - activeVideo.origin.y) : 0) + 'px',
            left: (activeVideo.origin ? -($(wrap).innerWidth() / 2 - activeVideo.origin.x) : 0) + 'px',
            'max-width': activeVideo.origin ? activeVideo.origin.width + 'px' : activeVideo.options.initialWidth
        };
        var centerTarget = {
            top: '0px',
            left: '0px',
            'max-width': activeVideo.options.width + 'px'
        };
        $(center).css(centerOrigin);
        $([wrap, overlay]).toggleClass('visible', true);
        $(wrap).toggleClass('animating', true);
        if (activeVideo.origin) {
            var originRatio = ((activeVideo.origin.height * 100) / activeVideo.origin.width) || targetRatio;
            if (originRatio != targetRatio) {
                animations.push(responsive.animate([
                    { 'padding-bottom': originRatio + '%' },
                    { 'padding-bottom': targetRatio + '%' }
                ], activeVideo.options.animation));
            }
        }
        var centerAnimation = center.animate([
            centerOrigin,
            centerTarget
        ], activeVideo.options.animation);
        centerAnimation.addEventListener('finish', function () {
            $(center).css(centerTarget);
            var bottomAnimation = bottomContainer.animate([
                { 'max-height': '0px' },
                { 'max-height': '200px' }
            ], activeVideo.options.animation);
            bottomAnimation.addEventListener('finish', function () {
                $(bottomContainer).toggleClass('visible', true);
                showVideo();
            });
            animations.push(bottomAnimation);
        });
        animations.push(centerAnimation);
        return false;
    }
    function setPlayerSizePosition() {
        if (!activeVideo)
            return;
        var width = activeVideo.options.width;
        var height = activeVideo.options.height;
        $(wrap).css({
            top: $(activeVideo.options.root).scrollTop(),
            left: $(activeVideo.options.root).scrollLeft()
        });
        if (width + 2 * activeVideo.options.padding > $(wrap).innerWidth()) {
            var nw = $(wrap).innerWidth() - 2 * activeVideo.options.padding;
            height = (height * nw) / width;
            width = nw;
        }
        if (height + 2 * activeVideo.options.padding > $(wrap).innerHeight())
            height = $(wrap).innerHeight() - 2 * activeVideo.options.padding;
        var ratio = (height * 100) / width;
        $(responsive).css('padding-bottom', ratio + '%');
        return ratio;
    }
    function showVideo() {
        if (!open || $(video).attr('src') != '')
            return;
        $(video).show();
        video.src = activeVideo.url;
        $(wrap).toggleClass('animating', false);
    }
    function stop() {
        for (var i = 0; i < animations.length; i++)
            animations[i].cancel();
        animations = [];
        open = false;
        video.src = "";
        $(video).hide();
        $(wrap).toggleClass('animating', false);
    }
    $(window).on('load', function () {
        $(defaults.root).append($([
            overlay = $('<div id="vbOverlay" />').click($.vbClose)[0],
            wrap = $('<div id="vbWrap" />')[0]
        ]));
        center = $('<div id="vbCenter" />').appendTo(wrap).append([
            responsive = $('<div id="vbResponsive" />')[0],
            bottomContainer = $('<div id="vbBottomContainer" />')[0],
        ])[0];
        video = $('<iframe id="vbVideo" frameborder="0" allowfullscreen="true" oallowfullscreen msallowfullscreen webkitallowfullscreen mozallowfullscreen />').css('display', 'none').appendTo(responsive)[0];
        bottom = $('<div id="vbBottom" />').appendTo(bottomContainer).append([
            button = $('<a id="vbCloseLink" href="#" ><span id="vbCloseText">' + defaults.closeText + '</span><i class="vb-icon-close"></i></a>').click($.vbClose)[0],
            caption = $('<strong id="vbCaption" />')[0]
        ])[0];
        closeText = $(bottom).find('#vbCloseText')[0];
        win.on("resize", function () {
            if (!open || !activeVideo)
                return;
            setPlayerSizePosition();
        });
    });
})(jQuery);

/// <reference path="helpers.d.ts" />
/// <reference path="interfaces.d.ts" />
/// <reference path="videobox.ts" />
(function ($) {
    var wrap, responsive, caption, button, video, win = $(window), activeVideo, open = false, hidding = false, animations = [], hidden = [], defaults = {
        width: 720,
        height: 405,
        closeText: 'Close',
        padding: 30,
        animation: {
            duration: 500,
            iterations: 1,
            delay: 0,
            easing: 'ease-in-out'
        }
    };
    $.vbinline = function (_video) {
        $.vbClose();
        _video.options = $.extend(true, {}, defaults, _video.options);
        var link = _video.origin.target;
        var target = $($(link).find($(link).attr("data-target"))[0] || link);
        target.toggleClass('vb_line_fix', true);
        _video.origin = $.extend(true, {}, {
            width: target.innerWidth(),
            height: target.innerHeight()
        }, _video.origin);
        target.toggleClass('vb_line_fix', false);
        $.vbiClose(function () {
            changeVideo(_video);
        });
        return false;
    };
    $.vbiClose = function (callback) {
        stop();
        if (!hidding) {
            if ($(wrap).parent().length > 0 && activeVideo) {
                hidding = true;
                var v1 = wrap.animate([
                    {
                        'max-width': (activeVideo.options.width + 2 * activeVideo.options.padding) + 'px'
                    }, {
                        'max-width': (activeVideo.origin ? activeVideo.origin.width : activeVideo.options.initialWidth) + 'px'
                    }
                ], activeVideo.options.animation);
                v1.addEventListener('finish', function () {
                    $(wrap).detach();
                    for (var i = 0; i < hidden.length; i++)
                        $(hidden[i]).show();
                    hidden = [];
                    hidding = false;
                    activeVideo = null;
                    if (typeof callback == "function")
                        callback();
                });
                if (activeVideo.origin) {
                    var v2 = responsive.animate([
                        {
                            'padding-bottom': ((activeVideo.options.height * 100) / activeVideo.options.width) + '%'
                        }, {
                            'padding-bottom': ((activeVideo.origin.height * 100) / activeVideo.origin.width) + '%'
                        }
                    ], activeVideo.options.animation);
                }
            }
            else {
                if ($(wrap).parent().length > 0) {
                    $(wrap).detach();
                    for (var i = 0; i < hidden.length; i++)
                        $(hidden[i]).show();
                    hidden = [];
                }
                activeVideo = null;
                if (typeof callback == "function")
                    callback();
            }
        }
        return false;
    };
    $.fn.vbinline = function (_options, linkMapper) {
        if (_options === void 0) { _options = {}; }
        if (linkMapper === void 0) { linkMapper = function (el) {
            var v = {
                url: el.getAttribute("href") || "",
                title: el.getAttribute("title") || "",
                options: JSON.parse(el.getAttribute("data-videobox")) || {},
                origin: { target: el }
            };
            return v;
        }; }
        var links = this;
        links.unbind("click").click(function (evt) {
            var _video = linkMapper(this);
            _video.options = $.extend(true, {}, _options, _video.options);
            return $.vbinline(_video);
        });
        return false;
    };
    function changeVideo(newVideo) {
        activeVideo = newVideo;
        setup();
        $(wrap).attr('style', activeVideo.options.style);
        $(wrap).attr('class', activeVideo.options.class);
        $(caption).html(activeVideo.title);
        open = true;
        var wrapOrigin = {
            'max-width': (activeVideo.origin ? activeVideo.origin.width : activeVideo.options.initialWidth) + 'px'
        };
        var wrapDest = {
            'max-width': (activeVideo.options.width + 2 * activeVideo.options.padding) + 'px'
        };
        var animation = wrap.animate([wrapOrigin, wrapDest], activeVideo.options.animation);
        animation.addEventListener('finish', function () {
            $(wrap).css(wrapDest);
            showVideo();
        });
        animations.push(animation);
        var responsiveDest = {
            'padding-bottom': ((activeVideo.options.height * 100) / activeVideo.options.width) + '%'
        };
        if (activeVideo.origin) {
            var responsiveOrigin = {
                'padding-bottom': ((activeVideo.origin.height * 100) / activeVideo.origin.width) + '%'
            };
            var animation = responsive.animate([responsiveOrigin, responsiveDest], activeVideo.options.animation);
            animation.addEventListener('finish', function () {
                $(responsive).css(responsiveDest);
            });
            animations.push(animation);
        }
        else {
            $(responsive).css(responsiveDest);
        }
    }
    function setup() {
        $(activeVideo.origin.target).after(wrap);
        $(activeVideo.origin.target).hide();
        hidden.push(activeVideo.origin.target);
    }
    function showVideo() {
        if (!open)
            return;
        $(video).show();
        video.src = activeVideo.url;
    }
    function stop() {
        for (var i = 0; i < animations.length; i++)
            animations[i].cancel();
        animations = [];
        open = false;
        video.src = "";
        $(video).hide();
    }
    $(window).on('load', function () {
        wrap = $('<div id="vbiWrap" />').append([
            responsive = $('<div id="vbiResponsive" />')[0],
            caption = $('<span class="vb_video_title"></span>')[0],
            button = $('<div id="vbiClose"><i class="vb-icon-circle-close-invert"></i></div>').click($.vbiClose)[0],
        ])[0];
        video = $('<iframe id="vbiVideo" frameborder="0" allowfullscreen="true" oallowfullscreen msallowfullscreen webkitallowfullscreen mozallowfullscreen />').css('display', 'none').appendTo(responsive)[0];
    });
})(jQuery);

/// <reference path="helpers.d.ts" />
/// <reference path="vbinline.ts" />
(function ($) {
    var _vbSlider = (function () {
        function _vbSlider(target, _options) {
            this.outer = $('<div class="vb_slider_outer"></div>')[0];
            this.wrap = $('<div class="vb_slider_wrap"></div>').appendTo(this.outer)[0];
            this.content = $('<div class="vb_slider_cont"></div>').appendTo(this.wrap)[0];
            this.prev = $('<div class="vb_slider_prev"><i class="vb-icon-prev"></i></div>').prependTo(this.outer)[0];
            this.next = $('<div class="vb_slider_next"><i class="vb-icon-next"></i></div>').appendTo(this.outer)[0];
            this.buttons = $(this.outer).find('i');
            this.queue = [];
            this.timeout = -1;
            this.moving = false;
            this.visible = -1;
            this.detachedElements = [];
            this.options = {
                moveAll: false,
                target: '',
                singleDuration: 500,
                doubleClickTimeout: 200,
                animation: {
                    duration: 500,
                    iterations: 1,
                    delay: 0,
                    easing: 'ease-in-out'
                },
            };
            this.target = target;
            var elements = $(target).children();
            $(this.outer).insertAfter(target);
            $(this.content).append(target);
            this.basis = parseInt($(target).attr('data-width')) || elements.innerWidth();
            $.extend(this.options, _options);
            $(this.content).toggleClass('vb-slider__move-all', this.options.moveAll);
            var slider = this;
            $(this.prev).click(function () {
                slider.showPrev();
            });
            $(this.next).click(function () {
                slider.showNext();
            });
            this.setCount();
        }
        _vbSlider.prototype.showPrev = function () {
            this.queueMove('r');
        };
        _vbSlider.prototype.showNext = function () {
            this.queueMove('l');
        };
        _vbSlider.prototype.setBasis = function (_basis) {
            if (_basis != this.basis) {
                this.basis = _basis;
                this.setCount();
            }
        };
        _vbSlider.prototype.isMoving = function () {
            return this.moving;
        };
        _vbSlider.prototype.getTarget = function () {
            return this.target;
        };
        _vbSlider.prototype.queueMove = function (dir) {
            if (this.queue.length > 0 && this.queue[this.queue.length - 1] != dir) {
                this.queue.pop();
            }
            else {
                this.queue.push(dir);
            }
            if (this.timeout >= 0)
                clearTimeout(this.timeout);
            var slider = this;
            this.timeout = setTimeout(function () {
                slider.timeout = -1;
                if (!slider.moving && slider.queue.length > 0)
                    slider.move();
            }, this.options.doubleClickTimeout);
        };
        _vbSlider.prototype.move = function () {
            this.moving = true;
            var dir = this.queue.pop();
            var num = 1;
            while (this.queue.length > 0)
                num += this.queue.pop() == dir ? 1 : -1;
            if (num == 0) {
                this.moving = false;
                return;
            }
            if (num < 0) {
                dir = dir == 'l' ? 'r' : 'l';
                num = 0 - num;
            }
            var count = (this.options.moveAll ? this.visible : 1) * num;
            count = count % (this.visible + this.detachedElements.length);
            for (var i = 0; i < count && this.detachedElements.length > 0; i++) {
                dir == 'l' ? $(this.target).append(this.detachedElements.shift()) : $(this.target).prepend(this.detachedElements.pop());
            }
            var attached = $(this.target).children();
            var oldElements = dir == 'l' ? attached.slice(0, attached.length - this.visible) : attached.slice(this.visible);
            _vbSlider.detach(oldElements);
            var height = $(this.target).innerHeight(), width = 100 * count / this.visible;
            dir == 'l' ? $(this.target).prepend(oldElements) : $(this.target).append(oldElements);
            var animationProperties = this.options.singleDuration ? $.extend({}, this.options.animation, { duration: this.options.singleDuration * count }) : this.options.animation;
            var slider = this;
            var positionOrigin = {
                'margin-left': (dir == 'l' ? 0 : -width) + '%',
                'margin-right': (dir == 'l' ? -width : 0) + '%',
            };
            var positionDest = {
                'margin-left': (dir == 'l' ? -width : 0) + '%',
                'margin-right': (dir == 'l' ? 0 : -width) + '%',
            };
            var anim = this.content.animate([positionOrigin, positionDest], animationProperties);
            anim.addEventListener('finish', function () {
                slider.skip(dir);
            });
            var anim = this.content.animate([{
                    height: $(this.content).css('height')
                }, {
                    height: height + 'px'
                }], animationProperties);
            anim.addEventListener('finish', function () {
                $(slider.content).css('height', height);
            });
            this.buttons.css('top', this.options.target ? ($(this.target).find(this.options.target).outerHeight(true) / 2) : '');
        };
        _vbSlider.detach = function (el) {
            if (el.find('#vbiWrap').length > 0)
                $.vbiClose();
            el.detach();
        };
        _vbSlider.prototype.skip = function (dir) {
            var attached = $(this.target).children();
            if (dir == 'l') {
                var el = attached.slice(0, attached.length - this.visible);
                _vbSlider.detach(el);
                for (var i = 0; i < el.length; i++)
                    this.detachedElements.push(el[i]);
            }
            else if (dir == 'r') {
                var el = attached.slice(this.visible);
                _vbSlider.detach(el);
                for (var i = 0; i < el.length; i++)
                    this.detachedElements.unshift(el[i]);
            }
            if (this.queue.length > 0 && this.timeout < 0) {
                this.move();
            }
            else {
                this.moving = false;
            }
        };
        _vbSlider.prototype.setCount = function () {
            var current = this.visible;
            var width = $(this.target).innerWidth();
            var base = this.basis + $(this.target).children().outerWidth(true) - $(this.target).children().innerWidth(); // base width including any offset
            // calculate number of displayed items
            var visible = Math.floor(width / this.basis);
            if (visible < 1) {
                visible = 1;
            }
            else {
                var w1 = 2 - base / (width / visible);
                var w2 = base / (width / (visible + 1));
                if (w2 < w1)
                    visible++;
            }
            // add or remove visible items if needed
            if (visible != current) {
                this.visible = visible;
                this.setAttached();
            }
            // set new size
            $(this.content).css('height', $(this.target).innerHeight());
            this.buttons.css('top', this.options.target ? ($(this.target).find(this.options.target).outerHeight(true) / 2) : '');
        };
        _vbSlider.prototype.setAttached = function () {
            var attached = $(this.target).children();
            if (attached.length < this.visible) {
                for (var i = attached.length; i < this.visible && this.detachedElements.length > 0; i++) {
                    $(this.target).append(this.detachedElements.shift());
                }
            }
            else if (attached.length > this.visible) {
                for (var i = attached.length - 1; i >= this.visible; i--) {
                    this.detachedElements.unshift(attached[i]);
                    _vbSlider.detach($(attached[i]));
                }
            }
        };
        return _vbSlider;
    }());
    var sliders = [];
    $.vbSlider = function (target, _options) {
        if (_options === void 0) { _options = {}; }
        // update and return an existing slider
        for (var i = 0; i < sliders.length; i++)
            if (sliders[i].getTarget() == target) {
                $.extend(sliders[i].options, _options);
                return sliders[i];
            }
        return new _vbSlider(target, _options);
    };
    $.fn.vbSlider = function (_options) {
        if (_options === void 0) { _options = {}; }
        var sliders = [];
        for (var i = 0; i < this.length; i++) {
            var target = this[i], _op = {}, tr = $(target).attr("data-target"), mo = $(target).attr("data-move");
            if (tr)
                _op.target = tr;
            if (mo && mo.trim())
                _op.moveAll = mo.trim() == 'all';
            sliders.push($.vbSlider(target, $.extend({}, _options, _op)));
        }
        return sliders;
    };
    $(window).on("resize", function () {
        for (var i = 0; i < sliders.length; i++)
            sliders[i].setCount();
    });
})(jQuery);

/// <reference path="videobox.ts" />
/// <reference path="vbinline.ts" />
/// <reference path="vbslider.ts" />
(function ($) {
    $(window).on('load', function () {
        var r = $(".mdl-layout.mdl-js-layout")[0];
        if (!r)
            r = $("body")[0];
        $("a[rel^='videobox']").videobox({
            root: r
        });
        $("a[rel^='vbinline']").vbinline({});
        $(".vb_slider").vbSlider({});
    });
})(jQuery);
