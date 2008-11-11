<?php
/**
 * Ff Mpeg Video Medium Adapter File
 *
 * Copyright (c) 2007-2008 David Persson
 *
 * Distributed under the terms of the MIT License.
 * Redistributions of files must retain the above copyright notice.
 *
 * PHP version 5
 * CakePHP version 1.2
 *
 * @package    media
 * @subpackage media.libs.medium.adapter
 * @author     David Persson <davidpersson@qeweurope.org>
 * @copyright  2007-2008 David Persson <davidpersson@qeweurope.org>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/media
 */
/**
 * Ff Mpeg Video Medium Adapter Class
 *
 * @package    media
 * @subpackage media.libs.medium.adapter
 * @link       http://ffmpeg.mplayerhq.hu/
 */
class FfMpegVideoMediumAdapter extends MediumAdapter {

	var $require = array(
							'mimeTypes' => array(
											'video/mpeg',
											'video/mswmv',
											'video/msasf',
											'video/msvideo',
											'video/quicktime',
											'video/flv',
											),
							'extensions' => array('ffmpeg', 'gd'),
							);

	function initialize(&$Medium) {
		if (isset($Medium->objects['ffmpeg_movie'])) {
			return true;
		}

		if (!isset($Medium->file)) {
			return false;
		}

		$Medium->objects['ffmpeg_movie'] = new ffmpeg_movie($Medium->file);

		return true;
	}

	function convert(&$Medium, $mimeType) {
		if(Medium::name(null, $mimeType) === 'Image') {
			$randomFrame = rand(1, $Medium->objects['ffmpeg_movie']->getFrameCount());
			$resource = $Medium->objects['ffmpeg_movie']->getFrame($randomFrame)->toGDImage();

			if (!is_resource($resource)) {
				return false;
			}

			$Image = Medium::factory(array('gd' => $resource), 'image/gd');
			return $Image->convert($mimeType);
		}
		return false;
	}

	function duration(&$Medium) {
		return $Medium->objects['ffmpeg_movie']->getDuration();
	}

	function width(&$Medium) {
		return $Medium->objects['ffmpeg_movie']->getFrameWidth();
	}

	function height(&$Medium) {
		return $Medium->objects['ffmpeg_movie']->getFrameHeight();
	}
}
?>