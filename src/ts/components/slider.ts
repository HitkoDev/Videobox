/**
 * Implementation of the Videobox thumbnail slider interface 
 */
class _vbSlider implements vbSlider {

    private target: HTMLElement
    private outer: HTMLDivElement = <HTMLDivElement>$('<div class="vb_slider_outer"></div>')[0]
    private wrap: HTMLDivElement = <HTMLDivElement>$('<div class="vb_slider_wrap"></div>').appendTo(this.outer)[0]
    private content: HTMLDivElement = <HTMLDivElement>$('<div class="vb_slider_cont"></div>').appendTo(this.wrap)[0]
    private prev: HTMLDivElement = <HTMLDivElement>$('<div class="vb_slider_prev"><i class="vb-icon-prev"></i></div>').prependTo(this.outer)[0]
    private next: HTMLDivElement = <HTMLDivElement>$('<div class="vb_slider_next"><i class="vb-icon-next"></i></div>').appendTo(this.outer)[0]
    private buttons: JQuery = $(this.outer).find('i')

    private basis: number
    private queue: Array<string> = []
    private timeout: number = -1
    private moving: boolean = false
    private visible: number = -1
    private detachedElements: Array<HTMLElement> = []

    options: vbSliderOptions = {
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

    constructor(target: HTMLElement, _options: vbSliderOptions) {
        this.target = target
        let elements: JQuery = $(target).children()

        $(this.outer).insertAfter(target)
        $(this.content).append(target)

        this.basis = parseInt($(target).attr('data-width')) || elements.innerWidth()

        $.extend(this.options, _options)

        $(this.content).toggleClass('vb-slider__move-all', this.options.moveAll)

        $(this.prev).click(() => this.showPrev())
        $(this.next).click(() => this.showNext())

        this.setCount()
    }

    showPrev(): void {
        this.queueMove('r')
    }

    showNext(): void {
        this.queueMove('l')
    }

    setBasis(_basis: number): void {
        if (_basis != this.basis) {
            this.basis = _basis
            this.setCount()
        }
    }

    isMoving(): boolean {
        return this.moving
    }

    getTarget(): HTMLElement {
        return this.target
    }

    private queueMove(dir: string): void {
        if (this.queue.length > 0 && this.queue[this.queue.length - 1] != dir)
            this.queue.pop()
        else
            this.queue.push(dir)

        if (this.timeout >= 0)

            clearTimeout(this.timeout)
        this.timeout = setTimeout(() => {
            this.timeout = -1
            if (!this.moving && this.queue.length > 0)
                this.move()
        }, this.options.doubleClickTimeout)
    }

    private move(): void {
        this.moving = true
        let dir = this.queue.pop()

        let num: number = 1
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

        let count: number = (this.options.moveAll ? this.visible : 1) * num
        count = count % (this.visible + this.detachedElements.length)
        for (let i = 0; i < count && this.detachedElements.length > 0; i++)
            dir == 'l' ? $(this.target).append(this.detachedElements.shift()) : $(this.target).prepend(this.detachedElements.pop())

        let attached: JQuery = $(this.target).children()
        let oldElements = dir == 'l' ? attached.slice(0, attached.length - this.visible) : attached.slice(this.visible)
        _vbSlider.detach(oldElements)

        let
            height: number = $(this.target).innerHeight(),
            width: number = 100 * count / this.visible

        dir == 'l' ? $(this.target).prepend(oldElements) : $(this.target).append(oldElements)

        let animationProperties = this.options.singleDuration ? $.extend({}, this.options.animation, { duration: this.options.singleDuration * count }) : this.options.animation

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
                height: $(this.content).css('height')
            }, {
                height: height + 'px'
            }
        ], animationProperties)
        $(this.content).css('height', height)
        this.buttons.css('top', this.options.target ? ($(this.target).find(this.options.target).outerHeight(true) / 2) : '')
    }

    private static detach(el: JQuery): void {
        if (el.find('#vbiWrap').length > 0) $.vbiClose()
        el.detach()
    }

    private skip(dir: string): void {
        let attached: JQuery = $(this.target).children()
        if (dir == 'l') {
            let el: JQuery = attached.slice(0, attached.length - this.visible)
            _vbSlider.detach(el)
            for (let i = 0; i < el.length; i++)
                this.detachedElements.push(el[i])
        } else if (dir == 'r') {
            let el = attached.slice(this.visible)
            _vbSlider.detach(el)
            for (let i = 0; i < el.length; i++)
                this.detachedElements.unshift(el[i])
        }

        if (this.queue.length > 0 && this.timeout < 0)
            this.move()
        else
            this.moving = false

    }

    setCount(): void {
        let current = this.visible
        let width = $(this.target).innerWidth()
        let base = this.basis + $(this.target).children().outerWidth(true) - $(this.target).children().innerWidth()	// base width including any offset

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
        $(this.content).css('height', $(this.target).innerHeight())
        this.buttons.css('top', this.options.target ? ($(this.target).find(this.options.target).outerHeight(true) / 2) : '')
    }

    private setAttached(): void {
        let attached = $(this.target).children()
        if (attached.length < this.visible)
            for (let i = attached.length; i < this.visible && this.detachedElements.length > 0; i++) {
                $(this.target).append(this.detachedElements.shift())
            }
        else if (attached.length > this.visible)
            for (let i = attached.length - 1; i >= this.visible; i--) {
                this.detachedElements.unshift(attached[i])
                _vbSlider.detach($(attached[i]))
            }

    }

}

/**
 * Function to load the Videobox slider
 */
export function slider($: JQueryStatic) {

    let sliders: Array<vbSlider> = []

    $.vbSlider = (target, options = {}) => {

        // update and return an existing slider
        for (let i = 0; i < sliders.length; i++) if (sliders[i].getTarget() == target) {
            $.extend(sliders[i].options, options)
            return sliders[i]
        }

        return new _vbSlider(target, options)
    }

    $.fn.vbSlider = function (options: vbSliderOptions = {}): Array<vbSlider> {
        let sliders = []
        for (let i = 0; i < this.length; i++) {
            let target: HTMLElement = this[i], op: vbSliderOptions = {}, tr: string = $(target).attr("data-target"), mo: string = $(target).attr("data-move")

            if (tr) op.target = tr
            if (mo && mo.trim()) op.moveAll = mo.trim() == 'all'

            sliders.push($.vbSlider(target, $.extend({}, options, op)))
        }
        return sliders
    }

    $(window).on("resize", () => {
        for (let i = 0; i < sliders.length; i++)
            sliders[i].setCount()
    })

}