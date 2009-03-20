<?php
/**
 * Ff Mpeg Audio Medium Adapter File
 *
 * Copyright (c) 2007-2009 David Persson
 *
 * Distributed under the terms of the MIT License.
 * Redistributions of files must retain the above copyright notice.
 *
 * PHP version 5
 * CakePHP version 1.2
 *
 * @package    media
 * @subpackage media.libs.medium.adapter
 * @copyright  2007-2009 David Persson <davidpersson@qeweurope.org>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/media
 */
/**
 * Ff Mpeg Audio Medium Adapter Class
 *
 * @package    media
 * @subpackage media.libs.medium.adapter
 * @link       http://ffmpeg.mplayerhq.hu/
 */
class FfMpegAudioMediumAdapter extends MediumAdapter {
	var $require = array(
							'mimeTypes' => array(
										'audio/ogg',
										'audio/mpeg',
										'audio/ms-wma',
										'audio/realaudio',
										'audio/wav',
										),
							'extensions' => array('ffmpeg'),
							);

	function initialize(&$Medium) {
		if (isset($Medium->objects['ffmpeg_movie'])) {
			return true;
		}
		if (!isset($Medium->file)) {
			return false;
		}

		$Medium->objects['ffmpeg_movie'] = new ffmpeg_movie($Medium->file);

		if (!$Medium->objects['ffmpeg_movie']->hasAudio()) {
			return false;
		}

		return true;
	}

	function artist(&$Medium) {
		return $Medium->objects['ffmpeg_movie']->getArtist();
	}

	function title(&$Medium) {
		return $Medium->objects['ffmpeg_movie']->getTitle();
	}

	function year(&$Medium) {
		return $Medium->objects['ffmpeg_movie']->getYear();
	}

	function duration(&$Medium) {
		return $Medium->objects['ffmpeg_movie']->getDuration();
	}

	function track(&$Medium) {
		return $Medium->objects['ffmpeg_movie']->getTrackNumber();
	}

	function samplingRate(&$Medium) {
		return $Medium->objects['ffmpeg_movie']->getAudioSampleRate();
	}
}
?>