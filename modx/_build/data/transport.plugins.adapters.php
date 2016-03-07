<?php

$plugins = array();

$plugin = $modx->newObject('modPlugin', array(
    'name' => 'HTML5 player',
    'plugincode' => getSnippetContent($sources['elements'].'plugins/plugin.html5-player.php'),
));
$properties = include $sources['data'].'properties/properties.html5-player.php';
$plugin->setProperties($properties);

$plugins[] = $plugin;

return $plugins;