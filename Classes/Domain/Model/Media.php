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
namespace WIRO\Html5mediaelements\Domain\Model;

/**
 * Media
 */
class Media extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity {

	/**
	 * Types
	 */
	const TYPE_VIDEO = 1;
	const TYPE_AUDIO = 2;

	/**
	 * Options for autoPoster
	 */
	const AUTO_POSTER_DISABLED = 0;
	const AUTO_POSTER_START = 1;
	const AUTO_POSTER_MIDDLE = 2;
	const AUTO_POSTER_END = 3;

	/**
	 * type
	 *
	 * @var integer
	 */
	protected $type = self::TYPE_VIDEO;

	/**
	 * title
	 *
	 * @var string
	 * @validate NotEmpty
	 */
	protected $title = '';

	/**
	 * description
	 *
	 * @var string
	 */
	protected $description = '';

	/**
	 * sourceFile
	 *
	 * @var \TYPO3\CMS\Extbase\Domain\Model\FileReference
	 */
	protected $sourceFile = NULL;

	/**
	 * autoConvert
	 *
	 * @var boolean
	 */
	protected $autoConvert = FALSE;

	/**
	 * autoCrop
	 *
	 * @var boolean
	 */
	protected $autoCrop = FALSE;

	/**
	 * autoPoster
	 *
	 * @var integer
	 */
	protected $autoPoster = self::AUTO_POSTER_START;

	/**
	 * poster
	 *
	 * @var \TYPO3\CMS\Extbase\Domain\Model\FileReference
	 */
	protected $poster = NULL;

	/**
	 * isConverted
	 *
	 * @var boolean
	 */
	protected $isConverted = FALSE;

	/**
	 * isCropped
	 *
	 * @var boolean
	 */
	protected $isCropped = FALSE;

	/**
	 * optimizedMedia
	 *
	 * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\WIRO\Html5mediaelements\Domain\Model\MediaOptimized>
	 * @cascade remove
	 */
	protected $optimizedMedia = NULL;

	/**
	 * __construct
	 */
	public function __construct() {
		//Do not remove the next line: It would break the functionality
		$this->initStorageObjects();
	}

	/**
	 * Initializes all ObjectStorage properties
	 * Do not modify this method!
	 * It will be rewritten on each save in the extension builder
	 * You may modify the constructor of this class instead
	 *
	 * @return void
	 */
	protected function initStorageObjects() {
		$this->optimizedMedia = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
	}

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
	 * Returns the title
	 *
	 * @return string $title
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * Sets the title
	 *
	 * @param string $title
	 * @return void
	 */
	public function setTitle($title) {
		$this->title = $title;
	}

	/**
	 * Returns the description
	 *
	 * @return string $description
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * Sets the description
	 *
	 * @param string $description
	 * @return void
	 */
	public function setDescription($description) {
		$this->description = $description;
	}

	/**
	 * Returns the sourceFile
	 *
	 * @return \TYPO3\CMS\Extbase\Domain\Model\FileReference $sourceFile
	 */
	public function getSourceFile() {
		return $this->sourceFile;
	}

	/**
	 * Sets the sourceFile
	 *
	 * @param \TYPO3\CMS\Extbase\Domain\Model\FileReference $sourceFile
	 * @return void
	 */
	public function setSourceFile(\TYPO3\CMS\Extbase\Domain\Model\FileReference $sourceFile) {
		$this->sourceFile = $sourceFile;
	}

	/**
	 * Returns the autoConvert
	 *
	 * @return boolean $autoConvert
	 */
	public function getAutoConvert() {
		return $this->autoConvert;
	}

	/**
	 * Sets the autoConvert
	 *
	 * @param boolean $autoConvert
	 * @return void
	 */
	public function setAutoConvert($autoConvert) {
		$this->autoConvert = $autoConvert;
	}

	/**
	 * Returns the boolean state of autoConvert
	 *
	 * @return boolean
	 */
	public function isAutoConvert() {
		return $this->autoConvert;
	}

	/**
	 * Returns the autoCrop
	 *
	 * @return boolean $autoCrop
	 */
	public function getAutoCrop() {
		return $this->autoCrop;
	}

	/**
	 * Sets the autoCrop
	 *
	 * @param boolean $autoCrop
	 * @return void
	 */
	public function setAutoCrop($autoCrop) {
		$this->autoCrop = $autoCrop;
	}

	/**
	 * Returns the boolean state of autoCrop
	 *
	 * @return boolean
	 */
	public function isAutoCrop() {
		return $this->autoCrop;
	}

	/**
	 * Returns the autoPoster
	 *
	 * @return integer $autoPoster
	 */
	public function getAutoPoster() {
		return $this->autoPoster;
	}

	/**
	 * Sets the autoPoster
	 *
	 * @param integer $autoPoster
	 * @return void
	 */
	public function setAutoPoster($autoPoster) {
		$this->autoPoster = $autoPoster;
	}

	/**
	 * Returns the boolean state of autoPoster
	 *
	 * @return boolean
	 */
	public function isAutoPoster() {
		return ($this->autoPoster > self::AUTO_POSTER_DISABLED);
	}

	/**
	 * Returns the poster
	 *
	 * @return \TYPO3\CMS\Extbase\Domain\Model\FileReference $poster
	 */
	public function getPoster() {
		return $this->poster;
	}

	/**
	 * Sets the poster
	 *
	 * @param \TYPO3\CMS\Extbase\Domain\Model\FileReference $poster
	 * @return void
	 */
	public function setPoster(\TYPO3\CMS\Extbase\Domain\Model\FileReference $poster) {
		$this->poster = $poster;
	}

	/**
	 * Returns the isConverted
	 *
	 * @return boolean $isConverted
	 */
	public function getIsConverted() {
		return $this->isConverted;
	}

	/**
	 * Sets the isConverted
	 *
	 * @param boolean $isConverted
	 * @return void
	 */
	public function setIsConverted($isConverted) {
		$this->isConverted = $isConverted;
	}

	/**
	 * Returns the boolean state of isConverted
	 *
	 * @return boolean
	 */
	public function isIsConverted() {
		return $this->isConverted;
	}

	/**
	 * Returns the isCropped
	 *
	 * @return boolean $isCropped
	 */
	public function getIsCropped() {
		return $this->isCropped;
	}

	/**
	 * Sets the isCropped
	 *
	 * @param boolean $isCropped
	 * @return void
	 */
	public function setIsCropped($isCropped) {
		$this->isCropped = $isCropped;
	}

	/**
	 * Returns the boolean state of isCropped
	 *
	 * @return boolean
	 */
	public function isIsCropped() {
		return $this->isCropped;
	}

	/**
	 * Adds a MediaOptimized
	 *
	 * @param \WIRO\Html5mediaelements\Domain\Model\MediaOptimized $optimizedMedium
	 * @return void
	 */
	public function addOptimizedMedium(\WIRO\Html5mediaelements\Domain\Model\MediaOptimized $optimizedMedium) {
		$this->optimizedMedia->attach($optimizedMedium);
	}

	/**
	 * Removes a MediaOptimized
	 *
	 * @param \WIRO\Html5mediaelements\Domain\Model\MediaOptimized $optimizedMediumToRemove The MediaOptimized to be removed
	 * @return void
	 */
	public function removeOptimizedMedium(\WIRO\Html5mediaelements\Domain\Model\MediaOptimized $optimizedMediumToRemove) {
		$this->optimizedMedia->detach($optimizedMediumToRemove);
	}

	/**
	 * Returns the optimizedMedia
	 *
	 * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\WIRO\Html5mediaelements\Domain\Model\MediaOptimized> $optimizedMedia
	 */
	public function getOptimizedMedia() {
		return $this->optimizedMedia;
	}

	/**
	 * Sets the optimizedMedia
	 *
	 * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\WIRO\Html5mediaelements\Domain\Model\MediaOptimized> $optimizedMedia
	 * @return void
	 */
	public function setOptimizedMedia(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $optimizedMedia) {
		$this->optimizedMedia = $optimizedMedia;
	}

	/**
	 * Convert model to array
	 * @return array  Converted model
	 */
	public function toArray() {
		return \TYPO3\CMS\Extbase\Reflection\ObjectAccess::getGettableProperties($this);
	}
}