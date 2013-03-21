<?php
/**
 * Breadcrumbs for the Perfectum Theme
 *
 * @version 1.0
 * @since   1.0
 * @author  Daniel Noel-Davies
 *
 * @param   array  $block       Array of block values
 *
 */
function block_pagination( $block ) {
	// Get instances...
	$objPage = coreObj::getPage();
	$objTPL = coreObj::getTPL();

	$breadcrumbs = $objPage->getOptions('breadcrumbs');
	$length = sizeOf( $breadcrumbs );

	// Check we have breadcrumbs to work with
	if( !empty( $breadcrumbs ) ) {
		return false;
	}

	// Give this block a handle
	$objTPl->set_filenames(array('perfectum_breadcrumbs', Page::$THEME_ROOT . 'breadcrumbs.tpl'));

	// Loop through breadcrumbs and assign the array values to each template block
	foreach( $breadcrumbs as $index => $crumb ) {
		if( $index < $length ) {
			$objTPL->assign_block_vars('crumb', array(
				'URL'	=> $crumb['url'],
				'TITLE'	=> $crumb['name']
			));

		// If this is the last crumb, make it un-clickable
		} else {
			$objTPL->assign_block_vars('crumb', array(
				'TITLE'	=> $crumb['name']
			));
		}
	}

	// Return the block's contents
	return $objTPL->get_html('perfectum_breadcrumbs');
}