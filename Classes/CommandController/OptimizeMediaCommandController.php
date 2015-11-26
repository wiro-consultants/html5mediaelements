<?php
namespace WIRO\Html5mediaelements\CommandController;

use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Backend\Utility\BackendUtility;
use \WIRO\Html5mediaelements\Domain\Model\Media;
use \WIRO\Html5mediaelements\Domain\Model\MediaOptimized;

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
 * OptimizeMediaCommandController
 */
class OptimizeMediaCommandController extends \TYPO3\CMS\Extbase\Mvc\Controller\CommandController {
	/**
	 * Persistence Manager
	 *
	 * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
	 * @inject
	 */
	protected $persistenceManager;

	/**
	 * TypoScript Service
	 *
	 * @var \TYPO3\CMS\Extbase\Service\TypoScriptService
	 * @inject
	 */
	protected $typoScriptService;

	/**
	 * MetaData Repository
	 * @var \TYPO3\CMS\Core\Resource\Index\MetaDataRepository
	 * @inject
	 */
	protected $metaDataRepository;

	/**
	 * Media Repository
	 *
	 * @var \WIRO\Html5mediaelements\Domain\Repository\MediaRepository
	 * @inject
	 */
	protected $mediaRepository;

	protected $pageTSconfigCache = array();

	protected $tempPath;
	protected $tempPathPhp;
	protected $tempPathFiles;

	/**
	 * Perform media file optimization
	 * @param  string $ffmpegPath   Path to ffmpeg executable
	 * @param  string $ffprobePath  Path to ffprobe executable
	 * @param  string $tempPath     Temporary path
	 * @return string               Task status
	 */
	public function optimizeMediaCommand(
		$ffmpegPath = '/usr/local/bin/ffmpeg',
		$ffprobePath = '/usr/local/bin/ffprobe',
		$tempPath = 'typo3temp/phpvideotoolkit'
	) {
		// Don't do anything without valid configuration
		if (!$tempPath || !$ffmpegPath || !$ffprobePath) {
			return false;
		}

		// Create folder structure for temporary files
		$this->tempPath = rtrim(GeneralUtility::getFileAbsFileName($tempPath), '/') . '/';
		GeneralUtility::mkdir_deep($this->tempPath);

		$this->tempPathPhp = $this->tempPath . 'php/';
		GeneralUtility::mkdir($this->tempPathPhp);

		$this->tempPathFiles = $this->tempPath . 'files/';
		GeneralUtility::mkdir($this->tempPathFiles);

		// Initialize PHP Video Toolkit
		require \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('html5mediaelements', 'Resources/Private/Php/phpvideotoolkit/autoloader.php');

		// Configure PHP Video Toolkit
		$toolkitConfig = new \PHPVideoToolkit\Config(array(
			'temp_directory' => $this->tempPathPhp,
			'cache_driver' => 'InTempDirectory',

			'ffmpeg' => $ffmpegPath,
			'ffprobe' => $ffprobePath,
		), true);

		// Find all media records that need to be optimized
		$mediaRecords = $this->mediaRepository->findOptimizeQueue();

		foreach ($mediaRecords as $mediaRecord) {
			// Get configuration for this page
			$config = $this->getPageTSconfig($mediaRecord->getPid());

			// TODO Validate config

			// Set dimension metadata for the source video file
			$this->setVideoDimensions(
				$mediaRecord->getSourceFile()->getOriginalResource()->getOriginalFile()
			);

			// Should the video file get cropped?
			if (!$mediaRecord->getIsCropped() && $mediaRecord->getAutoCrop()) {
				// Determine cropping factor
				$croppingChanged = false;
			}

			// Should the media files be converted?
			if ($mediaRecord->getAutoConvert() && (!$mediaRecord->getIsConverted() || $croppingChanged)) {
				// Convert video
				$this->convertVideo($mediaRecord, $config['video']);
			}

			// Should a poster image be generated
			if ($mediaRecord->getAutoPoster() !== Media::AUTO_POSTER_DISABLED && (!$mediaRecord->getPoster() || $croppingChanged)) {
				$this->generatePosterImage($mediaRecord, $config['poster']);
			}
		}

		return true;
	}

	/**
	 * Generates a poster image from the video source file
	 * @param  \WIRO\Html5mediaelements\Domain\Model\Media $media   media record
	 * @param  array                                       $config  conversion configuration for poster
	 * @return void
	 */
	protected function generatePosterImage(Media $media, array $config) {
		// Create a local file for processing
		$originalFile = $media->getSourceFile()->getOriginalResource()
			->getOriginalFile();
		$localFile = $originalFile->getForLocalProcessing(FALSE);

		// Extract video information
		$parser = new \PHPVideoToolkit\MediaParser();
		$fileInfo = $parser->getFileInformation($localFile);

		// Decide which frame from the video should be extracted
		switch ($media->getAutoPoster()) {
			// First frame
			case Media::AUTO_POSTER_START:
				$timecode = $fileInfo['start'];
				break;

			// Middle frame
			case Media::AUTO_POSTER_MIDDLE:
				$timecode = new \PHPVideoToolkit\Timecode($fileInfo['duration']->total_seconds / 2);
				break;

			// Last frame
			case Media::AUTO_POSTER_END:
				$timecode = $fileInfo['duration'];
				break;

			// Invalid configuration => Skip
			default:
				return;
		}

		// Generate poster file name
		$fileIdentifier = basename(
			$originalFile->getProperty('name'),
			'.' . $originalFile->getProperty('extension')
		);
		$fileName = sprintf($config['filename'], $fileIdentifier);
		$filePath = $this->tempPathFiles . $fileName;

		// Configure output format
		$format = \PHPVideoToolkit\Format::getFormatFor($filePath, null, 'VideoFormat');
		// TODO set configuration options

		// Extract video frame
		$video  = new \PHPVideoToolkit\Video($localFile);
		$process = $video->extractFrame($timecode)
			->save($filePath, $format, \PHPVideoToolkit\Media::OVERWRITE_UNIQUE);

		// Add poster image to FAL
		$filePath = $process->getOutput()->getMediaPath();
		$file = $originalFile->getParentFolder()->addFile($filePath, $fileName, 'changeName');
		// TODO check for permission of user (_cli_scheduler)

		// Add reference to poster image in media record
		$errors = $this->addFileReference(
			'tx_html5mediaelements_domain_model_media',
			'poster',
			$media,
			$file
		);

		// Output errors
		if (!empty($errors)) {
			throw new \Exception(implode("\n", $errors));
		}
	}

	/**
	 * Converts source video to configured video formats and references them
	 *
	 * @param  \WIRO\Html5mediaelements\Domain\Model\Media $media   media record
	 * @param  array                                       $config  conversion configuration for videos
	 * @return void
	 */
	protected function convertVideo(Media $media, array $config) {
		// Create a local file for processing
		$originalFile = $media->getSourceFile()->getOriginalResource()
			->getOriginalFile();
		$localFile = $originalFile->getForLocalProcessing(FALSE);

		// Generate video file name (1)
		$fileIdentifier = basename(
			$originalFile->getProperty('name'),
			'.' . $originalFile->getProperty('extension')
		);

		// Prepare for conversion
		$video  = new \PHPVideoToolkit\Video($localFile);
		$multiOutput = new \PHPVideoToolkit\MultiOutput();

		$inputFiles = array();
		foreach ($config as $formatName => $format) {
			// Generate video file name (2)
			$fileName = sprintf($format['filename'], $fileIdentifier);
			$filePath = $this->tempPathFiles . $fileName;

			// Store information about conversion input
			$inputFiles[] = array(
				'format' => $formatName,
				'path' => $filePath,
				'name' => $fileName
			);

			// Configure output format
			$format = \PHPVideoToolkit\Format::getFormatFor($filePath, null, 'VideoFormat');
			//$format->setVideoDimensions(\PHPVideoToolkit\VideoFormat::DIMENSION_HD480);
			// TODO set configuration options

			// Add to conversion command
			$multiOutput->addOutput($filePath, $format);
		}

		// Start conversion
		$progressHandler = new \PHPVideoToolkit\ProgressHandlerNative(null);
		$process = $video->saveNonBlocking($multiOutput, null, \PHPVideoToolkit\Media::OVERWRITE_UNIQUE, $progressHandler);

		// Wait for files being converted
		// TODO make asynchronous
		$lastStatus = null;
		while ($progressHandler->completed !== true) {
			$probe = $progressHandler->probe(true);
			if ($probe['status'] !== $lastStatus) {
				//echo $probe['status'] . PHP_EOL;
				$lastStatus = $probe['status'];
			}

			sleep(0.5);
		}

		// Get converted files
		$outputFiles = $process->getAllOutput();

		foreach ($inputFiles as $i => $fileOptions) {
			// Add converted file to FAL
			$file = $originalFile->getParentFolder()->addFile($outputFiles[$i], $fileOptions['name'], 'changeName');
			// TODO check for permission of user (_cli_scheduler)

			// Set dimension metadata of converted file
			// TODO performance!
			$this->setVideoDimensions($file);

			// Create new optimized media record
			$mediaOptimized = $this->objectManager->get('WIRO\Html5mediaelements\Domain\Model\MediaOptimized');
			$mediaOptimized->setPid($media->getPid());
			$mediaOptimized->setFormat($fileOptions['format']);

			// Add reference to media record
			$media->addOptimizedMedium($mediaOptimized);
			$this->mediaRepository->update($media);

			$this->persistenceManager->persistAll();

			// Add reference to converted file
			$this->addFileReference(
				'tx_html5mediaelements_domain_model_mediaoptimized',
				'optimized_file',
				$mediaOptimized,
				$file
			);
		}

		// Set converted flag in media record
		$media->setIsConverted(true);
		$this->mediaRepository->update($media);
	}

	/**
	 * Creates a sys_file_reference record
	 *
	 * @param string $table                                  table    the file reference relates to
	 * @param string $field                                  field    the file reference relates to
	 * @param \TYPO3\CMS\Extbase\DomainObject\AbstractEntity $record  record the file reference relates to
	 * @param \TYPO3\CMS\Core\Resource\File                  $file    file the file reference relates to
	 * @return array                                                  error log if an error occurs
	 */
	protected function addFileReference($table, $field, \TYPO3\CMS\Extbase\DomainObject\AbstractEntity $record, \TYPO3\CMS\Core\Resource\File $file) {
		// Make user admin temporarily to circumvent permission issues
		$adminStatus = $GLOBALS['BE_USER']->user['admin'];
		$GLOBALS['BE_USER']->user['admin'] = true;

		// Define content for file reference record
		$data = array();
		$data['sys_file_reference']['NEW1234'] = array(
			'uid_local' => $file->getUid(),
			'uid_foreign' => $record->getUid(),
			'tablenames' => $table,
			'fieldname' => $field,
			'pid' => $record->getPid(),
			'table_local' => 'sys_file',
		);
		// Create relation to main record
		$data[$table][$record->getUid()] = array($field => 'NEW1234');

		// Use TYPO3 data handler to simulate form submit in backend
		$tce = GeneralUtility::makeInstance('TYPO3\CMS\Core\DataHandling\DataHandler');
		$tce->start($data, array());
		$tce->process_datamap();

		// Revert original admin status
		$GLOBALS['BE_USER']->user['admin'] = $adminStatus;

		return $tce->errorLog;
	}

	/**
	 * Fetch page TSconfig of the specified page
	 *
	 * @param  int         $pageUid  page
	 * @param  string|null $branch   if specified only this branch of the tree will be returned
	 * @return array                 configuration array
	 */
	protected function getPageTSconfig($pageUid, $branch = 'tx_html5mediaelements') {
		// Not in cache?
		if (!isset($this->pageTSconfigCache[$pageUid])) {
			// Fetch and convert page TSconfig
			$this->pageTSconfigCache[$pageUid] = $this->typoScriptService->convertTypoScriptArrayToPlainArray(
				BackendUtility::getPagesTSconfig($pageUid)
			);
		}

		// Return configuration from cache
		return (isset($branch)) ? $this->pageTSconfigCache[$pageUid][$branch] : $this->pageTSconfigCache[$pageUid];
	}

	/**
	 * Updates the dimension (height, width) metadata of the specified video file in FAL
	 *
	 * @param  \TYPO3\CMS\Core\Resource\File $file  FAL record
	 * @return void
	 */
	protected function setVideoDimensions(\TYPO3\CMS\Core\Resource\File $file) {
		// Get file for local processing
		$localFile = $file->getForLocalProcessing(FALSE);

		// Fetch media information
		$parser = new \PHPVideoToolkit\MediaParser();
		$fileInfo = $parser->getFileInformation($localFile);

		// Update metadata of FAL record
		$videoDimensions = array(
			'height' => $fileInfo['video']['dimensions']['height'],
			'width' => $fileInfo['video']['dimensions']['width']
		);
		$file->_updateMetaDataProperties($videoDimensions);
		$this->metaDataRepository->update($file->getUid(), $videoDimensions);
	}
}