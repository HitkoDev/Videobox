import { VideoboxObj as Videobox } from './components/box'
import { VbInlineObj as VbInline } from './components/inline'
import { VbSlider } from './components/slider'

function bindAll() {
    Videobox.bind("a[rel^='videobox']")
    VbInline.bind("a[rel^='vbinline']")
    VbSlider.bind(".vb_slider")
}

if (document.readyState != 'loading')
    bindAll()
else
    document.addEventListener('readystatechange', function ready() {
        if (document.readyState != 'loading') {
            document.removeEventListener('readystatechange', ready)
            bindAll()
        }
    })