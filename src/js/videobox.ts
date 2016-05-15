/// <reference path="helpers.d.ts" />
/// <reference path="interfaces.d.ts" />
/// <reference path="vbinline.ts" />

interface JQueryStatic {

    /**
     * Open Videobox pop-up player
     * 
     * @param _video video to show
     */
    videobox: (_video: vbVideo) => boolean,

    /**
     * Close the open pop-up
     */
    vbClose: () => boolean
}

interface JQuery {

    /**
     * Map pop-up player to elements matched by the query 
     * 
     * @param _options player configuration
     * @param linkMapper function to get a Videobox video object from the clicked element
     */
    videobox: (_options?: vbOptions, linkMapper?: (el: HTMLElement) => vbVideo) => boolean
}

(function($: JQueryStatic) {

    var
        closeText: HTMLSpanElement,
        center: HTMLDivElement,
        caption: HTMLElement,
        wrap: HTMLDivElement,
        responsive: HTMLDivElement,
        overlay: HTMLDivElement,
        bottomContainer: HTMLDivElement,
        video: HTMLIFrameElement,
        bottom: HTMLDivElement,
        button: HTMLLinkElement,
        win = $(window),

        activeVideo: vbVideo,
        open: boolean = false,
        animations: Array<webAnimation> = [],

        defaults: vbOptions = {
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

    $.videobox = function(_video: vbVideo): boolean {
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
    }

    $.vbClose = function(): boolean {
        stop();
        $([wrap, bottomContainer, overlay]).toggleClass('visible', false);
        $(wrap).css({
            top: 0,
            left: 0
        });
        activeVideo = null;
        return false;
    };

    $.fn.videobox = function(
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

            return $.videobox(_video);
        });
        return false;
    };

    function setup(newVideo: vbVideo): void {
        $(closeText).html(newVideo.options.closeText);
        $(newVideo.options.root).append([overlay, wrap]);
        setPlayerPosition();
    }

    function changeVideo(newVideo: vbVideo): boolean {

        activeVideo = newVideo;

        $(caption).html(activeVideo.title);

        var targetRatio = setPlayerSizePosition();

        open = true;

        var centerOrigin = {
            top: (activeVideo.origin ? -($(wrap).innerHeight() / 2 - activeVideo.origin.y) : 0) + 'px',
            left: (activeVideo.origin ? -($(wrap).innerWidth() / 2 - activeVideo.origin.x) : 0) + 'px',
            'maxWidth': activeVideo.origin ? activeVideo.origin.width + 'px' : activeVideo.options.initialWidth
        };

        var centerTarget = {
            top: '0px',
            left: '0px',
            'maxWidth': activeVideo.options.width + 'px'
        };

        $(center).css(centerOrigin);
        $([wrap, overlay]).toggleClass('visible', true);
        $(wrap).toggleClass('animating', true);

        if (activeVideo.origin) {
            var originRatio = ((activeVideo.origin.height * 100) / activeVideo.origin.width) || targetRatio;
            if (originRatio != targetRatio) {
                animations.push(responsive.animate([
                    { 'paddingBottom': originRatio + '%' },
                    { 'paddingBottom': targetRatio + '%' }
                ], activeVideo.options.animation));
            }
        }

        var centerAnimation = center.animate([
            centerOrigin,
            centerTarget
        ], activeVideo.options.animation);
        $(center).css(centerTarget);
        centerAnimation.addEventListener('finish', function() {
            var bottomAnimation = bottomContainer.animate([
                { 'maxHeight': '0px' },
                { 'maxHeight': '200px' }
            ], activeVideo.options.animation);
            $(bottomContainer).toggleClass('visible', true);
            bottomAnimation.addEventListener('finish', showVideo);
            animations.push(bottomAnimation);
        });
        animations.push(centerAnimation);
        return false;
    }

    function setPlayerSizePosition(): number {
        if (!activeVideo) return;

        setPlayerPosition();

        var width: number = activeVideo.options.width;
        var height: number = activeVideo.options.height;

        if (width + 2 * activeVideo.options.padding > $(wrap).innerWidth()) {
            var nw = $(wrap).innerWidth() - 2 * activeVideo.options.padding;
            height = (height * nw) / width;
            width = nw;
        }
        if (height + 2 * activeVideo.options.padding > $(wrap).innerHeight()) height = $(wrap).innerHeight() - 2 * activeVideo.options.padding;

        var ratio = (height * 100) / width;
        $(responsive).css('paddingBottom', ratio + '%');
        return ratio;
    }

    function setPlayerPosition(): void {
        var pos = $(wrap).position();
        var rect = $(wrap).offset();
        var bdy = $('html').offset();
        $(wrap).css({
            top: pos.top + bdy.top + window.scrollY - rect.top,
            left: pos.left + bdy.left + window.scrollX - rect.left
        });
    }

    function showVideo(): void {
        if (!open || $(video).attr('src') != '') return;
        $(video).show();
        video.src = activeVideo.url;
        $(wrap).toggleClass('animating', false);
    }

    function stop(): void {
        for (var i = 0; i < animations.length; i++) animations[i].cancel();
        animations = [];
        open = false;
        video.src = "";
        $(video).hide();
        $(wrap).toggleClass('animating', false);
    }

    $(window).on('load', function() {
        $(defaults.root).append(
            $([
                overlay = <HTMLDivElement>$('<div id="vbOverlay" />').click($.vbClose)[0],
                wrap = <HTMLDivElement>$('<div id="vbWrap" />')[0]
            ])
        );
        center = <HTMLDivElement>$('<div id="vbCenter" />').appendTo(wrap).append([
            responsive = <HTMLDivElement>$('<div id="vbResponsive" />')[0],
            bottomContainer = <HTMLDivElement>$('<div id="vbBottomContainer" />')[0],
        ])[0];
        video = <HTMLIFrameElement>$('<iframe id="vbVideo" frameborder="0" allowfullscreen="true" oallowfullscreen msallowfullscreen webkitallowfullscreen mozallowfullscreen />').css('display', 'none').appendTo(responsive)[0];
        bottom = <HTMLDivElement>$('<div id="vbBottom" />').appendTo(bottomContainer).append([
            button = <HTMLLinkElement>$('<a id="vbCloseLink" href="#" ><span id="vbCloseText">' + defaults.closeText + '</span><i class="vb-icon-close"></i></a>').click($.vbClose)[0],
            caption = $('<strong id="vbCaption" />')[0]
        ])[0];
        closeText = $(bottom).find('#vbCloseText')[0];

        win.on("resize", function() {
            if (!open || !activeVideo) return;
            setPlayerSizePosition();
        });
    });

})(jQuery);