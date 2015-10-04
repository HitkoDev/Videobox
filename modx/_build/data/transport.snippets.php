<?php

$snippets = array();

$snippet = $modx->newObject('modSnippet');
$snippet->fromArray(array(
    'name' => 'Videobox',
    'description' => 'Displays videos',
    'snippet' => getSnippetContent($sources['elements'].'snippets/snippet.videobox.php'),
), '', true, true);
$properties = include $sources['data'].'properties/properties.videobox.php';
$snippet->setProperties($properties);
$snippets[] = $snippet;

unset($properties);
unset($snippet);
return $snippets;