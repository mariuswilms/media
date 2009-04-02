<?php
/**
 * Pear Ogg Video Medium Adapter File
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
 * Pear Ogg Video Medium Adapter Class
 *
 * @package    media
 * @subpackage media.libs.medium.adapter
 * @link       http://pear.php.net/package/File_Ogg
 */
class PearOggVideoMediumAdapter extends MediumAdapter {
	var $require = array(
							'mimeTypes' => array('video/ogg'),
							'imports' => array(array('type' => 'Vendor', 'name' => 'File_Ogg', 'file' => 'File/Ogg.php')),
							);

	function initialize(&$Medium) {
		if (isset($Medium->objects['File_Ogg_Theora'])) {
			return true;
		}

		if (!isset($Medium->file)) {
			return false;
		}

		$Ogg = new File_Ogg($Medium->file);

		if (!$Ogg->hasStream(OGG_STREAM_THEORA)) {
			return false;
		}

		$stream = current($Ogg->listStreams(OGG_STREAM_THEORA));

		if (!$Object = $Ogg->getStream($stream)) {
			return false;
		}

		$Medium->objects['File_Ogg_Theora'] = $Object;

		return true;
	}

	function title(&$Medium) {
		$comments = $Medium->objects['File_Ogg_Theora']->getComments();

		if (isset($comments['TITLE'])) {
			return $comments['TITLE'];
		}
		return false;
	}

	function year(&$Medium) {
		$comments = $Medium->objects['File_Ogg_Theora']->getComments();

		if ($date = strtotime($comments['DATE'])) {
			return strftime('%Y', $date);
		}
		return false;
	}

	function duration(&$Medium) {
		return $Medium->objects['File_Ogg_Theora']->getLength();
	}

	function width(&$Medium) {
		$streams = $Medium->objects['File_Ogg_Theora']->listStreams(OGG_STREAM_THEORA);
		$stream  = $Medium->objects['File_Ogg_Theora']->getStream(current($streams));
		$header  = $stream->getHeader();
		return $header['PICW'];
	}

	function height(&$Medium) {
		$streams = $Medium->objects['File_Ogg_Theora']->listStreams(OGG_STREAM_THEORA);
		$stream  = $Medium->objects['File_Ogg_Theora']->getStream(current($streams));
		$header  = $stream->getHeader();
		return $header['PICH'];
	}

	function bitrate(&$Medium) {
		$header = $Medium->objects['File_Ogg_Theora']->getHeader();
		if (isset($header['NOMBR'])) {
			return $header['NOMBR'];
		}
		if ($duration = $this->duration($Medium)) {
			return filesize($Medium->file) / ($duration * 8);
		}
		return false;
	}
}
?>
