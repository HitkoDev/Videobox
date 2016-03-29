<img src="https://cloud.githubusercontent.com/assets/4700881/10467953/b5fb0616-71fc-11e5-9847-0e2afd05ff1f.png" alt="videobox logo" width="200" align="right">
[Videobox - a lightweight video player](http://hitko.eu/videobox)
========================================================================
I've created Videobox with one thing on my mind: **to replace huge, content-obscuring video players with a clean pop-up / collapsible player**. 

Getting started
---------------
1. <a href="https://github.com/HitkoDev/Videobox/releases" target="_blank">Download the latest version of Videobox</a>
2. Check out the ```examples``` directory for some basic examples
3. Include Videobox on your website:
 + add ```<link href="path/to/videobox/dist/videobox.css" rel="stylesheet" >``` to the ```<head>``` tag
 + include ```<script src="path/to/videobox/dist/videobox.js" type="text/javascript" ></script>``` before the ```</body>``` tag
4. Make sure jQuery is loaded before Videobox. Since some browsers don't support Web animations yet, you'll probably want to include the polyfill on the page as well.
 + If you've installed Videobox through npm, jQuery and Web animations polyfill are inside the ```node_modules``` folder
 + If you've installed through bower, jQuery and Web animations polyfill are inside the ```bower_components``` folder

**That's it, you're now ready to start using Videobox on your page.**  


To insert a pop-up player
-------------  
```
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
```
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
Videobox is a javascript player, if you're looking for Videobox extensions, you can find them here:
* [Joomla!](https://github.com/HitkoDev/Videobox-Joomla)
* [MODX](https://github.com/HitkoDev/Videobox-MODX)
