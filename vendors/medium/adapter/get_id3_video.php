<?php
/**
 * GetId3 Video Medium Adapter File
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
 * GetId3 Video Medium Adapter Class
 *
 * @package    media
 * @subpackage media.libs.medium.adapter
 * @link       http://getid3.sourceforge.net/
 */
class GetId3VideoMediumAdapter extends MediumAdapter {
	var $require = array(
					'mimeTypes' => array(
								'video/matroska',
								'video/ms-wmv',
								'video/ms-asf',
								'video/ms-video',
								'video/mpeg',
								'video/quicktime',
								'video/avi',
								'video/mp4',
								'video/flv',
								'video/real-video',
								'video/vnd.rn-realvideo',
								'video/pn-realvideo',
								'video/pn-multirate-realvideo',
								'video/nsv',
								/* Will not be used since video Medium can't have application/... mime type */
								'application/shockwave-flash',
								),
					'imports' => array(
							array('type' => 'Vendor', 'name'=> 'getID3', 'file' => 'getid3/getid3.php')
							),
					);

	function initialize(&$Medium) {
		if (isset($Medium->objects['getID3'])) {
			return true;
		}

		if (!isset($Medium->file)) {
			return false;
		}

		$Object = new getID3();
		$Object->analyze($Medium->file);

		if (isset($Object->info['error'])) {
			return false;
		}

		$Medium->objects['getID3'] =& $Object;

		return true;
	}

	function title(&$Medium) {
		if (isset($Medium->objects['getID3']->info['comments']['title'][0])) {
			return $Medium->objects['getID3']->info['comments']['title'][0];
		}
		return false;
	}

	function year(&$Medium) {
		if (isset($Medium->objects['getID3']->info['comments']['year'][0])) {
			return $Medium->objects['getID3']->info['comments']['year'][0];
		}
		return false;
	}

	function duration(&$Medium) {
		if (isset($Medium->objects['getID3']->info['playtime_seconds'])) {
			return $Medium->objects['getID3']->info['playtime_seconds'];
		}
		return false;
	}

	function width(&$Medium) {
		if (isset($Medium->objects['getID3']->info['video']['resolution_x'])) {
			return $Medium->objects['getID3']->info['video']['resolution_x'];
		}
		return false;
	}

	function height(&$Medium) {
		if (isset($Medium->objects['getID3']->info['video']['resolution_y'])) {
			return false;
		}
		return $Medium->objects['getID3']->info['video']['resolution_y'];
	}

	function bitrate(&$Medium) {
		if (isset($Medium->objects['getID3']->info['bitrate'])) {
			return $Medium->objects['getID3']->info['bitrate'];
		}
		return false;
	}
}
?>
