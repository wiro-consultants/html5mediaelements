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

	/**
	 * ContentObject Renderer
	 * @var \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer
	 * @inject
	 */
	protected $cObj;

	protected $pageTSconfigCache = array();

	protected $tempPath;
	protected $tempPathPhp;
	protected $tempPathFiles;

	protected $defaultPoster;
	protected $defaultAudio;
	protected $defaultVideo;
	protected $defaultConfig;

	/**
	 * Initializes the task
	 * @return void
	 */
	protected function initialize() {
		$this->defaultAudio = array(
			'filename' => '###NAME###',
			'format' => '',
			'audio' => array(
				'enabled' => true,
				'codec' => '',
				'bitrate' => '',
				'quality' => '',
				'sampleFrequency' => '',
				'channels' => '',
				'volume' => ''
			)
		);

		$this->defaultVideo = $this->defaultAudio;
		$this->defaultVideo['video'] = array(
			'enabled' => true,
			'codec' => '',
			'height' => '',
			'width' => '',
			'aspectRatio' => '',
			'frameRate' => '',
			'maxFrames' => '',
			'bitrate' => '',
			'pixelFormat' => '',
			'quality' => '',
			'h264' => array(
				'preset' => '',
				'tune' => '',
				'profile' => ''
			)
		);

		$this->defaultConfig = array(
			'notification' => array(
				'error' => array(
					'subject' => 'Error during task execution',
					'message' => "An error occured during task execution\n\n###ERROR###",
					'mediafiles' => '###TITLE### (###UID###)',
					'signature' => ''
				),
				'success' => array(
					'subject' => 'Conversion successful',
					'message' => '###MEDIAFILES###',
					'mediafiles' => '###TITLE### (###UID###)',
					'signature' => ''
				),
			),
			'poster' => array(
				'filename' => '###NAME###.jpg',
				'format' => '',
				'height' => '',
				'width' => '',
				'quality' => ''
			),
			'audio' => array(),
			'video' => array()
		);
	}

	/**
	 * Perform media file optimization
	 * @param  string $ffmpegPath    Path to ffmpeg executable
	 * @param  string $ffprobePath   Path to ffprobe executable
	 * @param  string $tempPath      Temporary path
	 * @param  string $errorEmail    Notification recipient(s) for errors
	 * @param  string $successEmail  Notification recipient(s) for success
	 * @return string                Task status
	 */
	public function optimizeMediaCommand(
		$ffmpegPath = '/usr/local/bin/ffmpeg',
		$ffprobePath = '/usr/local/bin/ffprobe',
		$tempPath = 'typo3temp/phpvideotoolkit',
		$errorEmail = '',
		$successEmail = ''
	) {
		// Don't do anything without valid configuration
		if (!$tempPath || !$ffmpegPath || !$ffprobePath) {
			return false;
		}

		try {
			// Initialize task
			$this->initialize();

			// Create folder structure for temporary files
			$this->tempPath = rtrim(GeneralUtility::getFileAbsFileName($tempPath), '/') . '/';
			GeneralUtility::mkdir_deep($this->tempPath);

			$this->tempPathPhp = $this->tempPath . 'php/';
			GeneralUtility::mkdir($this->tempPathPhp);

			$this->tempPathFiles = $this->tempPath . 'files/';
			GeneralUtility::mkdir($this->tempPathFiles);

			// Initialize PHP Video Toolkit
			require \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath(
				'html5mediaelements',
				'Resources/Private/Php/phpvideotoolkit/autoloader.php'
			);

			// Configure PHP Video Toolkit
			$toolkitConfig = new \PHPVideoToolkit\Config(array(
				'temp_directory' => $this->tempPathPhp,
				'cache_driver' => 'InTempDirectory',

				'ffmpeg' => $ffmpegPath,
				'ffprobe' => $ffprobePath,
			), true);

			// Find all media records that need to be optimized
			$mediaRecords = $this->mediaRepository->findOptimizeQueue();

			foreach ($mediaRecords as $media) {
				// Get configuration for this page
				$config = $this->getPageTSconfig($media->getPid());
				$config = $this->validateConfig($config);

				// Set dimension metadata for the source media file
				$this->setFileMetadata(
					$media->getSourceFile()->getOriginalResource()->getOriginalFile()
				);

				// Should the video file get cropped?
				$croppingChanged = false;
				/*
				if ($media->isVideo() && !$media->getIsCropped() && $media->getAutoCrop()) {
					// TODO Determine cropping factor
					$croppingChanged = false;
				}
				*/

				// Should the media files be converted?
				if (
					$media->getAutoConvert() &&
					(!$media->getIsConverted() || $croppingChanged)
				) {
					// Convert video
					$convertConfig = ($media->isAudio()) ? $config['audio'] : $config['video'];
					$this->convertMedia($media, $convertConfig);
				}

				// Should a poster image be generated?
				if (
					$media->isVideo() &&
					$media->getAutoPoster() !== Media::AUTO_POSTER_DISABLED &&
					!$media->getPoster()
				) {
					$this->generatePosterImage($media, $config['poster']);
				}

				// Send notification email on success?
				if ($successEmail) {
					$this->sendNotification($successEmail, $config['notification']['success'], $media);
				}
			}
		} catch (\Exception $e) {
			// No recipient for errors specified? => Don't handle exceptions
			if (!$errorEmail) {
				throw $e;
			}

			// Use default configuration as fallback if the error happened before a
			// configuration was present
			if (!isset($config)) {
				$config = $this->defaultConfig;
			}

			// Send notification email with error output
			$this->sendNotification($errorEmail, $config['notification']['error'], $media, $e);

			// Show error status of task
			return false;
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
		$fileName = $this->generateFilename($originalFile, $config['filename']);
		$filePath = $this->tempPathFiles . $fileName;

		// Configure output format
		$className = '\\PHPVideoToolkit\\ImageFormat_' . ucfirst($config['format']);
		if ($config['format'] && class_exists($className)) {
			$format = new $className;
		} else {
			$format = \PHPVideoToolkit\Format::getFormatFor($filePath, null, 'ImageFormat');
		}

		// Set format options from configuration
		if ($config['quality']) {
			$format->setVideoQuality($config['quality']);
		}
		if ($config['width'] && $config['height']) {
			$format->setVideoDimensions($config['width'], $config['height'], true);
		}

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
	 * Converts source media to configured media formats and references them
	 *
	 * @param  \WIRO\Html5mediaelements\Domain\Model\Media $media   media record
	 * @param  array                                       $config  conversion configuration for media formats
	 * @return void
	 */
	protected function convertMedia(Media $media, array $config) {
		// Create a local file for processing
		$originalFile = $media->getSourceFile()->getOriginalResource()
			->getOriginalFile();
		$localFile = $originalFile->getForLocalProcessing(FALSE);

		// Prepare for conversion
		if ($media->isAudio()) {
			$source  = new \PHPVideoToolkit\Audio($localFile);
		} else {
			$source = new \PHPVideoToolkit\Video($localFile);
		}
		$multiOutput = new \PHPVideoToolkit\MultiOutput();

		$outputContext = array();
		foreach ($config as $formatName => $formatConfig) {
			// Skip files without audio and video
			if (
				($media->isAudio() && !$formatConfig['audio']['enabled']) ||
				($media->isVideo() && !$formatConfig['audio']['enabled'] && !$formatConfig['video']['enabled'])
			) {
				continue;
			}

			// Generate video file name
			$fileName = $this->generateFilename($originalFile, $formatConfig['filename']);
			$filePath = $this->tempPathFiles . $fileName;

			// Store information about conversion input
			$outputContext[] = array(
				'format' => $formatName,
				'path' => $filePath,
				'name' => $fileName
			);

			// Configure output format
			$fallbackFormat = ($media->isAudio()) ? 'AudioFormat' : 'VideoFormat';
			$className = '\\PHPVideoToolkit\\' . $fallbackFormat . '_' . ucfirst($formatConfig['format']);
			if ($formatConfig['format'] && class_exists($className)) {
				$format = new $className;
			} else {
				$format = \PHPVideoToolkit\Format::getFormatFor($filePath, null, $fallbackFormat);
			}

			// Set format options from configuration
			if ($formatConfig['audio']['enabled']) {
				if ($formatConfig['audio']['codec']) {
					$format->setAudioCodec($formatConfig['audio']['codec']);
				}
				if ($formatConfig['audio']['bitrate']) {
					$format->setAudioBitrate((float) $formatConfig['audio']['bitrate']);
				}
				if ($formatConfig['audio']['quality']) {
					$format->setAudioQuality((float) $formatConfig['audio']['quality']);
				}
				if ($formatConfig['audio']['sampleFrequency']) {
					$format->setAudioSampleFrequency((int) $formatConfig['audio']['sampleFrequency']);
				}
				if ($formatConfig['audio']['channels']) {
					$format->setAudioChannels((int) $formatConfig['audio']['channels']);
				}
				if ($formatConfig['audio']['volume']) {
					$format->setVolume((int) $formatConfig['audio']['volume']);
				}
			} else {
				$format->disableAudio();
			}

			if ($media->isVideo()) {
				if ($formatConfig['video']['enabled']) {
					if ($formatConfig['video']['codec']) {
						$format->setVideoCodec($formatConfig['video']['codec']);
					}
					if ($formatConfig['video']['width'] && $formatConfig['video']['height']) {
						$format->setVideoDimensions($formatConfig['video']['width'], $formatConfig['video']['height'], true);
					}
					if ($formatConfig['video']['aspectRatio']) {
						$format->setVideoAspectRatio($formatConfig['video']['aspectRatio'], true);
					}
					if ($formatConfig['video']['frameRate']) {
						$format->setVideoFrameRate((float) $formatConfig['video']['frameRate']);
					}
					if ($formatConfig['video']['maxFrames']) {
						$format->setVideoMaxFrames($formatConfig['video']['maxFrames']);
					}
					if ($formatConfig['video']['bitrate']) {
						$format->setVideoBitrate($formatConfig['video']['bitrate']);
					}
					if ($formatConfig['video']['pixelFormat']) {
						$format->setVideoPixelFormat($formatConfig['video']['pixelFormat']);
					}
					if ($formatConfig['video']['quality']) {
						$format->setVideoQuality((float) $formatConfig['video']['quality']);
					}
					if ($formatConfig['video']['h264']['preset']) {
						$format->setH264Preset($formatConfig['video']['h264']['preset']);
					}
					if ($formatConfig['video']['h264']['tune']) {
						$format->setH264Tune($formatConfig['video']['h264']['tune']);
					}
					if ($formatConfig['video']['h264']['profile']) {
						$format->setH264Profile($formatConfig['video']['h264']['profile']);
					}
				} else {
					$format->disableVideo();
				}
			}

			// Add to conversion command
			$multiOutput->addOutput($filePath, $format);
		}

		if (empty($outputContext)) {
			return;
		}

		// TODO make asynchronous
		$process = $source->save($multiOutput, null, \PHPVideoToolkit\Media::OVERWRITE_UNIQUE);

		/*
		// Start conversion
		$progressHandler = new \PHPVideoToolkit\ProgressHandlerNative(null);
		$process = $source->saveNonBlocking($multiOutput, null, \PHPVideoToolkit\Media::OVERWRITE_UNIQUE, $progressHandler);

		\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($progressHandler, 'SP');
		return;

		// Wait for files being converted
		$lastStatus = null;
		while ($progressHandler->completed !== true) {
			$probe = $progressHandler->probe(true);
			if ($probe['status'] !== $lastStatus) {
				//echo $probe['status'] . PHP_EOL;
				$lastStatus = $probe['status'];
			}

			sleep(0.5);
		}
		*/

		// Get converted files
		$outputFiles = $process->getAllOutput();

		foreach ($outputContext as $i => $fileOptions) {
			// Add converted file to FAL
			$file = $originalFile->getParentFolder()->addFile($outputFiles[$i], $fileOptions['name'], 'changeName');
			// TODO check for permission of user (_cli_scheduler)

			// Set dimension metadata of converted file
			$this->setFileMetadata($file);

			// Create new optimized media record
			$mediaOptimized = $this->objectManager->get('WIRO\Html5mediaelements\Domain\Model\MediaOptimized');
			$mediaOptimized->setPid($media->getPid());
			$mediaOptimized->setType(($media->isAudio()) ? MediaOptimized::TYPE_AUDIO : MediaOptimized::TYPE_VIDEO);
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
	 * Updates the dimension (height, width) and duration metadata of the specified media file in FAL
	 *
	 * @param  \TYPO3\CMS\Core\Resource\File $file  FAL record
	 * @return void
	 */
	protected function setFileMetadata(\TYPO3\CMS\Core\Resource\File $file) {
		// Get file for local processing
		$localFile = $file->getForLocalProcessing(FALSE);

		// Fetch media information
		$parser = new \PHPVideoToolkit\MediaParser();
		$fileInfo = $parser->getFileInformation($localFile);

		// Collect relevant media information
		$fileMetadata = array();
		$fileMetadata['duration'] = $fileInfo['duration']->total_seconds;
		if (isset($fileInfo['video']['dimensions'])) {
			$fileMetadata['height'] = $fileInfo['video']['dimensions']['height'];
			$fileMetadata['width'] = $fileInfo['video']['dimensions']['width'];
		}

		// Update metadata of FAL record
		if (!empty($fileMetadata)) {
			$file->_updateMetaDataProperties($fileMetadata);
			$this->metaDataRepository->update($file->getUid(), $fileMetadata);
		}
	}

	/**
	 * Generates a new file name based on an existing file and the format of the new name
	 * @param  \TYPO3\CMS\Core\Resource\File $file            original file
	 * @param  string                        $filenameFormat  filename format with markers
	 * @return string                                         new file name
	 */
	protected function generateFilename(\TYPO3\CMS\Core\Resource\File $file, $filenameFormat) {
		return $this->cObj->substituteMarkerArray($filenameFormat, array(
			'###NAME###' => basename(
				$file->getProperty('name'),
				($file->getProperty('extension')) ? '.' . $file->getProperty('extension') : ''
			),
			'###EXTENSION###' => $file->getProperty('extension'),
			'###UID###' => $file->getUid(),
			'###SHA1###' => $file->getProperty('sha1'),
			'###HASH###' => $file->getProperty('identifier_hash'),
			'###UNIQUE###' => md5(uniqid($file->getProperty('name'), true))
		));
	}

	/**
	 * Sends a notification email
	 *
	 * @param  string     $recipients  comma-separated list of email addresses that should
	 *                                 receive the notification
	 * @param  array      $config      notification configuration
	 * @param  object     $media       one or multiple media records (QueryResult)
	 * @param  \Exception $exception   the exception that was thrown
	 * @return boolean                 success status of email
	 */
	protected function sendNotification($recipients, array $config, object $media, \Exception $exception = NULL) {
		// Convert comma-separated list to array
		$recipients = array_map('trim', explode(',', $recipients));

		// Generate markers for the email subject and content
		$markers = array(
			'###SIGNATURE###' => $config['signature'],
			'###BACKEND_URL###' => GeneralUtility::locationHeaderUrl('/typo3/')
		);

		if (isset($exception)) {
			$markers['###ERROR###'] = $exception->getMessage();
			$markers['###ERROR_FILE###'] = $exception->getFile();
			$markers['###ERROR_LINE###'] = $exception->getLine();
		}

		// Generate list of media files for the email
		$allMedia = ($media instanceof Media) ? array($media) : $media->toArray();
		$mediaFiles = array();
		foreach ($allMedia as $oneMedia) {
			// Get all properties of media record that can be outputted
			$mediaMarkers = array();
			foreach ($oneMedia->toArray() as $key => $value) {
				if (!is_object($value)) {
					$mediaMarkers[$key] = $value;
				}
			}

			// Provide properties as markers
			$mediaFiles[] = $this->cObj->substituteMarkerArray(
				$config['mediafiles'],
				$mediaMarkers,
				'###|###',
				TRUE
			);
		}
		$markers['###MEDIAFILES###'] = implode("\n", $mediaFiles);

		// Replace markers in subject and content
		$subject = $this->cObj->substituteMarkerArray($config['subject'], $markers);
		$message = $this->cObj->substituteMarkerArray($config['message'], $markers);

		// Send email
		return $this->objectManager->get('TYPO3\\CMS\\Core\\Mail\\MailMessage')
			->setFrom(\TYPO3\CMS\Core\Utility\MailUtility::getSystemFrom())
			->setTo($recipients)
			->setSubject($subject)
			->setBody($message)
			->send();
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
	 * Validates the conversion configuration
	 * @param  array $config  configuration
	 * @return array          validated configuration
	 */
	protected function validateConfig(array $config) {
		$config = array_merge($this->defaultConfig, $config);
		foreach (array('poster', 'notification') as $key) {
			$config[$key] = GeneralUtility::array_merge_recursive_overrule($this->defaultConfig[$key], $config[$key]);
		}
		foreach ($config['audio'] as &$audioConfig) {
			$audioConfig = GeneralUtility::array_merge_recursive_overrule($this->defaultAudio, $audioConfig);
		}
		foreach ($config['video'] as &$videoConfig) {
			$videoConfig = GeneralUtility::array_merge_recursive_overrule($this->defaultVideo, $videoConfig);
		}
		return $config;
	}
}