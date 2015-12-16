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

use WIRO\Html5mediaelements\Domain\Model\Media;

$GLOBALS['TCA']['tx_html5mediaelements_domain_model_media'] = array(
	'ctrl' => $GLOBALS['TCA']['tx_html5mediaelements_domain_model_media']['ctrl'],
	'interface' => array(
		'showRecordFieldList' => 'type, sys_language_uid, l10n_parent, l10n_diffsource, hidden, title, description, source_file, auto_convert, auto_crop, auto_poster, poster, optimized_media, is_converted, is_cropped',
	),
	'types' => array(
		1 => array('showitem' => 'type, sys_language_uid;;;;1-1-1, l10n_parent, l10n_diffsource, title, description, is_converted, is_cropped, --div--;LLL:EXT:html5mediaelements/Resources/Private/Language/locallang_db.xlf:tx_html5mediaelements_domain_model_media.tabs.media, source_file, auto_convert, auto_crop, poster, auto_poster, optimized_media, --div--;LLL:EXT:cms/locallang_ttc.xlf:tabs.access, hidden, starttime, endtime'),
		2 => array('showitem' => 'type, sys_language_uid;;;;1-1-1, l10n_parent, l10n_diffsource, title, description, is_converted, --div--;LLL:EXT:html5mediaelements/Resources/Private/Language/locallang_db.xlf:tx_html5mediaelements_domain_model_media.tabs.media, source_file, auto_convert, optimized_media, --div--;LLL:EXT:cms/locallang_ttc.xlf:tabs.access, hidden, starttime, endtime'),
	),
	'palettes' => array(
	),
	'columns' => array(
		'type' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:html5mediaelements/Resources/Private/Language/locallang_db.xlf:tx_html5mediaelements_domain_model_media.type',
			'config' => array(
				'type' => 'select',
				'renderType' => 'selectSingle',
				'items' => array(
					array('LLL:EXT:html5mediaelements/Resources/Private/Language/locallang_db.xlf:tx_html5mediaelements_domain_model_media.type.video', 1),
					array('LLL:EXT:html5mediaelements/Resources/Private/Language/locallang_db.xlf:tx_html5mediaelements_domain_model_media.type.audio', 2),
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
				'foreign_table' => 'tx_html5mediaelements_domain_model_media',
				'foreign_table_where' => 'AND tx_html5mediaelements_domain_model_media.pid=###CURRENT_PID### AND tx_html5mediaelements_domain_model_media.sys_language_uid IN (-1,0)',
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

		'title' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:html5mediaelements/Resources/Private/Language/locallang_db.xlf:tx_html5mediaelements_domain_model_media.title',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim,required'
			),
		),
		'description' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:html5mediaelements/Resources/Private/Language/locallang_db.xlf:tx_html5mediaelements_domain_model_media.description',
			'config' => array(
				'type' => 'text',
				'cols' => 40,
				'rows' => 10,
				'eval' => 'trim'
			)
		),
		'source_file' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:html5mediaelements/Resources/Private/Language/locallang_db.xlf:tx_html5mediaelements_domain_model_media.source_file',
			'config' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig(
				'sourceFile',
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
		'auto_convert' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:html5mediaelements/Resources/Private/Language/locallang_db.xlf:tx_html5mediaelements_domain_model_media.auto_convert',
			'config' => array(
				'type' => 'check',
				'default' => 0
			)
		),
		'auto_crop' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:html5mediaelements/Resources/Private/Language/locallang_db.xlf:tx_html5mediaelements_domain_model_media.auto_crop',
			'config' => array(
				'type' => 'check',
				'default' => 0
			)
		),
		'auto_poster' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:html5mediaelements/Resources/Private/Language/locallang_db.xlf:tx_html5mediaelements_domain_model_media.auto_poster',
			'config' => array(
				'type' => 'select',
				'renderType' => 'selectSingle',
				'items' => array(
					array('LLL:EXT:html5mediaelements/Resources/Private/Language/locallang_db.xlf:tx_html5mediaelements_domain_model_media.auto_poster.disabled', Media::AUTO_POSTER_DISABLED),
					array('LLL:EXT:html5mediaelements/Resources/Private/Language/locallang_db.xlf:tx_html5mediaelements_domain_model_media.auto_poster.start', Media::AUTO_POSTER_START),
					array('LLL:EXT:html5mediaelements/Resources/Private/Language/locallang_db.xlf:tx_html5mediaelements_domain_model_media.auto_poster.middle', Media::AUTO_POSTER_MIDDLE),
					array('LLL:EXT:html5mediaelements/Resources/Private/Language/locallang_db.xlf:tx_html5mediaelements_domain_model_media.auto_poster.end', Media::AUTO_POSTER_END)
				),
				'default' => Media::AUTO_POSTER_START,
				'size' => 1,
				'maxitems' => 1
			),
		),
		'poster' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:html5mediaelements/Resources/Private/Language/locallang_db.xlf:tx_html5mediaelements_domain_model_media.poster',
			'config' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig(
				'poster',
				array('maxitems' => 1),
				$GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext']
			),
		),
		'optimized_media' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:html5mediaelements/Resources/Private/Language/locallang_db.xlf:tx_html5mediaelements_domain_model_media.optimized_media',
			'config' => array(
				'type' => 'inline',
				'foreign_table' => 'tx_html5mediaelements_domain_model_mediaoptimized',
				'foreign_field' => 'media',
				'maxitems'      => 100,
				'appearance' => array(
					'collapseAll' => 1,
					'levelLinksPosition' => 'top',
					'showSynchronizationLink' => 1,
					'showPossibleLocalizationRecords' => 1,
					'useSortable' => 1,
					'showAllLocalizationLink' => 1
				),
			),
		),
		'is_converted' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:html5mediaelements/Resources/Private/Language/locallang_db.xlf:tx_html5mediaelements_domain_model_media.is_converted',
			'config' => array(
				'type' => 'check',
				'default' => 0
			)
		),
		'is_cropped' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:html5mediaelements/Resources/Private/Language/locallang_db.xlf:tx_html5mediaelements_domain_model_media.is_cropped',
			'config' => array(
				'type' => 'check',
				'default' => 0
			)
		),
	),
);
