<?php
/**
 * Ffmpeg Audio Medium Adapter File
 *
 * Copyright (c) 2007-2010 David Persson
 *
 * Distributed under the terms of the MIT License.
 * Redistributions of files must retain the above copyright notice.
 *
 * PHP version 5
 * CakePHP version 1.2
 *
 * @package    media
 * @subpackage media.libs.medium.adapter
 * @copyright  2007-2010 David Persson <davidpersson@gmx.de>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/media
 */
/**
 * Ffmpeg Audio Medium Adapter Class
 *
 * @package    media
 * @subpackage media.libs.medium.adapter
 * @link       http://ffmpeg.mplayerhq.hu/
 */
class FfmpegAudioMediumAdapter extends MediumAdapter {
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

	function initialize($Medium) {
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

	function artist($Medium) {
		return $Medium->objects['ffmpeg_movie']->getArtist();
	}

	function title($Medium) {
		return $Medium->objects['ffmpeg_movie']->getTitle();
	}

	function album($Medium) {
		return $Medium->objects['ffmpeg_movie']->getAlbum();
	}

	function year($Medium) {
		return $Medium->objects['ffmpeg_movie']->getYear();
	}

	function duration($Medium) {
		return $Medium->objects['ffmpeg_movie']->getDuration();
	}

	function track($Medium) {
		return $Medium->objects['ffmpeg_movie']->getTrackNumber();
	}

	function samplingRate($Medium) {
		return $Medium->objects['ffmpeg_movie']->getAudioSampleRate();
	}

	function bitRate($Medium) {
		return $Medium->objects['ffmpeg_movie']->getBitRate();
	}
}
?>