<?php

$snippets = array();

$snippet = $modx->newObject('modSnippet');
$snippet->fromArray(array(
    'name' => 'YouTube',
    'description' => 'YouTube adapter',
    'snippet' => getSnippetContent($sources['elements'].'snippets/snippet.youtube.php'),
), '', true, true);
$snippets[] = $snippet;

$snippet = $modx->newObject('modSnippet');
$snippet->fromArray(array(
    'name' => 'Vimeo',
    'description' => 'Vimeo adapter',
    'snippet' => getSnippetContent($sources['elements'].'snippets/snippet.vimeo.php'),
), '', true, true);
$snippets[] = $snippet;

$snippet = $modx->newObject('modSnippet');
$snippet->fromArray(array(
    'name' => 'SoundCloud',
    'description' => 'SoundCloud adapter',
    'snippet' => getSnippetContent($sources['elements'].'snippets/snippet.soundcloud.php'),
), '', true, true);
$properties = include $sources['data'].'properties/properties.soundcloud.php';
$snippet->setProperties($properties);
$snippets[] = $snippet;

unset($snippet);
return $snippets;