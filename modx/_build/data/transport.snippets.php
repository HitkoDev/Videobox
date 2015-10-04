<?php

function getSnippetContent($filename) {
    $o = file_get_contents($filename);
    $o = trim(str_replace(array('<?php','?>'),'',$o));
    return $o;
}

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

$snippet = $modx->newObject('modSnippet');
$snippet->fromArray(array(
    'name' => 'YouTube',
    'description' => 'YouTube adapter',
    'snippet' => getSnippetContent($sources['elements'].'snippets/snippet.youtube.php'),
), '', true, true);
$snippets[] = $snippet;

unset($properties);
unset($snippet);
return $snippets;