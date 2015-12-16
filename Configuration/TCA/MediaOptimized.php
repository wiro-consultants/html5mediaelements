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
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

$GLOBALS['TCA']['tx_html5mediaelements_domain_model_mediaoptimized'] = array(
	'ctrl' => $GLOBALS['TCA']['tx_html5mediaelements_domain_model_mediaoptimized']['ctrl'],
	'interface' => array(
		'showRecordFieldList' => 'type, sys_language_uid, l10n_parent, l10n_diffsource, hidden, optimized_file, format',
	),
	'types' => array(
		'1' => array('showitem' => 'type, sys_language_uid;;;;1-1-1, l10n_parent, l10n_diffsource, optimized_file, format, --div--;LLL:EXT:cms/locallang_ttc.xlf:tabs.access, hidden;;1, starttime, endtime'),
		'2' => array('showitem' => 'type, sys_language_uid;;;;1-1-1, l10n_parent, l10n_diffsource, optimized_file, format, --div--;LLL:EXT:cms/locallang_ttc.xlf:tabs.access, hidden;;1, starttime, endtime'),
	),
	'palettes' => array(
		'1' => array('showitem' => ''),
	),
	'columns' => array(
		'type' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:html5mediaelements/Resources/Private/Language/locallang_db.xlf:tx_html5mediaelements_domain_model_mediaoptimized.type',
			'config' => array(
				'type' => 'select',
				'renderType' => 'selectSingle',
				'items' => array(
					array('LLL:EXT:html5mediaelements/Resources/Private/Language/locallang_db.xlf:tx_html5mediaelements_domain_model_mediaoptimized.type.video', 1),
					array('LLL:EXT:html5mediaelements/Resources/Private/Language/locallang_db.xlf:tx_html5mediaelements_domain_model_mediaoptimized.type.audio', 2),
				),
				'size' => 1,
				'maxitems' => 1,
				'default' => 1
			)
		),
		'sys_language_uid' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.language',
			'config' => array(
				'type' => 'select',
				'renderType' => 'selectSingle',
				'foreign_table' => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => array(
					array('LLL:EXT:lang/locallang_general.xlf:LGL.allLanguages', -1),
					array('LLL:EXT:lang/locallang_general.xlf:LGL.default_value', 0)
				),
			),
		),
		'l10n_parent' => array(
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.l18n_parent',
			'config' => array(
				'type' => 'select',
				'renderType' => 'selectSingle',
				'items' => array(
					array('', 0),
				),
				'foreign_table' => 'tx_html5mediaelements_domain_model_mediaoptimized',
				'foreign_table_where' => 'AND tx_html5mediaelements_domain_model_mediaoptimized.pid=###CURRENT_PID### AND tx_html5mediaelements_domain_model_mediaoptimized.sys_language_uid IN (-1,0)',
			),
		),
		'l10n_diffsource' => array(
			'config' => array(
				'type' => 'passthrough',
			),
		),
		't3ver_label' => array(
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.versionLabel',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'max' => 255,
			)
		),

		'hidden' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.hidden',
			'config' => array(
				'type' => 'check',
			),
		),
		'starttime' => array(
			'exclude' => 1,
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.starttime',
			'config' => array(
				'type' => 'input',
				'size' => 13,
				'max' => 20,
				'eval' => 'datetime',
				'checkbox' => 0,
				'default' => 0,
				'range' => array(
					'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y'))
				),
			),
		),
		'endtime' => array(
			'exclude' => 1,
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.endtime',
			'config' => array(
				'type' => 'input',
				'size' => 13,
				'max' => 20,
				'eval' => 'datetime',
				'checkbox' => 0,
				'default' => 0,
				'range' => array(
					'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y'))
				),
			),
		),

		'optimized_file' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:html5mediaelements/Resources/Private/Language/locallang_db.xlf:tx_html5mediaelements_domain_model_mediaoptimized.optimized_file',
			'config' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig(
				'optimizedFile',
				array(
					'maxitems' => 1,
					'minitems' => 1,
					'appearance' => array(
						'headerThumbnail' => '__UNSET'
					)
				),
				'mp3,mp4,mpg,mpeg,m4a,m4v,aac,wav,aif,aiff,mov,webm,ogg,oga,ogv,wma,wmv,avi,mkv,mka,flac,opus,flv'
			),
		),
		'format' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:html5mediaelements/Resources/Private/Language/locallang_db.xlf:tx_html5mediaelements_domain_model_mediaoptimized.format',
			'config' => array(
				'type' => 'select',
				'renderType' => 'selectSingle',
				'items' => array(),
				'size' => 1,
				'maxitems' => 1,
				'eval' => 'required'
			),
		),
		'media' => array(
			'config' => array(
				'type' => 'passthrough',
			),
		),
	),
);
