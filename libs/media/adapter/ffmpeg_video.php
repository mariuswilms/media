<?php
/**
 * Ffmpeg Video Media Adapter File
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
 * Ffmpeg Video Media Adapter Class
 *
 * @package    media
 * @subpackage media.libs.media.adapter
 * @link       http://ffmpeg.mplayerhq.hu/
 */
class FfmpegVideoMediaAdapter extends MediaAdapter {
	var $require = array(
		'mimeTypes' => array(
			'video/mpeg',
			'video/mswmv',
			'video/msasf',
			'video/msvideo',
			'video/quicktime',
			'video/flv',
			'video/ogg',
		),
		'extensions' => array('ffmpeg', 'gd'),
	);

	function initialize($Media) {
		if (isset($Media->objects['ffmpeg_movie'])) {
			return true;
		}

		if (!isset($Media->file)) {
			return false;
		}

		$Media->objects['ffmpeg_movie'] = new ffmpeg_movie($Media->file);
		return true;
	}

	function convert($Media, $mimeType) {
		if (Media::name(null, $mimeType) === 'Image') {
			$randomFrame = rand(1, $Media->objects['ffmpeg_movie']->getFrameCount() - 1);
			$resource = $Media->objects['ffmpeg_movie']->getFrame($randomFrame)->toGDImage();

			if (!is_resource($resource)) {
				return false;
			}

			$Image = Media::factory(array('gd' => $resource), 'image/gd');
			return $Image->convert($mimeType);
		}
		return false;
	}

	function title($Media) {
		return $Media->objects['ffmpeg_movie']->getTitle();
	}

	function year($Media) {
		return $Media->objects['ffmpeg_movie']->getYear();
	}

	function duration($Media) {
		return $Media->objects['ffmpeg_movie']->getDuration();
	}

	function width($Media) {
		return $Media->objects['ffmpeg_movie']->getFrameWidth();
	}

	function height($Media) {
		return $Media->objects['ffmpeg_movie']->getFrameHeight();
	}

	function bitRate($Media) {
		return $Media->objects['ffmpeg_movie']->getBitRate();
	}
}
?>