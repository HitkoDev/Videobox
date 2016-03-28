/// <reference path="helpers.d.ts" />
/// <reference path="vbinline.d.ts" />
interface JQueryStatic {
    /**
     * Create a new Videobox slider, or find an existing slider and update it's configuration
     *
     * @param target an element containing the slider items
     * @param _options slider configuration
     * @returns slider containing the target element
     */
    vbSlider: (target: HTMLElement, _options?: vbSliderOptions) => vbSlider;
}
interface JQuery {
    /**
     * Map Videobox slider to elements matched by the query
     *
     * @param _options slider configuration
     * @returns sliders matching the corresponding query elements
     */
    vbSlider: (_options?: vbSliderOptions) => Array<vbSlider>;
}
/**
 * Interface for Videobox slider
 */
interface vbSlider {
    /**
     * slider configuration
     */
    options: vbSliderOptions;
    /**
     * scrolls slider to the left
     */
    showPrev(): void;
    /**
     * scrolls slider to the right
     */
    showNext(): void;
    /**
     * recalcuate slider metrics
     */
    setCount(): void;
    /**
     * returns true if slider is currently scrolling
     */
    isMoving(): boolean;
    /**
     * get the slider's target element
     */
    getTarget(): HTMLElement;
    /**
     * set element base width
     *
     * @param basis new base width
     */
    setBasis(basis: number): void;
}
/**
 * Interface for Videobox slider configuration
 */
interface vbSliderOptions {
    /**
     * if true, slider will scroll all visible elements
     */
    moveAll?: boolean;
    /**
     * target selector
     */
    target?: string;
    /**
     * transition duration for one element
     */
    singleDuration?: number;
    /**
     * clicks within the timeout are processed together
     */
    doubleClickTimeout?: number;
    /**
     * animation properties (see <a href="https://w3c.github.io/web-animations/">web animations specifications</a>)
     */
    animation?: {
        duration?: number;
        iterations?: number;
        delay?: number;
        easing?: string;
    };
}
