<img src="https://cloud.githubusercontent.com/assets/4700881/10467953/b5fb0616-71fc-11e5-9847-0e2afd05ff1f.png" alt="videobox logo" width="200" align="right">
[Videobox - a lightweight video player](http://hitko.eu/videobox)
========================================================================

> A picture is worth a thousand words - by that logic, a video tells 30.000 words per second

It's important to remove videos from your page
----------------------------------------------
Whatever the occasion, you can always open YouTube, find a video, and copy the embed code, which looks something like this: ```<iframe src="https://www.youtube.com/embed/...">```. And when you put that code on your page, something horrible happens: visitors have to wait for the player to load, it's eating away their bandwidth, it's storing those pesky cookies, and, if they're using a not-so-powerful device, the additional rendering required by the iframe is making your site appear laggy. And that's just one iframe, if there are several videos on the page it just makes things worse!

Social networks are smart - they don't load the full player, they only show a play button with a thumbnail image. This reduces the total bandwidth and rendering time to a minimum, and only when user clicks the play button, the player actually loads and the video starts playing. You can do the same - that's where Videobox comes in. Just replace your existing ```<iframe src="{player_url}"></iframe>``` with ```<a href="{player_url}" rel="videobox">Play video</a>```, and you're ready for the next-generation video content.

Aren't there already widgets for this?
--------------------------------------
Yes there are ... umm, actually ... just google `video lightbox` or `video popup` and see for yourself, I won't blame you if you pick one of those over Videobox. And I'll totally understand when you come back and click [Download](https://github.com/HitkoDev/Videobox/releases/latest).

Videobox is different from other similar widgets in a few key aspects:
 * **Completely free** - Even for commercial use
 * **Fully responsive & mobile-friendly** - Using vector icons instead of sprites, Videobox looks good even on high-density (retina) screens
 * **Better animations** - Web animations API allows modern browsers to utilise GPU and make smooth animations while rendering Videobox effects
 * **More than just a pop-up** - Videobox comes with a pop-up player, inline player, and a slider (carousel)
 * **No dependencies** - Videobox doesn't depend on jQuery, Zepto, MooTools, or other third-party libraries*
 * **Small** - Videobox is only 59 KB (16 KB gziped) total

Getting started
---------------
1. <a href="https://github.com/HitkoDev/Videobox/releases" target="_blank">Download the latest version of Videobox</a>
2. Check out the ```examples``` directory for some basic examples, and ```docs/index.html``` for the documentation
3. Include Videobox on your website:
 + add ```<link href="path/to/videobox/dist/videobox.min.css" rel="stylesheet">``` to the ```<head>``` tag
 + include ```<script src="path/to/videobox/dist/videobox.min.js" type="text/javascript"></script>``` before the ```</body>``` tag
4. *Since some older browsers don't support web animations yet, you'll probably want to use the web animations polyfill:
 + If you've installed Videobox through npm, web animations polyfill can be found in the ```node_modules``` folder
 + If you've installed through bower, web animations polyfill is inside the ```bower_components``` folder

To insert a pop-up player
-------------------------
```html
<a
    href="player-url"
    rel="videobox"
    title="Video title"
    data-videobox="JSON-encoded options"
>
    Link text / thumbnail
</a>
```

To insert an inline player
--------------------------
```html
<a
    href="player-url"
    rel="vbinline"
    title="Video title"
    data-videobox="JSON-encoded options"
>
    Link text / thumbnail
</a>
```

Videobox extensions
-------------------
Videobox is a JavaScript player effect, if you're looking for Videobox extensions, you can find them here:
* [Joomla!](https://github.com/HitkoDev/Videobox-Joomla)
* [MODX](https://github.com/HitkoDev/Videobox-MODX)
