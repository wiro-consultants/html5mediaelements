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
 * Namespace
 */
namespace WIRO\Html5mediaelements\Controller;

/**
 * MediaController
 */
class MediaController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

	/**
	 * mediaRepository
	 *
	 * @var \WIRO\Html5mediaelements\Domain\Repository\MediaRepository
	 * @inject
	 */
	protected $mediaRepository;

	/**
	 * action show
	 *
	 * @return mixed
	 */
	public function showAction() {
		// No media provided => Skip
		if (!$this->settings['media']) {
			return '';
		}

		// Fetch media records
		$mediaUids = array_map('intval', explode(',', $this->settings['media']));
		$mediaRecords = $this->mediaRepository->findByUids($mediaUids);

		// Keep original order of items
		$mediaSorted = array();
		foreach ($mediaUids as $uid) {
			foreach ($mediaRecords as $record) {
				if ($record->getUid() === $uid) {
					$mediaSorted[] = $record;
					break;
				}
			}
		}

		$this->view->assign('media', $mediaSorted);
	}

}