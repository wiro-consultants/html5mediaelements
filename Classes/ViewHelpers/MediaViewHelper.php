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
namespace WIRO\Html5mediaelements\ViewHelpers;

/**
 * MediaViewHelper
 */
class MediaViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper {
	/**
	 * Object Manager
	 *
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
	 * @inject
	 */
	protected $objectManager;

	/**
	 * Configuration Manager
	 *
	 * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
	 * @inject
	 */
	protected $configurationManager;

	/**
	 * Media Repository
	 *
	 * @var \WIRO\Html5mediaelements\Domain\Repository\MediaRepository
	 * @inject
	 */
	protected $mediaRepository;

	/**
	 * Extension key
	 *
	 * @var string
	 */
	protected $extensionKey = 'html5mediaelements';

	/**
	 * Tag name
	 *
	 * @var string
	 */
	protected $tagName = 'video';

	/**
	 * Default MIME types that should be prioritized in the output
	 *
	 * @var array
	 * @see prioritizeMedia() prioritizeMedia()
	 */
	protected $priorityFormats = array(
		'video/mp4',
		'video/webm',
		'video/ogg',
		'audio/mp4',
		'audio/mp3',
		'audio/mpeg',
		'audio/ogg',
		'application/ogg'
	);

	/**
	 * Default MIME types that should be used as flash fallback
	 *
	 * @var array
	 * @see prioritizeMedia() prioritizeMedia()
	 */
	protected $fallbackFormats = array(
		'video/mp4',
		'video/x-flv',
		'audio/mp3',
		'audio/mpeg'
	);

	/**
	 * Path to swf file that should be used as flash fallback
	 *
	 * @var string|boolean
	 */
	protected $flashMediaElement;

	/**
	 * Initializes the ViewHelper
	 *
	 * @return void
	 */
	public function initialize() {
		parent::initialize();

		// Default path to swf file
		$this->flashMediaElement = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath($this->extensionKey)
			. 'Resources/Public/JavaScript/MediaElement/flashmediaelement.swf';
	}

	/**
	 * Initializes passthrough arguments for the tag
	 *
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();

		$this->registerUniversalTagAttributes();
		$this->registerTagAttribute('controls', 'boolean', 'Specifies whether the player controls should be visible', FALSE);
		$this->registerTagAttribute('autoplay', 'boolean', 'Specifies whether the player should start playing without user interaction', FALSE);
		$this->registerTagAttribute('loop', 'boolean', 'Specifies whether the playback should be looped', FALSE);
		$this->registerTagAttribute('muted', 'boolean', 'Specifies whether the audio should be muted initially', FALSE);
		$this->registerTagAttribute('preload', 'string', 'Specifies the preloading behavior of the player', FALSE);
		$this->registerTagAttribute('crossorigin', 'string', 'Specifies the crossorigin policy for the media content', FALSE);
	}

	/**
	 * Renders an audio or video tag based on the provided media record
	 *
	 * @param  \WIRO\Html5mediaelements\Domain\Model\Media|int $media              media record or uid of media record
	 * @param  boolean|string                                  $flashMediaElement  path to swf file that will be used as flash fallback;
	 *                                                                             if set to TRUE the default file will be used; if set
	 *                                                                             to FALSE the flash fallback will be disabled
	 * @param  array|NULL                                      $priorityFormats    array of MIME types that should be prioritized in the output;
	 *                                                                             array order is respected
	 * @param  array|NULL                                      $fallbackFormats    array of MIME types that should be used as flash fallback;
	 *                                                                             array order is respected
	 * @return string                                                              <audio> or <video> tag
	 */
	public function render(\WIRO\Html5mediaelements\Domain\Model\Media $media, $flashMediaElement = TRUE, $priorityFormats = NULL, $fallbackFormats = NULL) {
		// Fetch media file with provided uid
		if (is_int($media)) {
			$media = $this->mediaRepository->findByUid($media);
		}

		// Validate and store output options
		if ($flashMediaElement !== TRUE) {
			$this->flashMediaElement = $flashMediaElement;
		}
		if (isset($priorityFormats)) {
			$this->priorityFormats = $priorityFormats;
		}
		if (isset($fallbackFormats)) {
			$this->fallbackFormats = $fallbackFormats;
		}

		// Render audio content with <audio> tag
		if ($media->isAudio()) {
			$this->tag->setTagName('audio');
		}

		// Use valid HTML5 boolean attributes
		foreach (array('controls', 'autoplay', 'loop', 'muted') as $attribute) {
			if ($this->tag->hasAttribute($attribute)) {
				$this->tag->addAttribute($attribute, $attribute);
			}
		}

		// Order media sources based on provided priorities
		$orderedMedia = $this->prioritizeMedia($media, $this->priorityFormats);
		// Use source file as fallback if no optimized media is available
		if (empty($orderedMedia)) {
			$orderedMedia = array($media->getSourceFile());
		}

		// Render HTML5 source candidates
		// Attempt to set video dimensions
		$sourceCandidates = $this->renderSources($media, $orderedMedia);

		// Set video poster image
		if ($media->isVideo() && $media->getPoster()) {
			$this->tag->addAttribute('poster', $this->generatePublicUrl($media->getPoster()->getOriginalResource()));

			// Attempt to set video dimensions (if not set already)
			$this->addVideoDimensions($media->getPoster()->getOriginalResource());
		}

		// First try to fetch simple fallback from tag children
		$simpleFallbackSource = $this->renderChildren();
		if (!$simpleFallbackSource) {
			// Render simple fallback (linked poster image or text link)
			$simpleFallbackSource = $this->renderSimpleFallback($media, $orderedMedia);
		}

		// Render Flash fallback (includes simple fallback)
		$fallbackSource = $this->renderFlashFallback($media, $simpleFallbackSource);

		// Add source candidates and fallback to tag
		$this->tag->setContent($sourceCandidates . $fallbackSource);

		return $this->tag->render();
	}

	/**
	 * Renders HTML5 source candidates
	 *
	 * @param  \WIRO\Html5mediaelements\Domain\Model\Media $media         media record
	 * @param  array                                       $orderedMedia  source candidates
	 * @return string                                                     HTML <source> tags
	 */
	protected function renderSources(\WIRO\Html5mediaelements\Domain\Model\Media $media, array $orderedMedia) {
		$sourceCandidates = '';
		foreach ($orderedMedia as $mediaFile) {
			// Create new <source> tag
			$sourceTag = $this->objectManager->get('TYPO3\CMS\Fluid\Core\ViewHelper\TagBuilder', 'source');
			$sourceTag->addAttributes(array(
				'src' => $this->generatePublicUrl($mediaFile->getOriginalResource()),
				'type' => $mediaFile->getOriginalResource()->getProperty('mime_type')
			));

			// Add to available sources
			$sourceCandidates .= $sourceTag->render();

			if ($media->isVideo()) {
				// Attempt to set video dimensions (first candidate wins)
				$this->addVideoDimensions($mediaFile->getOriginalResource());
			}
		}

		return $sourceCandidates;
	}

	/**
	 * Renders Flash fallback for <audio> or <video> tag including a simple fallback provided as argument
	 *
	 * @param  \WIRO\Html5mediaelements\Domain\Model\Media $media           media record
	 * @param  string                                      $simpleFallback  simple text link/image fallback HTML
	 * @return string                                                       Flash fallback HTML
	 */
	protected function renderFlashFallback(\WIRO\Html5mediaelements\Domain\Model\Media $media, $simpleFallback) {
		// Flash fallback is disabled => use simple fallback
		if (!$this->flashMediaElement) {
			return $simpleFallback;
		}

		// Collect possible fallback files
		$fallbackMedia = $this->prioritizeMedia($media, $this->fallbackFormats, false);

		// No fallback files available => use simple fallback
		if (empty($fallbackMedia)) {
			return $simpleFallback;
		}

		// Only use first fallback candidate
		$fallbackMedia = array_shift($fallbackMedia);

		// Generate HTML output for Flash fallback
		$objectTag = $this->objectManager->get('TYPO3\CMS\Fluid\Core\ViewHelper\TagBuilder', 'object');
		$objectTag->addAttributes(array(
			'type' => 'application/x-shockwave-flash',
			'data' => $this->flashMediaElement,
			'height' => $this->tag->getAttribute('height'),
			'width' => $this->tag->getAttribute('width')
		));

		$objectContent = '';

		$paramTag = $this->objectManager->get('TYPO3\CMS\Fluid\Core\ViewHelper\TagBuilder', 'param');
		$paramTag->addAttributes(array(
			'name' => 'movie',
			'value' => $this->flashMediaElement
		));
		$objectContent .= $paramTag->render();

		$paramTag = $this->objectManager->get('TYPO3\CMS\Fluid\Core\ViewHelper\TagBuilder', 'param');
		$paramTag->addAttributes(array(
			'name' => 'flashvars',
			'value' => 'controls=' . (($this->tag->hasAttribute('controls')) ? 'true' : 'false')
				. '&file=' . $this->generatePublicUrl($fallbackMedia->getOriginalResource())
		));
		$objectContent .= $paramTag->render();

		// Use simple fallback as fallback in case Flash is not available
		$objectContent .= $simpleFallback;

		$objectTag->setContent($objectContent);

		return $objectTag->render();
	}

	/**
	 * Renders a linked image or a text link as a simple fallback
	 *
	 * @param  \WIRO\Html5mediaelements\Domain\Model\Media $media         media record
	 * @param  array                                       $orderedMedia  HTML source candidates
	 * @return string                                                     <a> tag as simple fallback
	 */
	protected function renderSimpleFallback(\WIRO\Html5mediaelements\Domain\Model\Media $media, array $orderedMedia) {
		// Use first media file as link target
		$primaryMediaFile = array_shift($orderedMedia);

		// Generate <a> tag
		$aTag = $this->objectManager->get('TYPO3\CMS\Fluid\Core\ViewHelper\TagBuilder', 'a');
		$aTag->addAttribute('href', $this->generatePublicUrl($primaryMediaFile->getOriginalResource()));

		if ($media->isVideo() && $media->getPoster()) {
			// Generate <img> tag containing the poster image
			$imgTag = $this->objectManager->get('TYPO3\CMS\Fluid\Core\ViewHelper\TagBuilder', 'img');
			$imgTag->addAttributes(array(
				'src' => $this->generatePublicUrl($media->getPoster()->getOriginalResource()),
				'height' => $media->getPoster()->getOriginalResource()->getProperty('height'),
				'width' => $media->getPoster()->getOriginalResource()->getProperty('width'),
				'alt' => $media->getPoster()->getOriginalResource()->getProperty('alternative')
			));

			$aTag->setContent($imgTag->render());
		} else {
			// Output title of the media element
			$aTag->setContent($media->getTitle());
		}

		return $aTag->render();
	}

	/**
	 * Sets the dimension of the <video> tag based on the provided media file, but only if they
	 * aren't present
	 *
	 * @param  \TYPO3\CMS\Core\Resource\FileReference $mediaFile  media file
	 * @return void
	 */
	protected function addVideoDimensions(\TYPO3\CMS\Core\Resource\FileReference $mediaFile) {
		if (
			!$this->tag->hasAttribute('width') &&
			!$this->tag->hasAttribute('height') &&
			$mediaFile->getProperty('width') &&
			$mediaFile->getProperty('height')
		) {
			$this->tag->addAttribute('height', $mediaFile->getProperty('height'));
			$this->tag->addAttribute('width', $mediaFile->getProperty('width'));
		}
	}

	/**
	 * Sorts the optimized media files based on the provided priority options
	 *
	 * Example:
	 *
	 *   optimized media files: [1.ogv, 2.mp4, 3.webm, 4.mp4, 5.wmv]
	 *              priorities: [video/mp4, video/webm]
	 *            appendOthers: true
	 *           property name: mime_type
	 *                  result: [2.mp4, 3.webm, 1.ogv, 4.mp4, 5.wmv]
	 *
	 * @param  \WIRO\Html5mediaelements\Domain\Model\Media  $media         media record
	 * @param  array                                        $priorities    array of values for $propertyName that
	 *                                                                     define the priority of the media files
	 * @param  boolean                                      $appendOthers  append duplicate priority media files or files that don't
	 *                                                                     match a priority to the result array as well
	 *                                                                     (in their original order after the media files with priorities)
	 * @param  string                                       $propertyName  file property based on which priority should be measured
	 * @return array                                                       sorted array of media files
	 */
	protected function prioritizeMedia(\WIRO\Html5mediaelements\Domain\Model\Media $media, array $priorities, $appendOthers = true, $propertyName = 'mime_type') {
		// Reserve spots for prioritized formats
		$orderedMedia = array_fill(0, count($priorities), NULL);

		$priorityFormats = array_flip(array_unique($priorities));
		foreach ($media->getOptimizedMedia() as $optimizedMedia) {
			$optimizedFile = $optimizedMedia->getOptimizedFile();
			if (!$optimizedFile) {
				continue;
			}

			$property = $optimizedFile->getOriginalResource()->getProperty($propertyName);

			// Check if format should be prioritized
			if (isset($priorityFormats[$property]) && !isset($orderedMedia[$priorityFormats[$property]])) {
				// Prioritized format: Put into reserved spot
				$orderedMedia[$priorityFormats[$property]] = $optimizedFile;
			} else if ($appendOthers) {
				// Append others
				$orderedMedia[] = $optimizedFile;
			}
		}

		// Remove empty spots, use sequenced indexes
		return array_values(array_filter($orderedMedia));
	}

	/**
	 * Generates a public URL to the specified file
	 * This is necessary because TYPO3 doesn't prepend the absRefPrefix in all cases.
	 *
	 * @param  \TYPO3\CMS\Core\Resource\FileReference $mediaFile  media file
	 * @return string                                             URL to the file
	 */
	protected function generatePublicUrl(\TYPO3\CMS\Core\Resource\FileReference $mediaFile) {
		return $this->configurationManager->getContentObject()
			->typolink_URL(array(
				'parameter' => $mediaFile->getPublicUrl()
			));
	}
}