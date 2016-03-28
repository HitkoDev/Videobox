/// <reference path="helpers.d.ts" />
/// <reference path="interfaces.d.ts" />
/// <reference path="videobox.ts" />

interface JQueryStatic {

    /**
     * Open an inline player
     * 
     * @param _video video to show
     */
    vbinline: (_video: vbVideo) => boolean,

    /**
     * Close the open inline player
     * 
     * @param callback function to run when close animation is over
     */
    vbiClose: (callback?: () => void) => boolean
}

interface JQuery {

    /**
     * Map inline player to elements matched by the query 
     * 
     * @param _options player configuration
     * @param linkMapper function to get a Videobox video object from the clicked element
     */
    vbinline: (_options?: vbOptions, linkMapper?: (el: HTMLElement) => vbVideo) => boolean
}

(function($: JQueryStatic) {

    var
        wrap: HTMLDivElement,
        responsive: HTMLDivElement,
        caption: HTMLElement,
        button: HTMLDivElement,
        video: HTMLIFrameElement,
        win = $(window),

        activeVideo: vbVideo,
        open: boolean = false,
        hidding: boolean = false,
        animations: Array<webAnimation> = [],
        hidden: Array<HTMLElement> = [],

        defaults: vbOptions = {
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

    $.vbinline = function(_video: vbVideo): boolean {
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

        $.vbiClose(function() {
            changeVideo(_video);
        });
        return false;
    };

    $.vbiClose = function(callback: (() => void)): boolean {
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
                v1.addEventListener('finish', function() {
                    $(wrap).detach();
                    for (var i = 0; i < hidden.length; i++) $(hidden[i]).show();
                    hidden = [];
                    hidding = false;
                    activeVideo = null;
                    if (typeof callback == "function") callback();
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
            } else {
                if ($(wrap).parent().length > 0) {
                    $(wrap).detach();
                    for (var i = 0; i < hidden.length; i++) $(hidden[i]).show();
                    hidden = [];
                }
                activeVideo = null;
                if (typeof callback == "function") callback();
            }
        }
        return false;
    };

    $.fn.vbinline = function(
        _options: vbOptions = {},
        linkMapper: ((el: HTMLElement) => vbVideo) = (el: HTMLElement): vbVideo => {
            var v: vbVideo = {
                url: el.getAttribute("href") || "",
                title: el.getAttribute("title") || "",
                options: JSON.parse(el.getAttribute("data-videobox")) || {},
                origin: { target: el }
            };
            return v;
        }
    ): boolean {

        var links: JQuery = <JQuery>this;

        links.unbind("click").click(function(evt: JQueryEventObject): boolean {

            var _video = linkMapper(this);

            _video.options = $.extend(true, {}, _options, _video.options);

            return $.vbinline(_video);

        });
        return false;
    };

    function changeVideo(newVideo: vbVideo): void {

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
        animation.addEventListener('finish', function() {
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
            animation.addEventListener('finish', function() {
                $(responsive).css(responsiveDest);
            });
            animations.push(animation);
        } else {
            $(responsive).css(responsiveDest);
        }
    }

    function setup(): void {
        $(activeVideo.origin.target).after(wrap);
        $(activeVideo.origin.target).hide();
        hidden.push(activeVideo.origin.target);
    }

    function showVideo() {
        if (!open) return;
        $(video).show();
        video.src = activeVideo.url;
    }

    function stop() {
        for (var i = 0; i < animations.length; i++) animations[i].cancel();
        animations = [];
        open = false;
        video.src = "";
        $(video).hide();
    }

    $(window).on('load', function() {
        wrap = <HTMLDivElement>$('<div id="vbiWrap" />').append([
            responsive = <HTMLDivElement>$('<div id="vbiResponsive" />')[0],
            caption = $('<span class="vb_video_title"></span>')[0],
            button = <HTMLDivElement>$('<div id="vbiClose"><i class="vb-icon-circle-close-invert"></i></div>').click($.vbiClose)[0],
        ])[0];
        video = <HTMLIFrameElement>$('<iframe id="vbiVideo" frameborder="0" allowfullscreen="true" oallowfullscreen msallowfullscreen webkitallowfullscreen mozallowfullscreen />').css('display', 'none').appendTo(responsive)[0];
    })

})(jQuery);