/// <reference path="helpers.d.ts" />
/// <reference path="interfaces.d.ts" />
/// <reference path="videobox.d.ts" />
interface JQueryStatic {
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
}
interface JQuery {
    /**
     * Map inline player to elements matched by the query
     *
     * @param _options player configuration
     * @param linkMapper function to get a Videobox video object from the clicked element
     */
    vbinline: (_options?: vbOptions, linkMapper?: (el: HTMLElement) => vbVideo) => boolean;
}
