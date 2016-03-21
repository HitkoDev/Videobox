<?php
// no direct access
defined( '_JEXEC' ) or die;
JLoader::discover('Videobox', JPATH_LIBRARIES . '/videobox');
 
class plgSystemVideobox extends JPlugin {
	/**
	 * Load the language file on instantiation. Note this is only available in Joomla 3.1 and higher.
	 * If you want to support 3.0 series you must override the constructor
	 *
	 * @var    boolean
	 * @since  3.1
	 */
	protected $autoloadLanguage = true;
	
	public function onAfterRender(){
		$videobox = new VideoboxVideobox(array());
		var_dump($videobox);
	}
    
}