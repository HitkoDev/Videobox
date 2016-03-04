<?php

$plugins = array();

$plugin = $modx->newObject('modPlugin');
$plugin->fromArray(array(
    'name' => 'HTML5 player',
    'plugincode' => getSnippetContent($sources['elements'].'plugins/plugin.html5-player.php'),
), '', true, true);
$properties = include $sources['data'].'properties/properties.html5-player.php';
$plugin->setProperties($properties);

$events = array();

$events['OnWebPageInit']= $modx->newObject('modPluginEvent');
$events['OnWebPageInit']->fromArray(array(
    'event' => 'OnWebPageInit',
    'priority' => 0,
    'propertyset' => 0,
),'',true,true);

$plugin->addMany($events);
$plugins[] = $plugin;

return $plugins;