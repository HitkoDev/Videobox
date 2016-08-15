declare module "components/box" {
    /**
     * Function to load the pop-up effect
     */
    export function box($: JQueryStatic): void;
}
declare module "components/inline" {
    /**
     * Function to load the inlline effect
     */
    export function inline($: JQueryStatic): void;
}
declare module "components/slider" {
    /**
     * function to load the Videobox slider
     */
    export function slider($: JQueryStatic): void;
}
declare module "videobox" {
    /**
     * Function to load and bind all Videobox effects
     */
    export function videobox($: JQueryStatic): void;
}
declare module "index" {
    /*!
     *	@author		HitkoDev http://hitko.eu/videobox
     *	@copyright	Copyright (C) 2016 HitkoDev All Rights Reserved.
     *	@license	http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
     *
     *	This program is free software: you can redistribute it and/or modify
     *	it under the terms of the GNU General Public License as published by
     *	the Free Software Foundation, either version 3 of the License, or
     *	any later version.
     *
     *	This program is distributed in the hope that it will be useful,
     *	but WITHOUT ANY WARRANTY without even the implied warranty of
     *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
     *	GNU General Public License for more details.
     *
     *	You should have received a copy of the GNU General Public License
     *	along with this program. If not, see <http://www.gnu.org/licenses/>
     */
}
/**
 * Interface for the Videobox's video element
 */
interface vbVideo {
    /**
     * player url
     */
    url: string;
    /**
     * optional title, is supplied it'll dispalyed under the player
     */
    title?: string;
    /**
     * origin for Videobox actions, such as player open animation
     */
    origin: vbOrigin;
    /**
     * configuration overrides
     */
    options: vbOptions;
}
/**
 * Interface for videobox configuration
 */
interface vbOptions {
    /**
     * default player width
     */
    width?: number;
    /**
     * default player height
     */
    height?: number;
    /**
     * text for the close button
     */
    closeText?: string;
    /**
     * player padding
     */
    padding?: number;
    /**
     * initial width of the pop-up (used when origin width isn't set)
     */
    initialWidth?: string;
    /**
     * element the pop-up will be attached to
     */
    root?: HTMLElement;
    /**
     * adittional style for the player (doesn't apply to the pop-up player)
     */
    style?: string;
    /**
     * adittional class name(s) for the player (doesn't apply to the pop-up player)
     */
    class?: string;
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
/**
 * Interface for video origin
 */
interface vbOrigin {
    /**
     * X coordinate where the player will appear (relative to options.root)
     */
    x?: number;
    /**
     * Y coordinate where the player will appear (relative to options.root)
     */
    y?: number;
    /**
     * initial player width
     */
    width?: number;
    /**
     * initial player height
     */
    height?: number;
    /**
     * target element (the clicked element)
     */
    target: HTMLElement;
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
interface JQueryStatic {
    /**
     * Open Videobox pop-up player
     *
     * @param _video video to show
     */
    videobox: (_video: vbVideo) => boolean;
    /**
     * Close the open pop-up
     */
    vbClose: () => boolean;
    /**
     * Open an inline player
     *
     * @param _video video to show
     */
    vbinline: (_video: vbVideo) => boolean;
    /**
     * Close the open inline player
     *
     * @param callback function to run when close animation is over
     */
    vbiClose: (callback?: () => void) => boolean;
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
     * Map pop-up player to elements matched by the query
     *
     * @param _options player configuration
     * @param linkMapper function to get a Videobox video object from the clicked element
     */
    videobox: (_options?: vbOptions, linkMapper?: (el: HTMLElement) => vbVideo) => boolean;
    /**
     * Map inline player to elements matched by the query
     *
     * @param _options player configuration
     * @param linkMapper function to get a Videobox video object from the clicked element
     */
    vbinline: (_options?: vbOptions, linkMapper?: (el: HTMLElement) => vbVideo) => boolean;
    /**
     * Map Videobox slider to elements matched by the query
     *
     * @param _options slider configuration
     * @returns sliders matching the corresponding query elements
     */
    vbSlider: (_options?: vbSliderOptions) => Array<vbSlider>;
}
