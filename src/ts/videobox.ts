import { box } from './components/box'
import { inline } from './components/inline'
import { slider } from './components/slider'

/**
 * Function to load and bind all Videobox effects 
 */
export function videobox(
    $: JQueryStatic
) {

    box($)
    inline($)
    slider($)

    $(window).on('load', () => {
        var r = $(".mdl-layout.mdl-js-layout")[0]
        if (!r) r = $("body")[0]

        $("a[rel^='videobox']").videobox({
            root: r
        })

        $("a[rel^='vbinline']").vbinline({ /* Put custom options here */ })

        $(".vb_slider").vbSlider({ /* Put custom options here */ })
    })

}
