/// <reference path="jquery.d.ts" />

/**
 * Interface for the Videobox's video element
 */
interface vbVideo {

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
 * Interface for videobox configuration
 */
interface vbOptions {

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
     * initial width of the pop-up (used when origin width isn't set)
     */
    initialWidth?: string,

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
     * animation properties (see <a href="https://w3c.github.io/web-animations/">web animations specifications</a>)
     */
    animation?: {
        duration?: number,
        iterations?: number,
        delay?: number,
        easing?: string
    }
}

/**
 * Interface for video origin
 */
interface vbOrigin {

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