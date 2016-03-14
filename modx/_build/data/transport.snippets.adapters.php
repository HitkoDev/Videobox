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

$snippet = $modx->newObject('modSnippet');
$snippet->fromArray(array(
    'name' => 'HTML5',
    'description' => 'HTML5 video adapter',
    'snippet' => getSnippetContent($sources['elements'].'snippets/snippet.html5.php'),
), '', true, true);
$properties = include $sources['data'].'properties/properties.html5.php';
$snippet->setProperties($properties);
$snippets[] = $snippet;

$snippet = $modx->newObject('modSnippet');
$snippet->fromArray(array(
    'name' => 'Twitch',
    'description' => 'Twitch video & channel adapter',
    'snippet' => getSnippetContent($sources['elements'].'snippets/snippet.twitch.php'),
), '', true, true);
$properties = include $sources['data'].'properties/properties.twitch.php';
$snippet->setProperties($properties);
$snippets[] = $snippet;

unset($snippet);
return $snippets;