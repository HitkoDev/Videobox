import { create, applyStyles, vbOptions, vbVideo, hide, show, iterableToArray, insertAfter, toggleClass } from './helpers'
import { VideoboxObj as Videobox } from './box'

export class VbInline {

    private wrap: HTMLDivElement
    private responsive: HTMLDivElement
    private caption: HTMLElement
    private button: HTMLDivElement
    private video: HTMLIFrameElement

    private activeVideo: vbVideo
    private isOpen: boolean = false
    private hidding: boolean = false
    private animations: Array<Animation> = []
    private hidden: Array<HTMLElement> = []
    private timer: number

    private defaults: vbOptions = {
        width: 720,
        height: 405,
        closeText: 'Close',
        padding: 30,
        closeTimeout: 1000,
        animation: {
            duration: 500,
            iterations: 1,
            delay: 0,
            easing: 'ease-in-out'
        }
    }

    /** @internal */
    constructor(links?: Array<HTMLElement> | string, options: vbOptions = {}, linkMapper?: ((el: HTMLElement) => vbVideo)) {
        this.wrap = <HTMLDivElement>create('div', 'vbiWrap')

        this.responsive = <HTMLDivElement>create('div', 'vbiResponsive')
        this.wrap.appendChild(this.responsive)

        this.caption = document.createElement('span')
        this.caption.className = 'vb_video_title'
        this.wrap.appendChild(this.caption)

        this.button = <HTMLDivElement>create('div', 'vbiClose', () => this.close())
        this.button.innerHTML = '<i class="vb-icon-circle-close-invert"></i>'
        this.wrap.appendChild(this.button)

        this.video = <HTMLIFrameElement>create('iframe', 'vbiVideo')
        this.video.allowFullscreen = true
        this.video.frameBorder = '0px'
        hide(this.video)
        this.responsive.appendChild(this.video)

        if (links)
            this.bind(links, options, linkMapper || this.linkMapper)

    }

    /**
     * Map inline player to elements matched by the query 
     * 
     * @param links array of elements or query selector to bind inline player to
     * @param options player configuration
     * @param linkMapper function to get a Videobox video object from the clicked element
     */
    bind(
        links: Array<HTMLElement> | string,
        options: vbOptions = {},
        linkMapper: ((el: HTMLElement) => vbVideo) = this.linkMapper
    ): void {
        if (typeof links == 'string')
            links = iterableToArray<HTMLElement>(document.querySelectorAll(links))

        links.forEach(link => {
            if (link['vbListener']) link.removeEventListener('click', link['vbListener'])
            if (link['vbiListener']) link.removeEventListener('click', link['vbiListener'])
            link['vbiListener'] = (evt) => {
                evt.preventDefault()
                evt.stopPropagation()
                let video = linkMapper(link)
                video.options = Object.assign({}, options, video.options)
                this.open(video)
                return false
            }
            link.addEventListener('click', link['vbiListener'])
        })
    }

    /**
     * Open an inline player
     * 
     * @param video video to show
     */
    open(video: vbVideo): void {
        if ('Videobox' in window)
            window['Videobox'].close()

        video.options = Object.assign({}, this.defaults, video.options)

        let link = video.origin.target
        let target = <HTMLElement>link.querySelector(link.getAttribute("data-target")) || link

        toggleClass(target, 'vb_line_fix', true)
        video.origin = Object.assign({}, {
            x: target.clientWidth / 2,
            y: target.clientHeight / 2,
            width: target.clientWidth,
            height: target.clientHeight
        }, video.origin)
        toggleClass(target, 'vb_line_fix', false)

        this.close(() => this.changeVideo(video))
    }

    /**
     * Close the open inline player
     * 
     * @param callback function to run when close animation is over
     */
    close(callback?: () => any) {
        this.stop()

        if (!this.hidding)
            if (this.wrap.parentElement && this.activeVideo) {
                this.hidding = true
                let maxW = this.activeVideo.origin.width + 'px'
                let v1 = this.wrap.animate([
                    { 'maxWidth': (this.activeVideo.options.width + 2 * this.activeVideo.options.padding) + 'px' },
                    { 'maxWidth': maxW }
                ], this.activeVideo.options.animation)

                v1.onfinish = () => this.hide(callback)
                this.wrap.style.maxWidth = maxW
                v1.play()

                let padding = ((this.activeVideo.origin.height * 100) / this.activeVideo.origin.width) + '%'
                let v2 = this.responsive.animate([
                    { 'paddingBottom': ((this.activeVideo.options.height * 100) / this.activeVideo.options.width) + '%' },
                    { 'paddingBottom': padding }
                ], this.activeVideo.options.animation)
                this.responsive.style.paddingBottom = padding
                v2.play()
            } else
                this.hide(callback)

        return false
    }

    private linkMapper(el: HTMLElement): vbVideo {
        return {
            url: el.getAttribute("href") || "",
            title: el.getAttribute("title") || "",
            options: JSON.parse(el.getAttribute("data-videobox")) || {},
            origin: { target: el }
        }
    }

    private setup(): void {
        insertAfter(this.wrap, this.activeVideo.origin.target)
        hide(this.activeVideo.origin.target)
        this.hidden.push(this.activeVideo.origin.target)
    }

    private changeVideo(newVideo: vbVideo): void {

        this.activeVideo = newVideo

        this.setup()

        this.wrap.setAttribute('style', this.activeVideo.options.style)
        this.wrap.setAttribute('class', this.activeVideo.options.class)
        this.caption.innerHTML = this.activeVideo.title
        toggleClass(this.button, 'visible', true)
        this.isOpen = true

        let wrapOrigin = {
            'maxWidth': this.activeVideo.origin.width + 'px'
        }
        let wrapDest = {
            'maxWidth': (this.activeVideo.options.width + 2 * this.activeVideo.options.padding) + 'px'
        }
        let animation = this.wrap.animate([wrapOrigin, wrapDest], this.activeVideo.options.animation)
        applyStyles(this.wrap, wrapDest)
        animation.onfinish = () => this.showVideo()
        this.animations.push(animation)
        animation.play()

        let responsiveDest = {
            'paddingBottom': ((this.activeVideo.options.height * 100) / this.activeVideo.options.width) + '%'
        }
        let responsiveOrigin = {
            'paddingBottom': ((this.activeVideo.origin.height * 100) / this.activeVideo.origin.width) + '%'
        }
        let animation2 = this.responsive.animate([responsiveOrigin, responsiveDest], this.activeVideo.options.animation)
        animation2.onfinish = () => {
            this.timer = window.setTimeout(() => toggleClass(this.button, 'visible', false), newVideo.options.closeTimeout)
        }
        this.animations.push(animation2)
        animation2.play()
        applyStyles(this.responsive, responsiveDest)
    }

    private showVideo() {
        if (!this.isOpen) return
        show(this.video)
        this.video.setAttribute('src', this.activeVideo.url)
    }

    private hide(callback?: () => any) {
        if (this.wrap.parentElement)
            this.wrap.parentNode.removeChild(this.wrap)

        this.hidden.forEach(el => show(el))
        this.hidden = []

        this.hidding = false
        this.activeVideo = null
        if (typeof callback == "function")
            callback()
    }

    private stop() {
        this.animations.forEach(anim => anim.cancel())
        this.animations = []
        this.isOpen = false
        this.video.setAttribute('src', '')
        window.clearTimeout(this.timer)
        hide(this.video)
    }
}

export const VbInlineObj = new VbInline()
window['VbInline'] = VbInlineObj

export declare interface JQueryStatic {
    /**
     * Open an inline player
     * 
     * @param video video to show
     */
    vbInline: (video: vbVideo) => void

    /**
     * Close the open inline player
     * 
     * @param callback function to run when close animation is over
     */
    vbiClose: (callback?: () => any) => void
}

export declare interface JQuery {
    /**
     * Map inline player to elements matched by the query
     * 
     * @example 
     * ```javascript
     * $('a[rel=vbinline]').vbInline() // Bind the inline effect to every <a rel="vbinline"> element
     * ```
     * 
     * @param options player configuration
     * @param linkMapper function to get a Videobox video object from the clicked element
     */
    vbInline: (options?: vbOptions, linkMapper?: ((el: HTMLElement) => vbVideo)) => void
}

function libBind($) {
    $.vbInline = function (video: vbVideo): void {
        VbInlineObj.open(video)
    }

    $.vbiClose = function (callback?: () => any): void {
        VbInlineObj.close(callback)
    }

    $.fn.vbInline = function (options: vbOptions = {}, linkMapper?: ((el: HTMLElement) => vbVideo)): void {
        let elements = iterableToArray<HTMLElement>(this)
        if (linkMapper)
            VbInlineObj.bind(elements, options, linkMapper)
        else
            VbInlineObj.bind(elements, options)
    }
}

if (typeof jQuery != 'undefined') libBind(jQuery)
if (typeof Zepto != 'undefined') libBind(Zepto)