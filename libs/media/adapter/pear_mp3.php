<?php
/**
 * Pear Mp3 Media Adapter File
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
 * Pear Mp3 Media Adapter Class
 *
 * @package    media
 * @subpackage media.libs.media.adapter
 * @link       http://pear.php.net/package/MP3_Id
 */
class PearMp3MediaAdapter extends MediaAdapter {
	var $require = array(
		'mimeTypes' => array('audio/mpeg'),
		'imports' => array(
			array('type' => 'Vendor', 'name'=> 'MP3_Id', 'file' => 'MP3/Id.php')
	));

	function initialize($Media) {
		if (isset($Media->objects['MP3_Id'])) {
			return true;
		}

		if (!isset($Media->file)) {
			return false;
		}

		$Object = new MP3_Id();
		$Object->read($Media->file);
		$Object->study();

		$Media->objects['MP3_Id'] =& $Object;
		return true;
	}

	function artist($Media) {
		return $Media->objects['MP3_Id']->getTag('artists');
	}

	function title($Media) {
		return $Media->objects['MP3_Id']->getTag('name');
	}

	function album($Media) {
		return $Media->objects['MP3_Id']->getTag('album');
	}

	function year($Media) {
		return $Media->objects['MP3_Id']->getTag('year');
	}

	function duration($Media) {
		$duration = $Media->objects['MP3_Id']->getTag('lengths');

		if ($duration != -1) {
			return $duration;
		}
	}

	function track($Media) {
		return $Media->objects['MP3_Id']->getTag('track');
	}

	function samplingRate($Media) {
		return $Media->objects['MP3_Id']->getTag('frequency');
	}

	function bitRate($Media) {
		if ($bitrate = $Media->objects['MP3_Id']->getTag('bitrate')) {
			return $bitrate * 1000;
		}
	}
}
?>