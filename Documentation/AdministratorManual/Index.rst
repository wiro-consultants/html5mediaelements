.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _admin-manual:

Administrator Manual
====================

Installation
------------

Obvilously, the first step is to install the extension.

Next you should include the static TypoScript template in your preferred template record. With the following TypoScript constants you can define which files should be loaded in the frontend:

::

	plugin.tx_html5mediaelements.settings {
		# Include the built-in jQuery
		includeJquery = 1

		# Include the JavaScript of the plugin
		includeJs = 1

		# Include the CSS file of the plugin
		includeCss = 1
	}

Of course you can set the *storagePid* and the various fluid template paths as well.

Task configuration
^^^^^^^^^^^^^^^^^^

If you want to use the automatic conversion with ffmpeg, make sure that *ffmpeg* and *ffprobe* are installed on your server. Also make sure that the *_cli_scheduler* backend user has permission to write to the file storage in which you keep your media files.

Next, create a new scheduler task:

- Class: *Extbase CommandController Task*
- CommandController Command: *Html5mediaelements OptimizeMedia: optimizeMedia*
- It is strongly suggested to disable parallel execution!

After saving the new task, new options become available:

- Set *ffmpegPath* to the full path of the ffmpeg binary (try *which ffmpeg* on the terminal)
- Set *ffprobePath* to the full path of the ffprobe binary (try *which ffprobe* on the terminal)
- The *tempPath* can probably stay the same
- If you want to be notified on conversion errors, set *errorEmail* to your preferred email address
- If you want to be notified when a conversion was successful, set *successEmail* to your preferred email address

Notifications
-------------

The notification content can be configured with page TSconfig (see also: *EXT:html5mediaelements/Configuration/TSconfig/tsconfig.txt*):

::

	tx_html5mediaelements {
		# Email notification content
		notification {
			success {
				subject = Media files converted successfully
				message (
	The following media files have been converted to web-friendly formats:

	###MEDIAFILES###

	If you want to embed the converted files, just enter the TYPO3 backend.

	###SIGNATURE###
	)
				mediafiles = * ###TITLE### (###UID###)
				signature =
			}
			error {
				subject = Error while converting media files
				message (
	An error occured while converting the following media files:

	###MEDIAFILES###

	During the conversion the following error occured:

	###ERROR###

	###SIGNATURE###
	)
				mediafiles = * ###TITLE### (###UID###)
				signature =
			}
		}
	}

The following markers are available in *message*:

* *###SIGNATURE###*: Email signature provided with *signature* option
* *###MEDIAFILES###*: A list of media files that have been converted, each rendered with the *mediafiles* option

For error messages these extra markers are available:

* *###ERROR###*: Error message
* *###ERROR_FILE###*: File in which the error occured
* *###ERROR_LINE###*: Line in which the error occured

Media encoding configuration
----------------------------

The automatic media conversion is based on a library called `PHP Video Toolkit version 2 <https://github.com/buggedcom/phpvideotoolkit-v2>`_. The library provides various options for the audio, video and image generation. Some of those options can be set in page TSconfig to adjust encoding parameters of the web-friendly media files.

The default configuration looks like this (see also: *EXT:html5mediaelements/Configuration/TSconfig/tsconfig.txt*):

::

	tx_html5mediaelements {
		# Poster image format
		poster {
			filename = ###NAME###.jpg
			format = Jpeg

			height =
			width =
			quality =
		}

		# Web-friendly audio formats
		audio {
			mp3 {
				filename = ###NAME###.mp3
				format = Mp3

				audio {
					enabled = 1
					codec =
					bitrate =
					quality =
					sampleFrequency =
					channels =
					volume =
				}
			}

			ogg {
				filename = ###NAME###.oga
				format = Oga
			}
		}

		# Web-friendly video formats
		video {
			mp4 {
				filename = ###NAME###.mp4
				format = Mp4

				audio {
					enabled = 1
					codec =
					bitrate =
					quality =
					sampleFrequency =
					channels =
					volume =
				}

				video {
					enabled = 1
					codec =
					height =
					width =
					aspectRatio =
					frameRate =
					maxFrames =
					bitrate =
					pixelFormat =
					quality =
					h264 {
						preset =
						tune =
						profile = main
					}
				}
			}
			webm {
				filename = ###NAME###.webm
				format = Webm
			}
			ogv {
				filename = ###NAME###.ogv
				format = Ogg
			}
		}
	}

All options except for the filename can be left out, as you can see for *ogg*, *webm* and *ogv*. Just to be safe, you can define the file format explicitly with the *format* option, but if you leave it out, the library will decide based on the provided file extension.

The filename option allows the following markers:

* *###NAME###*: Original filename without the file extension
* *###EXTENSION###*: The file extension without leading dot
* *###UID###*: The UID of the FAL file record
* *###SHA1###*: The SHA1 hash of the original file
* *###HASH###*: The FAL identifier hash of the file
* *###UNIQUE###*: A MD5-hashed (semi-)unique string to obfuscate the file name

All other options correspond directly to the options of the library:

* format: The suffix of one of the supported `output format PHP classes <https://github.com/buggedcom/phpvideotoolkit-v2#phpvideotoolkit-output-formats>`_

Image options
^^^^^^^^^^^^^

tx_html5mediaelements.poster.

* height: *ImageFormat::setVideoDimensions()*
* width: *ImageFormat::setVideoDimensions()*
* quality: *ImageFormat::setVideoQuality()*

Audio options
^^^^^^^^^^^^^

tx_html5mediaelements.audio.

* enabled: *AudioFormat::enableAudio()* and *AudioFormat::disableAudio()*
* codec: *AudioFormat::setAudioCodec()*
* bitrate: *AudioFormat::setAudioBitrate()*
* quality: *AudioFormat::setAudioQuality()*
* sampleFrequency: *AudioFormat::setAudioSampleFrequency()*
* channels: *AudioFormat::setAudioChannels()*
* volume: *AudioFormat::setVolume()*

Video options
^^^^^^^^^^^^^

tx_html5mediaelements.video.

* enabled: *VideoFormat::enableVideo()* and *VideoFormat::disableVideo()*
* codec: *VideoFormat::setVideoCodec()*
* height: *VideoFormat::setVideoDimensions()*
* width: *VideoFormat::setVideoDimensions()*
* aspectRatio: *VideoFormat::setVideoAspectRatio()*
* frameRate: *VideoFormat::setVideoFrameRate()*
* maxFrames: *VideoFormat::setVideoMaxFrames()*
* bitrate: *VideoFormat::setVideoBitrate()*
* pixelFormat: *VideoFormat::setVideoPixelFormat()*
* quality: *VideoFormat::setVideoQuality()*
* h264.preset: *VideoFormat_H264::setH264Preset()*
* h264.tune: *VideoFormat_H264::setH264Tune()*
* h264.profile: *VideoFormat_H264::setH264Profile()*

Adding new encoding configurations
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

If you want to extend the default configuration to include other formats, the backend forms should be adjusted accordingly by using page TSconfig:

::

	# TCA adjustments for media formats
	TCEFORM.tx_html5mediaelements_domain_model_mediaoptimized.format.types {
		1.addItems {
			newVideoFormat = My new video format
		}

		2.addItems {
			newAudioFormat = My new audio format
		}
	}