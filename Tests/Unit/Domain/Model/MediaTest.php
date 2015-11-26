<?php

namespace WIRO\Html5mediaelements\Tests\Unit\Domain\Model;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2015 Simon Praetorius <praetorius@wiro-consultants.de>, WiRo Consultants
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
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
 * Test case for class \WIRO\Html5mediaelements\Domain\Model\Media.
 *
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 * @author Simon Praetorius <praetorius@wiro-consultants.de>
 */
class MediaTest extends \TYPO3\CMS\Core\Tests\UnitTestCase {
	/**
	 * @var \WIRO\Html5mediaelements\Domain\Model\Media
	 */
	protected $subject = NULL;

	protected function setUp() {
		$this->subject = new \WIRO\Html5mediaelements\Domain\Model\Media();
	}

	protected function tearDown() {
		unset($this->subject);
	}

	/**
	 * @test
	 */
	public function getTitleReturnsInitialValueForString() {
		$this->assertSame(
			'',
			$this->subject->getTitle()
		);
	}

	/**
	 * @test
	 */
	public function setTitleForStringSetsTitle() {
		$this->subject->setTitle('Conceived at T3CON10');

		$this->assertAttributeEquals(
			'Conceived at T3CON10',
			'title',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getDescriptionReturnsInitialValueForString() {
		$this->assertSame(
			'',
			$this->subject->getDescription()
		);
	}

	/**
	 * @test
	 */
	public function setDescriptionForStringSetsDescription() {
		$this->subject->setDescription('Conceived at T3CON10');

		$this->assertAttributeEquals(
			'Conceived at T3CON10',
			'description',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getOriginalFileReturnsInitialValueForFileReference() {
		$this->assertEquals(
			NULL,
			$this->subject->getOriginalFile()
		);
	}

	/**
	 * @test
	 */
	public function setOriginalFileForFileReferenceSetsOriginalFile() {
		$fileReferenceFixture = new \TYPO3\CMS\Extbase\Domain\Model\FileReference();
		$this->subject->setOriginalFile($fileReferenceFixture);

		$this->assertAttributeEquals(
			$fileReferenceFixture,
			'originalFile',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getAutoConvertReturnsInitialValueForBoolean() {
		$this->assertSame(
			FALSE,
			$this->subject->getAutoConvert()
		);
	}

	/**
	 * @test
	 */
	public function setAutoConvertForBooleanSetsAutoConvert() {
		$this->subject->setAutoConvert(TRUE);

		$this->assertAttributeEquals(
			TRUE,
			'autoConvert',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getAutoCropReturnsInitialValueForBoolean() {
		$this->assertSame(
			FALSE,
			$this->subject->getAutoCrop()
		);
	}

	/**
	 * @test
	 */
	public function setAutoCropForBooleanSetsAutoCrop() {
		$this->subject->setAutoCrop(TRUE);

		$this->assertAttributeEquals(
			TRUE,
			'autoCrop',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getAutoPosterReturnsInitialValueForInteger() {
		$this->assertSame(
			0,
			$this->subject->getAutoPoster()
		);
	}

	/**
	 * @test
	 */
	public function setAutoPosterForIntegerSetsAutoPoster() {
		$this->subject->setAutoPoster(12);

		$this->assertAttributeEquals(
			12,
			'autoPoster',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getPosterReturnsInitialValueForFileReference() {
		$this->assertEquals(
			NULL,
			$this->subject->getPoster()
		);
	}

	/**
	 * @test
	 */
	public function setPosterForFileReferenceSetsPoster() {
		$fileReferenceFixture = new \TYPO3\CMS\Extbase\Domain\Model\FileReference();
		$this->subject->setPoster($fileReferenceFixture);

		$this->assertAttributeEquals(
			$fileReferenceFixture,
			'poster',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getIsConvertedReturnsInitialValueForBoolean() {
		$this->assertSame(
			FALSE,
			$this->subject->getIsConverted()
		);
	}

	/**
	 * @test
	 */
	public function setIsConvertedForBooleanSetsIsConverted() {
		$this->subject->setIsConverted(TRUE);

		$this->assertAttributeEquals(
			TRUE,
			'isConverted',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getIsCroppedReturnsInitialValueForBoolean() {
		$this->assertSame(
			FALSE,
			$this->subject->getIsCropped()
		);
	}

	/**
	 * @test
	 */
	public function setIsCroppedForBooleanSetsIsCropped() {
		$this->subject->setIsCropped(TRUE);

		$this->assertAttributeEquals(
			TRUE,
			'isCropped',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getOptimizedMediaReturnsInitialValueForMediaOptimized() {
		$newObjectStorage = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
		$this->assertEquals(
			$newObjectStorage,
			$this->subject->getOptimizedMedia()
		);
	}

	/**
	 * @test
	 */
	public function setOptimizedMediaForObjectStorageContainingMediaOptimizedSetsOptimizedMedia() {
		$optimizedMedium = new \WIRO\Html5mediaelements\Domain\Model\MediaOptimized();
		$objectStorageHoldingExactlyOneOptimizedMedia = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
		$objectStorageHoldingExactlyOneOptimizedMedia->attach($optimizedMedium);
		$this->subject->setOptimizedMedia($objectStorageHoldingExactlyOneOptimizedMedia);

		$this->assertAttributeEquals(
			$objectStorageHoldingExactlyOneOptimizedMedia,
			'optimizedMedia',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function addOptimizedMediumToObjectStorageHoldingOptimizedMedia() {
		$optimizedMedium = new \WIRO\Html5mediaelements\Domain\Model\MediaOptimized();
		$optimizedMediaObjectStorageMock = $this->getMock('TYPO3\\CMS\\Extbase\\Persistence\\ObjectStorage', array('attach'), array(), '', FALSE);
		$optimizedMediaObjectStorageMock->expects($this->once())->method('attach')->with($this->equalTo($optimizedMedium));
		$this->inject($this->subject, 'optimizedMedia', $optimizedMediaObjectStorageMock);

		$this->subject->addOptimizedMedium($optimizedMedium);
	}

	/**
	 * @test
	 */
	public function removeOptimizedMediumFromObjectStorageHoldingOptimizedMedia() {
		$optimizedMedium = new \WIRO\Html5mediaelements\Domain\Model\MediaOptimized();
		$optimizedMediaObjectStorageMock = $this->getMock('TYPO3\\CMS\\Extbase\\Persistence\\ObjectStorage', array('detach'), array(), '', FALSE);
		$optimizedMediaObjectStorageMock->expects($this->once())->method('detach')->with($this->equalTo($optimizedMedium));
		$this->inject($this->subject, 'optimizedMedia', $optimizedMediaObjectStorageMock);

		$this->subject->removeOptimizedMedium($optimizedMedium);

	}
}
