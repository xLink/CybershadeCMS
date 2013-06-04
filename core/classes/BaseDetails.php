<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
namespace CSCMS\Core\Classes;
defined('INDEX_CHECK') or die('Error: Cannot access directly.');


/**
 * Base Details Class
 *
 * @package Cybershade Core
 * @author
 **/
interface BaseDetails{

	/**
	 * Include various details about the current plugin/module/library
	 *
	 * @version 1.0
	 * @since   1.0
	 * @author  Daniel Noel-Davies
	 *
	 * @param   string  $version                Current version number of the Plugin/Module/Theme/Library
	 * @param   string  $since                  The Version number of the CMS when this was initially built
	 * @param   string  $min_version_required   The minimum version of the CMS required
	 *
	 * @param   string  $author   				The name of the author of this Plugin/Module/Theme/Library
	 * @param   string  $homepage_url			The url to the homepage of this Plugin/Module/Theme/Library
	 * @param   string  $repo_url    			The url to the repository of this Plugin/Module/Theme/Library
	 *
	 * @param   array  $requirements    		An array representation of the requirements of this Plugin/Module/Theme as set out below:
	 *
	 *			array(
	 *				'098f6bcd4621d373cade4e832627b4f6' => array( // The array key is the module hash that will never changed once issue'd
	 *					'min_version' => '1.0.0',
	 *					'name'		  => 'Pages' // Human Readable name of the Module
	 *				)
	 *          )
	 */
    public function details();

	/**
	 * Include the various install steps in order to have this module fully installed
	 *   Note: The installer must use the query builder
	 *
	 * @version 1.0
	 * @since   1.0
	 * @author  Daniel Noel-Davies
	 */
    public function install();

	/**
	 * Include the various uninstall steps in order to have this module fully uninstalled
	 *   Note: The uninstaller must use the query builder
	 *
	 * @version 1.0
	 * @since   1.0
	 * @author  Daniel Noel-Davies
	 */
    public function uninstall();
}
?>