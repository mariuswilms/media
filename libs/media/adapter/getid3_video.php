<?php
/**
 * Getid3 Video Media Adapter File
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
 * Getid3 Video Media Adapter Class
 *
 * @package    media
 * @subpackage media.libs.media.adapter
 * @link       http://getid3.sourceforge.net/
 */
class Getid3VideoMediaAdapter extends MediaAdapter {
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
			/* Will not be used since video Media can't have application/... MIME type */
			'application/shockwave-flash',
		),
		'imports' => array(
			array('type' => 'Vendor', 'name'=> 'getID3', 'file' => 'getid3/getid3.php')
	));

	function initialize($Media) {
		if (isset($Media->objects['getID3'])) {
			return true;
		}

		if (!isset($Media->file)) {
			return false;
		}

		$Object = new getID3();
		$Object->analyze($Media->file);

		if (isset($Object->info['error'])) {
			return false;
		}

		$Media->objects['getID3'] =& $Object;
		return true;
	}

	function title($Media) {
		if (isset($Media->objects['getID3']->info['comments']['title'][0])) {
			return $Media->objects['getID3']->info['comments']['title'][0];
		}
	}

	function year($Media) {
		foreach (array('year', 'date', 'creation_date') as $field) {
			if (!isset($Media->objects['getID3']->info['comments'][$field][0])) {
				continue;
			}
			$date = $Media->objects['getID3']->info['comments'][$field][0];

			if ($field !== 'year') {
				$date = strftime('%Y', strtotime($date));
			}
			if ($date) {
				return $date;
			}
		}
	}

	function duration($Media) {
		if (isset($Media->objects['getID3']->info['playtime_seconds'])) {
			return $Media->objects['getID3']->info['playtime_seconds'];
		}
	}

	function width($Media) {
		if (isset($Media->objects['getID3']->info['video']['resolution_x'])) {
			return $Media->objects['getID3']->info['video']['resolution_x'];
		}
	}

	function height($Media) {
		if (isset($Media->objects['getID3']->info['video']['resolution_y'])) {
			return $Media->objects['getID3']->info['video']['resolution_y'];
		}
	}

	function bitRate($Media) {
		if (isset($Media->objects['getID3']->info['ogg']['bitrate_nominal'])) {
			return $Media->objects['getID3']->info['ogg']['bitrate_nominal'];
		}
		if (isset($Media->objects['getID3']->info['bitrate'])) {
			return $Media->objects['getID3']->info['bitrate'];
		}
	}
}
?>