/// <reference path="jquery.d.ts" />

interface vbVideo {
    url: string,
    title: string,
    width?: number,
    height?: number,
    index: number,
    style?: string,
    class?: string,
    origin?: vbOrigin
}

interface vbOptions {
    videoWidth?: number,        // default player width
    videoHeight?: number,       // default player height
    closeText?: string,         // text for the close action
    padding?: number,           // player padding
    initialWidth?: string,      // initial width of the pop-up, when no origin is specified
    root?: HTMLElement,
    animation?: {               // animation properties
        duration?: number,
        iterations?: number,
        delay?: number,
        easing?: string
    }
}

interface vbOrigin {
    x?: number,         // window.x where the pop-up appears
    y?: number,         // window.y where the pop-up appears         
    width?: number,     // initial width of the pop-up    
    height?: number,    // initial height of the pop-up
    target: HTMLElement
}

interface webAnimation {
    addEventListener(event: string, callback: Function): void,
    cancel(): void,
}

interface HTMLElement {
    animate(a: any, b: any): webAnimation
}

interface JQueryStatic {
    videobox: (_videos: Array<vbVideo>, startVideo: number, _options?: vbOptions) => boolean,
    vbClose: () => boolean,

    vbinline: (_videos: Array<vbVideo>, startVideo: number, _options?: vbOptions) => boolean,
    vbiClose: (callback?: () => void) => boolean,

    vbSlider: (target: HTMLElement, _options?: vbSliderOptions) => vbSlider
}

interface JQuery {
    videobox: (_options?: vbOptions, linkMapper?: (el: HTMLElement, i: number) => vbVideo) => boolean,
    vbinline: (_options?: vbOptions, linkMapper?: (el: HTMLElement, i: number) => vbVideo) => boolean,
    vbSlider: (_options?: vbSliderOptions) => Array<vbSlider>
}

interface vbSliderOptions {
    move?: string,				    // move single or all
    target?: string,				// empty target
    singleDuration?: number,		// duration for single element
    doubleClickTimeout?: number,	// clicks within this period are joined
    animation?: {                   // animation properties (see web animations)
        duration?: number,
        iterations?: number,
        delay?: number,
        easing?: string
    }
}

interface vbSlider {

    target: HTMLElement,
    outer: HTMLDivElement,
    wrap: HTMLDivElement,
    content: HTMLDivElement,
    prev: HTMLDivElement,
    next: HTMLDivElement,
    buttons: JQuery,

    basis: number,
    queue: Array<string>,
    timeout: number,
    moving: boolean,
    visible: number,
    detachedElements: Array<HTMLElement>,
    options: vbSliderOptions,
    
    showPrev(): void,
    showNext(): void,
    queueMove(dir: string): void,
    move(): void,
    skip(dir: string): void,
    setCount(): void,
    setAttached(): void

}