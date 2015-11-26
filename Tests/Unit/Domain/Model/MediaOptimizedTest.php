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
 * Test case for class \WIRO\Html5mediaelements\Domain\Model\MediaOptimized.
 *
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 * @author Simon Praetorius <praetorius@wiro-consultants.de>
 */
class MediaOptimizedTest extends \TYPO3\CMS\Core\Tests\UnitTestCase {
	/**
	 * @var \WIRO\Html5mediaelements\Domain\Model\MediaOptimized
	 */
	protected $subject = NULL;

	protected function setUp() {
		$this->subject = new \WIRO\Html5mediaelements\Domain\Model\MediaOptimized();
	}

	protected function tearDown() {
		unset($this->subject);
	}

	/**
	 * @test
	 */
	public function getOptimizedFileReturnsInitialValueForFileReference() {
		$this->assertEquals(
			NULL,
			$this->subject->getOptimizedFile()
		);
	}

	/**
	 * @test
	 */
	public function setOptimizedFileForFileReferenceSetsOptimizedFile() {
		$fileReferenceFixture = new \TYPO3\CMS\Extbase\Domain\Model\FileReference();
		$this->subject->setOptimizedFile($fileReferenceFixture);

		$this->assertAttributeEquals(
			$fileReferenceFixture,
			'optimizedFile',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getFormatReturnsInitialValueForInteger() {
		$this->assertSame(
			0,
			$this->subject->getFormat()
		);
	}

	/**
	 * @test
	 */
	public function setFormatForIntegerSetsFormat() {
		$this->subject->setFormat(12);

		$this->assertAttributeEquals(
			12,
			'format',
			$this->subject
		);
	}
}
