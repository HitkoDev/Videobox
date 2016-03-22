/// <reference path="headers.d.ts" />

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
        content: HTMLDivElement,
        win = $(window),

        videos: Array<vbVideo> = [],
        activeVideo: vbVideo,
        open: boolean = false,
        animations: Array<webAnimation> = [],
        options: vbOptions = {},

        defaults: vbOptions = {
            videoWidth: 720,
            videoHeight: 405,
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

    $.videobox = function(_videos: Array<vbVideo>, startVideo: number, _options: vbOptions = {}): boolean {
        $.vbiClose();
        $.vbClose();

        $.extend(options, defaults, _options);
        setup();

        if (_videos[startVideo].origin && _videos[startVideo].origin.target) {
            var link = _videos[startVideo].origin.target;
            var target = $($(link).find($(link).attr("data-target"))[0] || link);

            var bw = wrap.getBoundingClientRect();
            var bt = target[0].getBoundingClientRect();

            target.toggleClass('vb_line_fix', true);
            _videos[startVideo].origin.x = _videos[startVideo].origin.x || (bt.left - bw.left + target.innerWidth() / 2);
            _videos[startVideo].origin.y = _videos[startVideo].origin.y || (bt.top - bw.top + target.innerHeight() / 2);
            _videos[startVideo].origin.width = _videos[startVideo].origin.width || target.innerWidth();
            _videos[startVideo].origin.height = _videos[startVideo].origin.height || target.innerHeight();
            target.toggleClass('vb_line_fix', false);
        }

        videos = _videos;
        changeVideo(startVideo);

        return false;
    }

    $.vbClose = function(): boolean {
        stop();
        $([wrap, bottomContainer, overlay]).toggleClass('visible', false);
        $(wrap).css({
            top: 0,
            left: 0
        });
        if (activeVideo) activeVideo = undefined;
        return false;
    };

    $.fn.videobox = function(
        _options: vbOptions = {},
        linkMapper: ((el: HTMLElement, i: number) => vbVideo) = (el: HTMLElement, i: number): vbVideo=> {
            var v: vbVideo = {
                url: el.getAttribute("href") || "",
                title: el.getAttribute("title") || "",
                width: parseInt(el.getAttribute("data-videowidth")) || undefined,
                height: parseInt(el.getAttribute("data-videoheight")) || undefined,
                index: i
            };
            return v;
        }
    ): boolean {

        var links: JQuery = <JQuery>this;

        links.unbind("click").click(function(evt: JQueryEventObject): boolean {

            var link: HTMLElement = this, startIndex: number = 0, mappedLinks: Array<vbVideo> = [];

            for (var i = 0; i < links.length; i++) {
                if (links[i] == link) startIndex = i;
                mappedLinks.push(linkMapper(links[i], i));
            }

            mappedLinks[startIndex].origin = {
                target: link
            };

            return $.videobox(mappedLinks, startIndex, _options);
        });
        return false;
    };

    function setup(): void {
        $(closeText).html(options.closeText);
        $(center).css('padding', options.padding);
        $(options.root).append([overlay, wrap]);
        $(wrap).css({
            top: $(options.root).scrollTop(),
            left: $(options.root).scrollLeft()
        });
    }

    function changeVideo(i: number): boolean {
        if (i < 0 || i >= videos.length) return false;

        activeVideo = videos[i];
        $(caption).html(activeVideo.title);

        setPlayerSizePosition();

        open = true;

        var centerOrigin = {
            top: (activeVideo.origin ? -($(wrap).innerHeight() / 2 - activeVideo.origin.y) : 0) + 'px',
            left: (activeVideo.origin ? -($(wrap).innerWidth() / 2 - activeVideo.origin.x) : 0) + 'px',
            'max-width': (activeVideo.origin ? (activeVideo.origin.width + 2 * options.padding) + 'px' : options.initialWidth)
        };

        var centerTarget = {
            top: '0px',
            left: '0px',
            'max-width': (activeVideo.width || options.videoWidth) + 2 * options.padding + 'px'
        };

        $(center).css(centerOrigin);
        $([wrap, overlay]).toggleClass('visible', true);
        $(wrap).toggleClass('animating', true);

        var centerAnimation = center.animate([
            centerOrigin,
            centerTarget
        ], options.animation);
        centerAnimation.addEventListener('finish', function() {
            $(center).css(centerTarget);
            var bottomAnimation = bottomContainer.animate([
                { 'max-height': '0px' },
                { 'max-height': '200px' }
            ], options.animation);
            bottomAnimation.addEventListener('finish', function() {
                $(bottomContainer).toggleClass('visible', true);
                showVideo();
            });
            animations.push(bottomAnimation);
        });
        animations.push(centerAnimation);
        return false;
    }

    function setPlayerSizePosition(): void {
        if (!activeVideo) return;

        var width: number = activeVideo.width || options.videoWidth;
        var height: number = activeVideo.height || options.videoHeight;

        $(wrap).css({
            top: $(options.root).scrollTop(),
            left: $(options.root).scrollLeft()
        });

        if (width + 2 * options.padding > $(wrap).innerWidth()) {
            var nw = $(wrap).innerWidth() - 2 * options.padding;
            height = (height * nw) / width;
            width = nw;
        }
        if (height + 2 * options.padding > $(wrap).innerHeight()) height = $(wrap).innerHeight() - 2 * options.padding;

        var ratio = (height * 100) / width;
        $(responsive).css('padding-bottom', ratio + '%');
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
        center = <HTMLDivElement>$('<div id="vbCenter" />').appendTo(wrap)[0];
        content = <HTMLDivElement>$('<div id="vbContent" />').appendTo(center).append([
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