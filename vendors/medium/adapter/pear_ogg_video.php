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
							'imports' => array(array('type' => 'Vendor','name'=> 'File_Ogg','file' => 'File/Ogg.php')),
							);

	function initialize(&$Medium) {
		if (isset($Medium->objects['File_Ogg_Theora'])) {
			return true;
		}

		if (!isset($Medium->file)) {
			return false;
		}

		$Object = new File_Ogg($Medium->file);

		if (!$Object = $Object->getStream('file_ogg_theora')) {
			return false;
		}

		$Medium->objects['File_Ogg_Theora'] = $Object;

		return true;
	}

	function duration(&$Medium) {
		return $Medium->objects['File_Ogg_Theora']->getLength();
	}

	function width(&$Medium) {
		$header = $Medium->objects['File_Ogg_Theora']->getHeader();
		return $header['FMBW'];
	}

	function height(&$Medium) {
		$header = $Medium->objects['File_Ogg_Theora']->getHeader();
		return $header['FMBH'];
	}

	function frameRate(&$Medium) {
		$header = $Medium->objects['File_Ogg_Theora']->getHeader();
		return $header['FRD'] == 0 ? 0 : $header['FRN'] / $header['FRD'];
	}

	function quality(&$Medium) {
		//a quality between 1 (low) and 10 (high)
		$header = $Medium->objects['File_Ogg_Theora']->getHeader();
		$rate = $this->frameRate($Medium);
		$quality = $header['QUAL'];

		if($quality > 9) {
			$quality = 5;
		} elseif($rate < 2) {
			$quality = 0;
		} else {
			/* Normalized between 1 and 5 where min = 8000 and max = 192.000 */
			$quality = round((($rate - 1) / (10 - 1)) * (5 * 1) + 1,0);
		}

		return $quality;
	}
}
?>