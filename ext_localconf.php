<?php
/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2015 Simon Praetorius <praetorius@wiro-consultants.de>, WiRo Consultants
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Prevent direct access
 */
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

//
// Configure media element plugin
//
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'WIRO.' . $_EXTKEY,
	'Mediaelement',
	array(
		'Media' => 'show',
	),
	// non-cacheable actions
	array(
		'Media' => '',
	)
);

//
// Add page TSconfig with default conversion rules
//
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(file_get_contents(
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY, 'Configuration/TSconfig/tsconfig.txt')
));

//
// Register CommandController that converts/optimizes media files
//
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] = 'WIRO\\Html5mediaelements\\CommandController\\OptimizeMediaCommandController';