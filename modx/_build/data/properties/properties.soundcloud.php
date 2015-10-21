<?php
$snip = $modx->getObject('modSnippet', array('name' => 'SoundCloud'));
return $snip->get('properties');