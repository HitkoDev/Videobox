/**
 * Interface for Videobox configuration
 */
export interface vbOptions {

    /**
     * default player width
     */
    width?: number,

    /**
     * default player height
     */
    height?: number,

    /**
     * text for the close button
     */
    closeText?: string,

    /**
     * player padding
     */
    padding?: number,

    /**
     * element the pop-up will be attached to 
     */
    root?: HTMLElement,

    /**
     * adittional style for the player (doesn't apply to the pop-up player)
     */
    style?: string,

    /**
     * adittional class name(s) for the player (doesn't apply to the pop-up player)
     */
    class?: string,

    /**
     * time (in ms) while the close button remains visible when the inline player is shown
     */
    closeTimeout?: number,

    /**
     * keycodes to close the pop-up / inlie player
     */
    closeKeys?: number[],

    /**
     * if true, the bottom line on the pop-up player won't be displayed
     */
    hideBottom?: boolean

    /**
     * animation properties (see <a href="https://w3c.github.io/web-animations/">web animations specifications</a>)
     */
    animation?: KeyframeAnimationOptions
}

/**
 * Interface for the Videobox's video element
 */
export interface vbVideo {

    /**
     * player url
     */
    url: string,

    /**
     * optional title, is supplied it'll dispalyed under the player
     */
    title?: string,

    /**
     * origin for Videobox actions, such as player open animation
     */
    origin: vbOrigin,

    /**
     * configuration overrides
     */
    options: vbOptions
}

/**
 * Interface for video origin
 */
export interface vbOrigin {

    /**
     * X coordinate where the player will appear (relative to options.root)
     */
    x?: number,

    /**
     * Y coordinate where the player will appear (relative to options.root)
     */
    y?: number,

    /**
     * initial player width
     */
    width?: number,

    /**
     * initial player height
     */
    height?: number,

    /**
     * target element (the clicked element)
     */
    target: HTMLElement
}

/** @internal */
export function create(tagName: string, id?: string, click?: any): HTMLElement {
    let el = document.createElement(tagName)
    if (id)
        el.id = id
    if (click)
        el.onclick = click
    return el
}

/** @internal */
export function createClass(tagName: string, className?: string, click?: any): HTMLElement {
    let el = document.createElement(tagName)
    if (className)
        el.className = className
    if (click)
        el.onclick = click
    return el
}

/** @internal */
export function iterableToArray<T>(iterable: any): Array<T> {
    let list = []
    for (let i = 0; i < iterable.length; i++)
        list.push(iterable[i])
    return list
}

/** @internal */
export function applyStyles(el: HTMLElement, styles: { [key: string]: string }): void {
    for (let key in styles)
        if (key in el.style)
            el.style[key] = styles[key]
}

/** @internal */
export function hide(el: HTMLElement) {
    el['originalDisplay'] = (el.style.display && (el.style.display == 'none' ? '' : el.style.display)) || ''
    el.style.display = 'none'
}

/** @internal */
export function show(el: HTMLElement) {
    if ('originalDisplay' in el && el['originalDisplay'])
        el.style.display = el['originalDisplay']
    else
        el.style.display = ''
}

/** @internal */
export function insertAfter(el: HTMLElement, target: HTMLElement) {
    target.parentElement.insertBefore(el, target.nextSibling)
}

/** @internal */
export function toggleClass(el: HTMLElement, className: string, on: boolean) {
    if (el.classList.contains(className) ? !on : on) el.classList.toggle(className)
}