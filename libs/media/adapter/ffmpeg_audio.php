<?php
/**
 * Ffmpeg Audio Media Adapter File
 *
 * Copyright (c) 2007-2010 David Persson
 *
 * Distributed under the terms of the MIT License.
 * Redistributions of files must retain the above copyright notice.
 *
 * PHP version 5
 * CakePHP version 1.3
 *
 * @package    media
 * @subpackage media.libs.media.adapter
 * @copyright  2007-2010 David Persson <davidpersson@gmx.de>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/media
 */

/**
 * Ffmpeg Audio Media Adapter Class
 *
 * @package    media
 * @subpackage media.libs.media.adapter
 * @link       http://ffmpeg.mplayerhq.hu/
 */
class FfmpegAudioMediaAdapter extends MediaAdapter {
	var $require = array(
		'mimeTypes' => array(
			'audio/mpeg',
			/* Ffmpeg Extension can't read meta info other than ID3! */
			'audio/ms-wma',
			'audio/realaudio',
			'audio/wav',
			'audio/ogg',
			/* Some Ogg files may have 'application/octet-stream' MIME type. */
			'application/octet-stream',
		),
		'extensions' => array('ffmpeg'),
	);

	function initialize($Media) {
		if (isset($Media->objects['ffmpeg_movie'])) {
			return true;
		}
		if (!isset($Media->file)) {
			return false;
		}

		$Media->objects['ffmpeg_movie'] = new ffmpeg_movie($Media->file);

		if (!$Media->objects['ffmpeg_movie']->hasAudio()) {
			return false;
		}

		return true;
	}

	function artist($Media) {
		return $Media->objects['ffmpeg_movie']->getArtist();
	}

	function title($Media) {
		return $Media->objects['ffmpeg_movie']->getTitle();
	}

	function album($Media) {
		return $Media->objects['ffmpeg_movie']->getAlbum();
	}

	function year($Media) {
		return $Media->objects['ffmpeg_movie']->getYear();
	}

	function duration($Media) {
		return $Media->objects['ffmpeg_movie']->getDuration();
	}

	function track($Media) {
		return $Media->objects['ffmpeg_movie']->getTrackNumber();
	}

	function samplingRate($Media) {
		return $Media->objects['ffmpeg_movie']->getAudioSampleRate();
	}

	function bitRate($Media) {
		return $Media->objects['ffmpeg_movie']->getBitRate();
	}
}
?>