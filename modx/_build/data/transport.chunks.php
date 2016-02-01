<?php

$chunks = array();

$chunk = $modx->newObject('modChunk');
$chunk->fromArray(array(
    'name' => 'vb.boxTpl',
    'description' => 'Box template',
    'snippet' => trim(file_get_contents($sources['source_core'].'/elements/chunks/chunk.box_tpl.tpl')),
),'',true,true);
$chunks[] = $chunk;

$chunk = $modx->newObject('modChunk');
$chunk->fromArray(array(
    'name' => 'vb.galleryItemTpl',
    'description' => 'Gallery item template',
    'snippet' => trim(file_get_contents($sources['source_core'].'/elements/chunks/chunk.gallery_item_tpl.tpl')),
),'',true,true);
$chunks[] = $chunk;

$chunk = $modx->newObject('modChunk');
$chunk->fromArray(array(
    'name' => 'vb.galleryTpl',
    'description' => 'Gallery template',
    'snippet' => trim(file_get_contents($sources['source_core'].'/elements/chunks/chunk.gallery_tpl.tpl')),
),'',true,true);
$chunks[] = $chunk;

$chunk = $modx->newObject('modChunk');
$chunk->fromArray(array(
    'name' => 'vb.linkTpl',
    'description' => 'Link template',
    'snippet' => trim(file_get_contents($sources['source_core'].'/elements/chunks/chunk.link_tpl.tpl')),
),'',true,true);
$chunks[] = $chunk;

$chunk = $modx->newObject('modChunk');
$chunk->fromArray(array(
    'name' => 'vb.playerTpl',
    'description' => 'Player template',
    'snippet' => trim(file_get_contents($sources['source_core'].'/elements/chunks/chunk.player_tpl.tpl')),
),'',true,true);
$chunks[] = $chunk;

$chunk = $modx->newObject('modChunk');
$chunk->fromArray(array(
    'name' => 'vb.sliderTpl',
    'description' => 'Slider template',
    'snippet' => trim(file_get_contents($sources['source_core'].'/elements/chunks/chunk.slider_tpl.tpl')),
),'',true,true);
$chunks[] = $chunk;

$chunk = $modx->newObject('modChunk');
$chunk->fromArray(array(
    'name' => 'vb.thumbTpl',
    'description' => 'Thumbnail template',
    'snippet' => trim(file_get_contents($sources['source_core'].'/elements/chunks/chunk.thumb_tpl.tpl')),
),'',true,true);
$chunks[] = $chunk;

$chunk = $modx->newObject('modChunk');
$chunk->fromArray(array(
    'name' => 'vb.html5player',
    'description' => 'Template for the HTML5 player',
    'snippet' => trim(file_get_contents($sources['source_core'].'/elements/chunks/chunk.html5player.tpl')),
),'',true,true);
$chunks[] = $chunk;

unset($chunk);
return $chunks;