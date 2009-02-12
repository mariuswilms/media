<?php
/**
 * Pear Ogg Audio Medium Adapter File
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
 * @author     David Persson <davidpersson@qeweurope.org>
 * @copyright  2007-2009 David Persson <davidpersson@qeweurope.org>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/media
 */
/**
 * Pear Ogg Audio Medium Adapter Class
 *
 * @package    media
 * @subpackage media.libs.medium.adapter
 * @link       http://pear.php.net/package/File_Ogg
 */
class PearOggAudioMediumAdapter extends MediumAdapter {
	var $require = array(
							'mimeTypes' => array('audio/ogg'),
							'imports' => array(array('type' => 'Vendor','name'=> 'File_Ogg','file' => 'File/Ogg.php')),
							);

	function initialize(&$Medium) {
		if (isset($Medium->objects['File_Ogg_Vorbis'])) {
			return true;
		}

		if (!isset($Medium->file)) {
			return false;
		}

		$Object = File_Ogg($Medium->file);

		if (!$Object = $Object->getStream('file_ogg_vorbis')) {
			return false;
		}

		$Medium->objects['File_Ogg_Vorbis'] =& $Object;

		return true;
	}

	function artist(&$Medium) {
		return $Medium->objects['File_Ogg_Vorbis']->getArtist();
	}

	function title(&$Medium) {
		return $Medium->objects['File_Ogg_Vorbis']->getTitle();
	}

	function album(&$Medium) {
		return $Medium->objects['File_Ogg_Vorbis']->getAlbum();
	}

	function year(&$Medium) {
		return strftime('Y',strtotime($Medium->objects['File_Ogg_Vorbis']->getDate(),time()));
	}

	function duration(&$Medium) {
		return $Medium->objects['File_Ogg_Vorbis']->getLength();
	}

	function track(&$Medium) {
		return $Medium->objects['File_Ogg_Vorbis']->getTrackNumber();
	}

	function samplingRate(&$Medium) {
		return $Medium->objects['File_Ogg_Vorbis']->getSampleRate();
	}
}
?>