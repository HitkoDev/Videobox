<?php

$snippets = array();

$snippet = $modx->newObject('modSnippet');
$snippet->fromArray(array(
    'name' => 'YouTube',
    'description' => 'YouTube adapter',
    'snippet' => getSnippetContent($sources['elements'].'snippets/snippet.youtube.php'),
), '', true, true);
$snippets[] = $snippet;

unset($snippet);
return $snippets;