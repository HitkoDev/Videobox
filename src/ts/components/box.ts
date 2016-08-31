/**
 * Function to load the pop-up effect
 */
export function box($: JQueryStatic) {

    let
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
        }

    $.videobox = video => {
        $.vbiClose()
        $.vbClose()

        video.options = $.extend(true, {}, defaults, video.options)
        setup(video)

        let link = video.origin.target
        let target = $($(link).find($(link).attr("data-target"))[0] || link)

        let bw = wrap.getBoundingClientRect()
        let bt = target[0].getBoundingClientRect()

        target.toggleClass('vb_line_fix', true)
        video.origin = $.extend(true, {}, {
            x: bt.left - bw.left + target.innerWidth() / 2,
            y: bt.top - bw.top + target.innerHeight() / 2,
            width: target.innerWidth(),
            height: target.innerHeight()
        }, video.origin)
        target.toggleClass('vb_line_fix', false)

        changeVideo(video)

        return false
    }

    $.vbClose = () => {
        stop()
        $([wrap, bottomContainer, overlay]).toggleClass('visible', false)
        $(wrap).css({
            top: 0,
            left: 0
        })
        activeVideo = null
        return false
    }

    $.fn.videobox = function (
        options: vbOptions = {},
        linkMapper: ((el: HTMLElement) => vbVideo) = el => {
            let options = JSON.parse(el.getAttribute("data-videobox")) || {}
            if (options.root) {
                let root = $(options.root)
                if (root.length > 0)
                    options.root = root[0]
                else
                    options.pop('root')
            }
            return {
                url: el.getAttribute("href") || "",
                title: el.getAttribute("title") || "",
                options: options,
                origin: { target: el }
            }
        }
    ): boolean {

        let links: JQuery = <JQuery>this

        links.off("click").on('click', function (evt) {

            let video = linkMapper(this)

            video.options = $.extend(true, {}, options, video.options)

            return $.videobox(video)
        })
        return false
    }

    function setup(newVideo: vbVideo): void {
        $(closeText).html(newVideo.options.closeText)
        $(newVideo.options.root).append([overlay, wrap])
        setPlayerPosition(newVideo.options.root)
    }

    function changeVideo(newVideo: vbVideo): boolean {

        activeVideo = newVideo

        $(caption).html(activeVideo.title)

        let targetRatio = setPlayerSizePosition()

        open = true

        let centerOrigin = {
            top: (activeVideo.origin ? -($(wrap).innerHeight() / 2 - activeVideo.origin.y) : 0) + 'px',
            left: (activeVideo.origin ? -($(wrap).innerWidth() / 2 - activeVideo.origin.x) : 0) + 'px',
            'maxWidth': activeVideo.origin ? activeVideo.origin.width + 'px' : activeVideo.options.initialWidth
        }

        let centerTarget = {
            top: '0px',
            left: '0px',
            'maxWidth': activeVideo.options.width + 'px'
        }

        $(center).css(centerOrigin)
        $([wrap, overlay]).toggleClass('visible', true)
        $(wrap).toggleClass('animating', true)

        if (activeVideo.origin) {
            let originRatio = ((activeVideo.origin.height * 100) / activeVideo.origin.width) || targetRatio
            if (originRatio != targetRatio)
                animations.push(responsive.animate([
                    { 'paddingBottom': originRatio + '%' },
                    { 'paddingBottom': targetRatio + '%' }
                ], activeVideo.options.animation))

        }

        let centerAnimation = center.animate([
            centerOrigin,
            centerTarget
        ], activeVideo.options.animation)
        $(center).css(centerTarget)
        centerAnimation.addEventListener('finish', () => {
            let bottomAnimation = bottomContainer.animate([
                { 'maxHeight': '0px' },
                { 'maxHeight': '200px' }
            ], activeVideo.options.animation)
            $(bottomContainer).toggleClass('visible', true)
            bottomAnimation.addEventListener('finish', showVideo)
            animations.push(bottomAnimation)
        })
        animations.push(centerAnimation)
        return false
    }

    function setPlayerSizePosition(): number {
        if (!activeVideo) return

        setPlayerPosition(activeVideo.options.root)

        let width: number = activeVideo.options.width
        let height: number = activeVideo.options.height

        if (width + 2 * activeVideo.options.padding > $(wrap).innerWidth()) {
            let nw = $(wrap).innerWidth() - 2 * activeVideo.options.padding
            height = (height * nw) / width
            width = nw
        }
        if (height + 2 * activeVideo.options.padding > $(wrap).innerHeight())
            height = $(wrap).innerHeight() - 2 * activeVideo.options.padding

        let ratio = (height * 100) / width
        $(responsive).css('paddingBottom', ratio + '%')
        return ratio
    }

    function setPlayerPosition(root): void {
        let pos = $(wrap).position()
        let rect = $(wrap).offset()
        let bdy = $('html').offset()
        if ($(root)[0] != $('body')[0]) {
            pos.top += $(root).scrollTop()
            pos.left += $(root).scrollLeft()
        }
        $(wrap).css({
            top: pos.top + bdy.top + window.scrollY - rect.top,
            left: pos.left + bdy.left + window.scrollX - rect.left
        })
    }

    function showVideo(): void {
        if (!open || $(video).attr('src') != '') return
        $(video).show()
        video.src = activeVideo.url
        $(wrap).toggleClass('animating', false)
    }

    function stop(): void {
        for (let i = 0; i < animations.length; i++)
            animations[i].cancel()
        animations = []
        open = false
        video.src = ""
        $(video).hide()
        $(wrap).toggleClass('animating', false)
    }

    $(window).on('load', () => {
        $(defaults.root).append(
            $([
                overlay = <HTMLDivElement>$('<div id="vbOverlay" />').click($.vbClose)[0],
                wrap = <HTMLDivElement>$('<div id="vbWrap" />')[0]
            ])
        )
        center = <HTMLDivElement>$('<div id="vbCenter" />').appendTo(wrap).append([
            responsive = <HTMLDivElement>$('<div id="vbResponsive" />')[0],
            bottomContainer = <HTMLDivElement>$('<div id="vbBottomContainer" />')[0],
        ])[0]
        video = <HTMLIFrameElement>$('<iframe id="vbVideo" frameborder="0" allowfullscreen="true" oallowfullscreen msallowfullscreen webkitallowfullscreen mozallowfullscreen />').css('display', 'none').appendTo(responsive)[0]
        bottom = <HTMLDivElement>$('<div id="vbBottom" />').appendTo(bottomContainer).append([
            button = <HTMLLinkElement>$('<a id="vbCloseLink" href="#" ><span id="vbCloseText">' + defaults.closeText + '</span><i class="vb-icon-close"></i></a>').click($.vbClose)[0],
            caption = $('<strong id="vbCaption" />')[0]
        ])[0]
        closeText = $(bottom).find('#vbCloseText')[0]

        win.on("resize", () => {
            if (!open || !activeVideo) return
            setPlayerSizePosition()
        })
    })

}