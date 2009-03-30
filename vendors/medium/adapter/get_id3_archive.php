<?php
/**
 * GetId3 Archive Medium Adapter File
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
 * GetId3 Archive Medium Adapter Class
 *
 * @package    media
 * @subpackage media.libs.medium.adapter
 * @link       http://getid3.sourceforge.net/
 */
class GetId3ArchiveMediumAdapter extends MediumAdapter {
	var $require = array(
					'mimeTypes' => array(
								'application/zip',
								'application/gzip',
								'application/bzip',
								'application/tar',
								),
					'imports'  => array(array('type' => 'Vendor', 'name' => 'getID3', 'file' => 'getid3/getid3.php')),
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

	function files(&$Medium) {
		if (!isset($Medium->objects['getID3']->info['fileformat'])) {
			return false;
		}
		$format = $Medium->objects['getID3']->info['fileformat'];

		if (!isset($Medium->objects['getID3']->info[$format]['files'])) {
			return false;
		}
		return array_keys($Medium->objects['getID3']->info[$format]['files']);
	}

	function compression(&$Medium) {
		if (!isset($Medium->objects['getID3']->info['fileformat'])) {
			return false;
		}
		$format = $Medium->objects['getID3']->info['fileformat'];

		if (!isset($Medium->objects['getID3']->info[$format]['files'])) {
			return false;
		}
		if (!isset($Medium->objects['getID3']->info['filesize'])) {
			return false;
		}
		$uncompressed = array_sum($Medium->objects['getID3']->info[$format]['files']);
		return $Medium->objects['getID3']->info['filesize'] / $uncompressed * 100;
	}
}
?>
