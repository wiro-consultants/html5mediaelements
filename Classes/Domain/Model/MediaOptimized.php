<?php
namespace WIRO\Html5mediaelements\Domain\Model;

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
 * MediaOptimized
 */
class MediaOptimized extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity {

	/**
	 * Types
	 */
	const TYPE_VIDEO = 1;
	const TYPE_AUDIO = 2;

	/**
	 * type
	 *
	 * @var integer
	 */
	protected $type = self::TYPE_VIDEO;

	/**
	 * optimizedFile
	 *
	 * @var \TYPO3\CMS\Extbase\Domain\Model\FileReference
	 * @validate NotEmpty
	 */
	protected $optimizedFile = NULL;

	/**
	 * format
	 *
	 * @var string
	 * @validate NotEmpty
	 */
	protected $format = '';

	/**
	 * Returns the type
	 *
	 * @return integer $type
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * Sets the type
	 *
	 * @param integer $type
	 * @return void
	 */
	public function setType($type) {
		$this->type = $type;
	}

	/**
	 * Checks if the media is a video file
	 *
	 * @return boolean  true if media is a video file
	 */
	public function isVideo() {
		return $this->type === self::TYPE_VIDEO;
	}

	/**
	 * Checks if the media is an audio file
	 *
	 * @return boolean  true if media is an audio file
	 */
	public function isAudio() {
		return $this->type === self::TYPE_AUDIO;
	}

	/**
	 * Returns the optimizedFile
	 *
	 * @return \TYPO3\CMS\Extbase\Domain\Model\FileReference $optimizedFile
	 */
	public function getOptimizedFile() {
		return $this->optimizedFile;
	}

	/**
	 * Sets the optimizedFile
	 *
	 * @param \TYPO3\CMS\Extbase\Domain\Model\FileReference $optimizedFile
	 * @return void
	 */
	public function setOptimizedFile(\TYPO3\CMS\Extbase\Domain\Model\FileReference $optimizedFile) {
		$this->optimizedFile = $optimizedFile;
	}

	/**
	 * Returns the format
	 *
	 * @return string $format
	 */
	public function getFormat() {
		return $this->format;
	}

	/**
	 * Sets the format
	 *
	 * @param string $format
	 * @return void
	 */
	public function setFormat($format) {
		$this->format = $format;
	}

}