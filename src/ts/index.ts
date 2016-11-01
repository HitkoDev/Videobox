import { VideoboxObj as Videobox } from './components/box'
import { VbInlineObj as VbInline } from './components/inline'
import { VbSlider } from './components/slider'

Videobox.bind("a[rel^='videobox']", {
    root: <HTMLElement>(document.querySelector(".mdl-layout.mdl-js-layout") || document.body)
})
VbInline.bind("a[rel^='vbinline']")
VbSlider.bind(".vb_slider")