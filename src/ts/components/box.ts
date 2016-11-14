import { create, applyStyles, vbOptions, vbVideo, hide, show, iterableToArray, toggleClass } from './helpers'
import { VbInlineObj as VbInline } from './inline'

export class Videobox {

    private closeText: HTMLSpanElement
    private center: HTMLDivElement
    private caption: HTMLElement
    private wrap: HTMLDivElement
    private responsive: HTMLDivElement
    private overlay: HTMLDivElement
    private bottomContainer: HTMLDivElement
    private video: HTMLIFrameElement
    private bottom: HTMLDivElement
    private button: HTMLLinkElement

    private activeVideo: vbVideo
    private isOpen: boolean = false
    private animations: Array<Animation> = []

    private defaults: vbOptions = {
        width: 720,
        height: 405,
        closeText: 'Close',
        padding: 30,
        initialWidth: '15%',
        root: document.body,
        animation: {
            duration: 500,
            iterations: 1,
            delay: 0,
            easing: 'ease-in-out'
        }
    }

    /** @internal */
    constructor(links?: Array<HTMLElement> | string, options: vbOptions = {}, linkMapper?: ((el: HTMLElement) => vbVideo)) {
        this.overlay = <HTMLDivElement>create('div', 'vbOverlay', () => this.close())
        this.defaults.root.appendChild(this.overlay)

        this.wrap = <HTMLDivElement>create('div', 'vbWrap')
        this.defaults.root.appendChild(this.wrap)

        this.center = <HTMLDivElement>create('div', 'vbCenter')
        this.wrap.appendChild(this.center)

        this.responsive = <HTMLDivElement>create('div', 'vbResponsive')
        this.center.appendChild(this.responsive)

        this.bottomContainer = <HTMLDivElement>create('div', 'vbBottomContainer')
        this.center.appendChild(this.bottomContainer)

        this.video = <HTMLIFrameElement>create('iframe', 'vbVideo')
        this.video.allowFullscreen = true
        this.video.frameBorder = '0px'
        hide(this.video)
        this.responsive.appendChild(this.video)

        this.bottom = <HTMLDivElement>create('div', 'vbBottom')
        this.bottomContainer.appendChild(this.bottom)

        this.button = <HTMLLinkElement>create('a', 'vbCloseLink', () => this.close())
        this.button.innerHTML = '<span id="vbCloseText">' + this.defaults.closeText + '</span><i class="vb-icon-close"></i>'
        this.bottom.appendChild(this.button)

        this.caption = create('strong', 'vbCaption')
        this.bottom.appendChild(this.caption)

        this.closeText = <HTMLSpanElement>this.button.querySelector('#vbCloseText')

        window.addEventListener('resize', () => {
            if (this.isOpen && this.activeVideo)
                this.setPlayerSizePosition()
        })

        if (links)
            this.bind(links, options, linkMapper || this.linkMapper)
    }

    /**
     * Map pop-up player to links 
     * 
     * @param links array of elements or query selector to bind Videobox to
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
            link['vbListener'] = (evt) => {
                evt.preventDefault()
                evt.stopPropagation()
                let video = linkMapper(link)
                video.options = Object.assign({}, options, video.options)
                this.open(video)
                return false
            }
            link.addEventListener('click', link['vbListener'])
        })
    }

    /**
     * Open Videobox pop-up player
     * 
     * @param video video to show
     */
    open(video: vbVideo): void {
        VbInline.close()
        this.close()

        video.options = Object.assign({}, this.defaults, video.options)
        this.setup(video)

        let link = video.origin.target
        let target = <HTMLElement>link.querySelector(link.getAttribute("data-target")) || link

        let bw = this.wrap.getBoundingClientRect()
        let bt = target.getBoundingClientRect()

        toggleClass(target, 'vb_line_fix', true)
        video.origin = Object.assign({}, {
            x: bt.left - bw.left + target.clientWidth / 2,
            y: bt.top - bw.top + target.clientHeight / 2,
            width: target.clientWidth,
            height: target.clientHeight
        }, video.origin)
        toggleClass(target, 'vb_line_fix', false)

        this.changeVideo(video)
    }

    /**
     * Close the open pop-up
     */
    close() {
        this.stop()
        new Array(this.wrap, this.bottomContainer, this.overlay).forEach(el => toggleClass(el, 'visible', false))
        this.wrap.style.top = '0px'
        this.wrap.style.left = '0px'
        this.activeVideo = null
        return false
    }

    /**
     * Calculate and set player position & size
     * 
     * @returns width to height ratio of the player (in percent)
     */
    setPlayerSizePosition(): number {
        if (!this.activeVideo) return

        this.setPlayerPosition(this.activeVideo.options.root)

        let width: number = this.activeVideo.options.width
        let height: number = this.activeVideo.options.height

        if (width + 2 * this.activeVideo.options.padding > this.wrap.clientWidth) {
            let nw = this.wrap.clientWidth - 2 * this.activeVideo.options.padding
            height = (height * nw) / width
            width = nw
        }
        if (height + 2 * this.activeVideo.options.padding > this.wrap.clientHeight)
            height = this.wrap.clientHeight - 2 * this.activeVideo.options.padding

        let ratio = (height * 100) / width
        this.responsive.style.paddingBottom = ratio + '%'
        return ratio
    }

    private linkMapper(el: HTMLElement): vbVideo {
        let options = JSON.parse(el.getAttribute("data-videobox")) || {}
        if (options.root) {
            let root = options.root
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

    private setup(newVideo: vbVideo): void {
        this.closeText.innerText = newVideo.options.closeText
        newVideo.options.root.appendChild(this.overlay)
        newVideo.options.root.appendChild(this.wrap)
        this.setPlayerPosition(newVideo.options.root)
    }

    private setPlayerPosition(root: HTMLElement = this.activeVideo.options.root): void {
        let parent = <HTMLElement>this.wrap.offsetParent
        let wbr = this.wrap.getBoundingClientRect()
        let obr = this.overlay.getBoundingClientRect()
        let pos = {
            top: this.wrap.offsetTop - parent.offsetTop,
            left: this.wrap.offsetLeft - parent.offsetLeft
        }
        let diff = {
            top: obr.top - wbr.top,
            left: obr.left - wbr.left
        }
        this.wrap.style.top = (pos.top + diff.top) + 'px'
        this.wrap.style.left = (pos.left + diff.left) + 'px'
    }

    private changeVideo(newVideo: vbVideo): void {
        this.activeVideo = newVideo
        this.caption.innerHTML = this.activeVideo.title

        let targetRatio = this.setPlayerSizePosition()

        this.isOpen = true

        let centerOrigin = {
            top: (this.activeVideo.origin ? -(this.wrap.clientHeight / 2 - this.activeVideo.origin.y) : 0) + 'px',
            left: (this.activeVideo.origin ? -(this.wrap.clientWidth / 2 - this.activeVideo.origin.x) : 0) + 'px',
            'maxWidth': this.activeVideo.origin ? this.activeVideo.origin.width + 'px' : this.activeVideo.options.initialWidth
        }

        let centerTarget = {
            top: '0px',
            left: '0px',
            'maxWidth': this.activeVideo.options.width + 'px'
        }

        applyStyles(this.center, centerOrigin)
        new Array(this.wrap, this.overlay).forEach(el => toggleClass(el, 'visible', true))
        toggleClass(this.wrap, 'animating', true)

        if (this.activeVideo.origin) {
            let originRatio = ((this.activeVideo.origin.height * 100) / this.activeVideo.origin.width) || targetRatio
            if (originRatio != targetRatio)
                this.animations.push(this.responsive.animate([
                    { 'paddingBottom': originRatio + '%' },
                    { 'paddingBottom': targetRatio + '%' }
                ], this.activeVideo.options.animation))

        }

        let centerAnimation = this.center.animate([
            centerOrigin,
            centerTarget
        ], this.activeVideo.options.animation)
        centerAnimation.onfinish = () => this.animateBotton()
        this.animations.push(centerAnimation)
        applyStyles(this.center, centerTarget)
        centerAnimation.play()
    }

    private animateBotton(): void {
        let bottomAnimation = this.bottomContainer.animate([
            { 'maxHeight': '0px' },
            { 'maxHeight': '200px' }
        ], this.activeVideo.options.animation)
        toggleClass(this.bottomContainer, 'visible', true)
        bottomAnimation.onfinish = () => this.showVideo()
        this.animations.push(bottomAnimation)
        bottomAnimation.play()
    }

    private showVideo(): void {
        if (!this.isOpen || this.video.getAttribute('src')) return
        show(this.video)
        this.video.setAttribute('src', this.activeVideo.url)
        toggleClass(this.wrap, 'animating', false)
    }

    private stop(): void {
        this.animations.forEach(anim => anim.cancel())
        this.animations = []
        this.isOpen = false
        this.video.setAttribute('src', '')
        hide(this.video)
        toggleClass(this.wrap, 'animating', false)
    }
}

export const VideoboxObj = new Videobox()
window['Videobox'] = VideoboxObj

export declare interface JQueryStatic {
    /**
     * Open Videobox pop-up player
     * 
     * @param video video to show
     */
    videobox: (video: vbVideo) => void

    /**
     * Close the open pop-up
     */
    vbClose: () => void
}

export declare interface JQuery {
    /**
     * Map pop-up player to elements matched by the query
     * 
     * @example 
     * ```javascript
     * $('a[rel=videobox]').videobox() // Bind the pop-up effect to every <a rel="videobox"> element
     * ```
     * 
     * @param options player configuration
     * @param linkMapper function to get a Videobox video object from the clicked element
     */
    videobox: (options?: vbOptions, linkMapper?: ((el: HTMLElement) => vbVideo)) => void
}

if (typeof (jQuery) !== 'undefined') {
    jQuery['videobox'] = function (video: vbVideo): void {
        VideoboxObj.open(video)
    }

    jQuery['vbClose'] = function (): void {
        VideoboxObj.close()
    }

    jQuery.fn.videobox = function (options: vbOptions = {}, linkMapper?: ((el: HTMLElement) => vbVideo)): void {
        let elements = iterableToArray<HTMLElement>(this)
        if (linkMapper)
            VideoboxObj.bind(elements, options, linkMapper)
        else
            VideoboxObj.bind(elements, options)
    }
}

function libBind($) {
    $.videobox = function (video: vbVideo): void {
        VideoboxObj.open(video)
    }

    $.vbClose = function (): void {
        VideoboxObj.close()
    }

    $.fn.videobox = function (options: vbOptions = {}, linkMapper?: ((el: HTMLElement) => vbVideo)): void {
        let elements = iterableToArray<HTMLElement>(this)
        if (linkMapper)
            VideoboxObj.bind(elements, options, linkMapper)
        else
            VideoboxObj.bind(elements, options)
    }
}

if (typeof jQuery != 'undefined') libBind(jQuery)
if (typeof Zepto != 'undefined') libBind(Zepto)