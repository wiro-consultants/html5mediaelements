<?php
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