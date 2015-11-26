<?php
namespace WIRO\Html5mediaelements\Domain\Repository;

use WIRO\Html5mediaelements\Domain\Model\Media;

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
 * The repository for Medias
 */
class MediaRepository extends \TYPO3\CMS\Extbase\Persistence\Repository {
	/**
	 * Finds multiple records by their uids
	 *
	 * @param  array                                              $uids  array of uids
	 * @return \TYPO3\CMS\Extbase\Persistence\Generic\QueryResult        records
	 */
	public function findByUids(array $uids) {
		$query = $this->createQuery();
		$query->matching(
			$query->in('uid', $uids)
		);
		return $query->execute();
	}

	/**
	 * Find all records that need some kind of processing
	 *
	 * @return \TYPO3\CMS\Extbase\Persistence\Generic\QueryResult  records
	 */
	public function findOptimizeQueue() {
		$query = $this->createQuery();

		// One task takes care of all records
		$query->getQuerySettings()->setRespectStoragePage(FALSE);

		// Only return records that need processing
		$query->matching(
			// Source file exists and ...
			$query->equals('sourceFile', 1),
			$query->logicalAnd(
				$query->logicalOr(
					// Media files need to be converted or ...
					$query->logicalAnd(
						$query->equals('isConverted', 0),
						$query->equals('autoConvert', 1)
					),
					// Video files need to be cropped or ...
					$query->logicalAnd(
						$query->equals('isCropped', 0),
						$query->equals('autoCrop', 1)
					),
					// A poster has to be created
					$query->logicalAnd(
						$query->logicalNot(
							$query->equals('autoPoster', Media::AUTO_POSTER_DISABLED)
						),
						$query->equals('poster', 0)
					)
				)
			)
		);

		return $query->execute();
	}
}