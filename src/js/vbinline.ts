/// <reference path="headers.d.ts" />

(function($: JQueryStatic) {

    var
        wrap: HTMLDivElement,
        responsive: HTMLDivElement,
        caption: HTMLElement,
        button: HTMLDivElement,
        video: HTMLIFrameElement,
        win = $(window),

        videos: Array<vbVideo> = [],
        activeVideo: vbVideo,
        open: boolean = false,
        hidding: boolean = false,
        animations: Array<webAnimation> = [],
        hidden: Array<HTMLElement> = [],
        options: vbOptions = {},

        defaults: vbOptions = {
            videoWidth: 720,
            videoHeight: 405,
            closeText: 'Close',
            padding: 30,
            animation: {
                duration: 500,
                iterations: 1,
                delay: 0,
                easing: 'ease-in-out'
            }
        };

    $.vbinline = function(_videos: Array<vbVideo>, startVideo: number, _options: vbOptions = {}): boolean {
        $.extend(options, defaults, _options);
        $.vbClose();
        $.vbiClose(function() {
            videos = _videos;
            changeVideo(startVideo);
        });
        return false;
    };

    $.vbiClose = function(callback?: () => void): boolean {
        stop();

        if (!hidding) {
            if ($(wrap).parent().length > 0) {
                hidding = true;
                var v1 = wrap.animate([{
                    'max-width': (activeVideo.width || options.videoWidth) + 2 * options.padding + 'px'
                }, {
                        'max-width': (activeVideo.origin ? activeVideo.origin.width : options.initialWidth) + 'px'
                    }], options.animation);
                v1.addEventListener('finish', function() {
                    $(wrap).detach();
                    for (var i = 0; i < hidden.length; i++) $(hidden[i]).show();
                    hidden = [];
                    hidding = false;
                    if (callback) callback();
                });

                if (activeVideo.origin) {
                    var v2 = responsive.animate([{
                        'padding-bottom': ((activeVideo.height || options.videoHeight) * 100) / (activeVideo.width || options.videoWidth) + '%'
                    }, {
                            'padding-bottom': (activeVideo.origin.height * 100) / activeVideo.origin.width + '%'
                        }], options.animation);
                }
            } else if (callback) callback();
        }
        return false;
    };

    $.fn.vbinline = function(
        _options: vbOptions = {},
        linkMapper: ((el: HTMLElement, i: number) => vbVideo) = (el: HTMLElement, i: number): vbVideo=> {
            var v: vbVideo = {
                url: el.getAttribute("href") || "",
                title: el.getAttribute("title") || "",
                width: parseInt(el.getAttribute("data-videowidth")) || undefined,
                height: parseInt(el.getAttribute("data-videoheight")) || undefined,
                style: el.getAttribute("data-style") || undefined,
                class: el.getAttribute("data-class") || undefined,
                index: i
            };
            return v;
        }
    ): boolean {

        var links: JQuery = <JQuery>this;

        links.unbind("click").click(function(evt: JQueryEventObject): boolean {

            var link: HTMLElement = this, startIndex: number = 0, mappedLinks: Array<vbVideo> = [], target = $($(this).find($(this).attr("data-target"))[0] || this);

            for (var i = 0; i < links.length; i++) {
                if (links[i] == link) startIndex = i;
                mappedLinks.push(linkMapper(links[i], i));
            }

            target.toggleClass('vb_line_fix', true);
            mappedLinks[startIndex].origin = {
                x: target.offset().left - win.scrollLeft() + $(target).innerWidth() / 2,
                y: target.offset().top - win.scrollTop() + $(target).innerHeight() / 2,
                width: target.innerWidth(),
                height: target.innerHeight(),
                target: link
            };
            target.toggleClass('vb_line_fix', false);

            return $.vbinline(mappedLinks, startIndex, _options);
        });
        return false;
    };

    function changeVideo(index: number): void {
        if (index < 0 || index >= videos.length) return;

        activeVideo = videos[index];

        setup();

        $(wrap).attr('style', activeVideo.style);
        $(wrap).attr('class', activeVideo.class);
        $(caption).html(activeVideo.title);
        open = true;

        var wrapOrigin = {
            'max-width': (activeVideo.origin ? activeVideo.origin.width : options.initialWidth) + 'px'
        };
        var wrapDest = {
            'max-width': (activeVideo.width || options.videoWidth) + 2 * options.padding + 'px'
        };
        var animation = wrap.animate([wrapOrigin, wrapDest], options.animation);
        animation.addEventListener('finish', function() {
            $(wrap).css(wrapDest);
            showVideo();
        });
        animations.push(animation);

        var responsiveDest = {
            'padding-bottom': ((activeVideo.height || options.videoHeight) * 100) / (activeVideo.width || options.videoWidth) + '%'
        };
        if (activeVideo.origin) {
            var responsiveOrigin = {
                'padding-bottom': (activeVideo.origin.height * 100) / activeVideo.origin.width + '%'
            };
            var animation = responsive.animate([responsiveOrigin, responsiveDest], options.animation);
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