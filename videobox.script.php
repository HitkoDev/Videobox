<?php
/*------------------------------------------------------------------------
# plg_videobox - Videobox
# ------------------------------------------------------------------------
# author	HitkoDev
# copyright	Copyright (C) 2014 HitkoDev. All Rights Reserved.
# @license	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites:	http://hitko.eu/software/videobox
-------------------------------------------------------------------------*/
// no direct access
defined( '_JEXEC' ) or die( 'Restricted Access' );

class plgsystemvideoboxInstallerScript{

	public function postflight($route, $adapter){
		if($route != 'uninstall'){
			$plugin = JPluginHelper::getPlugin('system', 'videobox');
			$params = new JRegistry;
			$params->loadString($plugin->params);
			if($params->get('adapters', '') == ''){
				$params->set('adapters', array('h5video.php', 'ytvideo.php', 'vmvideo.php', 'scvideo.php'));
				$db = JFactory::getDBO();
				$query = $db->getQuery(true);
				$query->update('#__extensions AS a');
				$query->set('a.params = ' . $db->quote((string)$params));
				$query->where('a.element = "videobox"');
				$db->setQuery($query);
				$db->query();
			}
		}
	}
	
}