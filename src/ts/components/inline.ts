/**
 * Function to load the inlline effect
 */
export function inline($: JQueryStatic) {

    let
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
        }

    $.vbinline = video => {
        $.vbClose()

        video.options = $.extend(true, {}, defaults, video.options)

        let link = video.origin.target
        let target = $($(link).find($(link).attr("data-target"))[0] || link)

        target.toggleClass('vb_line_fix', true)
        video.origin = $.extend(true, {}, {
            width: target.innerWidth(),
            height: target.innerHeight()
        }, video.origin)
        target.toggleClass('vb_line_fix', false)

        $.vbiClose(() => changeVideo(video))
        return false
    }

    $.vbiClose = callback => {
        stop()

        if (!hidding)
            if ($(wrap).parent().length > 0 && activeVideo) {
                hidding = true
                let maxW = (activeVideo.origin ? activeVideo.origin.width : activeVideo.options.initialWidth) + 'px'
                let v1 = wrap.animate([
                    {
                        'maxWidth': (activeVideo.options.width + 2 * activeVideo.options.padding) + 'px'
                    }, {
                        'maxWidth': maxW
                    }
                ], activeVideo.options.animation)
                $(wrap).css('maxWidth', maxW)
                v1.addEventListener('finish', () => {
                    $(wrap).detach()
                    for (let i = 0; i < hidden.length; i++)
                        $(hidden[i]).show()

                    hidden = []
                    hidding = false
                    activeVideo = null
                    if (typeof callback == "function")
                        callback()
                })

                if (activeVideo.origin) {
                    let padding = ((activeVideo.origin.height * 100) / activeVideo.origin.width) + '%'
                    let v2 = responsive.animate([
                        {
                            'paddingBottom': ((activeVideo.options.height * 100) / activeVideo.options.width) + '%'
                        }, {
                            'paddingBottom': padding
                        }
                    ], activeVideo.options.animation)
                    $(responsive).css('paddingBottom', padding)
                }
            } else {
                if ($(wrap).parent().length > 0) {
                    $(wrap).detach()
                    for (let i = 0; i < hidden.length; i++)
                        $(hidden[i]).show()

                    hidden = []
                }
                activeVideo = null
                if (typeof callback == "function")
                    callback()
            }

        return false
    }

    $.fn.vbinline = function (
        options: vbOptions = {},
        linkMapper: ((el: HTMLElement) => vbVideo) = el => {
            return {
                url: el.getAttribute("href") || "",
                title: el.getAttribute("title") || "",
                options: JSON.parse(el.getAttribute("data-videobox")) || {},
                origin: { target: el }
            }
        }
    ): boolean {

        let links: JQuery = <JQuery>this

        links.off("click").on('click', function (evt) {

            let video = linkMapper(this)

            video.options = $.extend(true, {}, options, video.options)

            return $.vbinline(video)

        })
        return false
    }

    function changeVideo(newVideo: vbVideo): void {

        activeVideo = newVideo

        setup()

        $(wrap).attr('style', activeVideo.options.style)
        $(wrap).attr('class', activeVideo.options.class)
        $(caption).html(activeVideo.title)
        open = true

        let wrapOrigin = {
            'maxWidth': (activeVideo.origin ? activeVideo.origin.width : activeVideo.options.initialWidth) + 'px'
        }
        let wrapDest = {
            'maxWidth': (activeVideo.options.width + 2 * activeVideo.options.padding) + 'px'
        }
        let animation = wrap.animate([wrapOrigin, wrapDest], activeVideo.options.animation)
        $(wrap).css(wrapDest)
        animation.addEventListener('finish', showVideo)
        animations.push(animation)

        let responsiveDest = {
            'paddingBottom': ((activeVideo.options.height * 100) / activeVideo.options.width) + '%'
        }
        if (activeVideo.origin) {
            let responsiveOrigin = {
                'paddingBottom': ((activeVideo.origin.height * 100) / activeVideo.origin.width) + '%'
            }
            let animation = responsive.animate([responsiveOrigin, responsiveDest], activeVideo.options.animation)
            animations.push(animation)
        }
        $(responsive).css(responsiveDest)
    }

    function setup(): void {
        $(activeVideo.origin.target).after(wrap)
        $(activeVideo.origin.target).hide()
        hidden.push(activeVideo.origin.target)
    }

    function showVideo() {
        if (!open) return
        $(video).show()
        video.src = activeVideo.url
    }

    function stop() {
        for (let i = 0; i < animations.length; i++)
            animations[i].cancel()
        animations = []
        open = false
        video.src = ""
        $(video).hide()
    }

    $(window).on('load', () => {
        wrap = <HTMLDivElement>$('<div id="vbiWrap" />').append([
            responsive = <HTMLDivElement>$('<div id="vbiResponsive" />')[0],
            caption = $('<span class="vb_video_title"></span>')[0],
            button = <HTMLDivElement>$('<div id="vbiClose"><i class="vb-icon-circle-close-invert"></i></div>').click($.vbiClose)[0],
        ])[0]
        video = <HTMLIFrameElement>$('<iframe id="vbiVideo" frameborder="0" allowfullscreen="true" oallowfullscreen msallowfullscreen webkitallowfullscreen mozallowfullscreen />').css('display', 'none').appendTo(responsive)[0]
    })

}