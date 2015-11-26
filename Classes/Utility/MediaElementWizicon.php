<?php

namespace WIRO\Html5mediaelements\Utility;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2015 Simon Praetorius <praetorius@wiro-consultants.de>, WiRo Energie & Konnex Consulting GmbH
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

class MediaElementWizicon {
	/**
	 * Processing the wizard items array
	 *
	 * @param  array $wizardItems The wizard items
	 * @return array              Modified array with wizard items
	 */
	public function proc(array $wizardItems)     {
		$wizardItems['plugins_tx_html5mediaelements_mediaelement'] = array(
			'icon' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('html5mediaelements') . 'Resources/Public/Icons/wizicon_mediaelement.gif',
			'title' => $GLOBALS['LANG']->sL('LLL:EXT:html5mediaelements/Resources/Private/Language/locallang_db.xlf:wizicon.mediaelement.title'),
			'description' => $GLOBALS['LANG']->sL('LLL:EXT:html5mediaelements/Resources/Private/Language/locallang_db.xlf:wizicon.mediaelement.description'),
			'params' => '&defVals[tt_content][CType]=list&&defVals[tt_content][list_type]=html5mediaelements_mediaelement'
		);

		return $wizardItems;
	}
}