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
// Register plugin
//
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
	$_EXTKEY,
	'Mediaelement',
	'LLL:EXT:html5mediaelements/Resources/Private/Language/locallang_db.xlf:plugin.mediaelement.name'
);

// Add plugin to new element wizard
if (TYPO3_MODE == 'BE') {
	$TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']['WIRO\Html5mediaelements\Utility\MediaElementWizicon'] = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'Classes/Utility/MediaElementWizicon.php';
}

//
// Add flexform to plugin
//
$extensionName = strtolower(\TYPO3\CMS\Core\Utility\GeneralUtility::underscoredToUpperCamelCase($_EXTKEY));
$pluginName = strtolower('Mediaelement');
$pluginSignature = $extensionName.'_'.$pluginName;

$TCA['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'layout,select_key,pages,recursive';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/media.xml');

//
// Add static TypoScript
//
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'HTML5 Media Elements');

//
// Table tx_html5mediaelements_domain_model_media
//
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_html5mediaelements_domain_model_media');
$GLOBALS['TCA']['tx_html5mediaelements_domain_model_media'] = array(
	'ctrl' => array(
		'title'	=> 'LLL:EXT:html5mediaelements/Resources/Private/Language/locallang_db.xlf:tx_html5mediaelements_domain_model_media',
		'label' => 'title',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'type' => 'type',
		'dividers2tabs' => TRUE,
		'typeicon_column' => 'type',

		'versioningWS' => 2,
		'versioning_followPages' => TRUE,

		'languageField' => 'sys_language_uid',
		'transOrigPointerField' => 'l10n_parent',
		'transOrigDiffSourceField' => 'l10n_diffsource',
		'delete' => 'deleted',
		'enablecolumns' => array(
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime',
		),
		'searchFields' => 'type,title,description,source_file,auto_convert,auto_crop,auto_poster,poster,is_converted,is_cropped,optimized_media,',
		'dynamicConfigFile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'Configuration/TCA/Media.php',
		'typeicon_classes' => array(
			'default' => 'mimetypes-media-video',
			1 => 'mimetypes-media-video',
			2 => 'mimetypes-media-audio'
		),
	),
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::makeCategorizable(
	$_EXTKEY,
	'tx_html5mediaelements_domain_model_media'
);

//
// Table tx_html5mediaelements_domain_model_mediaoptimized
//
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_html5mediaelements_domain_model_mediaoptimized');
$GLOBALS['TCA']['tx_html5mediaelements_domain_model_mediaoptimized'] = array(
	'ctrl' => array(
		'title'	=> 'LLL:EXT:html5mediaelements/Resources/Private/Language/locallang_db.xlf:tx_html5mediaelements_domain_model_mediaoptimized',
		'label' => 'format',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'type' => 'type',
		'dividers2tabs' => TRUE,
		'sortby' => 'sorting',
		'versioningWS' => 2,
		'versioning_followPages' => TRUE,
		'hideTable' => TRUE,
		'typeicon_column' => 'type',

		'languageField' => 'sys_language_uid',
		'transOrigPointerField' => 'l10n_parent',
		'transOrigDiffSourceField' => 'l10n_diffsource',
		'delete' => 'deleted',
		'enablecolumns' => array(
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime',
		),
		'searchFields' => 'type,optimized_file,format,',
		'dynamicConfigFile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'Configuration/TCA/MediaOptimized.php',
		'typeicon_classes' => array(
			'default' => 'mimetypes-media-video',
			1 => 'mimetypes-media-video',
			2 => 'mimetypes-media-audio'
		),
	),
);
