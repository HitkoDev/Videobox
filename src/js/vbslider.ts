/// <reference path="headers.d.ts" />

(function($: JQueryStatic) {

    class _vbSlider implements vbSlider {

        target: HTMLElement;
        outer: HTMLDivElement = <HTMLDivElement>$('<div class="vb_slider_outer"></div>')[0];
        wrap: HTMLDivElement = <HTMLDivElement>$('<div class="vb_slider_wrap"></div>').appendTo(this.outer)[0];
        content: HTMLDivElement = <HTMLDivElement>$('<div class="vb_slider_cont"></div>').appendTo(this.wrap)[0];
        prev: HTMLDivElement = <HTMLDivElement>$('<div class="vb_slider_prev"><i class="vb-icon-prev"></i></div>').prependTo(this.outer)[0];
        next: HTMLDivElement = <HTMLDivElement>$('<div class="vb_slider_next"><i class="vb-icon-next"></i></div>').appendTo(this.outer)[0];
        buttons: JQuery = $(this.outer).find('i');

        basis: number;
        queue: Array<string> = [];
        timeout: number = -1;
        moving: boolean = false;
        visible: number = 1;
        detachedElements: Array<HTMLElement> = [];

        options: vbSliderOptions = {
            move: 'single',
            target: '',
            singleDuration: 500,
            doubleClickTimeout: 200,
            animation: {
                duration: 500,
                iterations: 1,
                delay: 0,
                easing: 'ease-in-out'
            },
        };

        constructor(target: HTMLElement, _options) {
            this.target = target;
            var elements: JQuery = $(target).children();

            $(this.outer).insertAfter(target);
            $(this.content).append(target);

            this.basis = parseInt($(target).attr('data-width')) || elements.innerWidth();

            $.extend(this.options, _options);

            $(this.content).toggleClass(this.options.move, true);

            var slider: _vbSlider = this;
            $(this.prev).click(function() {
                slider.showPrev();
            });
            $(this.next).click(function() {
                slider.showNext();
            });
            
            this.setCount();
        }

        showPrev(): void {
            this.queueMove('r');
        }

        showNext(): void {
            this.queueMove('l');
        }

        queueMove(dir: string): void {
            if (this.queue.length > 0 && this.queue[this.queue.length - 1] != dir) {
                this.queue.pop();
            } else {
                this.queue.push(dir);
            }
            if (this.timeout >= 0) clearTimeout(this.timeout);
            var slider: _vbSlider = this;
            this.timeout = setTimeout(function() {
                slider.timeout = -1;
                if (!slider.moving && slider.queue.length > 0) slider.move();
            }, this.options.doubleClickTimeout);
        }

        move(): void {
            this.moving = true;
            var dir = this.queue.pop();

            var num: number = 1;
            while (this.queue.length > 0) num += this.queue.pop() == dir ? 1 : -1;
            if (num == 0) {
                this.moving = false;
                return;
            }
            if (num < 0) {
                dir = dir == 'l' ? 'r' : 'l';
                num = 0 - num;
            }

            var count: number = (this.options.move == 'single' ? 1 : this.visible) * num;
            count = count % (this.visible + this.detachedElements.length);
            for (var i = 0; i < count && this.detachedElements.length > 0; i++) {
                dir == 'l' ? $(this.target).append(this.detachedElements.shift()) : $(this.target).prepend(this.detachedElements.pop());
            }

            var attached: JQuery = $(this.target).children();
            var oldElements = dir == 'l' ? attached.slice(0, attached.length - this.visible) : attached.slice(this.visible);
            _vbSlider.detach(oldElements);

            var
                height: number = $(this.target).innerHeight(),
                width: number = 100 * count / this.visible;

            dir == 'l' ? $(this.target).prepend(oldElements) : $(this.target).append(oldElements);

            var animationProperties = this.options.singleDuration ? $.extend({}, this.options.animation, { duration: this.options.singleDuration * count }) : this.options.animation;
            var slider: _vbSlider = this;

            var positionOrigin = {
                'margin-left': (dir == 'l' ? 0 : -width) + '%',
                'margin-right': (dir == 'l' ? -width : 0) + '%',
            };
            var positionDest = {
                'margin-left': (dir == 'l' ? -width : 0) + '%',
                'margin-right': (dir == 'l' ? 0 : -width) + '%',
            };

            var anim = this.content.animate([positionOrigin, positionDest], animationProperties);
            anim.addEventListener('finish', function() {
                slider.skip(dir);
            });

            var anim = this.content.animate([{
                height: $(this.content).css('height')
            }, {
                    height: height + 'px'
                }], animationProperties);
            anim.addEventListener('finish', function() {
                $(slider.content).css('height', height);
            });
            this.buttons.css('top', this.options.target ? ($(this.target).find(this.options.target).outerHeight(true) / 2) : '');
        }

        private static detach(el: JQuery): void {
            if (el.find('#vbiWrap').length > 0) $.vbiClose();
            el.detach();
        }

        skip(dir: string): void {
            var attached: JQuery = $(this.target).children();
            if (dir == 'l') {
                var el: JQuery = attached.slice(0, attached.length - this.visible);
                _vbSlider.detach(el);
                for (var i = 0; i < el.length; i++) this.detachedElements.push(el[i]);
            } else if (dir == 'r') {
                var el = attached.slice(this.visible);
                _vbSlider.detach(el);
                for (var i = 0; i < el.length; i++) this.detachedElements.unshift(el[i]);
            }

            if (this.queue.length > 0 && this.timeout < 0) {
                this.move();
            } else {
                this.moving = false;
            }
        }

        setCount(): void {
            var current = this.visible;
            var width = $(this.target).innerWidth();
            var base = this.basis + $(this.target).children().outerWidth(true) - $(this.target).children().innerWidth();	// base width including any offset
            
            // calculate number of displayed items
            var visible = Math.floor(width / this.basis);
            if (visible < 1) {
                visible = 1;
            } else {
                var w1 = 2 - base / (width / visible);
                var w2 = base / (width / (visible + 1));
                if (w2 < w1) visible++;
            }
            
            // add or remove visible items if needed
            if (visible != current) {
                this.visible = visible;
                this.setAttached();
            }
            
            // set new size
            $(this.content).css('height', $(this.target).innerHeight());
            this.buttons.css('top', this.options.target ? ($(this.target).find(this.options.target).outerHeight(true) / 2) : '');
        }

        setAttached(): void {
            var attached = $(this.target).children();
            if (attached.length < this.visible) {
                for (var i = attached.length; i < this.visible && this.detachedElements.length > 0; i++) {
                    $(this.target).append(this.detachedElements.shift());
                }
            } else if (attached.length > this.visible) {
                for (var i = attached.length - 1; i >= this.visible; i--) {
                    this.detachedElements.unshift(attached[i]);
                    _vbSlider.detach($(attached[i]));
                }
            }
        }

    }

    var sliders: Array<vbSlider> = [];

    $.vbSlider = function(target: HTMLElement, _options: vbSliderOptions = {}): vbSlider {
		
        // update and return an existing slider
        for (var i = 0; i < sliders.length; i++) if (sliders[i].target == target) {
            $.extend(sliders[i].options, _options);
            return sliders[i];
        }

        return new _vbSlider(target, _options);
    }

    $.fn.vbSlider = function(_options: vbSliderOptions = {}): Array<vbSlider> {
        var sliders = [];
        for (var i = 0; i < this.length; i++) {
            var target: HTMLElement = this[i], _op: vbSliderOptions = {}, tr: string = $(target).attr("data-target"), mo: string = $(target).attr("data-move");

            if (tr) _op.target = tr;
            if (mo && mo.trim()) _op.move = mo.trim();

            sliders.push($.vbSlider(target, $.extend({}, _options, _op)));
        }
        return sliders;
    }

    $(window).on("resize", function() {
        for (var i = 0; i < sliders.length; i++) sliders[i].setCount();
    });

})(jQuery);