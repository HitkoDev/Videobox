/// <reference path="helpers.d.ts" />
/// <reference path="interfaces.d.ts" />
/// <reference path="vbinline.d.ts" />
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
}
interface JQuery {
    /**
     * Map pop-up player to elements matched by the query
     *
     * @param _options player configuration
     * @param linkMapper function to get a Videobox video object from the clicked element
     */
    videobox: (_options?: vbOptions, linkMapper?: (el: HTMLElement) => vbVideo) => boolean;
}
