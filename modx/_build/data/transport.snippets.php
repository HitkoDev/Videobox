<?php
function getSnippetContent($filename) {
    $o = file_get_contents($filename);
    $o = trim(str_replace(array('<?php','?>'),'',$o));
    return $o;
}
$snippets = array();
 
$snippets[1]= $modx->newObject('modSnippet');
$snippets[1]->fromArray(array(
    'name' => 'Videobox',
    'description' => 'Displays videos',
    'snippet' => getSnippetContent($sources['elements'].'snippets/snippet.videobox.php'),
), '', true, true);
$properties = include $sources['data'].'properties/properties.videobox.php';
$snippets[1]->setProperties($properties);
unset($properties);
 
return $snippets;