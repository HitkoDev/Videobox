<?php
$snip = $modx->getObject('modSnippet', array('name' => 'Videobox'));
return $snip->get('properties');