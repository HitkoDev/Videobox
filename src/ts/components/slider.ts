import { createClass, applyStyles, vbOptions, vbVideo, hide, show, iterableToArray, insertAfter } from './helpers'
import { VbInlineObj as VbInline } from './inline'

function detach(elements: Array<HTMLElement>): void {
    elements.forEach(el => {
        if ('querySelector' in el && el.querySelector('#vbiWrap')) VbInline.close()
        el.remove()
    })
}

/**
 * Interface for Videobox slider configuration
 */
export interface vbSliderOptions {

    /**
     * if true, slider will scroll all visible elements
     */
    moveAll?: boolean,

    /**
     * target selector
     */
    target?: string,

    /**
     * transition duration for one element
     */
    singleDuration?: number,

    /**
     * clicks within the timeout are processed together
     */
    doubleClickTimeout?: number,

    /**
     * animation properties (see <a href="https://w3c.github.io/web-animations/">web animations specifications</a>)
     */
    animation?: KeyframeAnimationOptions
}

/**
 * Implementation of the Videobox thumbnail slider interface 
 */
export class VbSlider {

    private static sliders: Array<VbSlider> = []

    /**
     * Map Videobox slider to elements matched by the query
     * 
     * @param elements array of elements or query selector to bind Videobox slider to
     * @param options slider configuration
     * @returns array of sliders matching the corresponding elements
     */
    static bind(elements: Array<HTMLElement> | string, options: vbSliderOptions = {}): Array<VbSlider> {
        if (typeof elements == 'string')
            elements = iterableToArray<HTMLElement>(document.querySelectorAll(elements))

        let sliders: Array<VbSlider> = []
        elements.forEach(target => {
            let sizeTarget = (target.getAttribute('data-target') || '').trim()
            let move = (target.getAttribute('data-move') || 'single').trim()
            let opts: vbSliderOptions = {}

            if (sizeTarget)
                opts.target = sizeTarget

            if (move)
                opts.moveAll = move == 'all'

            sliders.push(this.getSlider(target, Object.assign({}, options, opts)))
        })
        return sliders
    }

    /**
     * Create a new Videobox slider, or find an existing slider and update it's configuration
     * 
     * @param target an element containing the slider items
     * @param options slider configuration
     * @returns slider containing the target element
     */
    static getSlider(target: HTMLElement, options: vbSliderOptions = {}): VbSlider {
        let slider = this.sliders.find(slider => slider.target == target)
        if (slider) {
            slider.options = Object.assign(slider.options, options)
            return slider
        } else {
            return new VbSlider(target, options)
        }
    }

    static updateSizes() {
        this.sliders.forEach(slider => slider.setCount())
    }

    private target: HTMLElement
    private outer: HTMLDivElement
    private wrap: HTMLDivElement
    private content: HTMLDivElement
    private prev: HTMLDivElement
    private next: HTMLDivElement
    private buttons: Array<HTMLElement>

    private basis: number
    private queue: Array<string> = []
    private timeout: number = -1
    private moving: boolean = false
    private visible: number = -1
    private detachedElements: Array<HTMLElement> = []

    private options: vbSliderOptions = {
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
    }

    /**
     * Array of currently visible elements (includes both new and old elements when moving)
     */
    get attached(): Array<HTMLElement> {
        return iterableToArray<HTMLElement>(this.target.children)
    }

    /**
     * True when slider is moving
     */
    get isMoving(): boolean {
        return this.moving
    }

    /**
     * Scroll slider to the left
     */
    showPrev(): void {
        this.queueMove('r')
    }

    /**
     * Scroll slider to the right 
     */
    showNext(): void {
        this.queueMove('l')
    }

    /**
     * Set base width
     * 
     * @param basis new base width
     */
    setBasis(basis: number): void {
        if (basis != this.basis) {
            this.basis = basis
            this.setCount()
        }
    }

    /**
     * Get the slider's target element
     */
    getTarget(): HTMLElement {
        return this.target
    }

    private constructor(target: HTMLElement, options: vbSliderOptions = {}) {
        this.outer = <HTMLDivElement>createClass('div', 'vb_slider_outer')

        this.prev = <HTMLDivElement>createClass('div', 'vb_slider_prev', () => this.showPrev())
        this.prev.innerHTML = '<i class="vb-icon-prev"></i>'
        this.outer.appendChild(this.prev)

        this.wrap = <HTMLDivElement>createClass('div', 'vb_slider_wrap')
        this.outer.appendChild(this.wrap)

        this.next = <HTMLDivElement>createClass('div', 'vb_slider_next', () => this.showNext())
        this.next.innerHTML = '<i class="vb-icon-next"></i>'
        this.outer.appendChild(this.next)

        this.content = <HTMLDivElement>createClass('div', 'vb_slider_cont')
        this.wrap.appendChild(this.content)

        this.buttons = iterableToArray<HTMLElement>(this.outer.querySelectorAll('i'))

        this.target = target
        let elements = this.attached

        insertAfter(this.outer, this.target)
        this.content.appendChild(this.target)

        this.basis = parseInt(target.getAttribute('data-width')) || elements[0].clientWidth

        this.options = Object.assign(this.options, options)

        this.content.classList.toggle('vb-slider__move-all', this.options.moveAll)

        this.setCount()
    }

    private queueMove(dir: string): void {
        if (this.queue.length > 0 && this.queue[this.queue.length - 1] != dir)
            this.queue.pop()
        else
            this.queue.push(dir)

        if (this.timeout >= 0)

            clearTimeout(this.timeout)
        this.timeout = <any>setTimeout(() => {
            this.timeout = -1
            if (!this.moving && this.queue.length > 0)
                this.move()
        }, this.options.doubleClickTimeout)
    }

    private move(): void {
        this.moving = true
        let dir = this.queue.pop()

        let oldHeight = this.target.clientHeight

        let num = 1
        while (this.queue.length > 0)
            num += this.queue.pop() == dir ? 1 : -1
        if (num == 0) {
            this.moving = false
            return
        }
        if (num < 0) {
            dir = dir == 'l' ? 'r' : 'l'
            num = 0 - num
        }

        let count = (this.options.moveAll ? this.visible : 1) * num
        count = count % (this.visible + this.detachedElements.length)

        if (dir == 'l')
            for (let i = 0; i < count && this.detachedElements.length > 0; i++)
                this.target.appendChild(this.detachedElements.shift())
        else
            for (let i = 0; i < count && this.detachedElements.length > 0; i++)
                this.target.insertBefore(this.detachedElements.pop(), this.target.firstChild)

        let attached = this.attached
        let oldElements = dir == 'l' ? attached.slice(0, attached.length - this.visible) : attached.slice(this.visible)
        detach(oldElements)

        let height = this.target.clientHeight
        let width = 100 * count / this.visible

        if (dir == 'l')
            for (let i = oldElements.length - 1; i >= 0; i--)
                this.target.insertBefore(oldElements[i], this.target.firstChild)
        else
            for (let i = 0; i < oldElements.length; i++)
                this.target.appendChild(oldElements[i])

        let animationProperties = this.options.singleDuration ? Object.assign({}, this.options.animation, { duration: this.options.singleDuration * count }) : this.options.animation

        let positionOrigin = {
            'marginLeft': (dir == 'l' ? 0 : -width) + '%',
            'marginRight': (dir == 'l' ? -width : 0) + '%',
        }
        let positionDest = {
            'marginLeft': (dir == 'l' ? -width : 0) + '%',
            'marginRight': (dir == 'l' ? 0 : -width) + '%',
        }

        let anim = this.content.animate([positionOrigin, positionDest], animationProperties)
        anim.addEventListener('finish', () => this.skip(dir))

        anim = this.content.animate([
            {
                height: oldHeight + 'px'
            }, {
                height: height + 'px'
            }
        ], animationProperties)
        this.content.style.height = height + 'px'

        let top = 0
        if (this.options.target)
            top = (<HTMLElement>this.target.querySelector(this.options.target) || { offsetHeight: 0 }).offsetHeight / 2

        this.buttons.forEach(el => el.style.top = top ? top + 'px' : '')
    }

    private skip(dir: string): void {
        let attached = this.attached
        if (dir == 'l') {
            let el = attached.slice(0, attached.length - this.visible)
            detach(el)
            el.forEach(elm => this.detachedElements.push(elm))
        } else if (dir == 'r') {
            let el = attached.slice(this.visible)
            detach(el)
            el.forEach(elm => this.detachedElements.unshift(elm))
        }

        if (this.queue.length > 0 && this.timeout < 0)
            this.move()
        else
            this.moving = false

    }

    private setCount(): void {
        let current = this.visible
        let width = this.target.clientWidth
        let ch = this.attached
        let base = this.basis + ch[0].offsetWidth - ch[0].clientWidth  	// base width including any offset

        // calculate number of displayed items
        let visible = Math.floor(width / this.basis)
        if (visible < 1) {
            visible = 1
        } else {
            let w1 = 2 - base / (width / visible)
            let w2 = base / (width / (visible + 1))
            if (w2 < w1) visible++
        }

        // add or remove visible items if needed
        if (visible != current) {
            this.visible = visible
            this.setAttached()
        }

        // set new size
        this.content.style.height = this.target.clientHeight + 'px'

        let top = 0
        if (this.options.target)
            top = (<HTMLElement>this.target.querySelector(this.options.target) || { offsetHeight: 0 }).offsetHeight / 2

        this.buttons.forEach(el => el.style.top = top ? top + 'px' : '')
    }

    private setAttached(): void {
        let attached = this.attached
        if (attached.length < this.visible)
            for (let i = attached.length; i < this.visible && this.detachedElements.length > 0; i++) {
                this.target.appendChild(this.detachedElements.shift())
            }
        else if (attached.length > this.visible)
            for (let i = attached.length - 1; i >= this.visible; i--) {
                this.detachedElements.unshift(attached[i])
                detach([attached[i]])
            }

    }

}

window.addEventListener('resize', () => VbSlider.updateSizes())
window['VbSlider'] = VbSlider

export declare interface JQueryStatic {
    /**
     * Create a new Videobox slider, or find an existing slider and update it's configuration
     * 
     * @param target an element containing the slider items
     * @param options slider configuration
     * @returns slider containing the target element
     */
    vbSlider: (target: HTMLElement | JQuery, options: vbSliderOptions) => VbSlider
}

export declare interface JQuery {
    /**
     * Map Videobox slider to elements matched by the query
     * 
     * @param options slider configuration
     * @returns array of sliders matching the corresponding elements
     */
    vbSlider: (options?: vbSliderOptions) => Array<VbSlider>
}

if (typeof (jQuery) !== 'undefined') {
    jQuery['vbSlider'] = function (target: HTMLElement | JQuery, options: vbSliderOptions = {}): VbSlider {
        if ('length' in target)
            target = target[0]
        return VbSlider.getSlider(<HTMLElement>target, options)
    }

    jQuery.fn.vbSlider = function (options: vbSliderOptions = {}): Array<VbSlider> {
        let elements = iterableToArray<HTMLElement>(this)
        return VbSlider.bind(elements, options)
    }
}