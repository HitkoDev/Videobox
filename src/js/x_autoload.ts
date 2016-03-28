/// <reference path="videobox.ts" />
/// <reference path="vbinline.ts" />
/// <reference path="vbslider.ts" />

(function($: JQueryStatic) {

    $(window).on('load', function() {
        var r = $(".mdl-layout.mdl-js-layout")[0];
        if (!r) r = $("body")[0];

        $("a[rel^='videobox']").videobox({
            root: r
        });

        $("a[rel^='vbinline']").vbinline({ /* Put custom options here */ });

        $(".vb_slider").vbSlider({ /* Put custom options here */ });
    });

})(jQuery);