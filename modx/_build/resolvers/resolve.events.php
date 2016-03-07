<?php
if ($object->xpdo) {
    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
            $modx =& $object->xpdo;
            
            $plugin = $modx->getObject('modPlugin', array('name'=>'HTML5 player'));
            
            $event = $modx->newObject('modPluginEvent');
            $event->set('event', 'OnWebPageInit');
            $event->set('pluginid', $plugin->get('id'));
            $event->save(); 
 
            break;
        case xPDOTransport::ACTION_UPGRADE:
            break;
    }
}
return true;