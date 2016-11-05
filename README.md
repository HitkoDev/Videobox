<img src="https://cloud.githubusercontent.com/assets/4700881/10467953/b5fb0616-71fc-11e5-9847-0e2afd05ff1f.png" alt="videobox logo" width="200" align="right">
[Videobox - a lightweight video player](http://hitko.eu/videobox)
========================================================================
Videobox is a JavaScript widget and Joomla! / MODX extension for adding video (and audio) to the page in a clean way. It's been created with one thing in mind: **to replace huge, content-obscuring video players with a clean pop-up / collapsible player**.

Why Videobox?
---------------
Well, why no? It has several benefits over the traditional way of embedding videos with iframes:
 * An iframe element takes time and bandwidth to render, while a small thumbnail renders almost instantly and consumes only a few KB of bandwidth - and if you use a text link, it get even faster!
 * An iframe contains tens or even hundreds of other elements - this can significantly affect animations and rendering on mobile devices. With Videobox, an iframe is only visible when user is actually watching the video, so it gives your page a significant performance boost.
 * No matter how many videos there are on your page, Videobox only requires a single iframe, so you can insert your whole playlist without having to worry about page performance.
 * Since external content is only required when the user opens the video, it helps you eliminate external cookies, and protect the privacy of your users.
 * Unlike many other similar widgets, Videobox uses the new web animations API, thus allowing modern browsers to better optimise animations and to utilise hardware acceleration in doing so.
 * Most Videobox alternatives require jQuery or other external libraries, which can quickly lead to compatibility issues. Videobox doesn't use third-party libraries*, so you don't have to worry about that.

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

**That's it, you're now ready to start using Videobox on your page.**  


To insert a pop-up player
-------------  
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
-------------
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
Videobox is a javascript player effect, if you're looking for Videobox extensions, you can find them here:
* [Joomla!](https://github.com/HitkoDev/Videobox-Joomla)
* [MODX](https://github.com/HitkoDev/Videobox-MODX)
